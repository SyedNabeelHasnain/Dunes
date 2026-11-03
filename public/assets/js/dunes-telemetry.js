/**
 * Dunes Discovery Tourism — Real-time Session Recording & Behavioral Telemetry Engine
 * Powered by rrweb-record & HTML5 Canvas Heatmaps (Self-Hosted, MIT License)
 * Human-only verification, automated bot exclusion, traveler privacy masking.
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

    // 2. Resolve / Persist Session Token
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
    let stopRecordingFn = null;
    let flushTimer = null;
    let maxScrollPercent = 0;
    let lastClickTime = 0;

    const BATCH_INTERVAL_MS = 8000;
    const MAX_BUFFER_SIZE = 40;
    const ENDPOINT_RECORDING = '/api/telemetry/recordings/batch';
    const ENDPOINT_HEATMAP_CLICK = '/api/telemetry/heatmaps/click';
    const ENDPOINT_HEATMAP_SCROLL = '/api/telemetry/heatmaps/scroll';

    // Helper: Determine device type
    function getDeviceType() {
        const w = window.innerWidth;
        if (w < 768) return 'mobile';
        if (w < 1024) return 'tablet';
        return 'desktop';
    }

    // Helper: Safe network transmission with Beacon / keepalive fallback
    function transmitTelemetry(url, payload, useBeacon) {
        const body = JSON.stringify(payload);
        if (useBeacon && navigator.sendBeacon) {
            try {
                const blob = new Blob([body], { type: 'application/json' });
                if (navigator.sendBeacon(url, blob)) {
                    return;
                }
            } catch (err) {
                // Fallback to fetch
            }
        }

        try {
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: body,
                keepalive: true
            }).catch(function () {});
        } catch (e) {}
    }

    // Flush recording buffer
    function flushBuffer(isFinal) {
        if (eventBuffer.length === 0) {
            return;
        }

        const eventsToSend = eventBuffer.slice();
        eventBuffer = [];

        const payload = {
            session_id: sessionId,
            events: eventsToSend,
            current_page: window.location.pathname,
            is_final: !!isFinal,
            is_webdriver: !!navigator.webdriver,
            device_type: getDeviceType()
        };

        transmitTelemetry(ENDPOINT_RECORDING, payload, !!isFinal);
    }

    // Heatmap: Track Click
    function handleHeatmapClick(e) {
        const now = Date.now();
        if (now - lastClickTime < 300) return; // Debounce rapid spam
        lastClickTime = now;

        const docW = Math.max(document.documentElement.scrollWidth, window.innerWidth);
        const docH = Math.max(document.documentElement.scrollHeight, window.innerHeight);

        const xRatio = docW > 0 ? Number((e.pageX / docW).toFixed(4)) : 0;
        const yRatio = docH > 0 ? Number((e.pageY / docH).toFixed(4)) : 0;

        const target = e.target;
        const targetTag = target ? (target.tagName || '').toUpperCase() : '';
        const targetText = target ? (target.innerText || target.value || target.getAttribute('aria-label') || '').slice(0, 60).trim() : '';

        const payload = {
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
        };

        transmitTelemetry(ENDPOINT_HEATMAP_CLICK, payload, false);
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
        if (maxScrollPercent <= 0) return;

        const payload = {
            session_id: sessionId,
            page_path: window.location.pathname,
            device_type: getDeviceType(),
            max_scroll_percent: maxScrollPercent,
            viewport_height: window.innerHeight,
            page_height: Math.max(document.documentElement.scrollHeight, window.innerHeight)
        };

        transmitTelemetry(ENDPOINT_HEATMAP_SCROLL, payload, true);
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
                    if (eventBuffer.length >= MAX_BUFFER_SIZE) {
                        flushBuffer(false);
                    }
                },
                sampling: {
                    mousemove: true,
                    mouseInteraction: true,
                    scroll: 150,
                    input: 'last'
                },
                // Traveler Privacy Protocols:
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
                collectFonts: false
            });

            isRecordingActive = true;

            // Start periodic flush timer
            flushTimer = setInterval(function () {
                flushBuffer(false);
            }, BATCH_INTERVAL_MS);
        } catch (err) {
            // Non-blocking telemetry protection
        }
    }

    // 5. Bootstrap only upon genuine Human interaction
    function onFirstHumanInteraction() {
        window.removeEventListener('pointerdown', onFirstHumanInteraction, { passive: true });
        window.removeEventListener('keydown', onFirstHumanInteraction, { passive: true });
        window.removeEventListener('scroll', onFirstHumanInteraction, { passive: true });
        window.removeEventListener('touchstart', onFirstHumanInteraction, { passive: true });

        // Update scroll metrics immediately
        updateMaxScroll();

        // Dynamically load local self-hosted rrweb script if not already present
        if (!window.rrwebRecord && !(window.rrweb && window.rrweb.record)) {
            const script = document.createElement('script');
            script.src = '/assets/vendor/rrweb/rrweb-record.min.js';
            script.async = true;
            script.onload = function () {
                startRrwebRecording();
            };
            document.head.appendChild(script);
        } else {
            startRrwebRecording();
        }
    }

    // Listen for first genuine user interaction
    ['pointerdown', 'keydown', 'scroll', 'touchstart'].forEach(function (ev) {
        window.addEventListener(ev, onFirstHumanInteraction, { once: true, passive: true });
    });

    // Listen for heatmaps clicks & scroll
    window.addEventListener('click', handleHeatmapClick, { passive: true });
    window.addEventListener('scroll', function () {
        updateMaxScroll();
    }, { passive: true });

    // Flush on page exit / visibility hidden
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'hidden') {
            flushBuffer(true);
            flushScrollDepth();
        }
    });

    window.addEventListener('pagehide', function () {
        flushBuffer(true);
        flushScrollDepth();
    });

    window.addEventListener('beforeunload', function () {
        flushBuffer(true);
        flushScrollDepth();
    });

})();
