(function () {
    var script = document.currentScript;
    if (!script) return;

    var trackingId = script.getAttribute('data-website-id');
    var apiHost = (script.getAttribute('data-api-host') || '').replace(/\/$/, '');
    if (!trackingId || !apiHost) return;

    var VISITOR_KEY = 'vm_visitor_id';
    var PAUSED_KEY = 'vm_paused_' + trackingId;

    function getVisitorId() {
        try {
            var existing = sessionStorage.getItem(VISITOR_KEY);
            if (existing) return existing;
            var id = crypto.randomUUID ? crypto.randomUUID() : (
                'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                    var r = Math.random() * 16 | 0;
                    var v = c === 'x' ? r : (r & 0x3 | 0x8);
                    return v.toString(16);
                })
            );
            sessionStorage.setItem(VISITOR_KEY, id);
            return id;
        } catch (e) {
            return null;
        }
    }

    function isPaused() {
        try { return sessionStorage.getItem(PAUSED_KEY) === '1'; } catch (e) { return false; }
    }

    function markPaused() {
        try { sessionStorage.setItem(PAUSED_KEY, '1'); } catch (e) {}
    }

    function inferDevice() {
        var ua = navigator.userAgent || '';
        if (/Mobile|Android.*Mobile|iPhone|iPod/i.test(ua)) return 'mobile';
        if (/iPad|Tablet|Android(?!.*Mobile)/i.test(ua)) return 'tablet';
        return 'desktop';
    }

    function parseUtm() {
        var params = new URLSearchParams(window.location.search);
        var utm = {};
        ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'].forEach(function (key) {
            var value = params.get(key);
            if (value) utm[key] = value;
        });
        return utm;
    }

    function isSameOriginCollect() {
        try {
            return new URL(apiHost).origin === window.location.origin;
        } catch (e) {
            return false;
        }
    }

    function sendPayload(endpoint, payload) {
        var body = JSON.stringify(payload);

        if (isSameOriginCollect() && navigator.sendBeacon) {
            var blob = new Blob([body], { type: 'application/json' });
            if (navigator.sendBeacon(apiHost + endpoint, blob)) return Promise.resolve({ status: 204 });
        }

        return fetch(apiHost + endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: body,
            keepalive: true,
            mode: 'cors',
            credentials: 'omit',
        }).catch(function () {});
    }

    var lastUrl = null;

    function track() {
        if (isPaused()) return;

        var url = window.location.href;
        if (url === lastUrl) return;
        lastUrl = url;

        var payload = {
            tracking_id: trackingId,
            url: url,
            referrer: document.referrer || null,
            device: inferDevice(),
            visitor_id: getVisitorId(),
        };

        Object.assign(payload, parseUtm());

        var result = sendPayload('/api/collect', payload);

        if (result && typeof result.then === 'function') {
            result.then(function (r) {
                if (r && r.status === 404) markPaused();
            });
        }
    }

    function patchHistory(method) {
        var original = history[method];
        history[method] = function () {
            var result = original.apply(this, arguments);
            track();
            return result;
        };
    }

    patchHistory('pushState');
    patchHistory('replaceState');
    window.addEventListener('popstate', track);
    window.addEventListener('hashchange', track);

    if (document.readyState === 'complete') {
        track();
    } else {
        window.addEventListener('load', track, { once: true });
    }

    // Public API for custom event tracking
    window.Vibemetrics = {
        track: function (eventName, props) {
            if (!eventName || typeof eventName !== 'string') return;
            if (isPaused()) return;

            sendPayload('/api/event', {
                tracking_id: trackingId,
                name: eventName.slice(0, 100),
                url: window.location.href,
                visitor_id: getVisitorId(),
                props: (props && typeof props === 'object') ? props : undefined,
            });
        },
    };
})();
