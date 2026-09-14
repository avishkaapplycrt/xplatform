<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Runs a real check on a submitted URL, in two independent parts:
 *
 * - Speed: a Lighthouse audit via Google's PageSpeed Insights API — the same
 *   engine and scoring bands (red 0–49, orange 50–89, green 90–100) as
 *   Chrome DevTools' own Lighthouse panel. Requires a free Google API key
 *   (PAGESPEED_API_KEY); the unauthenticated tier has a hard quota of zero
 *   queries per day as of this writing, so there's no fallback for this part
 *   the way the chat assistant has one for a missing Anthropic key.
 *
 * - Free-tier page checks: fetches the page's own HTML and response headers
 *   once and checks them against nine client-facing categories — Page Titles
 *   & Descriptions, Content Structure, Images, Security & Trust, Mobile
 *   Friendliness, Links, Search Engine Visibility, Social Sharing Preview,
 *   and Rich Results Eligibility. No API key needed — this half always
 *   works, is reported separately from Speed, and is deliberately limited
 *   to checks that are 100% verifiable facts from a single page fetch (no
 *   judgment calls).
 */
class WebsiteAnalyzerService
{
    private const PAGESPEED_ENDPOINT = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';
    private const USER_AGENT = 'Mozilla/5.0 (compatible; XPlatformsAnalyzer/1.0; +https://xplatforms.example)';

    private const AUDITS = [
        'first-contentful-paint'   => 'First Contentful Paint',
        'largest-contentful-paint' => 'Largest Contentful Paint',
        'total-blocking-time'      => 'Total Blocking Time',
        'cumulative-layout-shift'  => 'Cumulative Layout Shift',
    ];

    /** Ordered free-tier check categories: key => client-facing display label. Order drives scoring iteration and UI category order. */
    private const CATEGORIES = [
        'titles'          => 'Page Titles & Descriptions',
        'structure'       => 'Content Structure',
        'images'          => 'Images',
        'security'        => 'Security & Trust',
        'mobile'          => 'Mobile Friendliness',
        'links'           => 'Links',
        'discoverability' => 'Search Engine Visibility',
        'social'          => 'Social Sharing Preview',
        'richresults'     => 'Rich Results Eligibility',
    ];

    private ?string $apiKey;

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? (string) config('services.pagespeed.api_key') ?: null;
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== null && $this->apiKey !== '';
    }

    /**
     * @return array{url: string, scores: array<string, int|null>, metrics: array<string, string|null>}
     *
     * @throws WebsiteAnalyzerException
     */
    public function analyze(string $url): array
    {
        if (!$this->isConfigured()) {
            throw new WebsiteAnalyzerException('PAGESPEED_API_KEY is not configured.');
        }

        $response = Http::timeout(45)->get(self::PAGESPEED_ENDPOINT, [
            'url'      => $url,
            'strategy' => 'mobile',
            'category' => ['performance', 'accessibility', 'best-practices', 'seo'],
            'key'      => $this->apiKey,
        ]);

        if (!$response->successful()) {
            $message = $response->json('error.message') ?? $response->body();
            Log::warning('PageSpeed Insights request failed', [
                'status'  => $response->status(),
                'message' => $message,
            ]);

            throw new WebsiteAnalyzerException(
                $response->status() === 429
                    ? 'Google\'s analysis quota is exhausted for today. Try again later.'
                    : "Could not analyze that URL: {$message}"
            );
        }

        $result = $response->json('lighthouseResult');

        if ($result === null) {
            throw new WebsiteAnalyzerException('Google returned no report for that URL — check it is publicly reachable.');
        }

        $scores = [];
        foreach (['performance', 'accessibility', 'best-practices', 'seo'] as $category) {
            $score = data_get($result, "categories.{$category}.score");
            $scores[$category] = $score !== null ? (int) round($score * 100) : null;
        }

        $metrics = [];
        foreach (self::AUDITS as $id => $label) {
            $metrics[$label] = data_get($result, "audits.{$id}.displayValue");
        }

        return [
            'url'     => $result['finalUrl'] ?? $url,
            'scores'  => $scores,
            'metrics' => $metrics,
        ];
    }

    /**
     * Fetches the page and checks it against nine objective, verifiable
     * free-tier categories — no API key required. Each check reports
     * 'pass' | 'warn' | 'fail' | 'info' ('info' items are observational and
     * don't count toward the score, e.g. word count, which has no
     * established pass/fail threshold).
     *
     * @return list<array{category: 'titles'|'structure'|'images'|'security'|'mobile'|'links'|'discoverability'|'social'|'richresults', name: string, status: string, detail: string}>
     *
     * @throws WebsiteAnalyzerException
     */
    public function analyzeSeoAndTechnical(string $url): array
    {
        $response = $this->fetchPage($url);

        if ($response === null || !$response->successful()) {
            throw new WebsiteAnalyzerException("Could not reach {$url} — check the URL is correct and publicly accessible.");
        }

        $xpath   = $this->parseHtml($response->body());
        $checks  = [];
        $origin  = $this->originOf($url);
        $isHttps = str_starts_with(strtolower($url), 'https://');

        // --- PAGE TITLES & DESCRIPTIONS ---
        $title = trim($xpath->query('//title')->item(0)?->textContent ?? '');
        $checks[] = $this->lengthCheck('titles', 'Title tag', $title, 30, 60);

        $description = trim($this->metaContent($xpath, 'description') ?? '');
        $checks[] = $this->lengthCheck('titles', 'Meta description', $description, 120, 160);

        // --- CONTENT STRUCTURE ---
        $h1Count = $xpath->query('//h1')->length;
        $checks[] = [
            'category' => 'structure', 'name' => 'H1 heading',
            'status'   => $h1Count === 1 ? 'pass' : 'warn',
            'detail'   => match (true) {
                $h1Count === 1 => '1 H1 found',
                $h1Count === 0 => 'No H1 found',
                default        => "{$h1Count} H1 tags found (should be exactly 1)",
            },
        ];

        $headingNodes  = $xpath->query('//h1 | //h2 | //h3 | //h4 | //h5 | //h6');
        $previousLevel = 0;
        $skipDetail    = null;
        foreach ($headingNodes as $heading) {
            $level = (int) substr($heading->nodeName, 1);
            if ($level > $previousLevel + 1 && $skipDetail === null) {
                $skipDetail = "h{$previousLevel} → h{$level}";
            }
            $previousLevel = $level;
        }
        $checks[] = [
            'category' => 'structure', 'name' => 'Heading order',
            'status'   => match (true) {
                $headingNodes->length === 0 => 'info',
                $skipDetail !== null        => 'warn',
                default                     => 'pass',
            },
            'detail'   => match (true) {
                $headingNodes->length === 0 => 'No headings found',
                $skipDetail !== null        => "Heading order skips a level ({$skipDetail})",
                default                     => 'Heading order is sequential',
            },
        ];

        $bodyText  = $xpath->query('//body')->item(0)?->textContent ?? '';
        $wordCount = str_word_count(preg_replace('/\s+/', ' ', trim($bodyText)));
        $checks[] = [
            'category' => 'structure', 'name' => 'Word count',
            'status'   => 'info',
            'detail'   => "{$wordCount} words",
        ];

        // --- IMAGES ---
        $images     = $xpath->query('//img');
        $missingAlt = 0;
        foreach ($images as $img) {
            if (trim($img->getAttribute('alt')) === '') {
                $missingAlt++;
            }
        }
        $checks[] = [
            'category' => 'images', 'name' => 'Image alt text',
            'status'   => $images->length === 0 ? 'info' : ($missingAlt === 0 ? 'pass' : 'warn'),
            'detail'   => $images->length === 0 ? 'No images found' : "{$missingAlt}/{$images->length} images missing alt text",
        ];

        // --- SECURITY & TRUST ---
        $checks[] = [
            'category' => 'security', 'name' => 'HTTPS',
            'status'   => $isHttps ? 'pass' : 'fail',
            'detail'   => $isHttps ? 'Site uses HTTPS' : 'Not using HTTPS',
        ];

        $mixedCount = $xpath->query('//img[starts-with(@src,"http://")] | //script[starts-with(@src,"http://")] | //link[@rel="stylesheet" and starts-with(@href,"http://")] | //iframe[starts-with(@src,"http://")] | //source[starts-with(@src,"http://")]')->length;
        $checks[] = [
            'category' => 'security', 'name' => 'Mixed content',
            'status'   => !$isHttps ? 'info' : ($mixedCount === 0 ? 'pass' : 'fail'),
            'detail'   => !$isHttps ? 'N/A — site is not served over HTTPS' : ($mixedCount === 0 ? 'No mixed content found' : "{$mixedCount} insecure (http://) resource(s) found"),
        ];

        // --- MOBILE FRIENDLINESS ---
        $hasViewport = $xpath->query('//meta[@name="viewport"]')->length > 0;
        $checks[] = [
            'category' => 'mobile', 'name' => 'Mobile viewport',
            'status'   => $hasViewport ? 'pass' : 'fail',
            'detail'   => $hasViewport ? 'Viewport tag present' : 'No viewport meta tag',
        ];

        // --- LINKS ---
        $targetHost = parse_url($url, PHP_URL_HOST);
        $internal   = 0;
        $external   = 0;
        foreach ($xpath->query('//a[@href]') as $anchor) {
            $href = $anchor->getAttribute('href');
            if ($href === '' || str_starts_with($href, '#')
                || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')
                || str_starts_with($href, 'javascript:')) {
                continue;
            }
            $linkHost = parse_url($href, PHP_URL_HOST);
            if ($linkHost === null || $linkHost === $targetHost) {
                $internal++;
            } else {
                $external++;
            }
        }
        $checks[] = [
            'category' => 'links', 'name' => 'Internal vs external links',
            'status'   => 'info',
            'detail'   => "{$internal} internal · {$external} external",
        ];

        $genericPhrases = ['click here', 'read more', 'learn more', 'here', 'more', 'this page', 'link', 'click', 'see more', 'continue reading'];
        $genericCount   = 0;
        foreach ($xpath->query('//a[normalize-space(text())!=""]') as $a) {
            $text = strtolower(trim(preg_replace('/\s+/', ' ', $a->textContent)));
            if (in_array($text, $genericPhrases, true)) {
                $genericCount++;
            }
        }
        $checks[] = [
            'category' => 'links', 'name' => 'Generic anchor text',
            'status'   => $genericCount === 0 ? 'pass' : 'warn',
            'detail'   => $genericCount === 0 ? 'No generic anchor text found' : "{$genericCount} generic anchor(s) found (e.g. \"click here\")",
        ];

        // --- SEARCH ENGINE VISIBILITY ---
        $robotsTxt = $this->fetchPage("{$origin}/robots.txt");
        $robotsOk  = $robotsTxt !== null && $robotsTxt->successful() && trim($robotsTxt->body()) !== '';
        $checks[] = [
            'category' => 'discoverability', 'name' => 'robots.txt',
            'status'   => $robotsOk ? 'pass' : 'warn',
            'detail'   => $robotsOk ? 'Found and readable' : 'Not found',
        ];

        $sitemapReferenced = $robotsOk && stripos($robotsTxt->body(), 'sitemap:') !== false;
        $sitemapDirect      = $sitemapReferenced ? null : $this->fetchPage("{$origin}/sitemap.xml");
        $sitemapOk           = $sitemapReferenced || ($sitemapDirect !== null && $sitemapDirect->successful());
        $checks[] = [
            'category' => 'discoverability', 'name' => 'sitemap.xml',
            'status'   => $sitemapOk ? 'pass' : 'warn',
            'detail'   => $sitemapOk ? 'Found' : 'Not found at /sitemap.xml or referenced in robots.txt',
        ];

        $hasCanonical = $xpath->query('//link[@rel="canonical"]')->length > 0;
        $checks[] = [
            'category' => 'discoverability', 'name' => 'Canonical tag',
            'status'   => $hasCanonical ? 'pass' : 'warn',
            'detail'   => $hasCanonical ? 'Present' : 'Missing',
        ];

        // --- SOCIAL SHARING PREVIEW ---
        $ogTitle       = $xpath->query('//meta[@property="og:title"]')->length > 0;
        $ogDescription = $xpath->query('//meta[@property="og:description"]')->length > 0;
        $ogImage       = $xpath->query('//meta[@property="og:image"]')->length > 0;
        $ogCount       = ($ogTitle ? 1 : 0) + ($ogDescription ? 1 : 0) + ($ogImage ? 1 : 0);
        $checks[] = [
            'category' => 'social', 'name' => 'Open Graph tags',
            'status'   => match (true) {
                $ogCount === 3 => 'pass',
                $ogCount === 0 => 'fail',
                default        => 'warn',
            },
            'detail'   => "{$ogCount}/3 Open Graph tags found",
        ];

        $hasTwitterCard = $xpath->query('//meta[@name="twitter:card"]')->length > 0;
        $checks[] = [
            'category' => 'social', 'name' => 'Twitter Card tags',
            'status'   => $hasTwitterCard ? 'pass' : 'warn',
            'detail'   => $hasTwitterCard ? 'twitter:card tag found' : 'No twitter:card tag found',
        ];

        // --- RICH RESULTS ELIGIBILITY ---
        $schemaCount = $xpath->query('//script[@type="application/ld+json"]')->length;
        $checks[] = [
            'category' => 'richresults', 'name' => 'Structured data',
            'status'   => $schemaCount > 0 ? 'pass' : 'warn',
            'detail'   => $schemaCount > 0 ? "{$schemaCount} JSON-LD block(s) found" : 'No JSON-LD schema found',
        ];

        return $checks;
    }

    /**
     * @param list<array{category: 'titles'|'structure'|'images'|'security'|'mobile'|'links'|'discoverability'|'social'|'richresults', name: string, status: string, detail: string}> $checks
     *
     * @return array{categoryScores: array<string, int|null>, counts: array{pass: int, warn: int, fail: int}}
     */
    public function scoreChecks(array $checks): array
    {
        $weight     = ['pass' => 1.0, 'warn' => 0.5, 'fail' => 0.0];
        $counts     = ['pass' => 0, 'warn' => 0, 'fail' => 0];
        $byCategory = [];

        foreach ($checks as $check) {
            if ($check['status'] !== 'info') {
                $counts[$check['status']]++;
            }
            $byCategory[$check['category']][] = $check;
        }

        $categoryScores = [];
        foreach ($byCategory as $category => $categoryChecks) {
            $scored = array_filter($categoryChecks, fn ($c) => $c['status'] !== 'info');
            if ($scored === []) {
                $categoryScores[$category] = null;
                continue;
            }
            $sum = array_sum(array_map(fn ($c) => $weight[$c['status']], $scored));
            $categoryScores[$category] = (int) round(($sum / count($scored)) * 100);
        }

        return ['categoryScores' => $categoryScores, 'counts' => $counts];
    }

    public function grade(int $score): string
    {
        return match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default      => 'F',
        };
    }

    /**
     * Turns the free-tier page checks into one chat-friendly report.
     *
     * @param list<array{category: 'titles'|'structure'|'images'|'security'|'mobile'|'links'|'discoverability'|'social'|'richresults', name: string, status: string, detail: string}> $checks
     */
    public function summarizeFull(string $url, array $checks): string
    {
        $scored         = $this->scoreChecks($checks);
        $categoryScores = $scored['categoryScores'];
        $counts         = $scored['counts'];

        $overallInputs = array_values(array_filter(array_values($categoryScores), fn ($v) => $v !== null));

        $overall = $overallInputs !== [] ? (int) round(array_sum($overallInputs) / count($overallInputs)) : 0;
        $grade   = $this->grade($overall);

        $lines   = [];
        $lines[] = "Site check for {$url} — {$overall}/100 (Grade {$grade}) · {$counts['pass']} pass, {$counts['warn']} warn, {$counts['fail']} fail";

        foreach (self::CATEGORIES as $category => $label) {
            $categoryChecks = array_values(array_filter($checks, fn ($c) => $c['category'] === $category));
            if ($categoryChecks === []) {
                continue;
            }
            $score  = $categoryScores[$category] ?? null;
            $header = $label . ($score !== null ? " — {$score}/100" : '');
            $lines[] = "\n{$header}";
            foreach ($categoryChecks as $check) {
                $mark = strtoupper($check['status']);
                $lines[] = "- {$check['name']}: {$mark} ({$check['detail']})";
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Structured version of summarizeFull() — same data, shaped for a UI
     * dashboard (per-category pie-chart cards) instead of a text blob.
     *
     * @param list<array{category: 'titles'|'structure'|'images'|'security'|'mobile'|'links'|'discoverability'|'social'|'richresults', name: string, status: string, detail: string}> $checks
     *
     * @return array{
     *   url: string, overall: int, grade: string,
     *   counts: array{pass: int, warn: int, fail: int},
     *   categories: list<array{key: string, label: string, score: int|null, checks?: array, subscores?: array, metrics?: array}>,
     * }
     */
    public function buildReport(string $url, array $checks): array
    {
        $scored         = $this->scoreChecks($checks);
        $categoryScores = $scored['categoryScores'];

        $overallInputs = array_values(array_filter(array_values($categoryScores), fn ($v) => $v !== null));
        $overall = $overallInputs !== [] ? (int) round(array_sum($overallInputs) / count($overallInputs)) : 0;

        $categories = [];

        foreach (self::CATEGORIES as $key => $label) {
            $categoryChecks = array_values(array_filter($checks, fn ($c) => $c['category'] === $key));
            if ($categoryChecks === []) {
                continue;
            }
            $categories[] = [
                'key'    => $key,
                'label'  => $label,
                'score'  => $categoryScores[$key] ?? null,
                'checks' => $categoryChecks,
            ];
        }

        return [
            'url'        => $url,
            'overall'    => $overall,
            'grade'      => $this->grade($overall),
            'counts'     => $scored['counts'],
            'categories' => $categories,
        ];
    }

    /**
     * Turns a Lighthouse-only analyze() result into a chat-friendly summary.
     * Kept for callers that only want the Speed half.
     */
    public function summarize(array $result): string
    {
        $labels = [
            'performance'    => 'Performance',
            'accessibility'  => 'Accessibility',
            'best-practices' => 'Best Practices',
            'seo'            => 'SEO',
        ];

        $scoreLines = [];
        $weakest    = null;
        foreach ($labels as $key => $label) {
            $score = $result['scores'][$key] ?? null;
            if ($score === null) {
                continue;
            }
            $scoreLines[] = "{$label} {$score}";
            if ($weakest === null || $score < $result['scores'][$weakest]) {
                $weakest = $key;
            }
        }

        $metricLines = [];
        foreach ($result['metrics'] as $label => $value) {
            if ($value !== null) {
                $metricLines[] = "{$label}: {$value}";
            }
        }

        $summary = "Lighthouse report for {$result['url']} (mobile): " . implode(' · ', $scoreLines);

        if ($metricLines !== []) {
            $summary .= "\n" . implode(' | ', $metricLines);
        }

        if ($weakest !== null && $result['scores'][$weakest] < 50) {
            $summary .= "\n\n{$labels[$weakest]} is the weak point (below 50, Lighthouse's red band) — that's the first thing worth fixing.";
        }

        return $summary;
    }

    private function fetchPage(string $url): ?Response
    {
        try {
            return Http::withHeaders(['User-Agent' => self::USER_AGENT])
                ->timeout(20)
                ->get($url);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseHtml(string $html): \DOMXPath
    {
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        return new \DOMXPath($doc);
    }

    private function metaContent(\DOMXPath $xpath, string $name): ?string
    {
        $node = $xpath->query("//meta[@name=\"{$name}\"]")->item(0);

        return $node?->getAttribute('content');
    }

    private function originOf(string $url): string
    {
        $parts = parse_url($url);

        return ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? '');
    }

    private function lengthCheck(string $category, string $name, string $value, int $min, int $max): array
    {
        $length = mb_strlen($value);

        if ($value === '') {
            return ['category' => $category, 'name' => $name, 'status' => 'fail', 'detail' => 'Missing'];
        }
        if ($length < $min || $length > $max) {
            return ['category' => $category, 'name' => $name, 'status' => 'warn', 'detail' => "{$length} characters (aim for {$min}\u{2013}{$max})"];
        }

        return ['category' => $category, 'name' => $name, 'status' => 'pass', 'detail' => "{$length} characters"];
    }
}
