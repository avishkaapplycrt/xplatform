<?php

namespace App\Services;

use App\Models\SocialIntegration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Keeps each client's Instagram connection alive. Long-lived Instagram tokens last about
 * 60 days; refreshing one (it must be at least 24 hours old) returns a new 60-day token.
 * Used daily by `php artisan instagram:refresh-tokens` and before "Sync Now".
 */
class InstagramTokenRefresher
{
    /** Refresh when the token expires within $days; returns true if the stored token was renewed. */
    public function refreshIfExpiring(SocialIntegration $connection, int $days = 10): bool
    {
        if ($connection->platform !== 'instagram' || $connection->status !== 'connected' || !$connection->access_token) {
            return false;
        }
        if ($connection->token_expires_at && $connection->token_expires_at->gt(now()->addDays($days))) {
            return false;
        }
        if ($connection->is_expired) {
            // Too late to refresh; the Connections page already flags expired tokens.
            $connection->update(['last_error' => 'Instagram access expired. Please reconnect Instagram.']);
            return false;
        }

        try {
            $response = Http::get('https://graph.instagram.com/refresh_access_token', [
                'grant_type'   => 'ig_refresh_token',
                'access_token' => Crypt::decryptString($connection->access_token),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Instagram token refresh failed', ['connection_id' => $connection->id, 'message' => $e->getMessage()]);
            return false;
        }

        $token = $response->json('access_token');
        if ($response->failed() || !$token) {
            // Too new to refresh (under 24 hours) or revoked — keep the current token; record why.
            $error = $response->json('error.message') ?? 'Instagram token refresh failed.';
            Log::warning('Instagram token refresh refused', ['connection_id' => $connection->id, 'error' => $error]);
            $connection->update(['last_error' => 'Token refresh: ' . $error]);
            return false;
        }

        $connection->update([
            'access_token'     => Crypt::encryptString($token),
            'token_expires_at' => now()->addSeconds((int) ($response->json('expires_in') ?: 60 * 86400)),
            'last_error'       => null,
        ]);

        return true;
    }
}
