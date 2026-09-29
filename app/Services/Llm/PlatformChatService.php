<?php

namespace App\Services\Llm;

use App\Services\RealAccountsService;
use App\Services\TransactionInsightsService;
use Illuminate\Support\Collection;

/**
 * Backs the floating "Ask Mira" widget available from every page under
 * /app — unlike SalesChatService (Sales-tab-only) or MockMasterChatService
 * (Mock Master pages only), this one is reachable from anywhere in the
 * platform (Business Helpers, Mock Master Helper, the platform layers
 * pages, etc.), so it answers from the client's overall real account data
 * rather than one module's slice of it. Same real-data-first pattern as
 * every other chat service here: RealAccountsService/TransactionInsightsService
 * compute the numbers first, OpenAI only ever phrases an answer from them.
 */
class PlatformChatService
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
                'answer' => "Hi, I'm Mira — ask me anything about your accounts, campaigns, or customers, from any page.",
                'ai_used' => false,
            ];
        }

        if (!$this->client->isConfigured()) {
            return [
                'answer' => "The AI assistant isn't configured yet — please try again later.",
                'ai_used' => false,
            ];
        }

        $accounts = $this->accounts->build();

        try {
            return ['answer' => $this->ask($question, $accounts), 'ai_used' => true];
        } catch (OpenAiException $e) {
            report($e);
            return [
                'answer' => "I couldn't reach the AI just now — try again in a moment.",
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

        $payingCustomers = $this->transactions->allPayingCustomers();

        $system = 'You are Mira, a helpful assistant available from every page of a B2B analytics platform '
            . '(marketing, sales, retention, and account data). Answer using ONLY the data provided as JSON — '
            . 'never invent a name, number, or company that isn\'t in it. '
            . '"accounts" is a ranked slice of real CRM accounts, each with seg (champion/loyal/new/dormant/at_risk), '
            . 'mrr (CRM deal value — not an actual payment), scores (intent, engagement, buying_readiness, trust, '
            . 'loyalty — higher is stronger; churn, frustration — higher is worse), and "transactions" (real Stripe '
            . 'payment detail when that account has paid). "paying_customers" is the complete, uncapped list of every '
            . 'real Stripe customer who has ever paid, with orders_count, lifetime_value, and every order amount — scan '
            . 'this list fully for any question about an actual payment or purchase, not just "accounts". '
            . 'If the data doesn\'t contain what\'s needed to answer precisely, say so plainly rather than guessing. '
            . 'Be brief, concrete, and friendly, in plain English, under 130 words.';

        $prompt = "Question: {$question}\n\naccounts:\n" . json_encode($enriched, JSON_PRETTY_PRINT)
            . "\n\npaying_customers:\n" . json_encode($payingCustomers, JSON_PRETTY_PRINT);

        return $this->client->chat($system, $prompt, ['max_tokens' => 350]);
    }
}
