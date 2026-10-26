<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppCloudService
{
    /**
     * Sends a pre-approved template message. Templates are the only message
     * type WhatsApp allows to a number that hasn't messaged the business in
     * the last 24 hours.
     *
     * @return array{ok: bool, message_id: ?string, error: ?string}
     */
    public function sendTemplate(string $to, string $template, string $language = 'en_US', array $params = []): array
    {
        $components = $params
            ? [['type' => 'body', 'parameters' => array_map(fn ($p) => ['type' => 'text', 'text' => (string) $p], $params)]]
            : [];

        return $this->post([
            'messaging_product' => 'whatsapp',
            'to' => $this->digits($to),
            'type' => 'template',
            'template' => [
                'name' => $template,
                'language' => ['code' => $language],
                'components' => $components,
            ],
        ]);
    }

    /**
     * Sends free-form text. Only works within 24 hours of the student's own
     * last message to the business number.
     *
     * @return array{ok: bool, message_id: ?string, error: ?string}
     */
    public function sendText(string $to, string $body): array
    {
        return $this->post([
            'messaging_product' => 'whatsapp',
            'to' => $this->digits($to),
            'type' => 'text',
            'text' => ['body' => $body],
        ]);
    }

    private function post(array $payload): array
    {
        $url = sprintf(
            'https://graph.facebook.com/%s/%s/messages',
            config('services.whatsapp.api_version'),
            config('services.whatsapp.phone_number_id')
        );

        $response = Http::withToken(config('services.whatsapp.access_token'))
            ->timeout(15)
            ->post($url, $payload);

        if ($response->successful()) {
            return ['ok' => true, 'message_id' => $response->json('messages.0.id'), 'error' => null];
        }

        return [
            'ok' => false,
            'message_id' => null,
            'error' => $response->json('error.message') ?? ('HTTP ' . $response->status()),
        ];
    }

    private function digits(string $phone): string
    {
        return preg_replace('/\D/', '', $phone);
    }
}
