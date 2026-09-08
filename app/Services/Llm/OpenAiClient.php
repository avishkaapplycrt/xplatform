<?php

namespace App\Services\Llm;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around OpenAI's Chat Completions API (POST /v1/chat/completions).
 * Mirrors AnthropicClient's shape (isConfigured() + a single text-in/text-out
 * call) so callers can treat either provider the same way.
 */
class OpenAiClient
{
    private const ENDPOINT = 'https://api.openai.com/v1/chat/completions';
    private const MAX_ATTEMPTS = 4;

    private string $apiKey;
    private string $model;

    public function __construct(?string $apiKey = null, ?string $model = null)
    {
        $this->apiKey = $apiKey ?? (string) config('services.openai.api_key');
        $this->model  = $model ?? (string) config('services.openai.model', 'gpt-4.1-mini');
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * Send one system+user turn and return the assistant's reply as plain text.
     *
     * @param string $system  System prompt — persona, scope, output style.
     * @param string $prompt  The user turn — in this app, a data snapshot plus a question.
     * @param array  $options ['max_tokens' => int, 'temperature' => float]
     *
     * @throws OpenAiException non-retryable error, retries exhausted, or empty response
     */
    public function chat(string $system, string $prompt, array $options = []): string
    {
        if (!$this->isConfigured()) {
            throw new OpenAiException('OPENAI_API_KEY is not configured.');
        }

        $payload = [
            'model'       => $this->model,
            'max_tokens'  => $options['max_tokens'] ?? 500,
            'temperature' => $options['temperature'] ?? 0.3,
            'messages'    => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $prompt],
            ],
        ];

        $body = $this->sendWithRetry($payload)->json();

        $text = $body['choices'][0]['message']['content'] ?? null;

        if ($text === null || trim($text) === '') {
            throw new OpenAiException('OpenAI response contained no message content.');
        }

        return trim($text);
    }

    /**
     * Retries on 429 (rate limit) and 5xx (server error) with backoff, honoring
     * Retry-After when present. Other 4xx errors (bad request, auth) fail
     * immediately since retrying a malformed/unauthorized request wastes the
     * attempt budget.
     */
    private function sendWithRetry(array $payload): \Illuminate\Http\Client\Response
    {
        $attempt = 0;

        while (true) {
            $attempt++;

            $response = Http::withToken($this->apiKey)
                ->timeout(60)
                ->post(self::ENDPOINT, $payload);

            if ($response->successful()) {
                return $response;
            }

            $status      = $response->status();
            $retryable   = $status === 429 || $status >= 500;
            $lastAttempt = $attempt >= self::MAX_ATTEMPTS;

            Log::warning('OpenAI API request failed', [
                'attempt' => $attempt,
                'status'  => $status,
                'message' => $response->json('error.message'),
            ]);

            if (!$retryable || $lastAttempt) {
                throw new OpenAiException(
                    "OpenAI API returned HTTP {$status}: " . ($response->json('error.message') ?? $response->body())
                );
            }

            $retryAfter = (int) $response->header('retry-after');
            $backoffMs  = $retryAfter > 0 ? $retryAfter * 1000 : (int) (500 * 2 ** $attempt);

            usleep($backoffMs * 1000);
        }
    }
}
