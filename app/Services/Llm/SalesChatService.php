<?php

namespace App\Services\Llm;

use App\Services\RealAccountsService;
use Illuminate\Support\Collection;

/**
 * Answers arbitrary free-typed questions in the Sales agent's chat box
 * ("who has the best engagement value", "who should I upsell this month",
 * etc.) using the same real CRM/Brevo-derived account data the page itself
 * renders from (see RealAccountsService) as grounding context for OpenAI.
 *
 * This only runs when the Blade view's static PLAYBOOKS keyword-matcher
 * finds no close match — a strong keyword hit still shows the curated
 * playbook answer first, since that's exact and free. This is purely a
 * fallback for the questions that matcher can't handle, not a replacement
 * for it.
 */
class SalesChatService
{
    public function __construct(
        private readonly RealAccountsService $accounts,
        private readonly OpenAiClient $client,
    ) {
    }

    /**
     * @return array{answer: string, ai_used: bool}
     */
    public function answer(string $question): array
    {
        $question = trim($question);

        if ($question === '') {
            return [
                'answer' => 'Ask me something about your accounts — for example, who has the best engagement, or who to call today.',
                'ai_used' => false,
            ];
        }

        if (!$this->client->isConfigured()) {
            return [
                'answer' => "The AI assistant isn't configured yet — try one of the buttons above.",
                'ai_used' => false,
            ];
        }

        $accounts = $this->accounts->build();

        if ($accounts->isEmpty()) {
            return [
                'answer' => 'There are no synced CRM accounts yet to answer that from — connect and sync a CRM first.',
                'ai_used' => false,
            ];
        }

        try {
            return ['answer' => $this->ask($question, $accounts), 'ai_used' => true];
        } catch (OpenAiException $e) {
            report($e);
            // A broken AI call should never take down a chat that has real
            // data behind it — surface a plain retry message instead.
            return [
                'answer' => "I couldn't reach the AI just now — try again in a moment, or use one of the buttons above.",
                'ai_used' => false,
            ];
        }
    }

    private function ask(string $question, Collection $accounts): string
    {
        $system = 'You are the Sales copilot inside a B2B analytics platform. '
            . 'Answer the sales rep\'s question using ONLY the account data provided as JSON — '
            . 'never invent a name, number, or company that isn\'t in that data. '
            . 'Each account has: seg (champion/loyal/new/dormant/at_risk), mrr (deal value in dollars), '
            . 'and scores: intent, engagement, buying_readiness, trust, loyalty (0-100, higher is stronger) '
            . 'and churn, frustration (0-100, higher is worse). '
            . 'Be brief and concrete — name the account(s) and the number(s) behind your answer, in plain English, under 120 words.';

        $prompt = "Question: {$question}\n\nAccounts:\n" . json_encode($accounts->values(), JSON_PRETTY_PRINT);

        return $this->client->chat($system, $prompt, ['max_tokens' => 300]);
    }
}
