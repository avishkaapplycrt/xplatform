// public/js/analytics-tracker.js
// Minified version to be served via CDN or your domain

(function() {
    'use strict';
    
    const CONFIG = {
        ENDPOINT: window.__analyticsEndpoint || 'https://xplatforms.ai/api/collect',
        SITE_ID: window.__analyticsSiteId,
        DEBUG: window.__analyticsDebug || false
    };
    
    if (!CONFIG.SITE_ID) {
        console.error('[Analytics] Site ID not configured');
        return;
    }
    
    // Utility: Generate or retrieve session ID
    function getSessionId() {
        let sid = sessionStorage.getItem('__analytics_sid');
        if (!sid) {
            sid = 's_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
            sessionStorage.setItem('__analytics_sid', sid);
        }
        return sid;
    }
    
    // Utility: Generate or retrieve visitor ID (daily)
    function getVisitorId() {
        const key = '__analytics_vid_' + new Date().toISOString().split('T')[0];
        let vid = localStorage.getItem(key);
        if (!vid) {
            // Clear old visitor IDs
            Object.keys(localStorage).forEach(k => {
                if (k.startsWith('__analytics_vid_')) localStorage.removeItem(k);
            });
            vid = 'v_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
            localStorage.setItem(key, vid);
        }
        return vid;
    }
    
    // Utility: Parse UTM params
    function getUtmParams() {
        const params = new URLSearchParams(window.location.search);
        return {
            source: params.get('utm_source'),
            medium: params.get('utm_medium'),
            campaign: params.get('utm_campaign'),
            term: params.get('utm_term'),
            content: params.get('utm_content')
        };
    }
    
    // Utility: Get referrer domain
    function getReferrerDomain() {
        if (!document.referrer) return null;
        try {
            return new URL(document.referrer).hostname;
        } catch (e) {
            return null;
        }
    }
    
    // Utility: Get device type
    function getDeviceType() {
        const width = window.innerWidth;
        if (/Mobi|Android/i.test(navigator.userAgent)) return 'mobile';
        if (/Tablet|iPad/i.test(navigator.userAgent)) return 'tablet';
        if (width <= 768) return 'mobile';
        if (width <= 1024) return 'tablet';
        return 'desktop';
    }
    
    // Utility: Get browser info
    function getBrowserInfo() {
        const ua = navigator.userAgent;
        let browser = 'Unknown';
        let version = '';
        
        if (ua.indexOf('Firefox') > -1) {
            browser = 'Firefox';
            version = ua.match(/Firefox\/(\d+\.\d+)/)?.[1] || '';
        } else if (ua.indexOf('SamsungBrowser') > -1) {
            browser = 'Samsung Browser';
            version = ua.match(/SamsungBrowser\/(\d+\.\d+)/)?.[1] || '';
        } else if (ua.indexOf('Opera') > -1 || ua.indexOf('OPR') > -1) {
            browser = 'Opera';
            version = ua.match(/(?:Opera|OPR)\/(\d+\.\d+)/)?.[1] || '';
        } else if (ua.indexOf('Trident') > -1) {
            browser = 'Internet Explorer';
            version = ua.match(/rv:(\d+\.\d+)/)?.[1] || '';
        } else if (ua.indexOf('Edge') > -1 || ua.indexOf('Edg') > -1) {
            browser = 'Edge';
            version = ua.match(/(?:Edge|Edg)\/(\d+\.\d+)/)?.[1] || '';
        } else if (ua.indexOf('Chrome') > -1) {
            browser = 'Chrome';
            version = ua.match(/Chrome\/(\d+\.\d+)/)?.[1] || '';
        } else if (ua.indexOf('Safari') > -1) {
            browser = 'Safari';
            version = ua.match(/Version\/(\d+\.\d+)/)?.[1] || '';
        }
        
        return { browser, version };
    }
    
    // Utility: Get OS info
    function getOSInfo() {
        const ua = navigator.userAgent;
        let os = 'Unknown';
        let version = '';
        
        if (/Windows NT 10/.test(ua)) { os = 'Windows'; version = '10'; }
        else if (/Windows NT 6.3/.test(ua)) { os = 'Windows'; version = '8.1'; }
        else if (/Windows NT 6.2/.test(ua)) { os = 'Windows'; version = '8'; }
        else if (/Windows NT 6.1/.test(ua)) { os = 'Windows'; version = '7'; }
        else if (/Mac OS X/.test(ua)) { 
            os = 'macOS'; 
            version = ua.match(/Mac OS X (\d+[._]\d+)/)?.[1]?.replace('_', '.') || '';
        }
        else if (/iPhone|iPad|iPod/.test(ua)) { 
            os = 'iOS'; 
            version = ua.match(/OS (\d+_\d+)/)?.[1]?.replace('_', '.') || '';
        }
        else if (/Android/.test(ua)) { 
            os = 'Android'; 
            version = ua.match(/Android (\d+\.\d+)/)?.[1] || '';
        }
        else if (/Linux/.test(ua)) { os = 'Linux'; }
        
        return { os, version };
    }
    
    // Core: Send beacon
    function sendEvent(eventType, extraData = {}) {
        const browser = getBrowserInfo();
        const os = getOSInfo();
        const utm = getUtmParams();
        
        const payload = {
            site_id: CONFIG.SITE_ID,
            session_id: getSessionId(),
            visitor_id: getVisitorId(),
            event_type: eventType,
            url: window.location.href,
            path: window.location.pathname + window.location.search,
            title: document.title,
            referrer: document.referrer || null,
            referrer_domain: getReferrerDomain(),
            utm_source: utm.source,
            utm_medium: utm.medium,
            utm_campaign: utm.campaign,
            utm_term: utm.term,
            utm_content: utm.content,
            device_type: getDeviceType(),
            browser: browser.browser,
            browser_version: browser.version,
            os: os.os,
            os_version: os.version,
            screen_width: window.screen.width,
            screen_height: window.screen.height,
            language: navigator.language,
            timestamp: Date.now(),
            ...extraData
        };
        
        // Use Beacon API for reliable delivery on page unload
        if (navigator.sendBeacon && eventType === 'page_exit') {
            navigator.sendBeacon(CONFIG.ENDPOINT, JSON.stringify(payload));
        } else {
            fetch(CONFIG.ENDPOINT, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
                keepalive: true
            }).catch(err => {
                if (CONFIG.DEBUG) console.error('[Analytics] Send failed:', err);
            });
        }
    }
    
    // Track page view
    function trackPageview() {
        sendEvent('pageview');
        
        // Update session activity
        sessionStorage.setItem('__analytics_last_activity', Date.now());
    }
    
    // Track page exit
    function trackExit() {
        const startTime = parseInt(sessionStorage.getItem('__analytics_page_start') || Date.now());
        const duration = Date.now() - startTime;
        sendEvent('page_exit', { duration_ms: duration });
    }
    
    // Track engagement (scroll depth)
    let maxScrollDepth = 0;
    function trackScroll() {
        const scrollPercent = Math.round(
            ((window.scrollY + window.innerHeight) / document.documentElement.scrollHeight) * 100
        );
        if (scrollPercent > maxScrollDepth) {
            maxScrollDepth = scrollPercent;
            if (maxScrollDepth % 25 === 0) { // Track at 25%, 50%, 75%, 100%
                sendEvent('scroll', { scroll_depth: maxScrollDepth });
            }
        }
    }
    
    // Track performance
    function trackPerformance() {
        if (window.performance && performance.timing) {
            const timing = performance.timing;
            const loadTime = timing.loadEventEnd - timing.navigationStart;
            if (loadTime > 0) {
                sendEvent('performance', { load_time_ms: loadTime });
            }
        }
    }
    
    // Initialize
    function init() {
        sessionStorage.setItem('__analytics_page_start', Date.now());
        
        // Track initial pageview
        trackPageview();
        
        // Track performance after load
        if (document.readyState === 'complete') {
            trackPerformance();
        } else {
            window.addEventListener('load', trackPerformance);
        }
        
        // Scroll tracking (throttled)
        let scrollTimeout;
        window.addEventListener('scroll', () => {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(trackScroll, 250);
        }, { passive: true });
        
        // Exit tracking
        window.addEventListener('beforeunload', trackExit);
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden') trackExit();
        });
        
        // SPA navigation support (History API)
        let lastUrl = location.href;
        new MutationObserver(() => {
            const currentUrl = location.href;
            if (currentUrl !== lastUrl) {
                lastUrl = currentUrl;
                trackPageview();
            }
        }).observe(document, { subtree: true, childList: true });
        
        // Heartbeat (every 30s to keep session alive)
        setInterval(() => {
            sendEvent('heartbeat', { elapsed_ms: Date.now() - parseInt(sessionStorage.getItem('__analytics_page_start')) });
        }, 30000);
    }
    
    // Auto-init
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Expose API for manual events
    window.Analytics = {
        track: (eventName, data = {}) => sendEvent('custom', { event_name: eventName, ...data }),
        pageview: trackPageview,
        identify: (userId, traits = {}) => {
            localStorage.setItem('__analytics_user_id', userId);
            sendEvent('identify', { user_id: userId, traits });
        }
    };
})();