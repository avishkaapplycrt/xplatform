<?php

namespace App\Services;

/**
 * Compares a visitor's website with a competitor's, for the homepage Ask
 * Mira "Analyse my website" tab. Runs the same free WebsiteAnalyzerService
 * checks on both sites, then lines the checks up one by one:
 *
 * - gaps:      checks the competitor does better — each with the fixed
 *              advice for the visitor's site (no OpenAI call; this is the
 *              free widget). The visitor's outright failures come first.
 * - strengths: checks the visitor already does better.
 *
 * Category scores and overall grades come from buildReport(), so the numbers
 * match a normal single-site analysis.
 */
class WebsiteComparisonService
{
    /** How good a check result is, for deciding who's ahead. 'info' checks aren't compared. */
    private const STATUS_RANK = ['fail' => 0, 'warn' => 1, 'pass' => 2];

    /**
     * Check names phrased as problems read oddly as a strength, so "Where
     * you're ahead" shows these labels instead (rows keep `name` unchanged).
     */
    private const STRENGTH_LABELS = [
        'Generic anchor text' => 'Descriptive anchor text',
    ];

    public function __construct(private ?WebsiteAnalyzerService $analyzer = null)
    {
        $this->analyzer ??= new WebsiteAnalyzerService();
    }

    /**
     * @return array{you: array, competitor: array, gaps: list<array>, strengths: list<array>}
     *
     * @throws WebsiteAnalyzerException naming which site couldn't be checked
     */
    public function compare(string $yourUrl, string $competitorUrl): array
    {
        // Tracking parameters (utm_*, gclid, …) are neither analysed nor shown.
        $yourUrl       = WebsiteAnalyzerService::cleanUrl($yourUrl);
        $competitorUrl = WebsiteAnalyzerService::cleanUrl($competitorUrl);
        if (rtrim(strtolower($yourUrl), '/') === rtrim(strtolower($competitorUrl), '/')) {
            throw new WebsiteAnalyzerException("Your competitor's website must be different from yours.");
        }

        // Each report is built straight after its own analysis: buildReport()
        // reads the page title from the most recently analysed page.
        $yourChecks = $this->checksFor($yourUrl, 'your website');
        $yourReport = $this->analyzer->buildReport($yourUrl, $yourChecks);

        $theirChecks = $this->checksFor($competitorUrl, "your competitor's website");
        $theirReport = $this->analyzer->buildReport($competitorUrl, $theirChecks);

        $labels = array_column($yourReport['categories'], 'label', 'key')
                + array_column($theirReport['categories'], 'label', 'key');

        $theirsByName = [];
        foreach ($theirChecks as $check) {
            $theirsByName[$check['name']] = $check;
        }

        $gaps = $strengths = $all = [];
        foreach ($yourChecks as $mine) {
            $theirs = $theirsByName[$mine['name']] ?? null;
            $all[] = $this->pairedCheck($mine, $theirs, $labels);
            $myRank    = self::STATUS_RANK[$mine['status']] ?? null;
            $theirRank = $theirs ? (self::STATUS_RANK[$theirs['status']] ?? null) : null;
            if ($myRank === null || $theirRank === null || $myRank === $theirRank) {
                continue;
            }

            $row = [
                'category'     => $mine['category'],
                'section'      => $labels[$mine['category']] ?? $mine['category'],
                'name'         => $mine['name'],
                // Display name for "Where you're ahead" (e.g. "Descriptive anchor text").
                'label'        => self::STRENGTH_LABELS[$mine['name']] ?? $mine['name'],
                'you_status'   => $mine['status'],
                'you_detail'   => $mine['detail'],
                'them_status'  => $theirs['status'],
                'them_detail'  => $theirs['detail'],
                'why'          => $mine['why'] ?? null,
                'check_with'   => $mine['check_with'] ?? null,
            ];

            if ($theirRank > $myRank) {
                $row['recommendation'] = $this->analyzer->recommendationFor($mine)
                    ?? "Your competitor passes this check — review \"{$mine['name']}\" on your site.";
                $gaps[] = $row;
            } else {
                $strengths[] = $row;
            }
        }

        // Biggest gaps first: your outright failures, then warnings.
        usort($gaps, fn ($a, $b) => self::STATUS_RANK[$a['you_status']] <=> self::STATUS_RANK[$b['you_status']]);

        return [
            'you'        => $this->summary($yourReport),
            'competitor' => $this->summary($theirReport),
            'gaps'       => $gaps,
            'strengths'  => $strengths,
            // KPIs a scan can't measure — "Not measured — connect <tool>", never estimated.
            'needs_connected_data' => $yourReport['needs_connected_data'] ?? [],
            // Every check side by side (incl. both passing / both failing), for the check-by-check report.
            'checks'     => $all,
            // Google's pipeline stages, each grouping some categories (config website_checks.stages).
            'stages'     => array_map(
                fn ($st) => ['key' => $st['key'], 'label' => $st['label'], 'question' => $st['question'], 'categories' => $st['categories']],
                (array) config('website_checks.stages', [])
            ),
            // The weights behind the scores, so the page can project a score as fixes are ticked off.
            'scoring'    => [
                'status_weights'   => config('website_checks.status_weights'),
                'category_weights' => config('website_checks.category_weights'),
            ],
        ];
    }

    /** One-line chat reply describing the result. */
    public function replyFor(array $comparison): string
    {
        $you  = $comparison['you'];
        $them = $comparison['competitor'];
        $gapCount = count($comparison['gaps']);

        $verdict = match (true) {
            $you['overall'] > $them['overall'] => "You're ahead overall",
            $you['overall'] < $them['overall'] => 'Your competitor is ahead overall',
            default                            => "You're level overall",
        };

        return "{$verdict}: {$you['overall']}/100 vs {$them['overall']}/100. "
            . ($gapCount > 0
                ? "They beat you on {$gapCount} check" . ($gapCount === 1 ? '' : 's') . " — the fixes are in the comparison below."
                : "They don't beat you on any check. Nice work — see the comparison below.");
    }

    /** @return list<array> the analysed checks, with errors naming which site failed */
    private function checksFor(string $url, string $whose): array
    {
        try {
            return $this->analyzer->analyzeSeoAndTechnical($url);
        } catch (WebsiteAnalyzerException $e) {
            throw new WebsiteAnalyzerException("I couldn't check {$whose} ({$url}). Make sure the address is correct and the site is publicly accessible.");
        }
    }

    /** One check for both sites: statuses, details, severity and (when you're not passing) the fix. */
    private function pairedCheck(array $mine, ?array $theirs, array $labels): array
    {
        return array_filter([
            'category'       => $mine['category'],
            'section'        => $labels[$mine['category']] ?? $mine['category'],
            'name'           => $mine['name'],
            'label'          => self::STRENGTH_LABELS[$mine['name']] ?? $mine['name'],
            'severity'       => config('website_checks.check_severity.' . $mine['name'], 'minor'),
            'you_status'     => $mine['status'],
            'you_detail'     => $mine['detail'],
            'them_status'    => $theirs['status'] ?? null,
            'them_detail'    => $theirs['detail'] ?? null,
            'why'            => $mine['why'] ?? null,
            'check_with'     => $mine['check_with'] ?? null,
            'recommendation' => $this->analyzer->recommendationFor($mine),
        ], fn ($v) => $v !== null);
    }

    private function summary(array $report): array
    {
        $host = parse_url($report['url'], PHP_URL_HOST) ?: $report['url'];

        return [
            'url'        => $report['url'],
            // Some sites double-encode their <title> ("&amp;amp;"), so decode what's left.
            'name'       => $report['title'] !== '' ? html_entity_decode($report['title'], ENT_QUOTES | ENT_HTML5) : preg_replace('/^www\./', '', $host),
            'overall'    => $report['overall'],
            'grade'      => $report['grade'],
            'categories' => array_map(
                fn ($c) => ['key' => $c['key'], 'label' => $c['label'], 'score' => $c['score']],
                $report['categories']
            ),
            'serp_preview'      => $report['serp_preview'] ?? null,
            'rich_result_types' => $report['rich_result_types'] ?? [],
            'open_graph'        => $report['open_graph'] ?? null,
            // AI crawler => 'allowed' | 'blocked' (robots.txt), llms.txt detail, and Core Web Vitals checks.
            'ai_crawlers'       => $this->checkField($report, 'AI crawler access', 'crawlers') ?? [],
            'llms_txt'          => $this->checkField($report, 'llms.txt', 'detail'),
            'vitals'            => array_values(array_map(
                fn ($c) => array_intersect_key($c, array_flip(['name', 'status', 'detail', 'value', 'unit', 'limits', 'source'])),
                array_filter($this->allChecks($report), fn ($c) => $c['category'] === 'speed')
            )),
        ];
    }

    private function allChecks(array $report): array
    {
        return array_merge(...array_map(fn ($c) => $c['checks'], $report['categories']));
    }

    private function checkField(array $report, string $name, string $field): mixed
    {
        foreach ($this->allChecks($report) as $check) {
            if ($check['name'] === $name) {
                return $check[$field] ?? null;
            }
        }

        return null;
    }
}
