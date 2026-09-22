<?php

namespace App\Services;

use App\Models\CrmContact;
use App\Models\CrmDeal;
use App\Services\Llm\OpenAiClient;
use App\Services\Llm\OpenAiException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Cross-references real Stripe transaction data (transactions,
 * transactions_customers — synced by StripeSyncService) against the CRM
 * (crm_contacts, crm_deals) to answer the Sales/Marketing/Retention
 * predefined prompts that need "did this CRM contact actually pay"
 * rather than just CRM/email signals alone.
 *
 * crm_contacts/crm_deals carry no client_id column in this system (see
 * RealAccountsService, RetentionSaveFirstService, the Marketing services —
 * none of them scope CRM data by client either), so, matching that existing
 * convention, only the transaction tables are scoped to the logged-in
 * client; CRM matching stays global. A CRM contact is matched to a
 * transactions_customers row by exact (case-insensitive) email — the same
 * kind of best-effort match already used to tie crm_deals to crm_contacts by
 * company-name prefix, since neither pair of tables stores a real foreign key.
 *
 * Marketing/Retention callers get the {ranked, answer, ai_used} shape (same
 * as RetentionSaveFirstService::buildAnswer) so they can reuse the existing
 * renderRiskAiAnswer() JS renderer and "view accounts" modal. Sales callers
 * use the raw list methods directly, since SalesPromptInsightsService has
 * its own {answer, ai_used} response shape with no ranked-accounts modal.
 */
class TransactionInsightsService
{
    private function clientId(): ?int
    {
        return Auth::guard('client')->id();
    }

    // ------------------------------------------------------------------
    // Sales — raw data, consumed directly by SalesPromptInsightsService
    // ------------------------------------------------------------------

    /**
     * CRM contacts with a matched deal (i.e. actually pitched) who also have
     * at least one completed transaction.
     */
    public function pitchedProspectsConverted(): array
    {
        $clientId = $this->clientId();
        $deals = $this->dealsCollection();
        $rows = [];

        foreach ($this->contactsWithCompany() as $contact) {
            $deal = $deals->first(fn (CrmDeal $d) => str_starts_with((string) $d->name, (string) $contact->company));
            if (!$deal) {
                continue;
            }

            $tc = $this->transactionCustomerForEmail($contact->email, $clientId);
            if (!$tc || (int) $tc->orders_count < 1) {
                continue;
            }

            $rows[] = [
                'name' => $this->contactName($contact),
                'company' => $contact->company,
                'deal_stage' => $deal->stage,
                'deal_value' => round((float) $deal->value, 2),
                'transaction_total' => round((float) $tc->lifetime_value, 2),
                'orders' => (int) $tc->orders_count,
            ];
        }

        usort($rows, fn ($a, $b) => $b['transaction_total'] <=> $a['transaction_total']);

        return $rows;
    }

    /**
     * Won deals where the closed deal value differs from the customer's
     * actual paid total.
     */
    public function quotedVsActualDifference(): array
    {
        $clientId = $this->clientId();
        $contacts = $this->contactsWithCompany();
        $rows = [];

        foreach ($this->dealsCollection('won') as $deal) {
            $contact = $contacts->first(fn (CrmContact $c) => str_starts_with((string) $deal->name, (string) $c->company));
            if (!$contact) {
                continue;
            }

            $tc = $this->transactionCustomerForEmail($contact->email, $clientId);
            if (!$tc || (int) $tc->orders_count < 1) {
                continue;
            }

            $quoted = round((float) $deal->value, 2);
            $actual = round((float) $tc->lifetime_value, 2);
            $difference = round($actual - $quoted, 2);

            if (abs($difference) < 0.01) {
                continue;
            }

            $rows[] = [
                'name' => $this->contactName($contact),
                'company' => $contact->company,
                'quoted' => $quoted,
                'actual' => $actual,
                'difference' => $difference,
            ];
        }

        usort($rows, fn ($a, $b) => abs($b['difference']) <=> abs($a['difference']));

        return $rows;
    }

    /**
     * Contacts with an open deal whose most recent transaction is a failed
     * payment at least 3 days old — long enough to call the deal "stalled"
     * rather than just mid-retry.
     */
    public function stalledAfterFailedPayment(): array
    {
        $clientId = $this->clientId();
        $contacts = $this->contactsWithCompany();
        $openDeals = $this->dealsCollection('open');
        $rows = [];

        $failedTx = DB::table('transactions')
            ->where('client_id', $clientId)
            ->where('status', 'failed')
            ->orderBy('created_at')
            ->get();

        $seenEmails = [];

        foreach ($failedTx as $tx) {
            $email = $this->emailForTransaction($tx);
            if (!$email || isset($seenEmails[strtolower($email)])) {
                continue;
            }

            $contact = $contacts->first(fn (CrmContact $c) => strtolower((string) $c->email) === strtolower($email));
            if (!$contact) {
                continue;
            }

            $deal = $openDeals->first(fn (CrmDeal $d) => str_starts_with((string) $d->name, (string) $contact->company));
            if (!$deal) {
                continue;
            }

            $daysAgo = (int) \Carbon\Carbon::parse($tx->created_at)->diffInDays(now());
            if ($daysAgo < 3) {
                continue;
            }

            $seenEmails[strtolower($email)] = true;

            $rows[] = [
                'name' => $this->contactName($contact),
                'company' => $contact->company,
                'deal_stage' => $deal->stage,
                'failed_amount' => round((float) $tx->amount, 2),
                'failed_days_ago' => $daysAgo,
            ];
        }

        usort($rows, fn ($a, $b) => $b['failed_days_ago'] <=> $a['failed_days_ago']);

        return $rows;
    }

    /**
     * Paying customers (orders_count > 0) whose email doesn't match any
     * crm_contacts row at all — never entered the CRM/sales pipeline.
     */
    public function purchasedWithoutContact(): array
    {
        $clientId = $this->clientId();
        $contactEmails = CrmContact::whereNotNull('email')
            ->pluck('email')
            ->map(fn ($e) => strtolower((string) $e))
            ->flip();

        $rows = [];

        foreach (DB::table('transactions_customers')->where('client_id', $clientId)->where('orders_count', '>', 0)->get() as $tc) {
            if ($tc->email && $contactEmails->has(strtolower($tc->email))) {
                continue;
            }

            $rows[] = [
                'name' => $tc->name,
                'email' => $tc->email,
                'orders' => (int) $tc->orders_count,
                'lifetime_value' => round((float) $tc->lifetime_value, 2),
            ];
        }

        usort($rows, fn ($a, $b) => $b['lifetime_value'] <=> $a['lifetime_value']);

        return $rows;
    }

    /**
     * The complete, real list of every paying customer for the current
     * client — independent of crm_contacts/RealAccountsService entirely, and
     * with no cap. RealAccountsService::build() only returns a capped,
     * CRM-ranked slice of accounts (14 by default), so a real paying
     * customer with a thin or no CRM record — like someone whose only
     * footprint in this system is a completed Stripe payment — would
     * otherwise be invisible to any transaction-specific question just for
     * not ranking into that slice. This is the source of truth for
     * "who paid X" / "who has transacted" style questions.
     *
     * @return array<int, array{name: string, email: ?string, orders_count: int, lifetime_value: float, order_amounts: array<int, float>}>
     */
    public function allPayingCustomers(): array
    {
        $clientId = $this->clientId();
        if (!$clientId) {
            return [];
        }

        $customers = DB::table('transactions_customers')
            ->where('client_id', $clientId)
            ->where('orders_count', '>', 0)
            ->get();

        $out = [];
        foreach ($customers as $c) {
            $amounts = DB::table('transactions')
                ->where('client_id', $clientId)
                ->where('customer_id', $c->id)
                ->where('status', 'completed')
                ->orderBy('created_at')
                ->pluck('amount')
                ->map(fn ($a) => round((float) $a, 2))
                ->values()
                ->all();

            $out[] = [
                'name' => $c->name,
                'email' => $c->email,
                'orders_count' => (int) $c->orders_count,
                'lifetime_value' => round((float) $c->lifetime_value, 2),
                'order_amounts' => $amounts,
            ];
        }

        return $out;
    }

    /**
     * Per-account real transaction detail, keyed by lowercased email — used
     * by SalesChatService to ground the free-text "Ask" box in real Stripe
     * data (order amounts, count, lifetime value) alongside the CRM/MRR data
     * it already has, rather than leaving amount-specific questions ("who
     * paid $99") with nothing to answer from but deal value.
     *
     * @return array<string, array{orders_count: int, lifetime_value: float, order_amounts: array<int, float>}>
     */
    public function accountTransactionContext(): array
    {
        $clientId = $this->clientId();
        if (!$clientId) {
            return [];
        }

        $customers = DB::table('transactions_customers')
            ->where('client_id', $clientId)
            ->where('orders_count', '>', 0)
            ->whereNotNull('email')
            ->get();

        $out = [];
        foreach ($customers as $c) {
            $amounts = DB::table('transactions')
                ->where('client_id', $clientId)
                ->where('customer_id', $c->id)
                ->where('status', 'completed')
                ->orderBy('created_at')
                ->pluck('amount')
                ->map(fn ($a) => round((float) $a, 2))
                ->values()
                ->all();

            $out[strtolower($c->email)] = [
                'orders_count' => (int) $c->orders_count,
                'lifetime_value' => round((float) $c->lifetime_value, 2),
                'order_amounts' => $amounts,
            ];
        }

        return $out;
    }

    // ------------------------------------------------------------------
    // Marketing / Retention — wrapped as {ranked, answer, ai_used}
    // ------------------------------------------------------------------

    public function ltvBySegment(): array
    {
        return $this->buildAnswer(
            $this->rankLtvBySegment(),
            'Which customer segment has the highest lifetime value?',
            'No segment has any paying customers yet — no completed transactions are matched to a scored CRM contact.',
            fn (array $ranked) => "Lifetime value by segment, highest first:\n" . implode("\n", array_map(
                fn ($r, $i) => ($i + 1) . ". {$r['segment']} — \${$this->fmt($r['avg_ltv'])} avg LTV across {$r['customers']} customer(s), \${$this->fmt($r['total_ltv'])} total",
                $ranked,
                array_keys($ranked)
            ))
        );
    }

    private function rankLtvBySegment(): array
    {
        $clientId = $this->clientId();
        $accounts = app(RealAccountsService::class)->build();
        $bySeg = [];

        foreach ($accounts as $account) {
            $tc = $this->transactionCustomerForEmail($account['email'] ?? null, $clientId);
            if (!$tc || (int) $tc->orders_count < 1) {
                continue;
            }

            $seg = $account['seg'] ?? 'Unclassified';
            $bySeg[$seg]['total'] = ($bySeg[$seg]['total'] ?? 0) + (float) $tc->lifetime_value;
            $bySeg[$seg]['count'] = ($bySeg[$seg]['count'] ?? 0) + 1;
        }

        $rows = [];
        foreach ($bySeg as $seg => $d) {
            $rows[] = [
                'segment' => $seg,
                'total_ltv' => round($d['total'], 2),
                'customers' => $d['count'],
                'avg_ltv' => round($d['total'] / $d['count'], 2),
            ];
        }

        usort($rows, fn ($a, $b) => $b['avg_ltv'] <=> $a['avg_ltv']);

        return $rows;
    }

    public function avgTimeBetweenFirstSecondPurchase(): array
    {
        return $this->buildAnswer(
            $this->rankAvgTimeBetweenPurchases(),
            "What's the average time between a customer's first and second purchase, by segment?",
            'No segment has two or more repeat customers yet — nothing to average.',
            fn (array $ranked) => "Average days between 1st and 2nd purchase, by segment:\n" . implode("\n", array_map(
                fn ($r, $i) => ($i + 1) . ". {$r['segment']} — {$r['avg_days_to_second_purchase']} days on average across {$r['customers']} repeat customer(s)",
                $ranked,
                array_keys($ranked)
            ))
        );
    }

    private function rankAvgTimeBetweenPurchases(): array
    {
        $clientId = $this->clientId();
        $accounts = app(RealAccountsService::class)->build()->keyBy(
            fn ($a) => strtolower($a['email'] ?? '')
        );

        $bySeg = [];

        $customers = DB::table('transactions_customers')
            ->where('client_id', $clientId)
            ->where('orders_count', '>=', 2)
            ->get();

        foreach ($customers as $c) {
            $orderDates = DB::table('transactions')
                ->where('client_id', $clientId)
                ->where('customer_id', $c->id)
                ->where('status', 'completed')
                ->orderBy('created_at')
                ->limit(2)
                ->pluck('created_at');

            if ($orderDates->count() < 2) {
                continue;
            }

            $gapDays = (int) \Carbon\Carbon::parse($orderDates[0])->diffInDays(\Carbon\Carbon::parse($orderDates[1]));
            $seg = $accounts->get(strtolower($c->email ?? ''))['seg'] ?? 'Unclassified';

            $bySeg[$seg]['total_days'] = ($bySeg[$seg]['total_days'] ?? 0) + $gapDays;
            $bySeg[$seg]['count'] = ($bySeg[$seg]['count'] ?? 0) + 1;
        }

        $rows = [];
        foreach ($bySeg as $seg => $d) {
            $rows[] = [
                'segment' => $seg,
                'avg_days_to_second_purchase' => round($d['total_days'] / $d['count'], 1),
                'customers' => $d['count'],
            ];
        }

        usort($rows, fn ($a, $b) => $a['avg_days_to_second_purchase'] <=> $b['avg_days_to_second_purchase']);

        return $rows;
    }

    public function oneTimeBuyersQuiet(int $days = 90): array
    {
        return $this->buildAnswer(
            $this->rankOneTimeBuyersQuiet($days),
            "Which one-time buyers haven't returned in 90+ days?",
            "No one-time buyers have gone quiet for {$days}+ days yet.",
            fn (array $ranked) => "One-time buyers quiet for {$days}+ days — good remarketing targets:\n" . implode("\n", array_map(
                fn ($r, $i) => ($i + 1) . ". {$r['name']} ({$r['email']}) — \${$this->fmt($r['lifetime_value'])} spent, {$r['days_since_purchase']} days since their only purchase",
                $ranked,
                array_keys($ranked)
            ))
        );
    }

    private function rankOneTimeBuyersQuiet(int $days): array
    {
        $clientId = $this->clientId();
        $rows = [];

        $customers = DB::table('transactions_customers')
            ->where('client_id', $clientId)
            ->where('orders_count', 1)
            ->get();

        foreach ($customers as $c) {
            $lastOrder = DB::table('transactions')
                ->where('client_id', $clientId)
                ->where('customer_id', $c->id)
                ->where('status', 'completed')
                ->max('created_at');

            if (!$lastOrder) {
                continue;
            }

            $daysSince = (int) \Carbon\Carbon::parse($lastOrder)->diffInDays(now());
            if ($daysSince < $days) {
                continue;
            }

            $rows[] = [
                'name' => $c->name,
                'email' => $c->email,
                'lifetime_value' => round((float) $c->lifetime_value, 2),
                'days_since_purchase' => $daysSince,
            ];
        }

        usort($rows, fn ($a, $b) => $b['days_since_purchase'] <=> $a['days_since_purchase']);

        return $rows;
    }

    public function failedPaymentsRecent(int $days = 30): array
    {
        return $this->buildAnswer(
            $this->rankFailedPaymentsRecent($days),
            "No payment failures to fix.",
            "No failed payments in the last {$days} days.",
            fn (array $ranked) => "Failed payments in the last {$days} days:\n" . implode("\n", array_map(
                fn ($r, $i) => ($i + 1) . ". {$r['name']} — \${$this->fmt($r['amount'])} failed " . \Carbon\Carbon::parse($r['failed_at'])->diffForHumans(),
                $ranked,
                array_keys($ranked)
            ))
        );
    }

    private function rankFailedPaymentsRecent(int $days): array
    {
        $clientId = $this->clientId();

        $rows = DB::table('transactions')
            ->where('client_id', $clientId)
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subDays($days))
            ->orderByDesc('created_at')
            ->get();

        $out = [];
        foreach ($rows as $tx) {
            $email = $this->emailForTransaction($tx);
            $name = null;

            if ($tx->customer_id) {
                $tc = DB::table('transactions_customers')->where('id', $tx->customer_id)->first();
                if ($tc) {
                    $name = $tc->name;
                    $email = $email ?: $tc->email;
                }
            }

            $out[] = [
                'name' => $name ?: ($email ?: 'Unknown customer'),
                'email' => $email,
                'amount' => round((float) $tx->amount, 2),
                'failed_at' => $tx->created_at,
            ];
        }

        return $out;
    }

    public function decliningTransactionValue(): array
    {
        return $this->buildAnswer(
            $this->rankDecliningTransactionValue(),
            "Who's at risk of churn based on declining transaction value?",
            'No repeat customer shows a declining order value right now.',
            fn (array $ranked) => "Declining order value — churn risk:\n" . implode("\n", array_map(
                fn ($r, $i) => ($i + 1) . ". {$r['name']} — latest order \${$this->fmt($r['latest_amount'])}, down {$r['decline_pct']}% from a \${$this->fmt($r['previous_avg'])} average",
                $ranked,
                array_keys($ranked)
            ))
        );
    }

    private function rankDecliningTransactionValue(): array
    {
        $clientId = $this->clientId();

        $customers = DB::table('transactions_customers')
            ->where('client_id', $clientId)
            ->where('orders_count', '>=', 2)
            ->get();

        $rows = [];
        foreach ($customers as $c) {
            $amounts = DB::table('transactions')
                ->where('client_id', $clientId)
                ->where('customer_id', $c->id)
                ->where('status', 'completed')
                ->orderBy('created_at')
                ->pluck('amount')
                ->map(fn ($a) => (float) $a);

            if ($amounts->count() < 2) {
                continue;
            }

            $latest = $amounts->last();
            $earlierAvg = $amounts->slice(0, -1)->avg();

            if ($earlierAvg <= 0 || $latest >= $earlierAvg) {
                continue;
            }

            $rows[] = [
                'name' => $c->name,
                'email' => $c->email,
                'latest_amount' => round($latest, 2),
                'previous_avg' => round($earlierAvg, 2),
                'decline_pct' => round((1 - $latest / $earlierAvg) * 100, 1),
            ];
        }

        usort($rows, fn ($a, $b) => $b['decline_pct'] <=> $a['decline_pct']);

        return $rows;
    }

    public function highValueCustomersQuiet(int $days = 60): array
    {
        return $this->buildAnswer(
            $this->rankHighValueCustomersQuiet($days),
            "Which high-value customers haven't transacted in over 60 days?",
            "No above-average customer has gone quiet for {$days}+ days.",
            fn (array $ranked) => "High-value customers quiet for {$days}+ days:\n" . implode("\n", array_map(
                fn ($r, $i) => ($i + 1) . ". {$r['name']} — \${$this->fmt($r['lifetime_value'])} lifetime value, {$r['days_since_purchase']} days since last order",
                $ranked,
                array_keys($ranked)
            ))
        );
    }

    private function rankHighValueCustomersQuiet(int $days): array
    {
        $clientId = $this->clientId();

        $avgLtv = (float) (DB::table('transactions_customers')
            ->where('client_id', $clientId)
            ->where('orders_count', '>', 0)
            ->avg('lifetime_value') ?? 0);

        if ($avgLtv <= 0) {
            return [];
        }

        $customers = DB::table('transactions_customers')
            ->where('client_id', $clientId)
            ->where('orders_count', '>', 0)
            ->where('lifetime_value', '>=', $avgLtv)
            ->get();

        $rows = [];
        foreach ($customers as $c) {
            $lastOrder = DB::table('transactions')
                ->where('client_id', $clientId)
                ->where('customer_id', $c->id)
                ->where('status', 'completed')
                ->max('created_at');

            if (!$lastOrder) {
                continue;
            }

            $daysSince = (int) \Carbon\Carbon::parse($lastOrder)->diffInDays(now());
            if ($daysSince < $days) {
                continue;
            }

            $rows[] = [
                'name' => $c->name,
                'email' => $c->email,
                'lifetime_value' => round((float) $c->lifetime_value, 2),
                'days_since_purchase' => $daysSince,
            ];
        }

        usort($rows, fn ($a, $b) => $b['lifetime_value'] <=> $a['lifetime_value']);

        return $rows;
    }

    // ------------------------------------------------------------------
    // Shared helpers
    // ------------------------------------------------------------------

    /**
     * Deduped by email — the same contact is frequently synced in from more
     * than one connected CRM integration (see crm_integrations), producing
     * genuine duplicate crm_contacts rows for the same person. Matches the
     * dedup RetentionSaveFirstService::rankAll() already applies for the
     * same reason.
     */
    private function contactsWithCompany()
    {
        return CrmContact::whereNotNull('company')->whereNotNull('email')->get()->unique('email');
    }

    /**
     * Same duplicate-connection issue as contactsWithCompany(), applied to deals.
     */
    private function dealsCollection(?string $status = null)
    {
        $query = CrmDeal::query();
        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->get()->unique('name');
    }

    private function contactName(CrmContact $contact): string
    {
        return trim($contact->first_name . ' ' . $contact->last_name) ?: (string) $contact->company;
    }

    private function transactionCustomerForEmail(?string $email, ?int $clientId): ?object
    {
        if (!$email || !$clientId) {
            return null;
        }

        return DB::table('transactions_customers')
            ->where('client_id', $clientId)
            ->whereRaw('LOWER(email) = ?', [strtolower($email)])
            ->first();
    }

    private function emailForTransaction(object $tx): ?string
    {
        $meta = json_decode($tx->metadata ?? '{}', true) ?: [];

        return $meta['customer_email'] ?? null;
    }

    private function fmt(float $n): string
    {
        return number_format($n, 2);
    }

    /**
     * @return array{ranked: array<int, array>, answer: string, ai_used: bool}
     */
    private function buildAnswer(array $ranked, string $question, string $emptyMessage, callable $plainSummary): array
    {
        if (empty($ranked)) {
            return ['ranked' => [], 'answer' => $emptyMessage, 'ai_used' => false];
        }

        $client = app(OpenAiClient::class);

        if ($client->isConfigured()) {
            try {
                return [
                    'ranked' => $ranked,
                    'answer' => $this->askOpenAi($client, $question, $ranked),
                    'ai_used' => true,
                ];
            } catch (OpenAiException $e) {
                report($e);
            }
        }

        return ['ranked' => $ranked, 'answer' => $plainSummary($ranked), 'ai_used' => false];
    }

    private function askOpenAi(OpenAiClient $client, string $question, array $ranked): string
    {
        $system = 'You are a copilot inside a B2B analytics platform, answering a question using real Stripe '
            . 'transaction data cross-referenced with the CRM. Answer using ONLY the data provided as JSON — '
            . 'never invent a name, number, or fact that isn\'t in the data. Be brief and concrete, under 120 words.';

        $prompt = "Question: {$question}\n\nData:\n" . json_encode($ranked, JSON_PRETTY_PRINT);

        return $client->chat($system, $prompt, ['max_tokens' => 300]);
    }
}
