<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WebhookController extends Controller
{
    private ?string $analyticsUrl;
    private ?string $apiKey;
    private ?string $siteId;

    public function __construct()
    {
        $this->analyticsUrl = config('analytics.webhook_url');
        $this->apiKey = config('analytics.api_key');
        $this->siteId = config('analytics.site_id');
    }

    /**
     * Check if analytics is configured
     */
    private function isConfigured(): bool
    {
        return !empty($this->analyticsUrl) && !empty($this->apiKey) && !empty($this->siteId);
    }

    /**
     * Send event to analytics platform
     */
    public function sendEvent(string $eventType, array $data): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'X-Site-ID' => $this->siteId,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->analyticsUrl . '/api/v1/laravel-ingest', [
                    'site_id' => $this->siteId,
                    'event_type' => $eventType,
                    'data' => $data,
                    'timestamp' => now()->toDateTimeString(),
                ]);

            return $response->successful();

        } catch (\Exception $e) {
            \Log::error('Webhook failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Track user registration
     */
    public function trackUserRegistration($user): void
    {
        if (!$this->isConfigured()) return;

        $this->sendEvent('user_registration', [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at->toDateTimeString(),
        ]);
    }

    /**
     * Track user login
     */
    public function trackUserLogin($user): void
    {
        if (!$this->isConfigured()) return;

        $this->sendEvent('user_login', [
            'user_id' => $user->id,
            'email' => $user->email,
            'login_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Track user logout
     */
    public function trackUserLogout($user): void
    {
        if (!$this->isConfigured()) return;

        $this->sendEvent('user_logout', [
            'user_id' => $user->id,
            'email' => $user->email,
            'logout_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Track page view
     */
    public function trackPageView(Request $request): void
    {
        if (!$this->isConfigured()) return;

        $this->sendEvent('page_view', [
            'url' => $request->url(),
            'path' => $request->path(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->header('referer'),
            'user_id' => auth()->id(),
            'session_id' => $request->session()->getId(),
        ]);
    }

    /**
     * Track order
     */
    public function trackOrder($order): void
    {
        if (!$this->isConfigured()) return;

        $this->sendEvent('order_' . ($order->status ?? 'unknown'), [
            'order_id' => $order->id,
            'user_id' => $order->user_id ?? null,
            'total' => $order->total ?? 0,
            'status' => $order->status ?? 'unknown',
            'items_count' => $order->items->count() ?? 0,
            'created_at' => $order->created_at->toDateTimeString(),
        ]);
    }

    /**
     * Track custom event
     */
    public function trackCustomEvent(string $eventName, array $properties): void
    {
        if (!$this->isConfigured()) return;

        $this->sendEvent('custom_' . $eventName, $properties);
    }
}