/**
 * Dunes Discovery Tourism — Real-time Session Recording & Behavioral Telemetry Engine
 * Powered by rrweb-record & HTML5 Canvas Heatmaps (Self-Hosted, MIT License)
 * Human-only verification, automated bot exclusion, traveler privacy masking, gzip transport.
 */
(function () {
    'use strict';

    // 1. Bot & Headless Crawler Guard
    if (navigator.webdriver || window.__nightmare || window._phantom || window.callPhantom) {
        return;
    }

    // Don't record inside admin CMS
    if (window.location.pathname.startsWith('/admin')) {
        return;
    }

    // Check if visitor has testing IP exclusion cookie
    if (document.cookie.indexOf('dunes_ip_ignored=1') !== -1) {
        return;
    }

    // 2. Resolve / Persist Session Token across tabs' multi-page journeys
    let sessionId = null;
    try {
        sessionId = sessionStorage.getItem('dunes_telemetry_sid');
        if (!sessionId) {
            sessionId = 'dunes_' + Date.now() + '_' + Math.random().toString(36).substring(2, 10);
            sessionStorage.setItem('dunes_telemetry_sid', sessionId);
        }
    } catch (e) {
        sessionId = 'dunes_' + Date.now();
    }

    // 3. State Management
    let eventBuffer = [];
    let isRecordingActive = false;
    let isLoadingRecorder = false;
    let initialSnapshotFlushed = false;
    let stopRecordingFn = null;
    let flushTimer = null;
    let maxScrollPercent = 0;
    let lastFlushedScrollPercent = 0;
    let lastClickTime = 0;

    function isRecordingEnabled() {
        return !window.DunesConfig || window.DunesConfig.recordings_enabled !== false;
    }

    function isHeatmapEnabled() {
        return !window.DunesConfig || window.DunesConfig.heatmaps_enabled !== false;
    }

    const BATCH_INTERVAL_MS = 6000;
    const MAX_BUFFER_SIZE = 40;
    const MAX_RETRY_BUFFER = 250;
    const BEACON_SAFE_LIMIT_BYTES = 48000; // Strictly under Chromium/Safari 64 KB sendBeacon/keepalive cap
    const ENDPOINT_RECORDING = '/api/telemetry/recordings/batch';
    const ENDPOINT_HEATMAP_CLICK = '/api/telemetry/heatmaps/click';
    const ENDPOINT_HEATMAP_SCROLL = '/api/telemetry/heatmaps/scroll';

    function getDeviceType() {
        const w = window.innerWidth;
        if (w < 768) return 'mobile';
        if (w < 1024) return 'tablet';
        return 'desktop';
    }

    function hasAnalyticsConsent() {
        if (window.DunesConsent && typeof window.DunesConsent.has === 'function') {
            return window.DunesConsent.has('analytics');
        }
        return false;
    }

    async function gzipJsonBase64(jsonString) {
        if (typeof CompressionStream === 'undefined' || typeof TextEncoder === 'undefined') {
            return null;
        }
        try {
            const bytes = new TextEncoder().encode(jsonString);
            const stream = new Blob([bytes]).stream().pipeThrough(new CompressionStream('gzip'));
            const buffer = await new Response(stream).arrayBuffer();
            const u8 = new Uint8Array(buffer);
            let binary = '';
            const chunkSize = 0x8000;
            for (let i = 0; i < u8.length; i += chunkSize) {
                binary += String.fromCharCode.apply(null, u8.subarray(i, i + chunkSize));
            }
            return btoa(binary);
        } catch (e) {
            return null;
        }
    }

    // Helper: Small telemetry payloads (heatmaps clicks/scrolls)
    function transmitTelemetry(url, payload, useBeacon) {
        if (!hasAnalyticsConsent()) {
            return;
        }
        const body = JSON.stringify(payload);
        if (useBeacon && navigator.sendBeacon && body.length <= BEACON_SAFE_LIMIT_BYTES) {
            try {
                const blob = new Blob([body], { type: 'application/json' });
                if (navigator.sendBeacon(url, blob)) {
                    return;
                }
            } catch (err) {}
        }

        try {
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: body,
                keepalive: !!(useBeacon && body.length <= BEACON_SAFE_LIMIT_BYTES)
            }).catch(function () {});
        } catch (e) {}
    }

    // Dispatch a batch of events chunked safely when called during unload
    function sendBeaconChunked(eventsToSend, isFinal) {
        const chunks = [];
        let current = [];

        for (let i = 0; i < eventsToSend.length; i++) {
            const evt = eventsToSend[i];
            current.push(evt);
            const estSize = JSON.stringify(current).length;
            if (estSize >= BEACON_SAFE_LIMIT_BYTES && current.length > 1) {
                current.pop();
                chunks.push(current);
                current = [evt];
            }
        }
        if (current.length > 0) {
            chunks.push(current);
        }

        for (let c = 0; c < chunks.length; c++) {
            const chunkIsFinal = isFinal && (c === chunks.length - 1);
            const payload = {
                session_id: sessionId,
                events: chunks[c],
                current_page: window.location.pathname,
                is_final: !!chunkIsFinal,
                is_webdriver: !!navigator.webdriver,
                device_type: getDeviceType()
            };
            const body = JSON.stringify(payload);

            // sendBeacon / keepalive ONLY if strictly below 64 KB browser limit
            if (body.length <= BEACON_SAFE_LIMIT_BYTES && navigator.sendBeacon) {
                try {
                    const blob = new Blob([body], { type: 'application/json' });
                    if (navigator.sendBeacon(ENDPOINT_RECORDING, blob)) {
                        continue;
                    }
                } catch (err) {}
            }

            try {
                fetch(ENDPOINT_RECORDING, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: body,
                    keepalive: body.length <= BEACON_SAFE_LIMIT_BYTES
                }).catch(function () {});
            } catch (e) {}
        }
    }

    // Flush recording buffer safely (never drop >64KB snapshots via keepalive)
    async function flushBuffer(isFinal) {
        if (!hasAnalyticsConsent() || eventBuffer.length === 0) {
            return;
        }

        const eventsToSend = eventBuffer.slice();
        eventBuffer = [];

        if (isFinal) {
            sendBeaconChunked(eventsToSend, true);
            return;
        }

        const baseMeta = {
            session_id: sessionId,
            current_page: window.location.pathname,
            is_final: false,
            is_webdriver: !!navigator.webdriver,
            device_type: getDeviceType()
        };

        try {
            const rawEventsJson = JSON.stringify(eventsToSend);
            let requestBody = null;

            // Gzip-compress snapshots/batches > 1.5 KB to cut mobile bandwidth & server upload by ~15x
            if (rawEventsJson.length > 1500) {
                const b64 = await gzipJsonBase64(rawEventsJson);
                if (b64) {
                    requestBody = JSON.stringify(Object.assign({}, baseMeta, {
                        events_gz_b64: b64
                    }));
                }
            }

            if (!requestBody) {
                requestBody = JSON.stringify(Object.assign({}, baseMeta, {
                    events: eventsToSend
                }));
            }

            const res = await fetch(ENDPOINT_RECORDING, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: requestBody
            });

            if (!res.ok) {
                throw new Error('HTTP ' + res.status);
            }
        } catch (err) {
            // Re-queue failed events concisely so momentary blips don't erase initial FullSnapshot
            if (eventBuffer.length + eventsToSend.length <= MAX_RETRY_BUFFER) {
                eventBuffer = eventsToSend.concat(eventBuffer);
            }
        }
    }

    // Heatmap: Track Click
    function handleHeatmapClick(e) {
        const now = Date.now();
        if (now - lastClickTime < 300) return;
        lastClickTime = now;

        const docW = Math.max(document.documentElement.scrollWidth, window.innerWidth);
        const docH = Math.max(document.documentElement.scrollHeight, window.innerHeight);

        const xRatio = docW > 0 ? Number((e.pageX / docW).toFixed(4)) : 0;
        const yRatio = docH > 0 ? Number((e.pageY / docH).toFixed(4)) : 0;

        const target = e.target;
        const targetTag = target ? (target.tagName || '').toUpperCase() : '';
        const targetText = target ? (target.innerText || target.value || target.getAttribute('aria-label') || '').slice(0, 60).trim() : '';

        transmitTelemetry(ENDPOINT_HEATMAP_CLICK, {
            session_id: sessionId,
            page_path: window.location.pathname,
            device_type: getDeviceType(),
            x_ratio: xRatio,
            y_ratio: yRatio,
            click_x: Math.round(e.pageX),
            click_y: Math.round(e.pageY),
            viewport_width: window.innerWidth,
            viewport_height: window.innerHeight,
            target_tag: targetTag,
            target_text: targetText
        }, false);
    }

    // Heatmap: Track Scroll
    function updateMaxScroll() {
        const docH = Math.max(document.documentElement.scrollHeight, window.innerHeight);
        const currentScrollBottom = window.scrollY + window.innerHeight;
        const currentPercent = docH > 0 ? Math.min(100, Math.round((currentScrollBottom / docH) * 100)) : 100;
        if (currentPercent > maxScrollPercent) {
            maxScrollPercent = currentPercent;
        }
    }

    function flushScrollDepth() {
        if (!isHeatmapEnabled() || maxScrollPercent <= 0 || maxScrollPercent <= lastFlushedScrollPercent) return;
        lastFlushedScrollPercent = maxScrollPercent;

        transmitTelemetry(ENDPOINT_HEATMAP_SCROLL, {
            session_id: sessionId,
            page_path: window.location.pathname,
            device_type: getDeviceType(),
            max_scroll_percent: maxScrollPercent,
            viewport_height: window.innerHeight,
            page_height: Math.max(document.documentElement.scrollHeight, window.innerHeight)
        }, true);
    }

    // 4. Initialize rrweb recorder once vendor script loads
    function startRrwebRecording() {
        if (isRecordingActive) return;

        const recordFn = window.rrwebRecord || (window.rrweb && window.rrweb.record);
        if (typeof recordFn !== 'function') return;

        try {
            stopRecordingFn = recordFn({
                emit: function (event) {
                    eventBuffer.push(event);

                    // Flush initial FullSnapshot + Meta right away over regular fetch so fast navigation never drops it
                    if (event.type === 2 && !initialSnapshotFlushed) {
                        initialSnapshotFlushed = true;
                        setTimeout(function () {
                            flushBuffer(false);
                        }, 80);
                    } else if (eventBuffer.length >= MAX_BUFFER_SIZE) {
                        flushBuffer(false);
                    }
                },
                sampling: {
                    mousemove: true,
                    mouseInteraction: true,
                    scroll: 150,
                    input: 'last'
                },
                slimDOMOptions: 'all',
                inlineStylesheet: true,
                maskAllInputs: true,
                maskInputOptions: {
                    password: true,
                    color: false,
                    date: false,
                    'datetime-local': false,
                    email: true,
                    month: false,
                    number: true,
                    range: false,
                    search: false,
                    tel: true,
                    text: true,
                    time: false,
                    url: false,
                    week: false,
                    textarea: true,
                    select: false
                },
                blockClass: 'rr-block',
                maskTextClass: 'rr-mask',
                inlineImages: false,
                collectFonts: false,
                recordCanvas: false
            });

            isRecordingActive = true;

            if (!flushTimer) {
                flushTimer = setInterval(function () {
                    flushBuffer(false);
                }, BATCH_INTERVAL_MS);
            }
        } catch (err) {
            // Non-blocking telemetry protection
        }
    }

    function ensureRecorderLoadedAndStart() {
        if (isRecordingActive || !isRecordingEnabled() || !hasAnalyticsConsent()) {
            return;
        }

        if (window.rrwebRecord || (window.rrweb && window.rrweb.record)) {
            startRrwebRecording();
            return;
        }

        if (isLoadingRecorder) {
            return;
        }
        isLoadingRecorder = true;

        const script = document.createElement('script');
        script.src = '/assets/vendor/rrweb/rrweb-record.min.js';
        script.async = true;
        script.onload = function () {
            isLoadingRecorder = false;
            startRrwebRecording();
        };
        script.onerror = function () {
            isLoadingRecorder = false;
        };
        document.head.appendChild(script);
    }

    // 5. Bootstrap upon genuine Human interaction + Analytics Consent
    function onFirstHumanInteraction() {
        if (!hasAnalyticsConsent()) {
            return;
        }
        ['pointerdown', 'mousemove', 'keydown', 'scroll', 'touchstart'].forEach(function (ev) {
            window.removeEventListener(ev, onFirstHumanInteraction, { passive: true });
        });

        updateMaxScroll();
        ensureRecorderLoadedAndStart();
    }

    // Preload rrweb-record on idle when consent is already granted so first human gesture starts instantly
    if (isRecordingEnabled() && hasAnalyticsConsent()) {
        const preloader = document.createElement('link');
        preloader.rel = 'preload';
        preloader.as = 'script';
        preloader.href = '/assets/vendor/rrweb/rrweb-record.min.js';
        document.head.appendChild(preloader);
    }

    window.addEventListener('dunes:consent-updated', function (e) {
        if (e && e.detail && e.detail.analytics) {
            onFirstHumanInteraction();
        }
    });

    ['pointerdown', 'mousemove', 'keydown', 'scroll', 'touchstart'].forEach(function (ev) {
        window.addEventListener(ev, onFirstHumanInteraction, { once: true, passive: true });
    });

    if (isHeatmapEnabled()) {
        window.addEventListener('click', handleHeatmapClick, { passive: true });
        window.addEventListener('scroll', function () {
            updateMaxScroll();
        }, { passive: true });
    }

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'hidden') {
            flushBuffer(false);
            flushScrollDepth();
        }
    });

    window.addEventListener('pagehide', function (e) {
        flushBuffer(!e || !e.persisted);
        flushScrollDepth();
    });

    window.addEventListener('beforeunload', function () {
        flushBuffer(true);
        flushScrollDepth();
    });
})();
