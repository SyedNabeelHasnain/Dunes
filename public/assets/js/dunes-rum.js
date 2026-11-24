/**
 * Dunes Discovery Tourism - Lightweight Real-User Monitoring (RUM) Core Web Vitals Beacon
 * Captures LCP, INP, CLS, FCP, and TTFB via native browser PerformanceObserver APIs
 * and transmits strictly when user analytics consent is active (or strict gating disabled).
 */
(function () {
    'use strict';

    if (typeof window === 'undefined' || !('PerformanceObserver' in window) || navigator.webdriver) {
        return;
    }

    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    var pagePath = window.location.pathname || '/';
    var locale = document.documentElement.lang || 'en';
    var deviceType = window.innerWidth < 768 ? 'mobile' : (window.innerWidth < 1024 ? 'tablet' : 'desktop');
    var conn = navigator.connection && navigator.connection.effectiveType ? navigator.connection.effectiveType : null;
    var metrics = {};
    var sent = {};

    function canReport() {
        if (window.DunesConsent && typeof window.DunesConsent.has === 'function') {
            return window.DunesConsent.has('analytics');
        }
        return true;
    }

    function sendMetric(name, value) {
        if (!canReport() || sent[name] || typeof value !== 'number' || !isFinite(value) || value < 0) {
            return;
        }
        sent[name] = true;

        var navEntry = performance.getEntriesByType && performance.getEntriesByType('navigation')[0];
        var payload = JSON.stringify({
            page_path: pagePath,
            locale: locale,
            device_type: deviceType,
            metric_name: name,
            metric_value: name === 'CLS' ? Number(value.toFixed(3)) : Math.round(value),
            navigation_type: navEntry ? navEntry.type : 'navigate',
            connection_type: conn
        });

        try {
            if (navigator.sendBeacon) {
                var blob = new Blob([payload], { type: 'application/json' });
                navigator.sendBeacon('/api/v1/rum/vitals', blob);
                return;
            }
            fetch('/api/v1/rum/vitals', {
                method: 'POST',
                keepalive: true,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: payload
            }).catch(function () {});
        } catch (e) {}
    }

    // TTFB & FCP
    try {
        var navEntries = performance.getEntriesByType && performance.getEntriesByType('navigation');
        if (navEntries && navEntries[0] && navEntries[0].responseStart > 0) {
            metrics.TTFB = navEntries[0].responseStart;
        }
        new PerformanceObserver(function (list) {
            list.getEntries().forEach(function (entry) {
                if (entry.name === 'first-contentful-paint') {
                    metrics.FCP = entry.startTime;
                }
            });
        }).observe({ type: 'paint', buffered: true });
    } catch (e) {}

    // LCP
    try {
        new PerformanceObserver(function (list) {
            var entries = list.getEntries();
            if (entries.length) {
                metrics.LCP = entries[entries.length - 1].startTime;
            }
        }).observe({ type: 'largest-contentful-paint', buffered: true });
    } catch (e) {}

    // CLS (Cumulative Layout Shift excluding user-initiated transitions)
    try {
        var clsValue = 0;
        new PerformanceObserver(function (list) {
            list.getEntries().forEach(function (entry) {
                if (!entry.hadRecentInput) {
                    clsValue += entry.value;
                    metrics.CLS = clsValue;
                }
            });
        }).observe({ type: 'layout-shift', buffered: true });
    } catch (e) {}

    // INP approximation (max interaction duration >= 16ms)
    try {
        var maxInp = 0;
        new PerformanceObserver(function (list) {
            list.getEntries().forEach(function (entry) {
                if (entry.interactionId && entry.duration > maxInp) {
                    maxInp = entry.duration;
                    metrics.INP = maxInp;
                }
            });
        }).observe({ type: 'event', durationThreshold: 16, buffered: true });
    } catch (e) {}

    function flushAll() {
        Object.keys(metrics).forEach(function (k) {
            sendMetric(k, metrics[k]);
        });
    }

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'hidden') {
            flushAll();
        }
    });
    window.addEventListener('pagehide', flushAll);
})();
