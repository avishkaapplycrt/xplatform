<?php

namespace App\Services\MiraPremium;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Minimal Google Analytics 4 Data API client (read-only), signed in as a
 * Google Cloud service account (config mira_premium.ga4.credentials → the
 * account's JSON key file). No SDK: the OAuth access token comes from a
 * self-signed JWT (RS256, via OpenSSL) and is cached until shortly before it
 * expires. A GA4 property is readable once the client has added the service
 * account's email to it as a Viewer.
 */
class Ga4Client
{
    private const TOKEN_URL  = 'https://oauth2.googleapis.com/token';
    private const SCOPE      = 'https://www.googleapis.com/auth/analytics.readonly';
    private const REPORT_URL = 'https://analyticsdata.googleapis.com/v1beta/properties/%s:runReport';

    private ?array $key = null;

    /** Whether a usable service-account key is configured. */
    public function isConfigured(): bool
    {
        return $this->key() !== null;
    }

    /** The email a client adds as a Viewer on their GA4 property, or null. */
    public function serviceAccountEmail(): ?string
    {
        return $this->key()['client_email'] ?? null;
    }

    /**
     * runReport for one GA4 property.
     *
     * @param array $body the Data API request: dateRanges, dimensions, metrics, orderBys, limit
     *
     * @return array the API response (rows, totals, …)
     *
     * @throws MiraApiException InvalidInput when the property can't be read, Unavailable otherwise
     */
    public function runReport(string $propertyId, array $body): array
    {
        if (!$this->isConfigured()) {
            throw new MiraApiException('Unavailable', 'Google Analytics is not connected.');
        }

        try {
            $res = Http::withToken($this->accessToken())->acceptJson()->timeout(20)
                ->post(sprintf(self::REPORT_URL, $propertyId), $body);
        } catch (\Throwable $e) {
            throw new MiraApiException('Unavailable', 'Google Analytics could not be reached. Try again shortly.');
        }

        if ($res->status() === 403 || $res->status() === 404) {
            throw new MiraApiException('InvalidInput', "Mira can't read GA4 property {$propertyId}. Check the property ID, and that "
                . $this->serviceAccountEmail() . ' has Viewer access to it (GA4 → Admin → Property access management).');
        }
        if ($res->status() === 400) {
            throw new MiraApiException('InvalidInput', 'Google Analytics rejected the request: ' . ($res->json('error.message') ?? 'invalid property ID') . '.');
        }
        if (!$res->successful()) {
            Log::warning('GA4 runReport failed', ['status' => $res->status(), 'message' => $res->json('error.message')]);
            throw new MiraApiException('Unavailable', 'Google Analytics returned an error. Try again shortly.');
        }

        return $res->json() ?? [];
    }

    /** OAuth access token for the service account, cached until 5 minutes before expiry. */
    private function accessToken(): string
    {
        $key = $this->key();
        $cacheKey = 'mira_ga4_token:' . md5($key['client_email']);

        if ($token = Cache::get($cacheKey)) {
            return $token;
        }

        $now = time();
        $b64 = fn (string $s) => rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
        $unsigned = $b64(json_encode(['alg' => 'RS256', 'typ' => 'JWT']))
            . '.' . $b64(json_encode([
                'iss'   => $key['client_email'],
                'scope' => self::SCOPE,
                'aud'   => self::TOKEN_URL,
                'iat'   => $now,
                'exp'   => $now + 3600,
            ]));
        if (!openssl_sign($unsigned, $signature, $key['private_key'], OPENSSL_ALGO_SHA256)) {
            throw new MiraApiException('Unavailable', 'The Google Analytics key could not be used.');
        }

        $res = Http::asForm()->timeout(15)->post(self::TOKEN_URL, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $unsigned . '.' . $b64($signature),
        ]);
        if (!$res->successful() || !$res->json('access_token')) {
            Log::warning('GA4 token request failed', ['status' => $res->status(), 'error' => $res->json('error_description') ?? $res->json('error')]);
            throw new MiraApiException('Unavailable', 'Google Analytics sign-in failed. Check the service account key.');
        }

        Cache::put($cacheKey, $res->json('access_token'), max(60, (int) $res->json('expires_in', 3600) - 300));

        return $res->json('access_token');
    }

    /** The service-account key ({client_email, private_key, …}) or null when missing / unreadable. */
    private function key(): ?array
    {
        if ($this->key !== null) {
            return $this->key;
        }
        $path = (string) config('mira_premium.ga4.credentials');
        if ($path === '') {
            return null;
        }
        if (!is_file($path)) {
            $path = base_path($path);   // allow a path relative to the project
        }
        $key = is_readable($path) ? json_decode((string) file_get_contents($path), true) : null;

        return $this->key = (is_array($key) && !empty($key['client_email']) && !empty($key['private_key'])) ? $key : null;
    }
}
