<?php

namespace App\Services\MiraPremium;

use App\Models\Client;
use Illuminate\Http\Request;

/**
 * Who the requester is to Mira Premium and what they may do — decided on the
 * server from clients.plan / trial_ends_at / subscription_ends_at, never from
 * anything the browser sends. Rules: config/mira_premium.php.
 *
 * status: active | active_trial | expired | none (same vocabulary as the
 * billing model the page renders).
 */
class Entitlement
{
    private function __construct(
        public readonly ?Client $client,
        public readonly string $tier,
        public readonly string $status,
        public readonly string $subject,
        public readonly bool $devUnlock = false,
    ) {}

    public static function forRequest(Request $request): self
    {
        $client = auth('client')->user();

        return self::resolve($client instanceof Client ? $client : null, self::guestSubject($request));
    }

    public static function resolve(?Client $client, string $guestSubject): self
    {
        // Development unlock (config mira_premium.unlock_all) — never in production.
        $unlock = (string) config('mira_premium.unlock_all');
        if ($unlock !== '' && config("mira_premium.plans.{$unlock}") && !app()->environment('production')) {
            return new self($client, $unlock, 'active', $client ? 'client:' . $client->id : $guestSubject, true);
        }

        if ($client === null) {
            return new self(null, 'preview', 'none', $guestSubject);
        }

        $subject = 'client:' . $client->id;
        $plans   = (array) config('mira_premium.plans');
        $paid    = $client->plan !== 'free' && isset($plans[$client->plan]);

        if (in_array($client->status, ['suspended', 'cancelled'], true)) {
            return new self($client, 'preview', 'expired', $subject);
        }

        if ($client->trial_ends_at && $client->trial_ends_at->isFuture()) {
            return new self($client, $paid ? $client->plan : 'trial', 'active_trial', $subject);
        }

        if ($paid) {
            $lapsed = $client->subscription_ends_at && $client->subscription_ends_at->isPast();

            return $lapsed
                ? new self($client, 'preview', 'expired', $subject)
                : new self($client, $client->plan, 'active', $subject);
        }

        // Free plan with a trial that has ended reads as expired; never trialled reads as none.
        return new self($client, 'preview', $client->trial_ends_at ? 'expired' : 'none', $subject);
    }

    /** Guests are metered per network address (hashed — the raw IP is never stored). */
    public static function guestSubject(Request $request): string
    {
        return 'guest:' . substr(hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')), 0, 40);
    }

    public function isPremium(): bool
    {
        return $this->tier !== 'preview';
    }

    public function label(): string
    {
        return (string) config("mira_premium.plans.{$this->tier}.label", ucfirst($this->tier));
    }

    /** @return array{0: int, 1: ?string} [count, period] */
    public function limit(string $metric): array
    {
        $limit = config("mira_premium.plans.{$this->tier}.limits.{$metric}", [0, null]);

        return [(int) $limit[0], $limit[1] ?? null];
    }

    public function allows(string $metric): bool
    {
        return $this->limit($metric)[0] > 0;
    }

    public function aiRecommendations(): bool
    {
        return (bool) config("mira_premium.plans.{$this->tier}.ai_recommendations", false);
    }

    /** Throws NotEntitled unless this tier includes the feature at all. */
    public function require(string $metric, string $what): void
    {
        if (!$this->allows($metric)) {
            throw new MiraApiException('NotEntitled', "{$what} is part of Mira Premium.", [
                'upgrade_url' => config('mira_premium.upgrade_url'),
            ]);
        }
    }

    /** The subscription object the page renders (billing-model shape). */
    public function toArray(): array
    {
        $c = $this->client;

        return [
            'tier'          => $this->tier,
            'label'         => $this->label(),
            'status'        => $this->status,
            'premium'       => $this->isPremium(),
            'signed_in'     => $c !== null,
            'name'          => $c?->name,
            'company'       => $c?->company_name,
            'plan'          => $c?->plan,
            'trial_ends_at' => $c?->trial_ends_at?->toIso8601String(),
            'renews_at'     => $this->status === 'active' ? $c?->subscription_ends_at?->toIso8601String() : null,
            'trial_days_left' => $this->status === 'active_trial' && $c?->trial_ends_at
                ? max(0, (int) ceil(now()->diffInHours($c->trial_ends_at) / 24)) : null,
            'upgrade_url'   => config('mira_premium.upgrade_url'),
            'management_url' => $this->isPremium() ? config('mira_premium.management_url') : null,
            'ai_recommendations' => $this->aiRecommendations(),
            'dev_unlock'    => $this->devUnlock,
        ];
    }
}
