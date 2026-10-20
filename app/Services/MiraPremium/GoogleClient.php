<?php

namespace App\Services\MiraPremium;

use App\Models\MiraPremium\MiraGoogleConnection;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Google OAuth 2.0 (authorization code + PKCE, offline access) and the three
 * read-only APIs Mira Premium uses: Search Console, Analytics Admin (to list
 * GA4 properties) and Analytics Data (reports).
 *
 * The OAuth state is HMAC-signed and names the client, project and return
 * URL, so the callback does not depend on the session cookie (the redirect
 * URI's host may differ from the host the user started on). Each state is
 * single-use: its PKCE verifier sits in the cache under the state's nonce and
 * is pulled on the callback.
 *
 * Access tokens are refreshed a minute before expiry; a refresh Google refuses
 * (access revoked in the Google account) marks the connection revoked.
 */
class GoogleClient
{
    private const AUTH_URL   = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL  = 'https://oauth2.googleapis.com/token';
    private const REVOKE_URL = 'https://oauth2.googleapis.com/revoke';
    private const GSC        = 'https://www.googleapis.com/webmasters/v3';
    private const ADMIN      = 'https://analyticsadmin.googleapis.com/v1beta';
    private const DATA       = 'https://analyticsdata.googleapis.com/v1beta';

    public const SCOPE_GSC = 'https://www.googleapis.com/auth/webmasters.readonly';
    public const SCOPE_GA  = 'https://www.googleapis.com/auth/analytics.readonly';

    public function isConfigured(): bool
    {
        return (string) config('services.google.client_id') !== '' && (string) config('services.google.client_secret') !== '';
    }

    // ------------------------------------------------------------------- oauth

    /** The Google consent URL for a client (and optionally the project to return to). */
    public function authorizationUrl(int $clientId, ?int $projectId, string $returnUrl): string
    {
        $nonce    = Str::random(32);
        $verifier = Str::random(64);
        Cache::put('mira:google:pkce:' . $nonce, $verifier, now()->addMinutes(15));

        return self::AUTH_URL . '?' . http_build_query([
            'client_id'              => config('services.google.client_id'),
            'redirect_uri'           => config('services.google.redirect'),
            'response_type'          => 'code',
            'scope'                  => implode(' ', (array) config('mira_premium.google.scopes')),
            'access_type'            => 'offline',
            'include_granted_scopes' => 'true',
            // consent every time, so Google always returns a refresh token
            'prompt'                 => 'consent',
            'state'                  => $this->signState(['c' => $clientId, 'p' => $projectId, 'r' => $returnUrl, 'n' => $nonce, 'e' => now()->addMinutes(15)->getTimestamp()]),
            'code_challenge'         => rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '='),
            'code_challenge_method'  => 'S256',
        ], '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Verify the callback's state and swap the code for tokens.
     *
     * @return array{state: array{c: int, p: ?int, r: string}, tokens: array}
     */
    public function exchange(string $state, string $code): array
    {
        $data = $this->readState($state);
        $verifier = Cache::pull('mira:google:pkce:' . $data['n']);
        if (!is_string($verifier)) {
            throw new MiraApiException('Expired', 'That Google sign-in link was already used or has expired. Please connect again.');
        }

        $res = Http::asForm()->timeout(20)->post(self::TOKEN_URL, [
            'client_id'     => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'code'          => $code,
            'code_verifier' => $verifier,
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => config('services.google.redirect'),
        ]);
        if (!$res->successful() || !$res->json('access_token')) {
            throw new MiraApiException('Unavailable', 'Google did not accept the sign-in (' . ($res->json('error_description') ?: $res->json('error') ?: 'HTTP ' . $res->status()) . ').');
        }

        return ['state' => $data, 'tokens' => $res->json()];
    }

    /** Store (or replace) a client's connection from a token response. */
    public function store(int $clientId, array $tokens): MiraGoogleConnection
    {
        $claims = $this->idTokenClaims((string) ($tokens['id_token'] ?? ''));
        $conn   = MiraGoogleConnection::firstOrNew(['client_id' => $clientId]);
        $conn->fill([
            'google_sub'    => $claims['sub'] ?? null,
            'email'         => $claims['email'] ?? null,
            'access_token'  => $tokens['access_token'],
            // Google omits the refresh token when it was already granted; keep the old one.
            'refresh_token' => $tokens['refresh_token'] ?? $conn->refresh_token,
            'expires_at'    => now()->addSeconds((int) ($tokens['expires_in'] ?? 3600)),
            'scopes'        => (string) ($tokens['scope'] ?? ''),
            'status'        => 'active',
            'connected_at'  => now(),
        ])->save();

        return $conn;
    }

    /** Best-effort revoke at Google, then forget the tokens. */
    public function disconnect(MiraGoogleConnection $conn): void
    {
        try {
            Http::asForm()->timeout(10)->post(self::REVOKE_URL, ['token' => $conn->refresh_token ?: $conn->access_token]);
        } catch (\Throwable) {
            // Already revoked or unreachable — the local tokens are deleted either way.
        }
        $conn->delete();
    }

    // -------------------------------------------------------------------- apis

    /** @return list<array{site_url: string, permission: string}> verified Search Console properties */
    public function sites(MiraGoogleConnection $conn): array
    {
        $rows = $this->call($conn, 'get', self::GSC . '/sites')->json('siteEntry') ?? [];

        return collect($rows)->filter(fn ($s) => ($s['permissionLevel'] ?? '') !== 'siteUnverifiedUser')
            ->map(fn ($s) => ['site_url' => (string) $s['siteUrl'], 'permission' => (string) ($s['permissionLevel'] ?? '')])
            ->sortBy('site_url')->values()->all();
    }

    /** @return list<array{property: string, name: string, account: string}> GA4 properties the account can read */
    public function properties(MiraGoogleConnection $conn): array
    {
        $out = [];
        $pageToken = null;
        do {
            $json = $this->call($conn, 'get', self::ADMIN . '/accountSummaries', array_filter(['pageSize' => 200, 'pageToken' => $pageToken]))->json();
            foreach ($json['accountSummaries'] ?? [] as $acc) {
                foreach ($acc['propertySummaries'] ?? [] as $p) {
                    $out[] = ['property' => (string) $p['property'], 'name' => (string) ($p['displayName'] ?? $p['property']), 'account' => (string) ($acc['displayName'] ?? '')];
                }
            }
            $pageToken = $json['nextPageToken'] ?? null;
        } while ($pageToken);

        return $out;
    }

    /** Search Console searchAnalytics.query → rows[{keys, clicks, impressions, ctr, position}]. */
    public function searchAnalytics(MiraGoogleConnection $conn, string $siteUrl, array $body): array
    {
        return $this->call($conn, 'post', self::GSC . '/sites/' . rawurlencode($siteUrl) . '/searchAnalytics/query', $body)->json('rows') ?? [];
    }

    /** Analytics Data runReport → rows as [dimension name => value, metric name => number]. */
    public function runReport(MiraGoogleConnection $conn, string $property, array $body): array
    {
        $json = $this->call($conn, 'post', self::DATA . '/' . $property . ':runReport', $body)->json();
        $dims = array_column($json['dimensionHeaders'] ?? [], 'name');
        $mets = array_column($json['metricHeaders'] ?? [], 'name');

        return array_map(function ($row) use ($dims, $mets) {
            $out = [];
            foreach ($dims as $i => $d) {
                $out[$d] = $row['dimensionValues'][$i]['value'] ?? null;
            }
            foreach ($mets as $i => $m) {
                $out[$m] = (float) ($row['metricValues'][$i]['value'] ?? 0);
            }

            return $out;
        }, $json['rows'] ?? []);
    }

    // ----------------------------------------------------------------- helpers

    private function call(MiraGoogleConnection $conn, string $method, string $url, array $data = [], bool $retried = false): Response
    {
        $req = Http::withToken($this->accessToken($conn))->acceptJson()->timeout(30);
        $res = $method === 'get' ? $req->get($url, $data) : $req->post($url, $data);

        if ($res->status() === 401 && !$retried) {
            $conn->forceFill(['expires_at' => now()->subMinute()])->save();

            return $this->call($conn, $method, $url, $data, true);
        }
        if (!$res->successful()) {
            // Google's own message is the most useful one ("API has not been used in project…",
            // "User does not have sufficient permission for site…").
            $msg = $res->json('error.message') ?: 'HTTP ' . $res->status();
            throw new MiraApiException('Unavailable', 'Google: ' . mb_substr((string) $msg, 0, 300), ['google_status' => $res->status()]);
        }

        return $res;
    }

    private function accessToken(MiraGoogleConnection $conn): string
    {
        if ($conn->status !== 'active') {
            throw new MiraApiException('Unavailable', 'Google access was revoked. Reconnect Google to continue.', ['reconnect' => true]);
        }
        if ($conn->expires_at && $conn->expires_at->gt(now()->addMinute())) {
            return $conn->access_token;
        }
        if (!$conn->refresh_token) {
            $conn->forceFill(['status' => 'revoked'])->save();
            throw new MiraApiException('Unavailable', 'Google access expired. Reconnect Google to continue.', ['reconnect' => true]);
        }

        $res = Http::asForm()->timeout(20)->post(self::TOKEN_URL, [
            'client_id'     => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'refresh_token' => $conn->refresh_token,
            'grant_type'    => 'refresh_token',
        ]);
        if ($res->json('error') === 'invalid_grant') {
            $conn->forceFill(['status' => 'revoked'])->save();
            throw new MiraApiException('Unavailable', 'Google access was revoked. Reconnect Google to continue.', ['reconnect' => true]);
        }
        if (!$res->successful() || !$res->json('access_token')) {
            throw new MiraApiException('Unavailable', 'Google is not responding right now. Please try again shortly.');
        }

        $conn->forceFill([
            'access_token' => $res->json('access_token'),
            'expires_at'   => now()->addSeconds((int) ($res->json('expires_in') ?? 3600)),
        ])->save();

        return $conn->access_token;
    }

    /** Claims of the id_token received straight from Google's token endpoint over TLS. */
    private function idTokenClaims(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return [];
        }
        $claims = json_decode((string) base64_decode(strtr($parts[1], '-_', '+/')), true);

        return is_array($claims) ? $claims : [];
    }

    private function signState(array $data): string
    {
        $payload = rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');

        return $payload . '.' . hash_hmac('sha256', $payload, $this->stateKey());
    }

    private function readState(string $state): array
    {
        $parts = explode('.', $state);
        if (count($parts) !== 2 || !hash_equals(hash_hmac('sha256', $parts[0], $this->stateKey()), $parts[1])) {
            throw new MiraApiException('InvalidSignature', 'That Google sign-in could not be verified. Please connect again.');
        }
        $data = json_decode((string) base64_decode(strtr($parts[0], '-_', '+/')), true);
        if (!is_array($data) || !isset($data['c'], $data['n'], $data['e'], $data['r'])) {
            throw new MiraApiException('InvalidSignature', 'That Google sign-in could not be verified. Please connect again.');
        }
        if ((int) $data['e'] < now()->getTimestamp()) {
            throw new MiraApiException('Expired', 'That Google sign-in took too long. Please connect again.');
        }

        return $data;
    }

    private function stateKey(): string
    {
        return 'mira-premium-google-state|' . config('app.key');
    }
}
