<?php

namespace App\Services\MockMaster\Concerns;

use App\Services\Llm\OpenAiClient;
use App\Services\Llm\OpenAiException;

/**
 * Shared "real arithmetic first, AI narration second, graceful no-key
 * fallback" shape used by every MockMaster agent service — the same pattern
 * MarketingCampaignService etc. use for the CRM/email-backed agents, applied
 * here to the mm_* tables (a real MockMaster PTE Portal export). The rows
 * passed in are already the real answer; OpenAI, when configured, only turns
 * them into readable copy — it never gets to invent a number.
 */
trait AnswersWithAi
{
    /**
     * @return array{rows: array<int, array>, answer: string, ai_used: bool}
     */
    protected function buildAnswer(
        array $rows,
        string $question,
        string $emptyMessage,
        callable $plainSummary,
        array $context = [],
        ?string $instruction = null
    ): array {
        if (empty($rows)) {
            return ['rows' => [], 'answer' => $emptyMessage, 'ai_used' => false];
        }

        $client = app(OpenAiClient::class);

        if ($client->isConfigured()) {
            try {
                return [
                    'rows' => $rows,
                    'answer' => $this->askOpenAi($client, $question, $rows, $context, $instruction),
                    'ai_used' => true,
                ];
            } catch (OpenAiException $e) {
                report($e);
                // A broken AI call should never take down an answer that
                // already has real, computed data behind it.
            }
        }

        return ['rows' => $rows, 'answer' => $plainSummary($rows), 'ai_used' => false];
    }

    private function askOpenAi(OpenAiClient $client, string $question, array $rows, array $context, ?string $instruction): string
    {
        $system = ($this->systemRole ?? 'You are a data copilot for a B2B analytics platform.')
            . ' Use ONLY the JSON data provided — never invent a name, number, or detail that is not in it. '
            . 'Output PLAIN TEXT only, never JSON or markdown. '
            . ($instruction ?? 'Answer briefly and concretely, in plain English, under 120 words.');

        $prompt = "Question: {$question}\n\nContext:\n" . json_encode($context, JSON_PRETTY_PRINT)
            . "\n\nData:\n" . json_encode($rows, JSON_PRETTY_PRINT);

        return $client->chat($system, $prompt, ['max_tokens' => 300]);
    }
}
