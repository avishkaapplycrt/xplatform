<?php

namespace App\Services;

use App\Models\BehavioralProfile;
use App\Models\CrmConnection;
use App\Models\UserEvent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HubSpotService
{
    private const BASE_URL  = 'https://api.hubapi.com';
    private const AUTH_URL  = 'https://app.hubspot.com/oauth/authorize';
    private const TOKEN_URL = 'https://api.hubapi.com/oauth/v1/token';

    // Contact properties to pull on every sync
    private const CONTACT_PROPERTIES = [
        'email',
        'firstname',
        'lastname',
        'hs_last_sales_activity_date',
        'notes_last_contacted',
        'hs_email_last_open_date',
        'hs_email_last_click_date',
        'num_unique_conversion_events',
        'num_contacted_notes',
        'hs_sequences_enrolled_count',
    ];

    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $redirectUri,
    ) {}

    // ── OAuth ─────────────────────────────────────────────────────────────────

    public function getAuthorizationUrl(string $state): string
    {
        return self::AUTH_URL . '?' . http_build_query([
            'client_id'    => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope'        => 'crm.objects.contacts.read crm.objects.deals.read',
            'state'        => $state,
        ]);
    }

    public function exchangeCode(string $code): array
    {
        $response = Http::asForm()->post(self::TOKEN_URL, [
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
            'code'          => $code,
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('HubSpot token exchange failed: ' . $response->body());
        }

        return $response->json();
    }

    public function refreshToken(CrmConnection $connection): void
    {
        $response = Http::asForm()->post(self::TOKEN_URL, [
            'grant_type'    => 'refresh_token',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $connection->refresh_token,
        ]);

        if (! $response->successful()) {
            $connection->update(['status' => 'error', 'last_error' => 'Token refresh failed']);
            throw new \RuntimeException('HubSpot token refresh failed: ' . $response->body());
        }

        $data = $response->json();

        $connection->update([
            'access_token'     => $data['access_token'],
            'refresh_token'    => $data['refresh_token'] ?? null,
            'token_expires_at' => now()->addSeconds($data['expires_in'] ?? 1800),
            'status'           => 'connected',
            'last_error'       => null,
        ]);
    }

    // Returns hub_domain and hub_id from the token info endpoint
    public function getAccountInfo(string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->get(self::BASE_URL . '/oauth/v1/access-tokens/' . $accessToken);

        return $response->successful() ? $response->json() : [];
    }

    // ── Contact Sync ──────────────────────────────────────────────────────────

    /**
     * Fetches all HubSpot contacts and maps them to BehavioralProfiles +
     * UserEvents for the given client. Returns the count of contacts synced.
     *
     * Events are created without the observer so the queue isn't flooded;
     * one RefreshUserScore job per unique email is dispatched at the end.
     */
    public function syncContacts(CrmConnection $connection): int
    {
        $this->ensureFreshToken($connection);

        $synced  = 0;
        $after   = null;
        $emails  = [];

        do {
            $params = [
                'limit'      => 100,
                'properties' => implode(',', self::CONTACT_PROPERTIES),
            ];

            if ($after) {
                $params['after'] = $after;
            }

            $response = Http::withToken($connection->access_token)
                ->get(self::BASE_URL . '/crm/v3/objects/contacts', $params);

            if (! $response->successful()) {
                Log::error('HubSpot contacts fetch failed', [
                    'client_id' => $connection->client_id,
                    'status'    => $response->status(),
                    'body'      => $response->body(),
                ]);
                break;
            }

            $data = $response->json();

            // Bypass observer so we don't fire RefreshUserScore 100× per batch
            UserEvent::withoutEvents(function () use ($data, $connection, &$synced, &$emails) {
                foreach ($data['results'] ?? [] as $contact) {
                    $email = $this->upsertContact($contact, $connection->client_id);
                    if ($email) {
                        $emails[] = $email;
                        $synced++;
                    }
                }
            });

            $after = $data['paging']['next']['after'] ?? null;

        } while ($after);

        // Dispatch one score-refresh job per unique email after all events are written
        foreach (array_unique($emails) as $email) {
            \App\Jobs\RefreshUserScore::dispatch($email)->delay(now()->addSeconds(5));
        }

        return $synced;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function ensureFreshToken(CrmConnection $connection): void
    {
        if ($connection->isTokenExpired() && $connection->refresh_token) {
            $this->refreshToken($connection);
            $connection->refresh();
        }
    }

    private function upsertContact(array $contact, int $clientId): ?string
    {
        $props = $contact['properties'] ?? [];
        $email = $props['email'] ?? null;

        if (! $email) return null;

        $name = trim(($props['firstname'] ?? '') . ' ' . ($props['lastname'] ?? '')) ?: $email;

        // Determine last active date from whichever HubSpot field is populated
        $lastActive = null;
        foreach (['hs_last_sales_activity_date', 'notes_last_contacted', 'hs_email_last_open_date'] as $field) {
            if (! empty($props[$field])) {
                $lastActive = \Carbon\Carbon::parse($props[$field]);
                break;
            }
        }

        $profile = BehavioralProfile::firstOrNew([
            'client_id' => $clientId,
            'email'     => $email,
        ]);

        if (! $profile->exists) {
            $profile->name                  = $name;
            $profile->segment               = 'new';
            $profile->intent_score          = 0;
            $profile->engagement_score      = 0;
            $profile->churn_score           = 0;
            $profile->loyalty_score         = 0;
            $profile->trust_score           = 0;
            $profile->frustration_score     = 0;
            $profile->buying_readiness      = 0;
            $profile->dropoff_risk          = 0;
            $profile->reactivation_potential = 0;
            $profile->overall_score         = 0;
        }

        $profile->last_active_at = $lastActive ?? $profile->last_active_at ?? now()->subDays(30);
        $profile->save();

        $this->writeEvents($props, $profile);

        return $email;
    }

    /**
     * Maps HubSpot contact properties to UserEvent rows.
     * Uses firstOrCreate so re-syncing never duplicates events.
     */
    private function writeEvents(array $props, BehavioralProfile $profile): void
    {
        $base = [
            'client_id'             => $profile->client_id,
            'behavioral_profile_id' => $profile->id,
            'email'                 => $profile->email,
        ];

        // Email open / click
        if (! empty($props['hs_email_last_open_date'])) {
            UserEvent::firstOrCreate(array_merge($base, ['event_type' => 'email_open']));
        }

        if (! empty($props['hs_email_last_click_date'])) {
            UserEvent::firstOrCreate(array_merge($base, ['event_type' => 'email_click']));
        }

        // Conversions → checkout_complete
        $conversions = min((int) ($props['num_unique_conversion_events'] ?? 0), 5);
        for ($i = 0; $i < $conversions; $i++) {
            UserEvent::create(array_merge($base, ['event_type' => 'checkout_complete']));
        }

        // HubSpot-contacted notes → support_ticket (capped to avoid over-scoring)
        $contacts = min((int) ($props['num_contacted_notes'] ?? 0), 3);
        for ($i = 0; $i < $contacts; $i++) {
            UserEvent::create(array_merge($base, ['event_type' => 'support_ticket']));
        }

        // Enrolled in sequences → intent signal
        if ((int) ($props['hs_sequences_enrolled_count'] ?? 0) > 0) {
            UserEvent::firstOrCreate(array_merge($base, ['event_type' => 'pricing_view']));
        }
    }
}
