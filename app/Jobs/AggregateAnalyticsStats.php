<?php
// app/Jobs/AggregateAnalyticsStats.php

namespace App\Jobs;

use App\Models\AnalyticsDailyStat;
use App\Models\AnalyticsHourlyStat;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AggregateAnalyticsStats implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public int $tries = 2;
    
    public function handle(): void
    {
        $this->aggregateHourly();
        $this->aggregateDaily();
    }
    
    protected function aggregateHourly(): void
    {
        $hour = now()->subHour()->format('Y-m-d H:00:00');
        $date = now()->subHour()->format('Y-m-d');
        $hourNum = now()->subHour()->format('H');
        
        // Get all active sites
        $siteIds = AnalyticsPageview::where('created_at', '>=', $hour)
            ->where('created_at', '<', now()->format('Y-m-d H:00:00'))
            ->distinct()
            ->pluck('site_id');
            
        foreach ($siteIds as $siteId) {
            $stats = $this->calculateStatsForPeriod($siteId, $hour, now()->format('Y-m-d H:00:00'));
            
            AnalyticsHourlyStat::updateOrCreate(
                ['site_id' => $siteId, 'date' => $date, 'hour' => $hourNum],
                $stats
            );
        }
    }
    
    protected function aggregateDaily(): void
    {
        $yesterday = now()->subDay()->format('Y-m-d');
        
        $siteIds = AnalyticsPageview::whereDate('created_at', $yesterday)
            ->distinct()
            ->pluck('site_id');
            
        foreach ($siteIds as $siteId) {
            $start = $yesterday . ' 00:00:00';
            $end = $yesterday . ' 23:59:59';
            
            $stats = $this->calculateStatsForPeriod($siteId, $start, $end);
            
            // Calculate bounce rate and avg duration from sessions
            $sessionStats = AnalyticsSession::where('site_id', $siteId)
                ->whereDate('started_at', $yesterday)
                ->selectRaw('
                    COUNT(*) as total_sessions,
                    SUM(CASE WHEN is_bounce = 1 THEN 1 ELSE 0 END) as bounce_sessions,
                    AVG(duration_seconds) as avg_duration
                ')
                ->first();
                
            $stats['bounce_sessions'] = $sessionStats->bounce_sessions ?? 0;
            $stats['avg_session_duration'] = round($sessionStats->avg_duration ?? 0, 2);
            $stats['bounce_rate'] = $sessionStats->total_sessions > 0 
                ? round(($sessionStats->bounce_sessions / $sessionStats->total_sessions) * 100, 2) 
                : 0;
            
            AnalyticsDailyStat::updateOrCreate(
                ['site_id' => $siteId, 'date' => $yesterday],
                $stats
            );
        }
    }
    
    protected function calculateStatsForPeriod(int $siteId, string $start, string $end): array
    {
        $pageviews = AnalyticsPageview::where('site_id', $siteId)
            ->where('created_at', '>=', $start)
            ->where('created_at', '<', $end);
            
        return [
            'pageviews' => $pageviews->count(),
            'unique_visitors' => $pageviews->distinct('visitor_id')->count('visitor_id'),
            'sessions' => AnalyticsSession::where('site_id', $siteId)
                ->where('started_at', '>=', $start)
                ->where('started_at', '<', $end)
                ->count(),
            'countries' => $this->aggregateDimension($pageviews, 'country'),
            'devices' => $this->aggregateDimension($pageviews, 'device_type'),
            'browsers' => $this->aggregateDimension($pageviews, 'browser'),
            'oses' => $this->aggregateDimension($pageviews, 'os'),
            'pages' => $this->aggregateTopPages($pageviews),
            'referrers' => $this->aggregateDimension($pageviews, 'referrer_domain'),
            'utm_sources' => $this->aggregateDimension($pageviews, 'utm_source')
        ];
    }
    
    protected function aggregateDimension($query, string $column): array
    {
        return $query->clone()
            ->select($column, DB::raw('COUNT(*) as count'))
            ->whereNotNull($column)
            ->groupBy($column)
            ->orderByDesc('count')
            ->limit(20)
            ->pluck('count', $column)
            ->toArray();
    }
    
    protected function aggregateTopPages($query): array
    {
        return $query->clone()
            ->select('path', DB::raw('COUNT(*) as count'))
            ->groupBy('path')
            ->orderByDesc('count')
            ->limit(20)
            ->pluck('count', 'path')
            ->toArray();
    }
}