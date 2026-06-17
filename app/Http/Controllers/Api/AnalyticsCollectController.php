<?php
// app/Http/Controllers/Api/AnalyticsCollectController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSession;
use App\Models\AnalyticsSite;
use App\Services\GeoLocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class AnalyticsCollectController extends Controller
{
    protected GeoLocationService $geoService;
    
    public function __construct(GeoLocationService $geoService)
    {
        $this->geoService = $geoService;
    }
    
    /**
     * Main collection endpoint - receives tracking data
     */
    public function collect(Request $request)
    {
        $startTime = microtime(true);
        
        try {
            // Validate site
            $site = $this->validateSite($request->input('site_id'));
            if (!$site) {
                return response()->json(['status' => 'ok'], 200); // Silent fail
            }
            
            // Rate limiting per IP (prevent spam)
            $ipKey = 'analytics:rate:' . $request->ip();
            if (Cache::has($ipKey) && Cache::get($ipKey) > 100) { // 100 requests/min
                return response()->json(['status' => 'ok'], 200);
            }
            Cache::increment($ipKey);
            Cache::expire($ipKey, 60);
            
            // Get geo data from IP
            $geoData = $this->geoService->lookup($request->ip());
            
            // Build payload
            $payload = [
                'site_id' => $site->id,
                'session_id' => $request->input('session_id'),
                'visitor_id' => $request->input('visitor_id'),
                'url' => substr($request->input('url'), 0, 2048),
                'path' => substr($request->input('path'), 0, 1024),
                'title' => substr($request->input('title', ''), 0, 500),
                'referrer' => $request->input('referrer') ? substr($request->input('referrer'), 0, 2048) : null,
                'referrer_domain' => $request->input('referrer_domain'),
                'utm_source' => $request->input('utm_source'),
                'utm_medium' => $request->input('utm_medium'),
                'utm_campaign' => $request->input('utm_campaign'),
                'utm_term' => $request->input('utm_term'),
                'utm_content' => $request->input('utm_content'),
                'country' => $geoData['country_code'] ?? null,
                'country_name' => $geoData['country_name'] ?? null,
                'city' => $geoData['city'] ?? null,
                'region' => $geoData['region'] ?? null,
                'latitude' => $geoData['latitude'] ?? null,
                'longitude' => $geoData['longitude'] ?? null,
                'device_type' => $request->input('device_type'),
                'browser' => $request->input('browser'),
                'browser_version' => $request->input('browser_version'),
                'os' => $request->input('os'),
                'os_version' => $request->input('os_version'),
                'screen_width' => $request->input('screen_width'),
                'screen_height' => $request->input('screen_height'),
                'load_time_ms' => $request->input('load_time_ms'),
                'created_at' => now()
            ];
            
            // Queue for processing (use Redis for high throughput)
            Redis::lpush('analytics:pageviews:queue', json_encode($payload));
            
            // Update session in real-time for "active visitors"
            $this->updateSession($site->id, $request->all());
            
            // Response must be fast - don't process here
            return response()->json(['status' => 'ok'], 201)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type');
                
        } catch (\Exception $e) {
            Log::error('Analytics collect failed', [
                'error' => $e->getMessage(),
                'input' => $request->all()
            ]);
            return response()->json(['status' => 'ok'], 200); // Always return 200
        }
    }
    
    /**
     * Validate tracking ID
     */
    protected function validateSite(string $trackingId): ?AnalyticsSite
    {
        return Cache::remember("analytics:site:{$trackingId}", 3600, function() use ($trackingId) {
            return AnalyticsSite::where('tracking_id', $trackingId)
                ->where('is_active', true)
                ->first();
        });
    }
    
    /**
     * Update session tracking
     */
    protected function updateSession(int $siteId, array $data): void
    {
        $sessionKey = "analytics:session:{$data['session_id']}";
        $sessionData = Redis::hgetall($sessionKey);
        
        if (empty($sessionData)) {
            // New session
            Redis::hmset($sessionKey, [
                'site_id' => $siteId,
                'visitor_id' => $data['visitor_id'],
                'first_page' => $data['url'] ?? '',
                'pageviews' => 1,
                'started_at' => now()->toDateTimeString(),
                'last_activity' => now()->toDateTimeString()
            ]);
            Redis::expire($sessionKey, 1800); // 30 min session timeout
        } else {
            // Update existing session
            Redis::hincrby($sessionKey, 'pageviews', 1);
            Redis::hset($sessionKey, 'last_page', $data['url'] ?? '');
            Redis::hset($sessionKey, 'last_activity', now()->toDateTimeString());
            Redis::expire($sessionKey, 1800);
        }
    }
    
    /**
     * CORS preflight
     */
    public function options()
    {
        return response()->noContent()
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type')
            ->header('Access-Control-Max-Age', '86400');
    }
}