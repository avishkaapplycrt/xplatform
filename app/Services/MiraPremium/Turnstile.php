<?php

namespace App\Services\MiraPremium;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cloudflare Turnstile proof-of-human, verified server-side with the secret
 * key. Enabled only when both keys are configured (config mira_premium.turnstile);
 * otherwise every check passes so the page keeps working in development.
 */
class Turnstile
{
    public function enabled(): bool
    {
        return (string) config('mira_premium.turnstile.site_key') !== ''
            && (string) config('mira_premium.turnstile.secret_key') !== '';
    }

    public function siteKey(): ?string
    {
        return $this->enabled() ? (string) config('mira_premium.turnstile.site_key') : null;
    }

    public function verify(?string $token, ?string $ip = null): void
    {
        if (!$this->enabled()) {
            return;
        }

        if ($token === null || $token === '') {
            throw new MiraApiException('InvalidCaptcha', 'Please complete the human check, then try again.');
        }

        try {
            $ok = Http::asForm()->timeout(10)->post((string) config('mira_premium.turnstile.verify_url'), array_filter([
                'secret'   => config('mira_premium.turnstile.secret_key'),
                'response' => $token,
                'remoteip' => $ip,
            ]))->json('success') === true;
        } catch (\Throwable $e) {
            Log::warning('Turnstile verification failed to run', ['message' => $e->getMessage()]);
            $ok = false;
        }

        if (!$ok) {
            throw new MiraApiException('InvalidCaptcha', "We couldn't confirm you're human. Please try the check again.");
        }
    }
}
