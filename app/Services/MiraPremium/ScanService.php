<?php

namespace App\Services\MiraPremium;

use App\Jobs\RunMiraScan;
use App\Models\MiraPremium\MiraProject;
use App\Models\MiraPremium\MiraScan;
use App\Services\WebsiteAnalysis\CheckGuide;
use App\Services\WebsiteAnalysis\SiteCrawler;
use App\Services\WebsiteAnalyzerException;
use App\Services\WebsiteAnalyzerService;
use App\Services\WebsiteComparisonService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Site audits and competitor comparisons for Mira Premium, as a two-phase
 * protocol:
 *
 *   submit()  validate + normalise the URL(s), check the plan and quota,
 *             reuse a recent identical result or queue a new scan, and
 *             return a signed, expiring handle;
 *   report()  verify the handle and return the scan's state — and the full
 *             report once it is done.
 *
 * The heavy work (page fetches, PageSpeed, AI recommendations) runs in
 * RunMiraScan → run(), never inline with the request. Preview scans are
 * real audits but never carry the AI fixes: those are stripped server-side,
 * not just hidden in the page.
 */
class ScanService
{
    public function __construct(
        private UsageMeter $meter,
        private SignedHandle $handles,
        private UrlGuard $urls,
        private ?WebsiteAnalyzerService $analyzer = null,
        private ?WebsiteComparisonService $comparer = null,
        private ?SiteCrawler $crawler = null,
    ) {
        $this->analyzer ??= new WebsiteAnalyzerService();
        $this->comparer ??= new WebsiteComparisonService($this->analyzer);
        $this->crawler  ??= new SiteCrawler($this->urls);
    }

    /** @return array{handle: string, valid_until: string, scan: array, reused: bool} */
    public function submit(Entitlement $who, array $input): array
    {
        $url        = $this->urls->normalize($input['url'] ?? null);
        $competitor = trim((string) ($input['competitor_url'] ?? '')) !== ''
            ? $this->urls->normalize($input['competitor_url'], 'competitor_url')
            : null;

        if ($competitor !== null) {
            $who->require('competitors', 'Competitor comparison');
            if ($this->urls->domainOf($competitor) === $this->urls->domainOf($url)) {
                throw new MiraApiException('InvalidUrl', "Your competitor's website must be different from yours.", ['field' => 'competitor_url']);
            }
        }

        $project = null;
        if (!empty($input['project_id'])) {
            $project = MiraProject::where('id', $input['project_id'])->where('client_id', $who->client?->id)->first();
            if ($project === null) {
                throw new MiraApiException('NotFound', 'That project was not found.');
            }
        }

        $mode = $who->isPremium() ? 'premium' : 'preview';

        $recent = MiraScan::where('subject', $who->subject)
            ->where('url', $url)
            ->where('competitor_url', $competitor)
            ->where('mode', $mode)
            // "Re-run audit" (fresh) only reuses a scan that is still running.
            ->whereIn('status', empty($input['fresh']) ? ['queued', 'running', 'done'] : ['queued', 'running'])
            ->where('created_at', '>=', now()->subMinutes((int) config('mira_premium.scan_reuse_minutes', 30)))
            ->latest('id')
            ->first();

        if ($recent !== null) {
            if ($project && $recent->project_id === null) {
                $recent->update(['project_id' => $project->id]);
            }

            return $this->handleFor($recent, $who, true);
        }

        // A comparison checks two sites, so it costs two scans.
        $cost = $competitor !== null ? 2 : 1;
        $this->meter->consume($who, 'scans', $cost);

        $scan = MiraScan::create([
            'uuid'           => (string) Str::uuid(),
            'client_id'      => $who->client?->id,
            'project_id'     => $project?->id,
            'subject'        => $who->subject,
            'url'            => $url,
            'competitor_url' => $competitor,
            'kind'           => $competitor !== null ? 'comparison' : 'site',
            'mode'           => $mode,
            'status'         => 'queued',
        ]);

        $this->dispatch($scan, $who->aiRecommendations());

        return $this->handleFor($scan, $who, false);
    }

    public function report(Entitlement $who, string $handle): array
    {
        $uuid = $this->handles->verify($handle, $who->subject);
        $scan = MiraScan::where('uuid', $uuid)->first();

        if ($scan === null) {
            throw new MiraApiException('NotFound', 'That scan no longer exists.');
        }

        return $this->present($this->expireIfStuck($scan), true);
    }

    /**
     * A read-only link to one finished scan that anyone can open without
     * signing in (route mira-premium.shared). Signed and expiring, so only
     * the owner can mint one and it can't be altered to show another scan.
     */
    public function shareUrl(Entitlement $who, int $scanId): array
    {
        $scan = MiraScan::where('id', $scanId)->where('subject', $who->subject)->first();
        if ($scan === null) {
            throw new MiraApiException('NotFound', 'That scan was not found.');
        }
        if ($scan->status !== 'done') {
            throw new MiraApiException('InvalidInput', 'Only a finished audit can be shared.');
        }

        $until = now()->addDays((int) config('mira_premium.share_days', 30));

        return [
            'url'         => URL::temporarySignedRoute('mira-premium.shared', $until, ['uuid' => $scan->uuid]),
            // The same page opening the print dialog ("Download PDF"); print is part of the signature.
            'pdf_url'     => URL::temporarySignedRoute('mira-premium.shared', $until, ['uuid' => $scan->uuid, 'print' => 1]),
            'valid_until' => $until->toIso8601String(),
        ];
    }

    /** The report page data for a shared link (see shareUrl), or null when the scan is gone or unfinished. */
    public function sharedReport(string $uuid): ?array
    {
        $scan = MiraScan::where('uuid', $uuid)->where('status', 'done')->first();
        $data = $scan?->reportData();
        if (!$data) {
            return null;
        }

        return [
            'type'    => $scan->kind === 'comparison' ? 'comparison' : 'analysis',
            'premium' => true,
            'data'    => $data,
            'shared'  => true,
        ];
    }

    /** A subscriber reopening a scan from their history (no handle needed — ownership is checked). */
    public function owned(Entitlement $who, int $scanId): array
    {
        $scan = MiraScan::where('id', $scanId)->where('subject', $who->subject)->first();
        if ($scan === null) {
            throw new MiraApiException('NotFound', 'That scan was not found.');
        }

        return $this->present($this->expireIfStuck($scan), true);
    }

    /** Runs a queued scan. Called by RunMiraScan. */
    public function run(MiraScan $scan, bool $aiRecommendations): void
    {
        if ($scan->status !== 'queued') {
            return;
        }

        $scan->update(['status' => 'running', 'started_at' => now()]);
        @set_time_limit(240);

        try {
            if ($scan->kind === 'comparison') {
                $report = $this->comparer->compare($scan->url, $scan->competitor_url);
                $report['locked'] = $scan->mode !== 'premium';
                if ($scan->mode !== 'premium') {
                    $report = $this->withoutFixes($report);
                }
                $you = $report['you'];
                $checks = array_map(fn ($c) => ['status' => $c['you_status'], 'severity' => $c['severity'] ?? 'minor'], $report['checks']);
                $competitorOverall = $report['competitor']['overall'] ?? null;
            } else {
                $checks = $this->analyzer->analyzeSeoAndTechnical($scan->url);
                // "Pages analysed": other pages of the same site. A crawl failure never fails the audit.
                try {
                    $crawl = $this->crawler->crawl($scan->url);
                } catch (\Throwable $e) {
                    Log::warning('Mira Premium site crawl failed', ['scan' => $scan->uuid, 'message' => $e->getMessage()]);
                    $crawl = ['site' => null, 'checks' => []];
                }
                $checks = array_merge($checks, array_map(fn ($c) => $c + CheckGuide::for($c['name']), $crawl['checks']));
                $report = $this->analyzer->buildReport($scan->url, $checks, $scan->mode === 'premium' && $aiRecommendations);
                $report['site'] = $crawl['site'];
                $you    = $report;
                $checks = array_merge(...array_map(fn ($c) => $c['checks'], $report['categories'] ?: [['checks' => []]]));
                $competitorOverall = null;
            }

            $scan->update([
                'status'             => 'done',
                'overall'            => $you['overall'],
                'grade'              => $you['grade'],
                'competitor_overall' => $competitorOverall,
                'stage_scores'       => $this->stageScores($you['categories'] ?? []),
                'issue_counts'       => $this->issueCounts($checks),
                'report'             => json_encode($report),
                'finished_at'        => now(),
            ]);

            if ($scan->project_id) {
                MiraProject::where('id', $scan->project_id)->update(['last_scanned_at' => now()]);
            }
        } catch (WebsiteAnalyzerException $e) {
            $this->fail($scan, 'SiteUnreachable', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Mira Premium scan failed', ['scan' => $scan->uuid, 'message' => $e->getMessage()]);
            $this->fail($scan, 'Unavailable', 'The scan could not be completed. It has not been counted against your plan.');
        }
    }

    /** Stage score = weighted average of its categories' scores (config website_checks.stages / category_weights). */
    public function stageScores(array $categories): array
    {
        $byKey   = array_column($categories, 'score', 'key');
        $weights = (array) config('website_checks.category_weights', []);
        $scores  = [];

        foreach ((array) config('website_checks.stages', []) as $stage) {
            $total = $wsum = 0;
            foreach ($stage['categories'] as $key) {
                if (!isset($byKey[$key])) {
                    continue;
                }
                $w = $weights[$key] ?? 1;
                $total += $byKey[$key] * $w;
                $wsum  += $w;
            }
            $scores[$stage['key']] = $wsum ? (int) round($total / $wsum) : null;
        }

        return $scores;
    }

    /** Non-passing scored checks by severity, plus how many pass. */
    public function issueCounts(array $checks): array
    {
        $counts = ['critical' => 0, 'important' => 0, 'minor' => 0, 'passing' => 0];

        foreach ($checks as $c) {
            if (($c['status'] ?? null) === 'pass') {
                $counts['passing']++;
            } elseif (in_array($c['status'] ?? null, ['fail', 'warn'], true)) {
                $sev = $c['severity'] ?? 'minor';
                $counts[isset($counts[$sev]) ? $sev : 'minor']++;
            }
        }

        return $counts;
    }

    /** Summary row for lists, history and the overview — with the change since the previous scan of the same site. */
    public function summarize(MiraScan $scan): array
    {
        $previous = null;
        if ($scan->status === 'done') {
            $previous = MiraScan::where('subject', $scan->subject)
                ->where('url', $scan->url)
                ->where('status', 'done')
                ->where('id', '<', $scan->id)
                ->latest('id')
                ->first(['overall', 'issue_counts', 'stage_scores']);
        }

        $issues     = $scan->issue_counts;
        $openIssues = $issues ? $issues['critical'] + $issues['important'] + $issues['minor'] : null;
        $prevOpen   = $previous?->issue_counts ? array_sum(array_intersect_key($previous->issue_counts, array_flip(['critical', 'important', 'minor']))) : null;

        return [
            'id'                 => $scan->id,
            'uuid'               => $scan->uuid,
            'project_id'         => $scan->project_id,
            'kind'               => $scan->kind,
            'mode'               => $scan->mode,
            'status'             => $scan->status,
            'url'                => $scan->url,
            'competitor_url'     => $scan->competitor_url,
            'overall'            => $scan->overall,
            'grade'              => $scan->grade,
            'competitor_overall' => $scan->competitor_overall,
            'stage_scores'       => $scan->stage_scores,
            'issue_counts'       => $issues,
            'open_issues'        => $openIssues,
            'delta'              => [
                'overall'     => $previous && $scan->overall !== null ? $scan->overall - $previous->overall : null,
                'open_issues' => $prevOpen !== null && $openIssues !== null ? $openIssues - $prevOpen : null,
            ],
            'error'              => $scan->status === 'failed' ? ['code' => $scan->error_code, 'message' => $scan->error_message] : null,
            'created_at'         => $scan->created_at?->toIso8601String(),
            'finished_at'        => $scan->finished_at?->toIso8601String(),
        ];
    }

    private function present(MiraScan $scan, bool $withReport): array
    {
        return [
            'scan'   => $this->summarize($scan),
            'report' => $withReport && $scan->status === 'done' ? $scan->reportData() : null,
        ];
    }

    private function handleFor(MiraScan $scan, Entitlement $who, bool $reused): array
    {
        return $this->handles->issue($scan->uuid, $who->subject) + [
            'scan'   => $this->summarize($scan),
            'reused' => $reused,
        ];
    }

    private function dispatch(MiraScan $scan, bool $aiRecommendations): void
    {
        if (config('mira_premium.dispatch') === 'queue') {
            RunMiraScan::dispatch($scan->id, $aiRecommendations);
        } else {
            RunMiraScan::dispatchAfterResponse($scan->id, $aiRecommendations);
        }
    }

    private function fail(MiraScan $scan, string $code, string $message): void
    {
        $scan->update(['status' => 'failed', 'error_code' => $code, 'error_message' => mb_substr($message, 0, 500), 'finished_at' => now()]);

        // Failed scans are not charged: give the units back to the period they were taken from.
        $this->meter->refund($scan->subject, 'scans', $this->chargedPeriod($scan), $scan->kind === 'comparison' ? 2 : 1);
    }

    /** The quota period the scan was charged to: the owner's current tier's period for scans. */
    private function chargedPeriod(MiraScan $scan): string
    {
        $client = $scan->client_id ? \App\Models\Client::find($scan->client_id) : null;
        $who    = Entitlement::resolve($client, $scan->subject);

        return $who->limit('scans')[1] ?? 'month';
    }

    private function expireIfStuck(MiraScan $scan): MiraScan
    {
        $timeout = (int) config('mira_premium.scan_timeout_minutes', 6);

        if (!$scan->isFinished() && $scan->created_at->lt(now()->subMinutes($timeout))) {
            $this->fail($scan, 'Unavailable', 'The scan took too long and was stopped. It has not been counted against your plan.');
            $scan->refresh();
        }

        return $scan;
    }

    /** Preview comparisons keep every status and detail, but not the "how to fix" text. */
    private function withoutFixes(array $report): array
    {
        $strip = fn ($rows) => array_map(fn ($row) => array_diff_key($row, ['recommendation' => true]), $rows);
        $report['gaps']   = $strip($report['gaps'] ?? []);
        $report['checks'] = $strip($report['checks'] ?? []);

        return $report;
    }
}
