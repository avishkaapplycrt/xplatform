<?php

namespace App\Services\MiraPremium;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Minimal DataForSEO v3 client: one live task per call, HTTP Basic auth,
 * returns tasks[0].result[0]. Sandbox mode (config mira_premium.dataforseo.sandbox)
 * hits sandbox.dataforseo.com — free, identical response shape, dummy data.
 */
class DataForSeoClient
{
    public function isConfigured(): bool
    {
        return $this->isDemo() || ((string) config('mira_premium.dataforseo.login') !== ''
            && (string) config('mira_premium.dataforseo.password') !== '');
    }

    /** Sample data mode: generated locally, never in production, and only while no real login is set. */
    public function isDemo(): bool
    {
        return (bool) config('mira_premium.dataforseo.demo') && !app()->environment('production')
            && (string) config('mira_premium.dataforseo.login') === '';
    }

    public function isSandbox(): bool
    {
        return (bool) config('mira_premium.dataforseo.sandbox');
    }

    /**
     * @param string $path e.g. "backlinks/summary/live"
     * @return array tasks[0].result[0] (empty array when the provider has no data for the target)
     *
     * @throws MiraApiException Unavailable on transport / auth / task errors
     */
    public function live(string $path, array $task): array
    {
        if (!$this->isConfigured()) {
            throw new MiraApiException('Unavailable', 'DataForSEO is not connected.');
        }
        if ($this->isDemo()) {
            return DemoSeoData::result($path, $task);
        }

        $base = $this->isSandbox() ? 'https://sandbox.dataforseo.com/v3/' : 'https://api.dataforseo.com/v3/';

        try {
            $res = Http::withBasicAuth((string) config('mira_premium.dataforseo.login'), (string) config('mira_premium.dataforseo.password'))
                ->acceptJson()->timeout(45)
                ->post($base . ltrim($path, '/'), [$task]);
        } catch (\Throwable $e) {
            Log::warning('DataForSEO request failed', ['path' => $path, 'message' => $e->getMessage()]);
            throw new MiraApiException('Unavailable', 'The SEO data provider could not be reached. Try again shortly.');
        }

        // Every live call is billed; log what it cost so spend per project stays visible.
        if (!$this->isSandbox() && $res->json('cost') !== null) {
            Log::info('DataForSEO cost', ['path' => $path, 'target' => $task['target'] ?? null, 'cost' => (float) $res->json('cost')]);
        }

        $taskData = $res->json('tasks.0');
        // 20000 = ok at the request level; each task carries its own status (20000 ok, 40102 = no results).
        if (!$res->successful() || (int) $res->json('status_code') !== 20000 || !is_array($taskData)) {
            Log::warning('DataForSEO error', ['path' => $path, 'status' => $res->status(), 'message' => $res->json('status_message')]);
            throw new MiraApiException('Unavailable', 'The SEO data provider returned an error: ' . ($res->json('status_message') ?? 'HTTP ' . $res->status()));
        }

        $status = (int) ($taskData['status_code'] ?? 0);
        if ($status === 40102) {
            return [];
        }
        if ($status !== 20000) {
            Log::warning('DataForSEO task error', ['path' => $path, 'status' => $status, 'message' => $taskData['status_message'] ?? null]);
            throw new MiraApiException('Unavailable', 'The SEO data provider returned an error: ' . ($taskData['status_message'] ?? $status));
        }

        return $taskData['result'][0] ?? [];
    }
}
