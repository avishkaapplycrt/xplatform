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

class AnalyticsCollectController extends Controller
{
    protected GeoLocationService $geoService;
    
    public function __construct(GeoLocationService $geoService)
    {
        $this->geoService = $geoService;
    }
    
    public function collect(Request $request)
    {
        try {
            Log::info('Analytics collect request', ['input' => $request->all()]);
            
            // Validate site
            $site = $this->validateSite($request->input('site_id'));
            if (!$site) {
                Log::warning('Invalid site_id', ['site_id' => $request->input('site_id')]);
                return response()->json(['status' => 'ok'], 200);
            }
            
            // Rate limiting per IP
            $ipKey = 'analytics:rate:' . $request->ip();
            if (Cache::has($ipKey) && Cache::get($ipKey) > 100) {
                return response()->json(['status' => 'ok'], 200);
            }
            Cache::increment($ipKey);
            Cache::expire($ipKey, 60);
            
            // Get geo data from IP - wrap in try-catch
            $geoData = [];
            try {
                $geoData = $this->geoService->lookup($request->ip());
            } catch (\Exception $e) {
                Log::warning('GeoIP lookup failed in controller', ['error' => $e->getMessage()]);
            }
            
            // Build payload
            $payload = [
                'site_id' => $site->id,
                'session_id' => $request->input('session_id', 'unknown'),
                'visitor_id' => $request->input('visitor_id', 'unknown'),
                'url' => substr($request->input('url', ''), 0, 2048),
                'path' => substr($request->input('path', ''), 0, 1024),
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
            
            Log::info('Analytics payload', $payload);
            
            // Insert directly into database
            AnalyticsPageview::create($payload);
            
            // Update session
            $this->updateSession($site->id, $request->all());
            
            return response()->json(['status' => 'ok'], 201)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type');
                
        } catch (\Exception $e) {
            Log::error('Analytics collect failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);
            return response()->json(['status' => 'ok'], 200);
        }
    }
    
    protected function validateSite(string $trackingId): ?AnalyticsSite
    {
        return Cache::remember("analytics:site:{$trackingId}", 3600, function() use ($trackingId) {
            return AnalyticsSite::where('tracking_id', $trackingId)
                ->where('is_active', true)
                ->first();
        });
    }
    
    protected function updateSession(int $siteId, array $data): void
    {
        try {
            $sessionId = $data['session_id'] ?? 'unknown';
            $session = AnalyticsSession::where('session_id', $sessionId)->first();
            
            if (!$session) {
                AnalyticsSession::create([
                    'site_id' => $siteId,
                    'session_id' => $sessionId,
                    'visitor_id' => $data['visitor_id'] ?? 'unknown',
                    'first_page' => $data['url'] ?? '',
                    'last_page' => $data['url'] ?? '',
                    'referrer' => $data['referrer'] ?? null,
                    'country' => $data['country'] ?? null,
                    'device_type' => $data['device_type'] ?? null,
                    'browser' => $data['browser'] ?? null,
                    'os' => $data['os'] ?? null,
                    'pageviews' => 1,
                    'duration_seconds' => 0,
                    'is_bounce' => true,
                    'started_at' => now(),
                    'ended_at' => now(),
                    'last_activity' => now()
                ]);
            } else {
                $session->pageviews += 1;
                $session->last_page = $data['url'] ?? '';
                $session->ended_at = now();
                $session->last_activity = now();
                $session->is_bounce = false;
                $session->save();
            }
        } catch (\Exception $e) {
            Log::error('Session update failed', ['error' => $e->getMessage()]);
        }
    }
    
    public function options()
    {
        return response()->noContent()
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With')
            ->header('Access-Control-Max-Age', '86400');
    }
}