<?php

namespace App\Services;

use App\Models\BrevoDeliveredRecipient;
use App\Models\EmailLog;
use App\Services\Llm\OpenAiClient;
use App\Services\Llm\OpenAiException;
use App\Services\Marketing\Concerns\ScoresMqlPool;
use Illuminate\Support\Collection;

/**
 * Answers genuinely open-ended, free-text questions typed into the Marketing
 * "Ask anything" box — not a fixed list of canned questions. It builds a
 * bounded, real snapshot from this client's own crm_contacts + crm_deals
 * (via ScoresMqlPool, the same scoring the rest of the Marketing agent uses)
 * joined to their real email_logs / email_logs_providers history by email,
 * then hands that snapshot plus the user's exact question to the LLM with a
 * strict instruction: answer using ONLY the data given, never invent a name,
 * number or fact. This means any phrasing works, not just questions that
 * happen to match a hardcoded keyword list — the tradeoff is that it needs
 * OPENAI_API_KEY configured (config('services.openai')) to work at all,
 * since there is no non-AI fallback for arbitrary natural language the way
 * there is for the rest of this app's fixed-question services.
 */
class MarketingAskService
{
    use ScoresMqlPool;

    private const DATASET_LIMIT = 150;

    /**
     * @return array{answer: string, ranked: array, ai_used: bool}
     */
    public function answer(string $question): array
    {
        $question = trim($question);

        if ($question === '') {
            return ['answer' => 'Ask me something about your Marketing data.', 'ranked' => [], 'ai_used' => false];
        }

        $dataset = $this->buildDataset();

        if (empty($dataset)) {
            return [
                'answer' => 'No synced crm_contacts/crm_deals data is on file yet to answer from.',
                'ranked' => [],
                'ai_used' => false,
            ];
        }

        $client = app(OpenAiClient::class);

        if (!$client->isConfigured()) {
            return [
                'answer' => "Free-text answers need an OpenAI key configured (OPENAI_API_KEY) — ask your admin to set one up. "
                    . 'Until then, try one of the buttons above for a ready-made answer.',
                'ranked' => [],
                'ai_used' => false,
            ];
        }

        try {
            $answer = $this->askOpenAi($client, $question, $dataset);
        } catch (OpenAiException $e) {
            report($e);

            return [
                'answer' => "Couldn't reach the AI to answer that just now — try again in a moment.",
                'ranked' => [],
                'ai_used' => false,
            ];
        }

        return ['answer' => $answer, 'ranked' => $dataset, 'ai_used' => true];
    }

    /**
     * One row per synced account (crm_contacts + crm_deals, via
     * ScoresMqlPool), enriched with real send/open/unsubscribe history
     * matched from email_logs + email_logs_providers by lowercased email.
     * Capped and sorted by deal value so the highest-value, most relevant
     * accounts are the ones that make the cut when there are more contacts
     * than the limit.
     *
     * @return array<int, array>
     */
    private function buildDataset(): array
    {
        $scored = $this->scoredContacts()->sortByDesc('deal_value')->take(self::DATASET_LIMIT)->values();

        $emails = $scored->pluck('email')->filter()->map(fn ($e) => mb_strtolower($e))->unique()->all();

        $logsByEmail = EmailLog::whereIn('email_address', $emails)
            ->get(['email_address', 'opened_at', 'clicked_at', 'unsubscribed_at'])
            ->groupBy(fn ($r) => mb_strtolower($r->email_address));

        $providersByEmail = BrevoDeliveredRecipient::whereIn('email', $emails)
            ->get(['email', 'opened_at', 'unsubscribed_at'])
            ->groupBy(fn ($r) => mb_strtolower($r->email));

        return $scored->map(function (array $row) use ($logsByEmail, $providersByEmail) {
            $email = $row['email'] ? mb_strtolower($row['email']) : null;
            /** @var Collection $logs */
            $logs = $email ? ($logsByEmail->get($email) ?? collect()) : collect();
            /** @var Collection $providers */
            $providers = $email ? ($providersByEmail->get($email) ?? collect()) : collect();

            return [
                'name' => $row['name'],
                'company' => $row['company'],
                'deal_value' => $row['deal_value'],
                'stage' => $row['stage_label'],
                'buying_readiness' => $row['buying_readiness'],
                'trust' => $row['trust'],
                'days_since_activity' => $row['days_since_activity'],
                'at_risk' => $row['at_risk'],
                'emails_sent' => $logs->count() + $providers->count(),
                'ever_opened' => $logs->contains(fn ($r) => $r->opened_at !== null) || $providers->contains(fn ($r) => $r->opened_at !== null),
                'ever_clicked' => $logs->contains(fn ($r) => $r->clicked_at !== null),
                'ever_unsubscribed' => $logs->contains(fn ($r) => $r->unsubscribed_at !== null) || $providers->contains(fn ($r) => $r->unsubscribed_at !== null),
            ];
        })->values()->all();
    }

    private function askOpenAi(OpenAiClient $client, string $question, array $dataset): string
    {
        $system = 'You are the Marketing copilot inside a B2B analytics platform. Answer the question using ONLY '
            . 'the JSON data provided — never invent a name, number or fact that is not in it. If the data cannot '
            . "answer the question, say so plainly instead of guessing. Output PLAIN TEXT only, never JSON or "
            . 'markdown — a short, direct paragraph, in plain English, under 150 words.';

        $prompt = "Question: {$question}\n\n"
            . "Data — one row per synced account, from crm_contacts + crm_deals joined to email_logs / "
            . "email_logs_providers by email address:\n" . json_encode($dataset, JSON_PRETTY_PRINT);

        return $client->chat($system, $prompt, ['max_tokens' => 400]);
    }
}
