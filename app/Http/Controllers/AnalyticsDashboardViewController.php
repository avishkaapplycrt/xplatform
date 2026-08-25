<?php
// app/Http/Controllers/AnalyticsDashboardViewController.php

namespace App\Http\Controllers;

use App\Models\AnalyticsSite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AnalyticsDashboardViewController extends Controller
{
    /**
     * Show analytics dashboard
     */
    public function index()
    {
        $sites = AnalyticsSite::where('client_id', auth()->id())
            ->where('is_active', true)
            ->get();
            
        return view('analytics.dashboard', compact('sites'));
    }
    
    /**
     * Show site detail with tracking code
     */
    public function showSite($siteId)
    {
        $site = AnalyticsSite::where('id', $siteId)
            ->where('client_id', auth()->id())
            ->firstOrFail();
            
        $trackingScript = $this->generateTrackingScript($site);
        
        return view('analytics.site-detail', compact('site', 'trackingScript'));
    }
    
    /**
     * Show create site form
     */
    public function createSiteForm()
    {
        return view('analytics.create-site');
    }
    
    /**
     * Create new site
     */
    public function storeSite(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|url|max:255',
        ]);
        
        $site = new AnalyticsSite();
        $site->client_id = auth()->id();
        $site->name = $validated['name'];
        $site->domain = parse_url($validated['domain'], PHP_URL_HOST) ?? $validated['domain'];
        $site->tracking_id = $site->generateTrackingId();
        $site->api_key = $site->generateApiKey();
        $site->settings = [
            'track_bots' => false,
            'anonymize_ip' => false,
        ];
        $site->is_active = true;
        $site->save();
        
        return redirect()->route('client.analytics.site.detail', $site->id)
            ->with('success', 'Website added successfully! Copy the tracking code below.');
    }
    
    /**
     * Show analytics data for a site
     */
    public function analyticsData($siteId)
    {
        $site = AnalyticsSite::where('id', $siteId)
            ->where('client_id', auth()->id())
            ->firstOrFail();
            
        // ===== TODAY'S STATS =====
        $todayPageviews = \App\Models\AnalyticsPageview::where('site_id', $siteId)
            ->whereDate('created_at', today())
            ->count();
            
        $todayUniqueVisitors = \App\Models\AnalyticsPageview::where('site_id', $siteId)
            ->whereDate('created_at', today())
            ->distinct('visitor_id')
            ->count('visitor_id');
            
        $todaySessions = \App\Models\AnalyticsPageview::where('site_id', $siteId)
            ->whereDate('created_at', today())
            ->distinct('session_id')
            ->count('session_id');
            
        $todayBounceRate = $todaySessions > 0 
            ? round((\App\Models\AnalyticsSession::where('site_id', $siteId)
                ->whereDate('started_at', today())
                ->where('is_bounce', true)
                ->count() / $todaySessions) * 100, 1)
            : 0;
            
        $todayStats = (object)[
            'pageviews' => $todayPageviews,
            'unique_visitors' => $todayUniqueVisitors,
            'sessions' => $todaySessions,
            'bounce_rate' => $todayBounceRate,
        ];
        
        // ===== CHART DATA (Last 7 Days) - Carbon objects =====
        $chartData = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartData->push((object)[
                'date' => $date, // Carbon object for ->format()
                'pageviews' => \App\Models\AnalyticsPageview::where('site_id', $siteId)
                    ->whereDate('created_at', $date)
                    ->count(),
                'unique_visitors' => \App\Models\AnalyticsPageview::where('site_id', $siteId)
                    ->whereDate('created_at', $date)
                    ->distinct('visitor_id')
                    ->count('visitor_id'),
                'sessions' => \App\Models\AnalyticsPageview::where('site_id', $siteId)
                    ->whereDate('created_at', $date)
                    ->distinct('session_id')
                    ->count('session_id'),
            ]);
        }
        
        // ===== COUNTRY DATA =====
        $countryData = \App\Models\AnalyticsPageview::where('site_id', $siteId)
            ->where('created_at', '>=', now()->subDays(7))
            ->whereNotNull('country') // Use 2-letter code
            ->selectRaw('country, COUNT(*) as count')
            ->groupBy('country')
            ->pluck('count', 'country')
            ->toArray();
            
        arsort($countryData);
        
        // ===== PAGE DATA =====
        $pageData = \App\Models\AnalyticsPageview::where('site_id', $siteId)
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('path, COUNT(*) as count')
            ->groupBy('path')
            ->pluck('count', 'path')
            ->toArray();
            
        arsort($pageData);
        
        // ===== DEVICE DATA =====
        $deviceData = \App\Models\AnalyticsPageview::where('site_id', $siteId)
            ->where('created_at', '>=', now()->subDays(7))
            ->whereNotNull('device_type')
            ->selectRaw('device_type, COUNT(*) as count')
            ->groupBy('device_type')
            ->pluck('count', 'device_type')
            ->toArray();
            
        arsort($deviceData);
        
        // ===== REALTIME VISITORS =====
        $realtimeVisitors = \App\Models\AnalyticsPageview::where('site_id', $siteId)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->distinct('session_id')
            ->count('session_id');
            
        return view('analytics.analytics-data', compact(
            'site', 'todayStats', 'chartData', 'countryData', 
            'pageData', 'deviceData', 'realtimeVisitors'
        ));
    }
    
    /**
     * Generate tracking script
     */
    protected function generateTrackingScript(AnalyticsSite $site): string
    {
        $baseUrl = rtrim(config('app.url'), '/');
        
        return <<<HTML
<!-- Analytics Tracking - {$site->name} -->
<script>
    window.__analyticsEndpoint = '{$baseUrl}/api/collect';
    window.__analyticsSiteId = '{$site->tracking_id}';
</script>
<script src="{$baseUrl}/js/analytics-tracker.js" defer></script>
<!-- End Analytics Tracking -->
HTML;
    }
    
    /**
     * Delete site
     */
    public function deleteSite($siteId)
    {
        $site = AnalyticsSite::where('id', $siteId)
            ->where('client_id', auth()->id())
            ->firstOrFail();
            
        $site->is_active = false;
        $site->save();
        
        return redirect()->route('analytics.dashboard')
            ->with('success', 'Website removed successfully.');
    }
}