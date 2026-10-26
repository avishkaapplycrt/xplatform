<?php

namespace App\Services\Llm;

use App\Services\RealAccountsService;
use App\Services\TransactionInsightsService;
use Illuminate\Support\Collection;

/**
 * Answers arbitrary free-typed questions in the Sales agent's chat box
 * ("who has the best engagement value", "who paid $99", "who should I
 * upsell this month", etc.) using the same real CRM/Brevo-derived account
 * data the page itself renders from (see RealAccountsService), enriched
 * with each account's real Stripe transaction history (see
 * TransactionInsightsService::accountTransactionContext()) when a matching
 * paying customer exists — so amount/purchase-specific questions have real
 * data to answer from rather than only each account's CRM deal value (mrr).
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
        private readonly TransactionInsightsService $transactions,
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
        $txByEmail = $this->transactions->accountTransactionContext();

        $enriched = $accounts->map(function (array $a) use ($txByEmail) {
            $email = strtolower((string) ($a['email'] ?? ''));
            $a['transactions'] = $txByEmail[$email] ?? null;

            return $a;
        })->values();

        // The complete, real list of every paying customer — independent of
        // accounts above, which is a capped, CRM-ranked slice (14 by
        // default) and can leave out a real paying customer who just
        // doesn't rank into it. Payment/transaction-amount questions must
        // be answered from this complete list, not the capped one.
        $payingCustomers = $this->transactions->allPayingCustomers();

        $system = 'You are the Sales copilot inside a B2B analytics platform. '
            . 'Answer the sales rep\'s question using ONLY the data provided as JSON — '
            . 'never invent a name, number, or company that isn\'t in that data. '
            . 'There are two data sets: "accounts" is a capped, CRM-ranked slice of accounts, each with '
            . 'seg (champion/loyal/new/dormant/at_risk), mrr (CRM deal value in dollars — NOT an actual payment), '
            . 'scores: intent, engagement, buying_readiness, trust, loyalty (0-100, higher is stronger) '
            . 'and churn, frustration (0-100, higher is worse), plus "transactions" (null, or real Stripe payment detail) '
            . 'when that account also appears in the paying-customers list. '
            . '"paying_customers" is the COMPLETE, uncapped list of every real Stripe customer who has ever paid, '
            . 'each with name, email, orders_count, lifetime_value, and order_amounts (every individual completed payment amount). '
            . 'For ANY question about an actual payment, purchase, or transaction amount (e.g. "who paid $99", '
            . '"who bought", "transaction history"), you MUST scan the ENTIRE "paying_customers" list and include '
            . 'every matching customer, even ones not present in "accounts" — never limit yourself to "accounts" or to mrr for these questions. '
            . 'Be brief and concrete — name every matching account/customer and the number(s) behind your answer, in plain English, under 130 words.';

        $prompt = "Question: {$question}\n\naccounts:\n" . json_encode($enriched, JSON_PRETTY_PRINT)
            . "\n\npaying_customers:\n" . json_encode($payingCustomers, JSON_PRETTY_PRINT);

        return $this->client->chat($system, $prompt, ['max_tokens' => 350]);
    }
}
