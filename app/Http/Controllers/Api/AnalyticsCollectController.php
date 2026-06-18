<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSession;
use App\Models\AnalyticsSite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AnalyticsCollectController extends Controller
{
    public function collect(Request $request)
    {
        try {
            $siteId = $request->input('site_id');
            
            if (empty($siteId)) {
                return response()->json(['status' => 'error', 'message' => 'site_id required'], 400)
                    ->header('Access-Control-Allow-Origin', '*');
            }
            
            $site = $this->validateSite($siteId);
            
            if (!$site) {
                return response()->json(['status' => 'error', 'message' => 'Site not found'], 404)
                    ->header('Access-Control-Allow-Origin', '*');
            }
            
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
                'country' => $request->input('country'),
                'country_name' => $request->input('country_name'),
                'city' => $request->input('city'),
                'region' => $request->input('region'),
                'device_type' => $request->input('device_type'),
                'browser' => $request->input('browser'),
                'browser_version' => $request->input('browser_version'),
                'os' => $request->input('os'),
                'os_version' => $request->input('os_version'),
                'screen_width' => $request->input('screen_width'),
                'screen_height' => $request->input('screen_height'),
                'load_time_ms' => $request->input('load_time_ms'),
                'created_at' => now(), // ← ADDED: Explicitly set created_at
            ];
            
            $pageview = AnalyticsPageview::create($payload);
            
            // Update session
            try {
                $this->updateSession($site->id, $request->all());
            } catch (\Exception $e) {
                Log::error('Session update failed: ' . $e->getMessage());
            }
            
            return response()->json(['status' => 'ok', 'id' => $pageview->id], 201)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type');
                
        } catch (\Exception $e) {
            Log::error('Collect error: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    protected function validateSite(?string $trackingId): ?AnalyticsSite
    {
        if (empty($trackingId)) {
            return null;
        }
        
        return Cache::remember("analytics:site:{$trackingId}", 3600, function() use ($trackingId) {
            return AnalyticsSite::where('tracking_id', $trackingId)
                ->where('is_active', true)
                ->first();
        });
    }
    
    protected function updateSession(int $siteId, array $data): void
    {
        $sessionId = $data['session_id'] ?? 'unknown';
        $session = AnalyticsSession::where('session_id', $sessionId)->first();
        
        if (!$session) {
            DB::table('analytics_sessions')->insert([
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