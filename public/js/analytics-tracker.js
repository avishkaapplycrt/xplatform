// public/js/analytics-tracker.js
(function() {
    'use strict';
    
    try {
        const CONFIG = {
            ENDPOINT: window.__analyticsEndpoint || 'https://xplatforms.ai/api/collect',
            SITE_ID: window.__analyticsSiteId,
            DEBUG: true
        };
        
        if (!CONFIG.SITE_ID) {
            console.error('[Analytics] Site ID not configured');
            return;
        }
        
        console.log('[Analytics] Tracker loaded, Site ID:', CONFIG.SITE_ID);
        
        function getSessionId() {
            let sid = sessionStorage.getItem('__analytics_sid');
            if (!sid) {
                sid = 's_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
                sessionStorage.setItem('__analytics_sid', sid);
            }
            return sid;
        }
        
        function getVisitorId() {
            const key = '__analytics_vid_' + new Date().toISOString().split('T')[0];
            let vid = localStorage.getItem(key);
            if (!vid) {
                Object.keys(localStorage).forEach(k => {
                    if (k.startsWith('__analytics_vid_')) localStorage.removeItem(k);
                });
                vid = 'v_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
                localStorage.setItem(key, vid);
            }
            return vid;
        }
        
        function getDeviceType() {
            const width = window.innerWidth;
            if (/Mobi|Android/i.test(navigator.userAgent)) return 'mobile';
            if (/Tablet|iPad/i.test(navigator.userAgent)) return 'tablet';
            if (width <= 768) return 'mobile';
            if (width <= 1024) return 'tablet';
            return 'desktop';
        }
        
        function getBrowserInfo() {
            const ua = navigator.userAgent;
            let browser = 'Unknown', version = '';
            
            if (ua.indexOf('Firefox') > -1) {
                browser = 'Firefox';
                version = ua.match(/Firefox\/(\d+\.\d+)/)?.[1] || '';
            } else if (ua.indexOf('Chrome') > -1) {
                browser = 'Chrome';
                version = ua.match(/Chrome\/(\d+\.\d+)/)?.[1] || '';
            } else if (ua.indexOf('Safari') > -1) {
                browser = 'Safari';
                version = ua.match(/Version\/(\d+\.\d+)/)?.[1] || '';
            } else if (ua.indexOf('Edge') > -1) {
                browser = 'Edge';
                version = ua.match(/Edge\/(\d+\.\d+)/)?.[1] || '';
            }
            
            return { browser, version };
        }
        
        function getOSInfo() {
            const ua = navigator.userAgent;
            let os = 'Unknown', version = '';
            
            if (/Windows NT 10/.test(ua)) { os = 'Windows'; version = '10'; }
            else if (/Mac OS X/.test(ua)) { os = 'macOS'; version = ''; }
            else if (/Linux/.test(ua)) { os = 'Linux'; }
            else if (/Android/.test(ua)) { os = 'Android'; version = ua.match(/Android (\d+\.\d+)/)?.[1] || ''; }
            else if (/iPhone|iPad|iPod/.test(ua)) { os = 'iOS'; version = ua.match(/OS (\d+_\d+)/)?.[1]?.replace('_', '.') || ''; }
            
            return { os, version };
        }
        
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
        
        function getReferrerDomain() {
            if (!document.referrer) return null;
            try {
                return new URL(document.referrer).hostname;
            } catch (e) {
                return null;
            }
        }
        
        // Fetch location from IP API (client-side)
        async function fetchLocation() {
            try {
                const response = await fetch('https://ipapi.co/json/', {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    console.log('[Analytics] Location detected:', data.country_code, data.country_name);
                    return {
                        country: data.country_code || null,
                        country_name: data.country_name || null,
                        city: data.city || null,
                        region: data.region || null
                    };
                }
            } catch (e) {
                console.warn('[Analytics] Location fetch failed:', e);
            }
            
            // Fallback: try ip-api.com
            try {
                const response = await fetch('http://ip-api.com/json/?fields=status,country,countryCode,regionName,city', {
                    method: 'GET'
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.status === 'success') {
                        console.log('[Analytics] Location detected (fallback):', data.countryCode, data.country);
                        return {
                            country: data.countryCode || null,
                            country_name: data.country || null,
                            city: data.city || null,
                            region: data.regionName || null
                        };
                    }
                }
            } catch (e) {
                console.warn('[Analytics] Location fallback failed:', e);
            }
            
            return { country: null, country_name: null, city: null, region: null };
        }
        
        async function sendEvent(eventType, extraData) {
            const browser = getBrowserInfo();
            const os = getOSInfo();
            const utm = getUtmParams();
            
            // Get location data
            const location = await fetchLocation();
            
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
                country: location.country,
                country_name: location.country_name,
                city: location.city,
                region: location.region,
                device_type: getDeviceType(),
                browser: browser.browser,
                browser_version: browser.version,
                os: os.os,
                os_version: os.version,
                screen_width: window.screen.width,
                screen_height: window.screen.height,
                language: navigator.language,
                timestamp: Date.now()
            };
            
            if (extraData) {
                Object.assign(payload, extraData);
            }
            
            console.log('[Analytics] Sending payload:', payload);
            
            fetch(CONFIG.ENDPOINT, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload),
                keepalive: true
            }).then(response => {
                console.log('[Analytics] Response status:', response.status);
                if (!response.ok) {
                    console.error('[Analytics] Response not OK:', response.statusText);
                }
                return response.json();
            }).then(data => {
                console.log('[Analytics] Response data:', data);
            }).catch(err => {
                console.error('[Analytics] Send failed:', err);
            });
        }
        
        async function init() {
            console.log('[Analytics] Initializing...');
            await sendEvent('pageview');
        }
        
        // Run immediately
        init();
        
        // Also run on DOM ready if not already loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                console.log('[Analytics] DOM ready, sending another pageview');
                sendEvent('pageview');
            });
        }
        
        // Expose API
        window.Analytics = {
            track: function(eventName, data) {
                sendEvent('custom', Object.assign({ event_name: eventName }, data));
            }
        };
        
    } catch (e) {
        console.error('[Analytics] Tracker initialization failed:', e);
    }
})();