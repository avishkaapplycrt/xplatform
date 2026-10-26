<?php

namespace App\Services\MiraPremium;

/**
 * Phase-1 result of the two-phase scan protocol: an HMAC-signed, expiring
 * handle naming one scan and the subject allowed to read it. The report
 * endpoint only trusts what the signature covers, so a client can't swap in
 * another scan, another person's scan, or extend the expiry.
 *
 * Format: base64url(json payload) . "." . base64url(hmac-sha256)
 */
class SignedHandle
{
    /** @return array{handle: string, valid_until: string} */
    public function issue(string $scanUuid, string $subject, ?int $ttlMinutes = null): array
    {
        $exp     = now()->addMinutes($ttlMinutes ?? (int) config('mira_premium.handle_ttl_minutes', 60))->getTimestamp();
        $payload = $this->encode(json_encode(['s' => $scanUuid, 'u' => $this->subjectTag($subject), 'e' => $exp]));

        return [
            'handle'      => $payload . '.' . $this->encode($this->mac($payload)),
            'valid_until' => date(DATE_ATOM, $exp),
        ];
    }

    /** @return string the scan uuid the handle grants */
    public function verify(string $handle, string $subject): string
    {
        $parts = explode('.', $handle);
        if (count($parts) !== 2) {
            throw new MiraApiException('InvalidSignature', 'This report link is not valid.');
        }

        [$payload, $sig] = $parts;
        if (!hash_equals($this->encode($this->mac($payload)), $sig)) {
            throw new MiraApiException('InvalidSignature', 'This report link is not valid.');
        }

        $data = json_decode($this->decode($payload), true);
        if (!is_array($data) || !isset($data['s'], $data['u'], $data['e'])) {
            throw new MiraApiException('InvalidSignature', 'This report link is not valid.');
        }

        if (!hash_equals($data['u'], $this->subjectTag($subject))) {
            throw new MiraApiException('InvalidSignature', 'This report belongs to another session.');
        }

        if ((int) $data['e'] < now()->getTimestamp()) {
            throw new MiraApiException('Expired', 'This report link has expired. Run the scan again.');
        }

        return (string) $data['s'];
    }

    private function subjectTag(string $subject): string
    {
        return substr(hash_hmac('sha256', $subject, $this->key()), 0, 16);
    }

    private function mac(string $payload): string
    {
        return hash_hmac('sha256', $payload, $this->key(), true);
    }

    private function key(): string
    {
        return 'mira-premium-handle|' . config('app.key');
    }

    private function encode(string $raw): string
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    private function decode(string $text): string
    {
        return (string) base64_decode(strtr($text, '-_', '+/'));
    }
}
