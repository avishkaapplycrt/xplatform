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
            
        // Get today's stats
        $todayStats = \App\Models\AnalyticsDailyStat::where('site_id', $siteId)
            ->whereDate('date', today())
            ->first();
            
        // Get last 7 days data for chart
        $chartData = \App\Models\AnalyticsDailyStat::where('site_id', $siteId)
            ->whereDate('date', '>=', now()->subDays(7))
            ->orderBy('date')
            ->get(['date', 'pageviews', 'unique_visitors', 'sessions']);
            
        // Get countries
        $countryData = \App\Models\AnalyticsDailyStat::where('site_id', $siteId)
            ->whereDate('date', '>=', now()->subDays(7))
            ->get(['countries'])
            ->pluck('countries')
            ->filter()
            ->reduce(function($carry, $item) {
                foreach ($item as $country => $count) {
                    $carry[$country] = ($carry[$country] ?? 0) + $count;
                }
                return $carry;
            }, []);
            
        arsort($countryData);
        
        // Get pages
        $pageData = \App\Models\AnalyticsDailyStat::where('site_id', $siteId)
            ->whereDate('date', '>=', now()->subDays(7))
            ->get(['pages'])
            ->pluck('pages')
            ->filter()
            ->reduce(function($carry, $item) {
                foreach ($item as $page => $count) {
                    $carry[$page] = ($carry[$page] ?? 0) + $count;
                }
                return $carry;
            }, []);
            
        arsort($pageData);
        
        // Get devices
        $deviceData = \App\Models\AnalyticsDailyStat::where('site_id', $siteId)
            ->whereDate('date', '>=', now()->subDays(7))
            ->get(['devices'])
            ->pluck('devices')
            ->filter()
            ->reduce(function($carry, $item) {
                foreach ($item as $device => $count) {
                    $carry[$device] = ($carry[$device] ?? 0) + $count;
                }
                return $carry;
            }, []);
            
        arsort($deviceData);
        
        // Realtime visitors (last 5 minutes)
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