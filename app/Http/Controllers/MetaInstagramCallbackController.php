<?php

namespace App\Http\Controllers;

use App\Models\InstagramAccountInsight;
use App\Models\InstagramMedia;
use App\Models\InstagramMessage;
use App\Models\SocialIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Callbacks Meta requires before the Instagram app can go Live, set in the Meta app under
 * Instagram → API setup with Instagram login → Business login settings:
 *   Deauthorize callback URL   → POST /meta/instagram/deauthorize
 *   Data deletion request URL  → POST /meta/instagram/data-deletion
 * Meta signs each call (signed_request) with the app secret; unsigned or wrongly signed
 * calls are rejected. They run outside the client login, so they are CSRF-exempt.
 */
class MetaInstagramCallbackController extends Controller
{
    /** Someone removed XPlatform from their Instagram settings: stop using their token. */
    public function deauthorize(Request $request)
    {
        $userId = $this->verifiedUserId($request);
        if ($userId === null) {
            return response()->json(['error' => 'Invalid signed_request'], 400);
        }

        $n = $this->connectionsFor($userId)->each(function (SocialIntegration $c) {
            $c->update([
                'status'           => 'disconnected',
                'access_token'     => null,
                'refresh_token'    => null,
                'token_expires_at' => null,
                'last_error'       => 'Access was removed from Instagram. Connect Instagram again to resume syncing.',
            ]);
        })->count();

        Log::info('Instagram deauthorize callback', ['instagram_user_id' => $userId, 'connections' => $n]);

        return response()->json(['success' => true]);
    }

    /** Someone asked Meta to delete their data: remove their connection and everything synced from it. */
    public function dataDeletion(Request $request)
    {
        $userId = $this->verifiedUserId($request);
        if ($userId === null) {
            return response()->json(['error' => 'Invalid signed_request'], 400);
        }

        $connections = $this->connectionsFor($userId);
        $ids = $connections->pluck('id')->all();
        if ($ids) {
            InstagramMedia::whereIn('social_integration_id', $ids)->delete();
            InstagramAccountInsight::whereIn('social_integration_id', $ids)->delete();
            InstagramMessage::whereIn('social_integration_id', $ids)->delete();
            $connections->each(fn (SocialIntegration $c) => $c->forceDelete());
        }

        $code = Str::upper(Str::random(12));
        Cache::forever('instagram_data_deletion:' . $code, ['completed_at' => now()->toDateTimeString(), 'connections' => count($ids)]);
        Log::info('Instagram data deletion callback', ['instagram_user_id' => $userId, 'connections' => count($ids), 'code' => $code]);

        return response()->json([
            'url'               => route('meta.instagram.data-deletion.status', ['code' => $code]),
            'confirmation_code' => $code,
        ]);
    }

    /** Status page Meta links the person to after a deletion request. */
    public function deletionStatus(string $code)
    {
        $record = Cache::get('instagram_data_deletion:' . strtoupper($code));

        return response()->view('meta.instagram-data-deletion', ['code' => strtoupper($code), 'record' => $record], $record ? 200 : 404);
    }

    /** Instagram user id from a valid signed_request, or null. */
    private function verifiedUserId(Request $request): ?string
    {
        $signed = (string) $request->input('signed_request', '');
        $secret = (string) config('services.instagram.client_secret');
        if ($signed === '' || $secret === '' || !str_contains($signed, '.')) {
            return null;
        }

        [$sig, $payload] = explode('.', $signed, 2);
        $decode = fn (string $v) => base64_decode(strtr($v, '-_', '+/') . str_repeat('=', (4 - strlen($v) % 4) % 4));
        if (!hash_equals(hash_hmac('sha256', $payload, $secret, true), $decode($sig))) {
            Log::warning('Instagram callback: bad signature');
            return null;
        }

        $data = json_decode($decode($payload), true);
        $userId = $data['user_id'] ?? null;

        return $userId !== null && $userId !== '' ? (string) $userId : null;
    }

    /** Every client's connection to this Instagram account (it may be connected by more than one). */
    private function connectionsFor(string $userId)
    {
        return SocialIntegration::withTrashed()->where('platform', 'instagram')
            ->where(fn ($q) => $q->where('account_id', $userId)->orWhere('settings->app_user_id', $userId))
            ->get();
    }
}
