<?php
// app/Jobs/ProcessAnalyticsPageview.php

namespace App\Jobs;

use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ProcessAnalyticsPageview implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public int $tries = 3;
    public int $timeout = 60;
    
    public function handle(): void
    {
        // Batch process from Redis queue
        $batch = [];
        $batchSize = 100;
        
        for ($i = 0; $i < $batchSize; $i++) {
            $item = Redis::rpop('analytics:pageviews:queue');
            if (!$item) break;
            $batch[] = json_decode($item, true);
        }
        
        if (empty($batch)) return;
        
        try {
            DB::transaction(function() use ($batch) {
                // Insert pageviews
                AnalyticsPageview::insert($batch);
                
                // Update sessions
                $this->updateSessions($batch);
            });
            
            // Dispatch aggregation job
            dispatch(new AggregateAnalyticsStats());
            
        } catch (\Exception $e) {
            Log::error('Analytics batch processing failed', ['error' => $e->getMessage()]);
            // Re-queue failed items
            foreach ($batch as $item) {
                Redis::lpush('analytics:pageviews:queue:failed', json_encode($item));
            }
        }
    }
    
    protected function updateSessions(array $pageviews): void
    {
        $sessionUpdates = [];
        
        foreach ($pageviews as $pv) {
            $sessionId = $pv['session_id'];
            $sessionUpdates[$sessionId] = [
                'site_id' => $pv['site_id'],
                'session_id' => $sessionId,
                'visitor_id' => $pv['visitor_id'],
                'first_page' => $pv['url'],
                'last_page' => $pv['url'],
                'referrer' => $pv['referrer'],
                'country' => $pv['country'],
                'device_type' => $pv['device_type'],
                'browser' => $pv['browser'],
                'os' => $pv['os'],
                'pageviews' => DB::raw('pageviews + 1'),
                'started_at' => $pv['created_at'],
                'ended_at' => $pv['created_at']
            ];
        }
        
        foreach ($sessionUpdates as $sessionId => $data) {
            AnalyticsSession::updateOrCreate(
                ['session_id' => $sessionId],
                $data
            );
        }
    }
}