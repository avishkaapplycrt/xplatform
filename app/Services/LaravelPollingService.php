<?php

namespace App\Services;

use App\Models\LaravelSite;
use App\Models\LaravelEvent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LaravelPollingService
{
    public function pollSite(LaravelSite $site): array
    {
        if (!$site->is_active) {
            return ['skipped' => true, 'reason' => 'Site inactive'];
        }

        $credentials = $site->decrypted_credentials;
        $results = [
            'site_id' => $site->id,
            'site_name' => $site->site_name,
            'events_stored' => 0,
            'errors' => [],
        ];

        try {
            $client = Http::timeout(30)
                ->withToken($credentials['api_token'])
                ->baseUrl($site->site_url);

            // 1. Get users
            $usersResult = $this->pollUsers($site, $client);
            $results['events_stored'] += $usersResult['stored'];

            // 2. Get page views
            $viewsResult = $this->pollPageViews($site, $client);
            $results['events_stored'] += $viewsResult['stored'];

            // 3. Get orders (if e-commerce)
            $ordersResult = $this->pollOrders($site, $client);
            $results['events_stored'] += $ordersResult['stored'];

            // 4. Get custom events
            $eventsResult = $this->pollEvents($site, $client);
            $results['events_stored'] += $eventsResult['stored'];

            $site->update(['last_sync_at' => now()]);

        } catch (\Exception $e) {
            $results['errors'][] = $e->getMessage();
            Log::error("Laravel poll failed for {$site->site_name}: " . $e->getMessage());
        }

        return $results;
    }

    private function pollUsers(LaravelSite $site, $client): array
    {
        $since = $site->last_sync_at ?? Carbon::now()->subDays(7);
        
        $response = $client->get('/api/analytics/users', [
            'since' => $since->toDateTimeString(),
        ]);

        if (!$response->successful()) {
            throw new \Exception("Users API failed: " . $response->body());
        }

        $data = $response->json();
        $users = $data['users'] ?? [];
        
        $events = [];
        foreach ($users as $user) {
            $events[] = [
                'site_id' => $site->id,
                'event_type' => 'user_registration',
                'entity_id' => $user['id'],
                'payload' => json_encode($user),
                'event_created_at' => Carbon::parse($user['created_at'])->toDateTimeString(),
                'synced_at' => now(),
            ];
        }

        if (!empty($events)) {
            LaravelEvent::insert($events);
        }

        return ['stored' => count($events)];
    }

    private function pollPageViews(LaravelSite $site, $client): array
    {
        $since = $site->last_sync_at ?? Carbon::now()->subDays(7);
        
        $response = $client->get('/api/analytics/page-views', [
            'since' => $since->toDateTimeString(),
        ]);

        if (!$response->successful()) {
            return ['stored' => 0, 'note' => 'Page views endpoint unavailable'];
        }

        $data = $response->json();
        $views = $data['views'] ?? [];
        
        $events = [];
        foreach ($views as $view) {
            $events[] = [
                'site_id' => $site->id,
                'event_type' => 'page_view',
                'entity_id' => null,
                'payload' => json_encode($view),
                'event_created_at' => Carbon::parse($view['date'])->toDateTimeString(),
                'synced_at' => now(),
            ];
        }

        if (!empty($events)) {
            LaravelEvent::insert($events);
        }

        return ['stored' => count($events)];
    }

    private function pollOrders(LaravelSite $site, $client): array
    {
        $since = $site->last_sync_at ?? Carbon::now()->subDays(7);
        
        $response = $client->get('/api/analytics/orders', [
            'since' => $since->toDateTimeString(),
        ]);

        if (!$response->successful()) {
            return ['stored' => 0, 'note' => 'Orders endpoint unavailable'];
        }

        $data = $response->json();
        $orders = $data['orders'] ?? [];
        
        $events = [];
        foreach ($orders as $order) {
            $events[] = [
                'site_id' => $site->id,
                'event_type' => 'order_' . ($order['status'] ?? 'unknown'),
                'entity_id' => $order['id'],
                'payload' => json_encode($order),
                'event_created_at' => Carbon::parse($order['created_at'])->toDateTimeString(),
                'synced_at' => now(),
            ];
        }

        if (!empty($events)) {
            LaravelEvent::insert($events);
        }

        return ['stored' => count($events)];
    }

    private function pollEvents(LaravelSite $site, $client): array
    {
        $since = $site->last_sync_at ?? Carbon::now()->subDays(7);
        
        $response = $client->get('/api/analytics/events', [
            'since' => $since->toDateTimeString(),
        ]);

        if (!$response->successful()) {
            return ['stored' => 0, 'note' => 'Events endpoint unavailable'];
        }

        $data = $response->json();
        $events = $data['events'] ?? [];
        
        $insertData = [];
        foreach ($events as $event) {
            $insertData[] = [
                'site_id' => $site->id,
                'event_type' => $event['event_type'] ?? 'custom',
                'entity_id' => $event['id'] ?? null,
                'payload' => json_encode($event),
                'event_created_at' => Carbon::parse($event['created_at'])->toDateTimeString(),
                'synced_at' => now(),
            ];
        }

        if (!empty($insertData)) {
            LaravelEvent::insert($insertData);
        }

        return ['stored' => count($insertData)];
    }
}