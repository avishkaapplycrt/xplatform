<?php

namespace App\Console\Commands;

use App\Services\RetentionSaveFirstService;
use Illuminate\Console\Command;

/**
 * Manual test harness for the Risk radar AI answers — run this after setting
 * OPENAI_API_KEY in .env to confirm the OpenAI call works end to end (it
 * also works, with a plain-text fallback, with no key set at all).
 */
class RetentionSaveFirst extends Command
{
    protected $signature = 'retention:save-first {--limit=5} {--question=both : save-first|watchlist|both}';

    protected $description = 'Print the Risk radar answers built from crm_contacts, crm_deals and email_logs_providers';

    public function handle(RetentionSaveFirstService $service): int
    {
        $question = $this->option('question');
        $limit = (int) $this->option('limit');

        if ($question === 'save-first' || $question === 'both') {
            $this->printResult('Who do I save first this week?', $service->answer($limit));
        }

        if ($question === 'watchlist' || $question === 'both') {
            $this->printResult('Who is drifting onto the watchlist?', $service->watchlistAnswer($limit));
        }

        return self::SUCCESS;
    }

    private function printResult(string $question, array $result): void
    {
        $this->newLine();
        $this->comment($question);
        $this->info($result['ai_used'] ? 'Answer (OpenAI-written):' : 'Answer (plain-text fallback — no OPENAI_API_KEY configured or the call failed):');
        $this->line($result['answer']);

        if (!empty($result['ranked'])) {
            $this->table(
                ['#', 'Name', 'Company', 'Deal value', 'Unsub?', 'Ever opened?', 'Days since activity', 'Risk score'],
                collect($result['ranked'])->map(fn ($r, $i) => [
                    $i + 1,
                    $r['name'],
                    $r['company'],
                    '$' . number_format($r['deal_value']),
                    $r['unsubscribed'] ? 'yes' : 'no',
                    $r['ever_opened'] ? 'yes' : 'no',
                    $r['days_since_activity'],
                    $r['risk_score'],
                ])->all()
            );
        }
    }
}
