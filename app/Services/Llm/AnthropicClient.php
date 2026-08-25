<?php

namespace App\Services\Llm;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around the Anthropic Messages API (POST /v1/messages), built
 * for a server-side batch job: no streaming, no chat history, one call in and
 * one JSON object out.
 *
 * Structured output is enforced via `output_config.format` (JSON Schema),
 * not prompting or tool-use — the API validates the shape server-side, so a
 * successful response is always parseable without a retry-on-bad-JSON loop.
 *
 * Retries are handled explicitly here rather than via Http::retry(), because
 * that helper's retry predicate only fires on transport-level exceptions —
 * Laravel's HTTP client does not throw on 4xx/5xx responses by default, so a
 * naive Http::retry() never actually retries a 429 or 529.
 */
class AnthropicClient
{
    private const ENDPOINT = 'https://api.anthropic.com/v1/messages';
    private const ANTHROPIC_VERSION = '2023-06-01';
    private const MAX_ATTEMPTS = 4;

    private string $apiKey;
    private string $model;

    public function __construct(?string $apiKey = null, ?string $model = null)
    {
        $this->apiKey = $apiKey ?? (string) config('services.anthropic.api_key');
        $this->model  = $model ?? (string) config('services.anthropic.model', 'claude-opus-5');
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * Send one request and return the response decoded against $schema.
     *
     * @param string $system  System prompt — role, constraints, output contract.
     * @param string $prompt  The user turn — in this app, always a JSON data snapshot.
     * @param array  $schema  JSON Schema the response must satisfy. Must set
     *                        `additionalProperties: false` on every object and
     *                        list every key in `required` — the Anthropic
     *                        structured-output validator requires both.
     * @param array  $options ['max_tokens' => int, 'effort' => 'low'|'medium'|'high'|'xhigh'|'max']
     *
     * @throws AnthropicException          non-retryable error, retries exhausted, or unparseable output
     * @throws AnthropicRefusedException   the model's safety classifiers declined the request
     */
    public function structuredCompletion(string $system, string $prompt, array $schema, array $options = []): array
    {
        if (!$this->isConfigured()) {
            throw new AnthropicException('ANTHROPIC_API_KEY is not configured.');
        }

        $payload = [
            'model'      => $this->model,
            'max_tokens' => $options['max_tokens'] ?? 8000,
            'system'     => $system,
            'messages'   => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'output_config' => [
                'effort' => $options['effort'] ?? 'medium',
                'format' => [
                    'type'   => 'json_schema',
                    'schema' => $schema,
                ],
            ],
        ];

        $response = $this->sendWithRetry($payload);
        $body     = $response->json();

        $stopReason = $body['stop_reason'] ?? null;

        if ($stopReason === 'refusal') {
            $category = $body['stop_details']['category'] ?? 'unspecified';
            throw new AnthropicRefusedException("Anthropic declined the request (category: {$category}).");
        }

        if ($stopReason === 'max_tokens') {
            throw new AnthropicException(
                'Response was truncated at max_tokens before completing. Increase max_tokens (thinking + output share the same budget on this model).'
            );
        }

        $text = collect($body['content'] ?? [])->firstWhere('type', 'text')['text'] ?? null;

        if ($text === null) {
            throw new AnthropicException('Anthropic response contained no text content block.');
        }

        $decoded = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new AnthropicException('Anthropic response was not valid JSON: ' . json_last_error_msg());
        }

        return $decoded;
    }

    /**
     * Send one conversational turn and return the assistant's reply as text.
     *
     * The counterpart to structuredCompletion(): it carries the full message
     * history instead of a single data snapshot, and returns free-form prose
     * rather than a schema-validated object. Both share the transport below.
     *
     * Thinking is on by default on this model family and `max_tokens` caps
     * thinking and reply text together, so the default budget leaves room for
     * both. Depth is tuned with `effort`, not by disabling thinking — a
     * thinking-disabled request can emit a tool call as plain text or leak
     * internal tags into the reply.
     *
     * @param string $system   System prompt — persona, scope, output style.
     * @param array  $messages Alternating history: [['role' => 'user'|'assistant', 'content' => string], ...]
     * @param array  $options  ['max_tokens' => int, 'effort' => 'low'|'medium'|'high'|'xhigh'|'max']
     *
     * @throws AnthropicException          non-retryable error, retries exhausted, or empty response
     * @throws AnthropicRefusedException   the model's safety classifiers declined the request
     */
    public function chat(string $system, array $messages, array $options = []): string
    {
        if (!$this->isConfigured()) {
            throw new AnthropicException('ANTHROPIC_API_KEY is not configured.');
        }

        $payload = [
            'model'      => $this->model,
            'max_tokens' => $options['max_tokens'] ?? 8000,
            'system'     => $system,
            'messages'   => $messages,
            'output_config' => [
                'effort' => $options['effort'] ?? 'low',
            ],
        ];

        $body = $this->sendWithRetry($payload)->json();

        $stopReason = $body['stop_reason'] ?? null;

        if ($stopReason === 'refusal') {
            $category = $body['stop_details']['category'] ?? 'unspecified';
            throw new AnthropicRefusedException("Anthropic declined the request (category: {$category}).");
        }

        if ($stopReason === 'max_tokens') {
            throw new AnthropicException(
                'Reply was truncated at max_tokens. Increase max_tokens (thinking + reply share the same budget on this model).'
            );
        }

        /* A turn can open with a thinking block whose text is empty, so take
           every text block rather than just the first content block. */
        $text = collect($body['content'] ?? [])
            ->where('type', 'text')
            ->pluck('text')
            ->implode('');

        if (trim($text) === '') {
            throw new AnthropicException('Anthropic response contained no text content block.');
        }

        return $text;
    }

    /**
     * Attempts up to MAX_ATTEMPTS times. Retries on 429 (rate limit) and 5xx/529
     * (server error / overloaded) with exponential backoff, honoring the
     * Retry-After header when the API sends one. 4xx errors other than 429
     * (bad request, auth, permission) fail immediately — retrying a malformed
     * request just wastes the attempt budget.
     */
    private function sendWithRetry(array $payload): \Illuminate\Http\Client\Response
    {
        $attempt = 0;

        while (true) {
            $attempt++;

            $response = Http::withHeaders([
                    'x-api-key'         => $this->apiKey,
                    'anthropic-version' => self::ANTHROPIC_VERSION,
                    'content-type'      => 'application/json',
                ])
                ->timeout(120)
                ->post(self::ENDPOINT, $payload);

            if ($response->successful()) {
                return $response;
            }

            $status     = $response->status();
            $retryable  = $status === 429 || $status === 529 || $status >= 500;
            $lastAttempt = $attempt >= self::MAX_ATTEMPTS;

            Log::warning('Anthropic API request failed', [
                'attempt' => $attempt,
                'status'  => $status,
                'type'    => $response->json('error.type'),
                'message' => $response->json('error.message'),
            ]);

            if (!$retryable || $lastAttempt) {
                throw new AnthropicException(
                    "Anthropic API returned HTTP {$status}: " . ($response->json('error.message') ?? $response->body())
                );
            }

            $retryAfter = (int) $response->header('retry-after');
            $backoffMs  = $retryAfter > 0 ? $retryAfter * 1000 : (int) (500 * 2 ** $attempt);

            usleep($backoffMs * 1000);
        }
    }
}
