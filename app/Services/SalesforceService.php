<?php

namespace App\Services;

use App\Jobs\RefreshUserScore;
use App\Models\BehavioralProfile;
use App\Models\CrmConnection;
use App\Models\UserEvent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SalesforceService
{
    private const AUTH_URL    = 'https://login.salesforce.com/services/oauth2/authorize';
    private const TOKEN_URL   = 'https://login.salesforce.com/services/oauth2/token';
    private const API_VERSION = 'v58.0';

    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $redirectUri,
    ) {}

    // ── OAuth ─────────────────────────────────────────────────────────────────

    public function getAuthorizationUrl(string $state): string
    {
        return self::AUTH_URL . '?' . http_build_query([
            'response_type' => 'code',
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->redirectUri,
            'scope'         => 'api refresh_token offline_access',
            'state'         => $state,
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
            throw new \RuntimeException('Salesforce token exchange failed: ' . $response->body());
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
            throw new \RuntimeException('Salesforce token refresh failed: ' . $response->body());
        }

        $data = $response->json();

        $connection->update([
            'access_token'     => $data['access_token'],
            'instance_url'     => $data['instance_url'] ?? $connection->instance_url,
            'token_expires_at' => now()->addSeconds(7200),
            'status'           => 'connected',
            'last_error'       => null,
        ]);
    }

    public function getOrganizationName(string $accessToken, string $instanceUrl): string
    {
        $response = Http::withToken($accessToken)
            ->get($instanceUrl . '/services/data/' . self::API_VERSION . '/query?' . http_build_query([
                'q' => 'SELECT Name FROM Organization LIMIT 1',
            ]));

        return $response->successful()
            ? ($response->json()['records'][0]['Name'] ?? 'Salesforce')
            : 'Salesforce';
    }

    // ── Record Sync ───────────────────────────────────────────────────────────

    /**
     * Pulls Opportunities (via OpportunityContactRole) and Cases from Salesforce,
     * maps them to BehavioralProfile + UserEvent records, then triggers score recalculation.
     * Returns the total number of contacts synced.
     */
    public function syncRecords(CrmConnection $connection): int
    {
        $this->ensureFreshToken($connection);

        $emails = [];
        $synced = 0;

        $synced += $this->syncOpportunities($connection, $emails);
        $synced += $this->syncCases($connection, $emails);

        foreach (array_unique($emails) as $email) {
            RefreshUserScore::dispatch($email)->delay(now()->addSeconds(5));
        }

        return $synced;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function syncOpportunities(CrmConnection $connection, array &$emails): int
    {
        // OpportunityContactRole is the standard join object for Contact ↔ Opportunity
        $soql = 'SELECT Contact.Email, Contact.Name, Opportunity.StageName, ' .
                'Opportunity.Amount, Opportunity.CreatedDate ' .
                'FROM OpportunityContactRole ' .
                'WHERE Contact.Email != null ' .
                'AND IsPrimary = true ' .
                'AND Opportunity.CreatedDate >= LAST_N_DAYS:90';

        $records = $this->querySoql($connection, $soql);
        $synced  = 0;

        UserEvent::withoutEvents(function () use ($records, $connection, &$synced, &$emails) {
            foreach ($records as $role) {
                $email = $role['Contact']['Email'] ?? null;
                if (! $email) continue;

                $name    = $role['Contact']['Name'] ?? $email;
                $profile = $this->upsertProfile($email, $name, $connection->client_id);
                $this->writeOpportunityEvents($role['Opportunity'] ?? [], $profile);
                $emails[] = $email;
                $synced++;
            }
        });

        return $synced;
    }

    private function syncCases(CrmConnection $connection, array &$emails): int
    {
        $soql = 'SELECT Subject, Status, Priority, ' .
                'Contact.Email, Contact.Name, ' .
                'CreatedDate ' .
                'FROM Case ' .
                'WHERE Contact.Email != null ' .
                'AND CreatedDate >= LAST_N_DAYS:90';

        $records = $this->querySoql($connection, $soql);
        $synced  = 0;

        UserEvent::withoutEvents(function () use ($records, $connection, &$synced, &$emails) {
            foreach ($records as $case) {
                $email = $case['Contact']['Email'] ?? null;
                if (! $email) continue;

                $name    = $case['Contact']['Name'] ?? $email;
                $profile = $this->upsertProfile($email, $name, $connection->client_id);
                $this->writeCaseEvents($case, $profile);
                $emails[] = $email;
                $synced++;
            }
        });

        return $synced;
    }

    private function querySoql(CrmConnection $connection, string $soql): array
    {
        $instanceUrl = $connection->instance_url;
        $records     = [];
        $url         = $instanceUrl . '/services/data/' . self::API_VERSION . '/query?' . http_build_query(['q' => $soql]);

        do {
            $response = Http::withToken($connection->access_token)->get($url);

            if (! $response->successful()) {
                Log::error('Salesforce SOQL query failed', [
                    'client_id' => $connection->client_id,
                    'status'    => $response->status(),
                    'body'      => $response->body(),
                ]);
                break;
            }

            $data    = $response->json();
            $records = array_merge($records, $data['records'] ?? []);

            // Salesforce paginates via nextRecordsUrl (relative path appended to instance_url)
            $nextPath = $data['nextRecordsUrl'] ?? null;
            $url      = $nextPath ? $instanceUrl . $nextPath : null;

        } while ($url);

        return $records;
    }

    private function upsertProfile(string $email, string $name, int $clientId): BehavioralProfile
    {
        $profile = BehavioralProfile::firstOrNew([
            'client_id' => $clientId,
            'email'     => $email,
        ]);

        if (! $profile->exists) {
            $profile->name                   = $name;
            $profile->segment                = 'new';
            $profile->intent_score           = 0;
            $profile->engagement_score       = 0;
            $profile->churn_score            = 0;
            $profile->loyalty_score          = 0;
            $profile->trust_score            = 0;
            $profile->frustration_score      = 0;
            $profile->buying_readiness       = 0;
            $profile->dropoff_risk           = 0;
            $profile->reactivation_potential = 0;
            $profile->overall_score          = 0;
            $profile->last_active_at         = now()->subDays(30);
        }

        $profile->save();
        return $profile;
    }

    /**
     * Maps Salesforce opportunity stages to UserEvent types:
     *   Closed Won  → checkout_complete  (loyalty + trust boost)
     *   Closed Lost → form_abandon        (dropoff_risk + frustration)
     *   Open/active → checkout_start      (buying_readiness signal)
     * Every opportunity also fires a pricing_view (intent signal).
     */
    private function writeOpportunityEvents(array $opportunity, BehavioralProfile $profile): void
    {
        $base  = [
            'client_id'             => $profile->client_id,
            'behavioral_profile_id' => $profile->id,
            'email'                 => $profile->email,
        ];
        $stage = strtolower($opportunity['StageName'] ?? '');

        if (str_contains($stage, 'closed won')) {
            UserEvent::create(array_merge($base, ['event_type' => 'checkout_complete']));
        } elseif (str_contains($stage, 'closed lost')) {
            UserEvent::firstOrCreate(array_merge($base, ['event_type' => 'form_abandon']));
        } else {
            UserEvent::firstOrCreate(array_merge($base, ['event_type' => 'checkout_start']));
        }

        UserEvent::firstOrCreate(array_merge($base, ['event_type' => 'pricing_view']));
    }

    /**
     * Each case creates a support_ticket event.
     * High/Critical priority cases add a second support_ticket to amplify frustration score.
     */
    private function writeCaseEvents(array $case, BehavioralProfile $profile): void
    {
        $base = [
            'client_id'             => $profile->client_id,
            'behavioral_profile_id' => $profile->id,
            'email'                 => $profile->email,
        ];

        UserEvent::create(array_merge($base, ['event_type' => 'support_ticket']));

        if (in_array($case['Priority'] ?? '', ['High', 'Critical'])) {
            UserEvent::create(array_merge($base, ['event_type' => 'support_ticket']));
        }
    }

    private function ensureFreshToken(CrmConnection $connection): void
    {
        if ($connection->isTokenExpired() && $connection->refresh_token) {
            $this->refreshToken($connection);
            $connection->refresh();
        }
    }
}
