<?php

namespace App\Services\WebsiteAnalysis;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Page Speed checks: Core Web Vitals (LCP, INP, CLS) for mobile from the
 * Google PageSpeed Insights API (key: config services.pagespeed.api_key /
 * PAGESPEED_API_KEY; limits: config website_checks.pagespeed).
 *
 * Prefers real-user field data (this URL, then the whole origin). Without
 * field data it falls back to Lighthouse lab data for LCP and CLS (INP has no
 * lab equivalent). Anything it can't get — no key, API error, no data — is an
 * 'info' "Not measured" check, which doesn't count toward the score.
 */
class CoreWebVitals
{
    private const ENDPOINT = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';

    /** check name => [field metric key, lab audit id or null, config limits key, unit] */
    private const METRICS = [
        'Largest Contentful Paint (LCP)'  => ['LARGEST_CONTENTFUL_PAINT_MS', 'largest-contentful-paint', 'lcp_ms', 'ms'],
        'Interaction to Next Paint (INP)' => ['INTERACTION_TO_NEXT_PAINT', null, 'inp_ms', 'ms'],
        'Cumulative Layout Shift (CLS)'   => ['CUMULATIVE_LAYOUT_SHIFT_SCORE', 'cumulative-layout-shift', 'cls', 'score'],
    ];

    /** Lighthouse category id => check name */
    private const LIGHTHOUSE = [
        'performance'    => 'Lighthouse Performance',
        'accessibility'  => 'Lighthouse Accessibility',
        'best-practices' => 'Lighthouse Best Practices',
    ];

    public function __construct(private ?string $apiKey = null)
    {
        $this->apiKey ??= (string) config('services.pagespeed.api_key') ?: null;
    }

    /** @return list<array{category: string, name: string, status: string, detail: string, source?: string}> */
    public function checks(string $url): array
    {
        if (empty($this->apiKey)) {
            return $this->notMeasured('Not measured — PageSpeed Insights isn\'t connected (add PAGESPEED_API_KEY)');
        }

        try {
            // category repeats (category=performance&category=accessibility…), which an
            // array parameter would encode as category[0]=…, so the query is built by hand.
            $query = http_build_query(['url' => $url, 'strategy' => 'mobile', 'key' => $this->apiKey])
                . '&category=' . implode('&category=', array_keys(self::LIGHTHOUSE));
            $response = Http::timeout((int) config('website_checks.pagespeed.timeout_seconds', 45))
                ->get(self::ENDPOINT . '?' . $query);
        } catch (\Throwable $e) {
            Log::warning('PageSpeed Insights request failed', ['message' => $e->getMessage()]);
            return $this->notMeasured('Not measured — PageSpeed Insights didn\'t respond');
        }

        if (!$response->successful()) {
            Log::warning('PageSpeed Insights request failed', ['status' => $response->status(), 'message' => $response->json('error.message')]);
            return $this->notMeasured('Not measured — PageSpeed Insights returned an error');
        }

        $data   = $response->json();
        $field  = $data['loadingExperience']['metrics'] ?? [];
        $origin = $data['originLoadingExperience']['metrics'] ?? [];
        $audits = $data['lighthouseResult']['audits'] ?? [];

        $checks = [];
        foreach (self::METRICS as $name => [$fieldKey, $auditId, $limitsKey, $unit]) {
            [$value, $source] = match (true) {
                isset($field[$fieldKey]['percentile'])  => [$field[$fieldKey]['percentile'], 'real users, this page'],
                isset($origin[$fieldKey]['percentile']) => [$origin[$fieldKey]['percentile'], 'real users, whole site'],
                $auditId !== null && isset($audits[$auditId]['numericValue']) => [$audits[$auditId]['numericValue'], 'lab test'],
                default => [null, null],
            };

            if ($value === null) {
                $checks[] = $this->check($name, 'info', 'Not measured — no real-user data for this site yet' . ($auditId === null ? ' (no lab equivalent)' : ''));
                continue;
            }

            // Field CLS is reported ×100 (e.g. 5 = 0.05); lab CLS is the real value.
            if ($unit === 'score' && $source !== 'lab test') {
                $value = $value / 100;
            }

            $limits = config("website_checks.pagespeed.{$limitsKey}");
            $status = match (true) {
                $value <= $limits['good'] => 'pass',
                $value <= $limits['poor'] => 'warn',
                default                   => 'fail',
            };

            $shown = $unit === 'ms'
                ? ($value >= 1000 ? round($value / 1000, 1) . ' s' : round($value) . ' ms')
                : (string) round($value, 2);
            $goal = $unit === 'ms'
                ? ($limits['good'] >= 1000 ? ($limits['good'] / 1000) . ' s' : $limits['good'] . ' ms')
                : (string) $limits['good'];

            $check = $this->check($name, $status, "{$shown} ({$source}; good is ≤ {$goal})", $source);
            // Raw numbers for charts: value in ms (LCP, INP) or as a score (CLS), plus the limits.
            $checks[] = $check + ['value' => round($value, 3), 'unit' => $unit, 'limits' => $limits];
        }

        // Lighthouse category scores (0–100), with Lighthouse's own bands: 90+ good, 50–89 needs work.
        foreach (self::LIGHTHOUSE as $key => $name) {
            $score = data_get($data, "lighthouseResult.categories.{$key}.score");
            if ($score === null) {
                $checks[] = $this->check($name, 'info', 'Not measured — PageSpeed Insights returned no score') + ['lighthouse' => true];
                continue;
            }
            $score  = (int) round($score * 100);
            $status = $score >= 90 ? 'pass' : ($score >= 50 ? 'warn' : 'fail');
            $checks[] = $this->check($name, $status, "{$score}/100 (lab test; good is 90+)", 'lab test')
                + ['lighthouse' => true, 'value' => $score, 'unit' => 'score100'];
        }

        // Time until the page looks complete (Lighthouse Speed Index), for the "load time" tile.
        $speedIndex = data_get($audits, 'speed-index.numericValue');
        if ($speedIndex !== null) {
            $checks[] = $this->check('Load time', 'info', round($speedIndex / 1000, 1) . ' s until the page looks complete (Speed Index, lab test)', 'lab test')
                + ['lighthouse' => true, 'value' => round($speedIndex), 'unit' => 'ms'];
        }

        return $checks;
    }

    /** @return list<array> every metric and Lighthouse score as 'info' with the same reason */
    private function notMeasured(string $reason): array
    {
        return array_merge(
            array_map(fn ($name) => $this->check($name, 'info', $reason), array_keys(self::METRICS)),
            array_map(fn ($name) => $this->check($name, 'info', $reason) + ['lighthouse' => true], array_values(self::LIGHTHOUSE))
        );
    }

    private function check(string $name, string $status, string $detail, ?string $source = null): array
    {
        return array_filter([
            'category' => 'speed',
            'name'     => $name,
            'status'   => $status,
            'detail'   => $detail,
            'source'   => $source,
        ], fn ($v) => $v !== null);
    }
}
