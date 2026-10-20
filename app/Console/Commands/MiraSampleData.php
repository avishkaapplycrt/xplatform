<?php

namespace App\Console\Commands;

use App\Models\MiraPremium\MiraProject;
use App\Models\MiraPremium\MiraPrompt;
use App\Models\MiraPremium\MiraScan;
use App\Models\MiraPremium\MiraVisibilityAnswer;
use App\Models\MiraPremium\MiraVisibilityRun;
use App\Services\MiraPremium\ScanService;
use App\Services\MiraPremium\VisibilityEngine;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Fills a project with clearly marked sample AI visibility history so every
 * Mira Premium report can be seen and demoed: tagged prompts with search
 * volumes, ~4 months of weekly runs, and a few past audits for History.
 *
 * Sample rows are flagged (prompts source=sample, runs trigger=sample, scans
 * mode=sample) and shown with a "Sample data" badge; the answers go through
 * the real VisibilityEngine parse + aggregate, so every number is internally
 * consistent. --purge removes them all; real prompts, runs and scans are never
 * touched. Refuses to run in production.
 */
class MiraSampleData extends Command
{
    protected $signature = 'mira:sample-data {project? : Project id (default: the first project)} {--purge : Remove the sample rows instead}';

    protected $description = 'Add (or --purge) clearly marked sample AI visibility history for a Mira Premium project';

    private const PROMPTS = [
        ['What is the best team chat app for remote teams?', 'Discovery', ['discovery', 'top-funnel'], 9900],
        ['Which messaging tool do startups use?', 'Discovery', ['discovery'], 2900],
        ['How much does a team chat tool cost per user?', 'Pricing', ['pricing', 'bottom-funnel'], 1600],
        ['What is the cheapest business messaging app?', 'Pricing', ['pricing'], 880],
        ['Compare the top workplace communication tools', 'Comparison', ['comparison', 'mid-funnel'], 4400],
        ['Which chat app is most secure for companies?', 'Trust', ['trust', 'enterprise'], 1300],
        ['How do I set up channels for project teams?', 'How-to', ['how-to', 'onboarding'], 720],
        ['What chat tool integrates best with Google Workspace?', 'Comparison', ['integrations', 'mid-funnel'], 1900],
    ];

    private const THIRD_PARTY = ['g2.com', 'reddit.com', 'capterra.com', 'techradar.com', 'zapier.com', 'forbes.com'];
    private const OTHER_BRANDS = ['Google Chat', 'Discord', 'Mattermost', 'Rocket.Chat', 'Twist'];

    public function handle(VisibilityEngine $engine, ScanService $scans): int
    {
        if (app()->environment('production')) {
            $this->error('Sample data is for local and demo environments only.');

            return self::FAILURE;
        }

        $project = $this->argument('project') ? MiraProject::find($this->argument('project')) : MiraProject::orderBy('id')->first();
        if ($project === null) {
            $this->error('No project found. Create one in /mira-premium first.');

            return self::FAILURE;
        }

        $this->purge($project);
        if ($this->option('purge')) {
            $this->info("Sample data removed from {$project->domain}.");

            return self::SUCCESS;
        }

        mt_srand(crc32($project->domain));
        $brand       = $project->brandTerms()[0];
        $competitors = $project->competitorList();

        $prompts = [];
        foreach (self::PROMPTS as [$text, $topic, $tags, $volume]) {
            $prompts[] = MiraPrompt::create([
                'project_id' => $project->id, 'prompt' => $text, 'topic' => $topic, 'source' => 'sample', 'is_active' => true,
                'keyword' => VisibilityEngine::keywordFor($text), 'search_volume' => $volume, 'volume_checked_at' => now(), 'tags' => $tags,
            ]);
        }

        // 16 weekly runs; the brand's mention odds climb over time, as AEO work would.
        for ($w = 15; $w >= 0; $w--) {
            $finished = now()->subWeeks($w)->subHours(mt_rand(0, 30));
            $run = MiraVisibilityRun::create([
                'uuid' => (string) Str::uuid(), 'project_id' => $project->id, 'status' => 'queued', 'trigger' => 'sample',
                'model' => 'sample', 'prompts_count' => count($prompts), 'started_at' => $finished, 'created_at' => $finished,
            ]);

            $brandOdds = 0.32 + (15 - $w) * 0.028;
            foreach ($prompts as $i => $prompt) {
                $answer = $this->answer($project, $brand, $competitors, $brandOdds, $i);
                $parsed = $engine->parse($answer, $project);
                MiraVisibilityAnswer::create([
                    'run_id' => $run->id, 'prompt_id' => $prompt->id, 'prompt' => $prompt->prompt, 'topic' => $prompt->topic, 'answer' => $answer,
                    'sentiment' => $parsed['brand_mentioned'] ? ['positive', 'positive', 'neutral', 'positive', 'negative'][mt_rand(0, 4)] : null,
                    'brands_found' => array_values(array_unique(array_merge($parsed['brands_mentioned'], array_slice(self::OTHER_BRANDS, mt_rand(0, 2), mt_rand(1, 3))))),
                    'created_at' => $finished, 'updated_at' => $finished,
                ] + $parsed);
            }

            $answers = $run->answers()->get()->all();
            $summary = $engine->aggregate($answers, $project);
            $run->forceFill([
                'status' => 'done', 'answered_count' => count($answers), 'mentions' => $summary['brand']['mentions'],
                'citations' => $summary['brand']['citations'], 'share_of_voice' => $summary['brand']['share'],
                'summary' => $summary, 'finished_at' => $finished, 'updated_at' => $finished,
            ])->save();
        }

        // Past audits for History: copies of the latest real audit with a rising score.
        $base = MiraScan::where('project_id', $project->id)->where('status', 'done')->where('mode', '!=', 'sample')->latest('id')->first()
            ?? MiraScan::where('status', 'done')->where('url', 'like', '%' . $project->domain . '%')->where('mode', '!=', 'sample')->latest('id')->first();
        $audits = 0;
        if ($base !== null) {
            foreach ([10, 8, 6, 4, 2] as $i => $weeks) {
                $when  = now()->subWeeks($weeks);
                $score = max(20, min(100, (int) $base->overall - 12 + $i * 3 + mt_rand(-1, 1)));
                $issues = $base->issue_counts ?: ['critical' => 2, 'important' => 5, 'minor' => 6, 'passing' => 20];
                foreach (['critical', 'important', 'minor'] as $k) {
                    $issues[$k] = max(0, ($issues[$k] ?? 0) + (4 - $i) - mt_rand(0, 1));
                }
                MiraScan::forceCreate([
                    'uuid' => (string) Str::uuid(), 'client_id' => $project->client_id, 'project_id' => $project->id, 'subject' => $base->subject,
                    'url' => $base->url, 'kind' => 'site', 'mode' => 'sample', 'status' => 'done', 'overall' => $score,
                    'grade' => app(\App\Services\WebsiteAnalyzerService::class)->grade($score),
                    'stage_scores' => array_map(fn ($v) => $v === null ? null : max(0, min(100, $v - 10 + $i * 2)), (array) $base->stage_scores),
                    'issue_counts' => $issues, 'report' => $base->report, 'started_at' => $when, 'finished_at' => $when, 'created_at' => $when, 'updated_at' => $when,
                ]);
                $audits++;
            }
        }

        $this->info("Sample data added to {$project->domain}: " . count($prompts) . ' tagged prompts, 16 weekly AI visibility runs'
            . ($audits ? ", {$audits} past audits" : ' (no real audit to base History on — run one first)') . '.');
        $this->line('Remove it any time: php artisan mira:sample-data ' . $project->id . ' --purge');

        return self::SUCCESS;
    }

    /** A plausible AI answer that names brands in varied order and cites a mix of sources. */
    private function answer(MiraProject $project, string $brand, array $competitors, float $brandOdds, int $i): string
    {
        $named = [];
        if (mt_rand(0, 1000) / 1000 < $brandOdds) {
            $named[] = ['name' => $brand, 'url' => "https://{$project->domain}/" . ['', 'pricing', 'features', 'enterprise', 'integrations'][$i % 5]];
        }
        foreach ($competitors as $c) {
            if (mt_rand(0, 100) < 62) {
                $named[] = ['name' => $c['name'], 'url' => $c['domain'] ? 'https://' . $c['domain'] . '/' : null];
            }
        }
        if (mt_rand(0, 100) < 45) {
            $named[] = ['name' => self::OTHER_BRANDS[mt_rand(0, 4)], 'url' => null];
        }
        shuffle($named);

        if ($named === []) {
            return 'It depends on your team size and budget. Look for strong search, integrations and admin controls, and trial two or three options. '
                . 'Independent reviews such as https://' . self::THIRD_PARTY[mt_rand(0, 5)] . '/ are a good place to compare.';
        }

        $lines = ['Here are the options most teams consider:'];
        foreach ($named as $n => $b) {
            $lines[] = ($n + 1) . '. ' . $b['name'] . ' — ' . ['great for fast-moving teams', 'strong integrations and search', 'good value for small teams', 'enterprise-grade security'][mt_rand(0, 3)]
                . ($b['url'] && mt_rand(0, 100) < 55 ? " ({$b['url']})" : '') . '.';
        }
        $lines[] = 'Sources: https://' . self::THIRD_PARTY[mt_rand(0, 5)] . '/best-team-chat-apps' . (mt_rand(0, 1) ? ' and https://' . self::THIRD_PARTY[mt_rand(0, 5)] . '/' : '');

        return implode("\n", $lines);
    }

    private function purge(MiraProject $project): void
    {
        $runs = MiraVisibilityRun::where('project_id', $project->id)->where('trigger', 'sample')->pluck('id');
        MiraVisibilityAnswer::whereIn('run_id', $runs)->delete();
        MiraVisibilityRun::whereIn('id', $runs)->delete();
        MiraPrompt::where('project_id', $project->id)->where('source', 'sample')->delete();
        MiraScan::where('project_id', $project->id)->where('mode', 'sample')->delete();
    }
}
