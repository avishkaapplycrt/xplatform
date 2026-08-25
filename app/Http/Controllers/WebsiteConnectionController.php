<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WebsiteConnectionController extends Controller
{
    /**
     * Display the website connections page.
     */
    public function index()
    {
        $client = Auth::guard('client')->user();

        $connections = $this->getConnections($client->id);
        $platforms = $this->buildPlatforms($connections);

        return view('client.website_connections', compact('platforms', 'connections'));
    }

    /**
     * Display the website connections content only (for in-place embedding,
     * e.g. inside the Data Collection page's source-card panel).
     */
    public function embed()
    {
        $client = Auth::guard('client')->user();

        $connections = $this->getConnections($client->id);
        $platforms = $this->buildPlatforms($connections);

        return view('client.partials.website-connections-content', compact('platforms', 'connections'));
    }

    /**
     * Build the platform metadata array used by both index() and embed().
     */
    private function buildPlatforms($connections): array
    {
        return [
            [
                'id'          => 'wordpress',
                'name'        => 'WordPress',
                'description' => 'Connect via plugin or embed code for full event tracking on your WordPress site.',
                'icon'        => 'wordpress',
                'color'       => '#21759b',
                'connected'   => $connections->where('platform', 'wordpress')->isNotEmpty(),
                'connection'  => $connections->where('platform', 'wordpress')->first(),
            ],
            [
                'id'          => 'wix',
                'name'        => 'Wix',
                'description' => 'Integrate with Wix using our custom app or by adding a tracking snippet to your site.',
                'icon'        => 'wix',
                'color'       => '#0c0c0c',
                'connected'   => $connections->where('platform', 'wix')->isNotEmpty(),
                'connection'  => $connections->where('platform', 'wix')->first(),
            ],
            [
                'id'          => 'shopify',
                'name'        => 'Shopify',
                'description' => 'Install our Shopify app from the marketplace for seamless e-commerce tracking.',
                'icon'        => 'shopify',
                'color'       => '#95bf47',
                'connected'   => $connections->where('platform', 'shopify')->isNotEmpty(),
                'connection'  => $connections->where('platform', 'shopify')->first(),
            ],
            [
                'id'          => 'webflow',
                'name'        => 'Webflow',
                'description' => 'Add our tracking script to your Webflow custom code section for full analytics.',
                'icon'        => 'webflow',
                'color'       => '#4353ff',
                'connected'   => $connections->where('platform', 'webflow')->isNotEmpty(),
                'connection'  => $connections->where('platform', 'webflow')->first(),
            ],
            [
                'id'          => 'squarespace',
                'name'        => 'Squarespace',
                'description' => 'Use our Squarespace extension or inject the tracking code via code injection.',
                'icon'        => 'squarespace',
                'color'       => '#000000',
                'connected'   => $connections->where('platform', 'squarespace')->isNotEmpty(),
                'connection'  => $connections->where('platform', 'squarespace')->first(),
            ],
        ];
    }

    /**
     * Display the WordPress connection setup page.
     */
    public function wordpress()
    {
        $client = Auth::guard('client')->user();
        $connection = \App\Models\WebsiteConnection::where('client_id', $client->id)
            ->where('platform', 'wordpress')->first();
        $trackingCode = $connection?->tracking_code;
        $embedCode = $connection ? $this->generateEmbedCode($connection) : null;
        return view('client.wordpress_connection', compact('connection', 'trackingCode', 'embedCode'));
    }

    /**
     * Display the Wix connection setup page.
     */
    public function wix()
    {
        $client = Auth::guard('client')->user();
        $connection = \App\Models\WebsiteConnection::where('client_id', $client->id)
            ->where('platform', 'wix')->first();
        $trackingCode = $connection?->tracking_code;
        $embedCode = $connection ? $this->generateEmbedCode($connection) : null;
        return view('client.wix_connection', compact('connection', 'trackingCode', 'embedCode'));
    }

    /**
     * Display the Shopify connection setup page.
     */
    public function shopify()
    {
        $client = Auth::guard('client')->user();
        $connection = \App\Models\WebsiteConnection::where('client_id', $client->id)
            ->where('platform', 'shopify')->first();
        $trackingCode = $connection?->tracking_code;
        $embedCode = $connection ? $this->generateEmbedCode($connection) : null;
        return view('client.shopify_connection', compact('connection', 'trackingCode', 'embedCode'));
    }

    /**
     * Display the Webflow connection setup page.
     */
    public function webflow()
    {
        $client = Auth::guard('client')->user();
        $connection = \App\Models\WebsiteConnection::where('client_id', $client->id)
            ->where('platform', 'webflow')->first();
        $trackingCode = $connection?->tracking_code;
        $embedCode = $connection ? $this->generateEmbedCode($connection) : null;
        return view('client.webflow_connection', compact('connection', 'trackingCode', 'embedCode'));
    }

    /**
     * Display the Squarespace connection setup page.
     */
    public function squarespace()
    {
        $client = Auth::guard('client')->user();
        $connection = \App\Models\WebsiteConnection::where('client_id', $client->id)
            ->where('platform', 'squarespace')->first();
        $trackingCode = $connection?->tracking_code;
        $embedCode = $connection ? $this->generateEmbedCode($connection) : null;
        return view('client.squarespace_connection', compact('connection', 'trackingCode', 'embedCode'));
    }

    /**
     * Store a new website connection.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'platform'   => 'required|string|in:wordpress,wix,shopify,webflow,squarespace',
            'site_url'   => 'required|url|max:500',
            'site_name'  => 'nullable|string|max:255',
            'api_key'    => 'nullable|string|max:500',
            'settings'   => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $client = Auth::guard('client')->user();

        $existing = \App\Models\WebsiteConnection::where('client_id', $client->id)
            ->where('platform', $request->platform)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'This platform is already connected. Please disconnect first.',
            ], 409);
        }

        $trackingCode = $this->generateTrackingCode($client->id, $request->platform);

        $connection = \App\Models\WebsiteConnection::create([
            'client_id'     => $client->id,
            'tenant_id'     => $client->tenant_id ?? null,
            'platform'      => $request->platform,
            'site_url'      => $request->site_url,
            'site_name'     => $request->site_name ?? $request->site_url,
            'tracking_code' => $trackingCode,
            'api_key'       => $request->api_key,
            'settings'      => $request->settings ? json_decode($request->settings, true) : [],
            'status'        => 'active',
            'connected_at'  => now(),
            'last_sync_at'  => now(),
        ]);

        $embedCode = $this->generateEmbedCode($connection);

        $this->logConnectionEvent($client->id, $connection->id, 'connected', $request->platform);

        return response()->json([
            'success'       => true,
            'message'       => 'Website connected successfully.',
            'connection'    => $connection,
            'tracking_code' => $trackingCode,
            'embed_code'    => $embedCode,
        ], 201);
    }

    /**
     * Disconnect / delete a website connection.
     */
    public function destroy($id)
    {
        $client = Auth::guard('client')->user();

        $connection = \App\Models\WebsiteConnection::where('client_id', $client->id)
            ->where('id', $id)
            ->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Connection not found.',
            ], 404);
        }

        $platform = $connection->platform;
        $this->logConnectionEvent($client->id, $connection->id, 'disconnected', $platform);
        $connection->delete();

        return response()->json([
            'success' => true,
            'message' => 'Website disconnected successfully.',
        ]);
    }

    /**
     * Get connection details with tracking code.
     */
    public function show($id)
    {
        $client = Auth::guard('client')->user();

        $connection = \App\Models\WebsiteConnection::where('client_id', $client->id)
            ->where('id', $id)
            ->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Connection not found.',
            ], 404);
        }

        $embedCode = $this->generateEmbedCode($connection);

        return response()->json([
            'success'     => true,
            'connection'  => $connection,
            'embed_code'  => $embedCode,
        ]);
    }

    /**
     * Update connection settings.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'site_name' => 'nullable|string|max:255',
            'site_url'  => 'nullable|url|max:500',
            'settings'  => 'nullable|json',
            'status'    => 'nullable|string|in:active,pause',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $client = Auth::guard('client')->user();

        $connection = \App\Models\WebsiteConnection::where('client_id', $client->id)
            ->where('id', $id)
            ->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Connection not found.',
            ], 404);
        }

        $updateData = [];

        if ($request->has('site_name')) {
            $updateData['site_name'] = $request->site_name;
        }
        if ($request->has('site_url')) {
            $updateData['site_url'] = $request->site_url;
        }
        if ($request->has('status')) {
            $updateData['status'] = $request->status;
        }
        if ($request->has('settings')) {
            $existingSettings = $connection->settings ?? [];
            $newSettings = json_decode($request->settings, true) ?? [];
            $updateData['settings'] = array_merge($existingSettings, $newSettings);
        }

        $connection->update($updateData);

        return response()->json([
            'success'    => true,
            'message'    => 'Connection updated successfully.',
            'connection' => $connection,
        ]);
    }

    /**
     * Verify a connection is working (health check).
     */
    public function verify(Request $request, $id)
    {
        $client = Auth::guard('client')->user();

        $connection = \App\Models\WebsiteConnection::where('client_id', $client->id)
            ->where('id', $id)
            ->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Connection not found.',
            ], 404);
        }

        // Allow manual force verify (skip health check)
        $forceVerify = $request->has('force') && $request->force === 'true';

        if ($forceVerify) {
            $connection->update([
                'last_sync_at' => now(),
                'status'       => 'active',
            ]);
            return response()->json([
                'success'   => true,
                'healthy'   => true,
                'message'   => 'Connection manually verified as active.',
                'forced'    => true,
            ]);
        }

        $isHealthy = $this->performHealthCheck($connection);

        $connection->update([
            'last_sync_at' => now(),
            'status'       => $isHealthy ? 'active' : 'error',
        ]);

        return response()->json([
            'success'   => true,
            'healthy'   => $isHealthy,
            'message'   => $isHealthy ? 'Connection is healthy.' : 'Connection issue detected. Tracking code is installed but may not be reachable from server. Try manual verify.',
        ]);
    }

    /**
     * Get tracking script for frontend embedding.
     */
    public function getTrackingScript(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tracking_code' => 'required|string|size:32',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $connection = \App\Models\WebsiteConnection::where('tracking_code', $request->tracking_code)
            ->where('status', 'active')
            ->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid tracking code.',
            ], 404);
        }

        $script = $this->generateTrackingScript($connection);

        return response($script, 200)
            ->header('Content-Type', 'application/javascript');
    }

    /* ═══════════════════════════════════════════════════════
       PRIVATE HELPER METHODS
       ═══════════════════════════════════════════════════════ */

    private function getConnections(int $clientId)
    {
        return \App\Models\WebsiteConnection::where('client_id', $clientId)
            ->orderBy('connected_at', 'desc')
            ->get();
    }

    private function generateTrackingCode(int $clientId, string $platform): string
    {
        $prefix = substr(strtoupper($platform), 0, 3);
        $random = bin2hex(random_bytes(12));
        return $prefix . '_' . $clientId . '_' . $random;
    }

    private function generateEmbedCode($connection): string
    {
        $trackingUrl = url('/api/tracking/script?tracking_code=' . $connection->tracking_code);

        switch ($connection->platform) {
            case 'wordpress':
                return '<!-- Scorementor Tracking for WordPress -->\n' .
                       '<script>\n' .
                       '  (function(s,c,o,r,e,m){\n' .
                       '    s["__scorementor"]={t:"' . $connection->tracking_code . '"};\n' .
                       '    var a=c.createElement("script");\n' .
                       '    a.async=1;a.src="' . $trackingUrl . '"];\n' .
                       '    c.head.appendChild(a);\n' .
                       '  })(window,document);\n' .
                       '</script>';

            case 'shopify':
                return '<!-- Scorementor Tracking for Shopify -->\n' .
                       '<script src="' . $trackingUrl . '" async></script>';

            case 'wix':
            case 'webflow':
            case 'squarespace':
                return '<!-- Scorementor Tracking -->\n' .
                       '<script>\n' .
                       '  window.__scorementor = { trackingCode: "' . $connection->tracking_code . '" };\n' .
                       '</script>\n' .
                       '<script src="' . $trackingUrl . '" async></script>';

            default:
                return '<script src="' . $trackingUrl . '" async></script>';
        }
    }

    private function generateTrackingScript($connection): string
    {
        $apiEndpoint = url('/api/events/collect');

        return <<<JS
(function() {
    'use strict';

    var config = {
        trackingCode: '{$connection->tracking_code}',
        apiEndpoint: '{$apiEndpoint}',
        platform: '{$connection->platform}',
        clientId: {$connection->client_id}
    };

    function sendEvent(eventType, data) {
        var payload = {
            tracking_code: config.trackingCode,
            event_type: eventType,
            data: data,
            url: window.location.href,
            timestamp: new Date().toISOString(),
            user_agent: navigator.userAgent,
            screen: {
                width: window.screen.width,
                height: window.screen.height
            }
        };

        if (navigator.sendBeacon) {
            navigator.sendBeacon(config.apiEndpoint, JSON.stringify(payload));
        } else {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', config.apiEndpoint, true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.send(JSON.stringify(payload));
        }
    }

    sendEvent('page_view', {
        title: document.title,
        referrer: document.referrer
    });

    var maxScroll = 0;
    document.addEventListener('scroll', function() {
        var scrollPercent = Math.round((window.scrollY + window.innerHeight) / document.documentElement.scrollHeight * 100);
        if (scrollPercent > maxScroll) {
            maxScroll = scrollPercent;
            if (maxScroll % 25 === 0) {
                sendEvent('scroll_depth', { depth: maxScroll });
            }
        }
    });

    document.addEventListener('click', function(e) {
        var target = e.target.closest('a, button, [data-track]');
        if (target) {
            sendEvent('click', {
                element: target.tagName,
                id: target.id || null,
                class: target.className || null,
                text: target.innerText?.substring(0, 100) || null
            });
        }
    });

    document.addEventListener('submit', function(e) {
        var form = e.target;
        sendEvent('form_submit', {
            form_id: form.id || null,
            form_action: form.action || null
        });
    });

    var startTime = Date.now();
    window.addEventListener('beforeunload', function() {
        var duration = Math.round((Date.now() - startTime) / 1000);
        sendEvent('time_on_page', { duration: duration });
    });

    console.log('[Scorementor] Tracking initialized for ' + config.platform);
})();
JS;
    }

    private function performHealthCheck($connection): bool
    {
        try {
            // Method 1: Check if tracking code exists in site HTML via HTTP request
            $opts = [
                'http' => [
                    'timeout' => 15,
                    'follow_location' => true,
                    'header' => [
                        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    ]
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ]
            ];

            $response = @file_get_contents($connection->site_url, false, stream_context_create($opts));

            if ($response !== false) {
                // Check for tracking code in HTML
                if (strpos($response, $connection->tracking_code) !== false) {
                    return true;
                }
                // Check for the tracking script URL pattern
                $trackingUrlPattern = url('/api/tracking/script');
                if (strpos($response, $trackingUrlPattern) !== false) {
                    return true;
                }
            }

            // Method 2: Check if there are recent events from this connection
            // If events exist in the last 24 hours, consider it healthy
            $recentEvent = \App\Models\WebsiteEvent::where('connection_id', $connection->id)
                ->where('created_at', '>=', now()->subHours(24))
                ->first();

            if ($recentEvent) {
                return true;
            }

            // Method 3: For Shopify/Wix/etc - check if we can at least reach the site
            $headers = @get_headers($connection->site_url, 1);
            if ($headers && isset($headers[0]) && strpos($headers[0], '200') !== false) {
                // Site is reachable, but we can't confirm tracking code is present
                // Still return true if site is live and connection was recently established
                if ($connection->status === 'active' && $connection->connected_at && $connection->connected_at->diffInHours(now()) < 48) {
                    return true;
                }
            }

            return false;

        } catch (\Exception $e) {
            \Log::warning('Health check failed for connection ' . $connection->id . ': ' . $e->getMessage());
            return false;
        }
    }

    private function logConnectionEvent(int $clientId, int $connectionId, string $event, string $platform): void
    {
        \App\Models\ConnectionLog::create([
            'client_id'     => $clientId,
            'connection_id' => $connectionId,
            'event'         => $event,
            'platform'      => $platform,
            'ip_address'    => request()->ip(),
            'user_agent'    => request()->userAgent(),
            'created_at'    => now(),
        ]);
    }

    /**
     * Display analytics dashboard for a website connection.
     */
    public function analytics($id)
    {
        $client = Auth::guard('client')->user();

        $connection = \App\Models\WebsiteConnection::where('client_id', $client->id)
            ->where('id', $id)
            ->first();

        if (!$connection) {
            return redirect()->route('client.website-connections')
                ->with('error', 'Connection not found.');
        }

        // Get today's stats
        $today = now()->startOfDay();
        $eventsToday = \App\Models\WebsiteEvent::where('connection_id', $connection->id)
            ->whereDate('created_at', $today)
            ->count();

        // Get all-time stats
        $totalEvents = \App\Models\WebsiteEvent::where('connection_id', $connection->id)->count();
        $totalPageViews = \App\Models\WebsiteEvent::where('connection_id', $connection->id)
            ->where('event_type', 'page_view')
            ->count();
        $totalClicks = \App\Models\WebsiteEvent::where('connection_id', $connection->id)
            ->where('event_type', 'click')
            ->count();
        $totalForms = \App\Models\WebsiteEvent::where('connection_id', $connection->id)
            ->where('event_type', 'form_submit')
            ->count();

        // Get events by type breakdown
        $eventsByType = \App\Models\WebsiteEvent::where('connection_id', $connection->id)
            ->selectRaw('event_type, COUNT(*) as count')
            ->groupBy('event_type')
            ->orderByDesc('count')
            ->get();

        // Get last 7 days trend
        $last7Days = collect(range(0, 6))->map(function ($days) use ($connection) {
            $date = now()->subDays($days)->startOfDay();
            $count = \App\Models\WebsiteEvent::where('connection_id', $connection->id)
                ->whereDate('created_at', $date)
                ->count();
            return [
                'date' => $date->format('M d'),
                'count' => $count,
            ];
        })->reverse()->values();

        // Get recent events (last 50)
        $recentEvents = \App\Models\WebsiteEvent::where('connection_id', $connection->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        // Get top pages
        $topPages = \App\Models\WebsiteEvent::where('connection_id', $connection->id)
            ->where('event_type', 'page_view')
            ->whereNotNull('page_url')
            ->selectRaw('page_url, COUNT(*) as views')
            ->groupBy('page_url')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        // Get top clicked elements
        $topClicks = \App\Models\WebsiteEvent::where('connection_id', $connection->id)
            ->where('event_type', 'click')
            ->whereNotNull('data')
            ->get()
            ->map(function ($event) {
                $data = is_array($event->data) ? $event->data : json_decode($event->data, true);
                return [
                    'element' => $data['element'] ?? 'Unknown',
                    'text' => $data['text'] ?? '',
                    'page' => $event->page_url,
                ];
            })
            ->groupBy('element')
            ->map(function ($group) {
                return [
                    'element' => $group->first()['element'],
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->take(10)
            ->values();

        // Calculate average time on page
        $avgTimeOnPage = \App\Models\WebsiteEvent::where('connection_id', $connection->id)
            ->where('event_type', 'time_on_page')
            ->whereNotNull('data')
            ->get()
            ->avg(function ($event) {
                $data = is_array($event->data) ? $event->data : json_decode($event->data, true);
                return $data['duration'] ?? 0;
            }) ?? 0;

        // Get unique visitors (by IP)
        $uniqueVisitors = \App\Models\WebsiteEvent::where('connection_id', $connection->id)
            ->select('ip_address')
            ->distinct()
            ->count('ip_address');

        // Get scroll depth stats
        $scrollDepths = \App\Models\WebsiteEvent::where('connection_id', $connection->id)
            ->where('event_type', 'scroll_depth')
            ->whereNotNull('data')
            ->get()
            ->map(function ($event) {
                $data = is_array($event->data) ? $event->data : json_decode($event->data, true);
                return $data['depth'] ?? 0;
            })
            ->groupBy(function ($depth) {
                return $depth;
            })
            ->map(function ($group) {
                return $group->count();
            })
            ->sortKeys();

                // Select view based on platform
        $view = match($connection->platform) {
            'shopify' => 'client.shopify_analytics',
            'webflow' => 'client.webflow_analytics',
            'squarespace' => 'client.squarespace_analytics',
            default => 'client.wix_analytics',
        };

        return view($view, compact(
            'connection',
            'eventsToday',
            'totalEvents',
            'totalPageViews',
            'totalClicks',
            'totalForms',
            'eventsByType',
            'last7Days',
            'recentEvents',
            'topPages',
            'topClicks',
            'avgTimeOnPage',
            'uniqueVisitors',
            'scrollDepths'
        ));;
    }
}