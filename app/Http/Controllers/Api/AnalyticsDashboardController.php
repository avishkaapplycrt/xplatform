<?php
// app/Http/Controllers/Api/AnalyticsDashboardController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsSite;
use App\Models\AnalyticsDailyStat;
use App\Models\AnalyticsHourlyStat;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redis;

class AnalyticsDashboardController extends Controller
{
    // ==========================================
    // SITE MANAGEMENT
    // ==========================================
    
    /**
     * List all sites for authenticated client
     */
    public function listSites(Request $request)
    {
        $sites = AnalyticsSite::where('client_id', auth()->user()->client_id ?? auth()->id())
            ->where('is_active', true)
            ->get(['id', 'name', 'domain', 'tracking_id', 'is_active', 'created_at']);
            
        return response()->json([
            'success' => true,
            'sites' => $sites
        ]);
    }
    
    /**
     * Create new tracking site
     */
    public function createSite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|url',
            'settings' => 'nullable|array'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $site = new AnalyticsSite();
        $site->client_id = auth()->user()->client_id ?? auth()->id();
        $site->name = $request->name;
        $site->domain = parse_url($request->domain, PHP_URL_HOST) ?? $request->domain;
        $site->tracking_id = $site->generateTrackingId();
        $site->api_key = $site->generateApiKey();
        $site->settings = $request->settings ?? [
            'track_bots' => false,
            'anonymize_ip' => false,
            'exclude_referrers' => []
        ];
        $site->is_active = true;
        $site->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Site created successfully',
            'site' => [
                'id' => $site->id,
                'name' => $site->name,
                'domain' => $site->domain,
                'tracking_id' => $site->tracking_id,
                'api_key' => $site->api_key,
                'tracking_script' => $this->generateTrackingScript($site),
                'created_at' => $site->created_at
            ]
        ], 201);
    }
    
    /**
     * Show site details
     */
    public function showSite(int $siteId)
    {
        $site = $this->authorizeSite($siteId);
        
        // Get today's stats
        $todayStats = AnalyticsDailyStat::where('site_id', $siteId)
            ->whereDate('date', today())
            ->first();
            
        return response()->json([
            'success' => true,
            'site' => [
                'id' => $site->id,
                'name' => $site->name,
                'domain' => $site->domain,
                'tracking_id' => $site->tracking_id,
                'api_key' => $site->api_key,
                'is_active' => $site->is_active,
                'settings' => $site->settings,
                'tracking_script' => $this->generateTrackingScript($site),
                'today_stats' => $todayStats ? [
                    'pageviews' => $todayStats->pageviews,
                    'unique_visitors' => $todayStats->unique_visitors,
                    'sessions' => $todayStats->sessions
                ] : null,
                'created_at' => $site->created_at,
                'updated_at' => $site->updated_at
            ]
        ]);
    }
    
    /**
     * Update site
     */
    public function updateSite(Request $request, int $siteId)
    {
        $site = $this->authorizeSite($siteId);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'domain' => 'sometimes|string|max:255|url',
            'is_active' => 'sometimes|boolean',
            'settings' => 'sometimes|array'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        if ($request->has('name')) $site->name = $request->name;
        if ($request->has('domain')) {
            $site->domain = parse_url($request->domain, PHP_URL_HOST) ?? $request->domain;
        }
        if ($request->has('is_active')) $site->is_active = $request->is_active;
        if ($request->has('settings')) {
            $site->settings = array_merge($site->settings ?? [], $request->settings);
        }
        
        $site->save();
        
        // Clear cache
        Cache::forget("analytics:site:{$site->tracking_id}");
        
        return response()->json([
            'success' => true,
            'message' => 'Site updated successfully',
            'site' => $site->only(['id', 'name', 'domain', 'tracking_id', 'is_active', 'settings'])
        ]);
    }
    
    /**
     * Delete site (soft delete by deactivating)
     */
    public function deleteSite(int $siteId)
    {
        $site = $this->authorizeSite($siteId);
        
        $site->is_active = false;
        $site->save();
        
        Cache::forget("analytics:site:{$site->tracking_id}");
        
        return response()->json([
            'success' => true,
            'message' => 'Site deactivated successfully'
        ]);
    }
    
    /**
     * Regenerate API key
     */
    public function regenerateApiKey(int $siteId)
    {
        $site = $this->authorizeSite($siteId);
        
        $site->api_key = $site->generateApiKey();
        $site->save();
        
        return response()->json([
            'success' => true,
            'message' => 'API key regenerated',
            'api_key' => $site->api_key
        ]);
    }
    
    // ==========================================
    // TRACKING SCRIPT GENERATOR
    // ==========================================
    
    /**
     * Generate tracking script for site owner
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
    
    // ==========================================
    // DASHBOARD DATA (from previous implementation)
    // ==========================================
    
    public function overview(Request $request, int $siteId)
    {
        $site = $this->authorizeSite($siteId);
        $period = $request->get('period', '7d');
        $dates = $this->getDateRange($period);
        
        $currentStats = AnalyticsDailyStat::where('site_id', $siteId)
            ->whereBetween('date', [$dates['current_start'], $dates['current_end']])
            ->selectRaw('
                SUM(pageviews) as pageviews,
                SUM(unique_visitors) as unique_visitors,
                SUM(sessions) as sessions,
                AVG(bounce_rate) as bounce_rate,
                AVG(avg_session_duration) as avg_session_duration
            ')
            ->first();
            
        $previousStats = AnalyticsDailyStat::where('site_id', $siteId)
            ->whereBetween('date', [$dates['previous_start'], $dates['previous_end']])
            ->selectRaw('
                SUM(pageviews) as pageviews,
                SUM(unique_visitors) as unique_visitors,
                SUM(sessions) as sessions
            ')
            ->first();
            
        $realtimeVisitors = AnalyticsPageview::where('site_id', $siteId)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->distinct('session_id')
            ->count('session_id');
            
        return response()->json([
            'success' => true,
            'site' => $site->only(['id', 'name', 'domain']),
            'period' => $period,
            'realtime' => ['active_visitors' => $realtimeVisitors],
            'current' => [
                'pageviews' => (int) $currentStats->pageviews,
                'unique_visitors' => (int) $currentStats->unique_visitors,
                'sessions' => (int) $currentStats->sessions,
                'bounce_rate' => round($currentStats->bounce_rate, 2),
                'avg_session_duration' => round($currentStats->avg_session_duration, 2),
                'pages_per_session' => $currentStats->sessions > 0 
                    ? round($currentStats->pageviews / $currentStats->sessions, 2) 
                    : 0
            ],
            'previous' => [
                'pageviews' => (int) $previousStats->pageviews,
                'unique_visitors' => (int) $previousStats->unique_visitors,
                'sessions' => (int) $previousStats->sessions
            ],
            'change' => [
                'pageviews' => $this->calculateChange($currentStats->pageviews, $previousStats->pageviews),
                'unique_visitors' => $this->calculateChange($currentStats->unique_visitors, $previousStats->unique_visitors),
                'sessions' => $this->calculateChange($currentStats->sessions, $previousStats->sessions)
            ]
        ]);
    }
    
    public function realtime(Request $request, int $siteId)
    {
        $this->authorizeSite($siteId);
        
        $last5Min = now()->subMinutes(5);
        $last30Min = now()->subMinutes(30);
        
        // Active visitors now
        $activeNow = AnalyticsPageview::where('site_id', $siteId)
            ->where('created_at', '>=', $last5Min)
            ->distinct('session_id')
            ->count('session_id');
            
        // Active visitors last 30 min
        $active30Min = AnalyticsPageview::where('site_id', $siteId)
            ->where('created_at', '>=', $last30Min)
            ->distinct('session_id')
            ->count('session_id');
            
        // Top active pages
        $topPages = AnalyticsPageview::where('site_id', $siteId)
            ->where('created_at', '>=', $last5Min)
            ->select('path', DB::raw('COUNT(DISTINCT session_id) as active_users'))
            ->groupBy('path')
            ->orderByDesc('active_users')
            ->limit(10)
            ->get();
            
        // Recent sessions
        $recentSessions = AnalyticsSession::where('site_id', $siteId)
            ->where('last_activity', '>=', $last5Min)
            ->orderByDesc('last_activity')
            ->limit(20)
            ->get(['session_id', 'first_page', 'last_page', 'country', 'device_type', 'pageviews', 'last_activity']);
            
        return response()->json([
            'success' => true,
            'realtime' => [
                'active_now' => $activeNow,
                'active_30min' => $active30Min,
                'top_pages' => $topPages,
                'recent_sessions' => $recentSessions
            ]
        ]);
    }
    
    public function timeseries(Request $request, int $siteId)
    {
        $this->authorizeSite($siteId);
        $period = $request->get('period', '7d');
        $metric = $request->get('metric', 'pageviews');
        
        $dates = $this->getDateRange($period);
        
        if ($period === '1d') {
            $data = AnalyticsHourlyStat::where('site_id', $siteId)
                ->where('date', $dates['current_start'])
                ->orderBy('hour')
                ->get(['hour', $metric])
                ->map(fn($item) => [
                    'label' => $item->hour . ':00',
                    'value' => (int) $item->{$metric}
                ]);
        } else {
            $data = AnalyticsDailyStat::where('site_id', $siteId)
                ->whereBetween('date', [$dates['current_start'], $dates['current_end']])
                ->orderBy('date')
                ->get(['date', $metric])
                ->map(fn($item) => [
                    'label' => $item->date->format('M d'),
                    'value' => (int) $item->{$metric}
                ]);
        }
        
        return response()->json(['success' => true, 'data' => $data]);
    }
    
    public function countries(Request $request, int $siteId)
    {
        $this->authorizeSite($siteId);
        $period = $request->get('period', '7d');
        $dates = $this->getDateRange($period);
        
        $countryData = AnalyticsDailyStat::where('site_id', $siteId)
            ->whereBetween('date', [$dates['current_start'], $dates['current_end']])
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
        $total = array_sum($countryData);
        
        $result = [];
        foreach (array_slice($countryData, 0, 20, true) as $country => $count) {
            $result[] = [
                'country' => $country,
                'country_name' => $this->getCountryName($country),
                'visitors' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 2) : 0
            ];
        }
        
        return response()->json(['success' => true, 'countries' => $result, 'total' => $total]);
    }
    
    public function pages(Request $request, int $siteId)
    {
        $this->authorizeSite($siteId);
        $period = $request->get('period', '7d');
        $dates = $this->getDateRange($period);
        
        $pageData = AnalyticsDailyStat::where('site_id', $siteId)
            ->whereBetween('date', [$dates['current_start'], $dates['current_end']])
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
        $total = array_sum($pageData);
        
        $result = [];
        foreach (array_slice($pageData, 0, 20, true) as $page => $count) {
            $result[] = [
                'page' => $page,
                'views' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 2) : 0
            ];
        }
        
        return response()->json(['success' => true, 'pages' => $result, 'total' => $total]);
    }
    
    public function devices(Request $request, int $siteId)
    {
        $this->authorizeSite($siteId);
        $period = $request->get('period', '7d');
        $dates = $this->getDateRange($period);
        
        $stats = AnalyticsDailyStat::where('site_id', $siteId)
            ->whereBetween('date', [$dates['current_start'], $dates['current_end']])
            ->get(['devices', 'browsers', 'oses'])
            ->reduce(function($carry, $item) {
                foreach (['devices', 'browsers', 'oses'] as $key) {
                    foreach ($item->{$key} ?? [] as $name => $count) {
                        $carry[$key][$name] = ($carry[$key][$name] ?? 0) + $count;
                    }
                }
                return $carry;
            }, ['devices' => [], 'browsers' => [], 'oses' => []]);
            
        return response()->json([
            'success' => true,
            'devices' => $this->formatBreakdown($stats['devices']),
            'browsers' => $this->formatBreakdown($stats['browsers']),
            'oses' => $this->formatBreakdown($stats['oses'])
        ]);
    }
    
    public function referrers(Request $request, int $siteId)
    {
        $this->authorizeSite($siteId);
        $period = $request->get('period', '7d');
        $dates = $this->getDateRange($period);
        
        $referrerData = AnalyticsDailyStat::where('site_id', $siteId)
            ->whereBetween('date', [$dates['current_start'], $dates['current_end']])
            ->get(['referrers'])
            ->pluck('referrers')
            ->filter()
            ->reduce(function($carry, $item) {
                foreach ($item as $referrer => $count) {
                    $carry[$referrer] = ($carry[$referrer] ?? 0) + $count;
                }
                return $carry;
            }, []);
            
        arsort($referrerData);
        
        $searchEngines = ['google', 'bing', 'yahoo', 'duckduckgo', 'baidu'];
        $social = ['facebook', 'twitter', 'instagram', 'linkedin', 'reddit', 'pinterest'];
        
        $result = ['search' => [], 'social' => [], 'direct' => 0, 'other' => []];
        
        foreach ($referrerData as $referrer => $count) {
            $domain = strtolower($referrer);
            if (empty($domain) || $domain === 'direct') {
                $result['direct'] += $count;
            } elseif (collect($searchEngines)->contains(fn($se) => str_contains($domain, $se))) {
                $result['search'][] = ['domain' => $referrer, 'visitors' => $count];
            } elseif (collect($social)->contains(fn($s) => str_contains($domain, $s))) {
                $result['social'][] = ['domain' => $referrer, 'visitors' => $count];
            } else {
                $result['other'][] = ['domain' => $referrer, 'visitors' => $count];
            }
        }
        
        return response()->json(['success' => true, 'referrers' => $result]);
    }
    
    public function campaigns(Request $request, int $siteId)
    {
        $this->authorizeSite($siteId);
        $period = $request->get('period', '7d');
        $dates = $this->getDateRange($period);
        
        $campaignData = AnalyticsDailyStat::where('site_id', $siteId)
            ->whereBetween('date', [$dates['current_start'], $dates['current_end']])
            ->get(['utm_sources'])
            ->pluck('utm_sources')
            ->filter()
            ->reduce(function($carry, $item) {
                foreach ($item as $source => $count) {
                    $carry[$source] = ($carry[$source] ?? 0) + $count;
                }
                return $carry;
            }, []);
            
        arsort($campaignData);
        
        return response()->json([
            'success' => true,
            'campaigns' => collect($campaignData)->map(function($count, $source) {
                return ['source' => $source, 'visitors' => $count];
            })->values()
        ]);
    }
    
    public function sessions(Request $request, int $siteId)
    {
        $this->authorizeSite($siteId);
        
        $sessions = AnalyticsSession::where('site_id', $siteId)
            ->orderByDesc('started_at')
            ->paginate(50);
            
        return response()->json([
            'success' => true,
            'sessions' => $sessions
        ]);
    }
    
    public function sessionDetail(Request $request, int $siteId, string $sessionId)
    {
        $this->authorizeSite($siteId);
        
        $session = AnalyticsSession::where('site_id', $siteId)
            ->where('session_id', $sessionId)
            ->firstOrFail();
            
        $pageviews = AnalyticsPageview::where('site_id', $siteId)
            ->where('session_id', $sessionId)
            ->orderBy('created_at')
            ->get(['url', 'path', 'title', 'created_at']);
            
        return response()->json([
            'success' => true,
            'session' => $session,
            'pageviews' => $pageviews
        ]);
    }
    
    public function export(Request $request, int $siteId)
    {
        $this->authorizeSite($siteId);
        $period = $request->get('period', '7d');
        $dates = $this->getDateRange($period);
        
        $data = AnalyticsDailyStat::where('site_id', $siteId)
            ->whereBetween('date', [$dates['current_start'], $dates['current_end']])
            ->orderBy('date')
            ->get();
            
        // Generate CSV
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="analytics-' . $siteId . '-' . $dates['current_start'] . '.csv"'
        ];
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Pageviews', 'Unique Visitors', 'Sessions', 'Bounce Rate', 'Avg Duration']);
            
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->date,
                    $row->pageviews,
                    $row->unique_visitors,
                    $row->sessions,
                    $row->bounce_rate . '%',
                    $row->avg_session_duration . 's'
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    // ==========================================
    // HELPER METHODS
    // ==========================================
    
    protected function authorizeSite(int $siteId): AnalyticsSite
    {
        $site = AnalyticsSite::findOrFail($siteId);
        
        // Check ownership - adjust based on your auth structure
        $clientId = auth()->user()->client_id ?? auth()->id();
        if ($site->client_id !== $clientId) {
            abort(403, 'Unauthorized access to this site');
        }
        
        return $site;
    }
    
    protected function getDateRange(string $period): array
    {
        $now = now();
        
        return match($period) {
            '1d' => [
                'current_start' => $now->copy()->subDay()->format('Y-m-d'),
                'current_end' => $now->format('Y-m-d'),
                'previous_start' => $now->copy()->subDays(2)->format('Y-m-d'),
                'previous_end' => $now->copy()->subDay()->format('Y-m-d')
            ],
            '7d' => [
                'current_start' => $now->copy()->subDays(7)->format('Y-m-d'),
                'current_end' => $now->format('Y-m-d'),
                'previous_start' => $now->copy()->subDays(14)->format('Y-m-d'),
                'previous_end' => $now->copy()->subDays(7)->format('Y-m-d')
            ],
            '30d' => [
                'current_start' => $now->copy()->subDays(30)->format('Y-m-d'),
                'current_end' => $now->format('Y-m-d'),
                'previous_start' => $now->copy()->subDays(60)->format('Y-m-d'),
                'previous_end' => $now->copy()->subDays(30)->format('Y-m-d')
            ],
            '90d' => [
                'current_start' => $now->copy()->subDays(90)->format('Y-m-d'),
                'current_end' => $now->format('Y-m-d'),
                'previous_start' => $now->copy()->subDays(180)->format('Y-m-d'),
                'previous_end' => $now->copy()->subDays(90)->format('Y-m-d')
            ],
            default => [
                'current_start' => $now->copy()->subDays(7)->format('Y-m-d'),
                'current_end' => $now->format('Y-m-d'),
                'previous_start' => $now->copy()->subDays(14)->format('Y-m-d'),
                'previous_end' => $now->copy()->subDays(7)->format('Y-m-d')
            ]
        };
    }
    
    protected function calculateChange($current, $previous): array
    {
        if ($previous == 0) return ['value' => 100, 'direction' => 'up'];
        $change = (($current - $previous) / $previous) * 100;
        return [
            'value' => round(abs($change), 2),
            'direction' => $change >= 0 ? 'up' : 'down'
        ];
    }
    
    protected function formatBreakdown(array $data): array
    {
        $total = array_sum($data);
        arsort($data);
        
        $result = [];
        foreach ($data as $name => $count) {
            $result[] = [
                'name' => $name,
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 2) : 0
            ];
        }
        return $result;
    }
    
    protected function getCountryName(string $code): string
    {
        $countries = [
            'US' => 'United States', 'IN' => 'India', 'GB' => 'United Kingdom',
            'CA' => 'Canada', 'AU' => 'Australia', 'DE' => 'Germany',
            'FR' => 'France', 'JP' => 'Japan', 'BR' => 'Brazil',
            'CN' => 'China', 'RU' => 'Russia', 'MX' => 'Mexico',
            'ES' => 'Spain', 'IT' => 'Italy', 'NL' => 'Netherlands',
            'SG' => 'Singapore', 'AE' => 'UAE', 'SA' => 'Saudi Arabia',
            'KR' => 'South Korea', 'ID' => 'Indonesia', 'TR' => 'Turkey',
            'PL' => 'Poland', 'SE' => 'Sweden', 'BE' => 'Belgium',
            'CH' => 'Switzerland', 'AT' => 'Austria', 'PT' => 'Portugal',
            'IE' => 'Ireland', 'DK' => 'Denmark', 'FI' => 'Finland',
            'NO' => 'Norway', 'NZ' => 'New Zealand', 'ZA' => 'South Africa',
            'AR' => 'Argentina', 'CO' => 'Colombia', 'CL' => 'Chile',
            'PE' => 'Peru', 'VE' => 'Venezuela', 'MY' => 'Malaysia',
            'PH' => 'Philippines', 'TH' => 'Thailand', 'VN' => 'Vietnam',
            'TW' => 'Taiwan', 'HK' => 'Hong Kong', 'IL' => 'Israel',
            'EG' => 'Egypt', 'NG' => 'Nigeria', 'KE' => 'Kenya',
            'MA' => 'Morocco', 'QA' => 'Qatar', 'KW' => 'Kuwait',
            'BH' => 'Bahrain', 'OM' => 'Oman', 'JO' => 'Jordan',
            'PK' => 'Pakistan', 'BD' => 'Bangladesh', 'LK' => 'Sri Lanka',
            'NP' => 'Nepal', 'MM' => 'Myanmar', 'KH' => 'Cambodia',
            'LA' => 'Laos', 'MN' => 'Mongolia', 'KZ' => 'Kazakhstan',
            'UZ' => 'Uzbekistan', 'AZ' => 'Azerbaijan', 'GE' => 'Georgia',
            'AM' => 'Armenia', 'BY' => 'Belarus', 'UA' => 'Ukraine',
            'RO' => 'Romania', 'BG' => 'Bulgaria', 'HU' => 'Hungary',
            'CZ' => 'Czech Republic', 'SK' => 'Slovakia', 'SI' => 'Slovenia',
            'HR' => 'Croatia', 'RS' => 'Serbia', 'BA' => 'Bosnia',
            'ME' => 'Montenegro', 'MK' => 'North Macedonia', 'AL' => 'Albania',
            'GR' => 'Greece', 'CY' => 'Cyprus', 'MT' => 'Malta',
            'LU' => 'Luxembourg', 'IS' => 'Iceland', 'LI' => 'Liechtenstein',
            'MC' => 'Monaco', 'SM' => 'San Marino', 'AD' => 'Andorra',
            'VA' => 'Vatican City', 'EE' => 'Estonia', 'LV' => 'Latvia',
            'LT' => 'Lithuania', 'MD' => 'Moldova', 'KG' => 'Kyrgyzstan',
            'TJ' => 'Tajikistan', 'TM' => 'Turkmenistan', 'AF' => 'Afghanistan',
            'IR' => 'Iran', 'IQ' => 'Iraq', 'SY' => 'Syria',
            'LB' => 'Lebanon', 'YE' => 'Yemen', 'PS' => 'Palestine',
            'ET' => 'Ethiopia', 'GH' => 'Ghana', 'TZ' => 'Tanzania',
            'UG' => 'Uganda', 'MZ' => 'Mozambique', 'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe', 'BW' => 'Botswana', 'NA' => 'Namibia',
            'SZ' => 'Eswatini', 'LS' => 'Lesotho', 'MG' => 'Madagascar',
            'MU' => 'Mauritius', 'SC' => 'Seychelles', 'KM' => 'Comoros',
            'CV' => 'Cape Verde', 'GW' => 'Guinea-Bissau', 'GN' => 'Guinea',
            'SL' => 'Sierra Leone', 'LR' => 'Liberia', 'CI' => 'Ivory Coast',
            'BF' => 'Burkina Faso', 'ML' => 'Mali', 'NE' => 'Niger',
            'TD' => 'Chad', 'MR' => 'Mauritania', 'SN' => 'Senegal',
            'GM' => 'Gambia', 'DJ' => 'Djibouti', 'ER' => 'Eritrea',
            'SO' => 'Somalia', 'SS' => 'South Sudan', 'SD' => 'Sudan',
            'CF' => 'Central African Republic', 'CM' => 'Cameroon', 'GQ' => 'Equatorial Guinea',
            'GA' => 'Gabon', 'CG' => 'Congo', 'CD' => 'DR Congo',
            'AO' => 'Angola', 'ST' => 'Sao Tome', 'BJ' => 'Benin',
            'TG' => 'Togo', 'AW' => 'Aruba', 'CW' => 'Curacao',
            'SX' => 'Sint Maarten', 'BQ' => 'Bonaire', 'AI' => 'Anguilla',
            'AG' => 'Antigua', 'BS' => 'Bahamas', 'BB' => 'Barbados',
            'BZ' => 'Belize', 'BM' => 'Bermuda', 'VG' => 'British Virgin Islands',
            'KY' => 'Cayman Islands', 'CR' => 'Costa Rica', 'CU' => 'Cuba',
            'DM' => 'Dominica', 'DO' => 'Dominican Republic', 'SV' => 'El Salvador',
            'GL' => 'Greenland', 'GD' => 'Grenada', 'GT' => 'Guatemala',
            'HT' => 'Haiti', 'HN' => 'Honduras', 'JM' => 'Jamaica',
            'MQ' => 'Martinique', 'MS' => 'Montserrat', 'NI' => 'Nicaragua',
            'PA' => 'Panama', 'PR' => 'Puerto Rico', 'BL' => 'Saint Barthelemy',
            'KN' => 'Saint Kitts', 'LC' => 'Saint Lucia', 'MF' => 'Saint Martin',
            'PM' => 'Saint Pierre', 'VC' => 'Saint Vincent', 'TT' => 'Trinidad',
            'TC' => 'Turks and Caicos', 'VI' => 'US Virgin Islands', 'AS' => 'American Samoa',
            'CK' => 'Cook Islands', 'FJ' => 'Fiji', 'PF' => 'French Polynesia',
            'GU' => 'Guam', 'KI' => 'Kiribati', 'MH' => 'Marshall Islands',
            'FM' => 'Micronesia', 'NR' => 'Nauru', 'NC' => 'New Caledonia',
            'NU' => 'Niue', 'NF' => 'Norfolk Island', 'MP' => 'Northern Mariana',
            'PW' => 'Palau', 'PG' => 'Papua New Guinea', 'WS' => 'Samoa',
            'SB' => 'Solomon Islands', 'TK' => 'Tokelau', 'TO' => 'Tonga',
            'TV' => 'Tuvalu', 'VU' => 'Vanuatu', 'WF' => 'Wallis and Futuna',
            'BO' => 'Bolivia', 'EC' => 'Ecuador', 'GY' => 'Guyana',
            'PY' => 'Paraguay', 'SR' => 'Suriname', 'UY' => 'Uruguay',
            'FK' => 'Falkland Islands', 'GF' => 'French Guiana', 'LO' => 'Local Network'
        ];
        
        return $countries[$code] ?? $code;
    }
}