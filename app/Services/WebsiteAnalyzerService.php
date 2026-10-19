<?php

namespace App\Services;

use App\Services\WebsiteAnalysis\CheckGuide;
use App\Services\WebsiteAnalysis\CoreWebVitals;
use App\Services\WebsiteAnalysis\ExtendedChecks;
use App\Services\WebsiteAnalysis\SerpPreview;
use App\Services\WebsiteAnalysis\SiteChecks;
use App\Services\WebsiteAnalysis\StructuredData;
use App\Services\WebsiteAnalysis\UrlCleaner;
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
    /** Second try when a site blocks the analyzer's own user agent (see fetchPage). */
    private const BROWSER_USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36';

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
        'crawl'           => 'Crawling & Indexing',
        'speed'           => 'Page Speed',
        'trust'           => 'Content Freshness & Trust',
        'ai'              => 'AI Visibility (AEO/GEO)',
        'social'          => 'Social Sharing Preview',
        'richresults'     => 'Rich Results Eligibility',
    ];

    private ?string $apiKey;

    private SeoRecommendationService $recommender;

    private CoreWebVitals $vitals;

    private ExtendedChecks $extended;

    private SiteChecks $site;

    /**
     * Content of the page most recently checked by analyzeSeoAndTechnical(),
     * so buildReport() can give OpenAI something site-specific to work from.
     *
     * @var array{url: string, title: string, description: string, headings: string[], text: string}|null
     */
    private ?array $lastPage = null;

    public function __construct(
        ?string $apiKey = null,
        ?SeoRecommendationService $recommender = null,
        ?CoreWebVitals $vitals = null,
        ?ExtendedChecks $extended = null,
        ?SiteChecks $site = null,
    ) {
        $this->apiKey      = $apiKey ?? (string) config('services.pagespeed.api_key') ?: null;
        $this->recommender = $recommender ?? new SeoRecommendationService();
        $this->vitals      = $vitals ?? new CoreWebVitals($this->apiKey);
        $this->extended    = $extended ?? new ExtendedChecks();
        $this->site        = $site ?? new SiteChecks();
    }

    /** Tracking parameters (utm_*, gclid, …) removed — see UrlCleaner. */
    public static function cleanUrl(string $url): string
    {
        return UrlCleaner::clean($url);
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
        $url      = self::cleanUrl($url);
        $response = $this->fetchPage($url);

        if ($response === null || !$response->successful()) {
            throw new WebsiteAnalyzerException("Could not reach {$url} — check the URL is correct and publicly accessible.");
        }

        $xpath   = $this->parseHtml($response->body());
        $checks  = [];
        $origin  = $this->originOf($url);
        $isHttps = str_starts_with(strtolower($url), 'https://');

        // --- PAGE TITLES & DESCRIPTIONS ---
        $titleLimits = config('website_checks.title_length', ['min' => 30, 'max' => 60]);
        $descLimits  = config('website_checks.description_length', ['min' => 120, 'max' => 160]);

        $title = trim($xpath->query('//title')->item(0)?->textContent ?? '');
        $checks[] = $this->lengthCheck('titles', 'Title tag', $title, $titleLimits['min'], $titleLimits['max']);

        $description = trim($this->metaContent($xpath, 'description') ?? '');
        $checks[] = $this->lengthCheck('titles', 'Meta description', $description, $descLimits['min'], $descLimits['max']);

        $this->lastPage = [
            'url'         => $url,
            'title'       => $title,
            'description' => $description,
            'headings'    => $this->pageHeadings($xpath),
            'text'        => $this->pageText($xpath),
        ];

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

        // Heading order: each heading at most one level deeper than the one
        // before. The first heading is compared against H1 (there's no "H0").
        $headingNodes  = $xpath->query('//h1 | //h2 | //h3 | //h4 | //h5 | //h6');
        $previousLevel = null;
        $orderProblem  = null;
        foreach ($headingNodes as $heading) {
            $level = (int) substr($heading->nodeName, 1);
            if ($orderProblem === null) {
                if ($previousLevel === null && $level > 1) {
                    $orderProblem = $h1Count === 0
                        ? "No H1 found; first heading is H{$level}"
                        : "Page starts with an H{$level} before its H1";
                } elseif ($previousLevel !== null && $level > $previousLevel + 1) {
                    $orderProblem = "Heading order skips a level (H{$previousLevel} → H{$level})";
                }
            }
            $previousLevel = $level;
        }
        $checks[] = [
            'category' => 'structure', 'name' => 'Heading order',
            'status'   => match (true) {
                $headingNodes->length === 0 => 'info',
                $orderProblem !== null      => 'warn',
                default                     => 'pass',
            },
            'detail'   => match (true) {
                $headingNodes->length === 0 => 'No headings found',
                $orderProblem !== null      => $orderProblem,
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

        // Language first: readability is only scored for English text.
        $language = $this->site->language($xpath, $this->lastPage['text']);
        $checks[] = $language;
        $checks[] = $this->site->readability($xpath, $language['detected'] ?? null);

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

        $checks[] = $this->site->brokenLinks($xpath, $url);

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
        // Some sites double-encode their tags ("&amp;amp;"), so decode what's left.
        $og = fn (string $property) => html_entity_decode(trim($xpath->query("//meta[@property=\"{$property}\"]")->item(0)?->getAttribute('content') ?? ''), ENT_QUOTES | ENT_HTML5);
        $this->lastPage['open_graph'] = [
            'title'       => $og('og:title'),
            'description' => $og('og:description'),
            'image'       => $og('og:image'),
        ];
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

        $structured = new StructuredData($xpath);

        // --- CRAWLING & INDEXING ---
        array_push($checks, ...$this->extended->crawl($response, $xpath));
        $checks[] = $this->site->redirects($url);

        // --- PAGE SPEED (Core Web Vitals via PageSpeed Insights; "Not measured" if unavailable) ---
        array_push($checks, ...$this->vitals->checks($url));

        // --- CONTENT FRESHNESS & TRUST ---
        array_push($checks, ...$this->extended->trust($xpath, $structured));

        // --- AI VISIBILITY (AEO/GEO) ---
        $llms    = $this->fetchPage("{$origin}/llms.txt");
        $hasLlms = $llms !== null && $llms->successful()
            && !str_contains(strtolower($llms->header('Content-Type')), 'html') && trim($llms->body()) !== '';
        array_push($checks, ...$this->extended->ai($xpath, $structured, $robotsOk ? $robotsTxt->body() : null, $hasLlms));

        // --- RICH RESULTS ELIGIBILITY ---
        // Which rich results the markup qualifies for, not just how many JSON-LD blocks there are.
        $richTypes = $structured->richResultTypes();
        $checks[] = [
            'category' => 'richresults', 'name' => 'Structured data',
            'status'   => $richTypes !== [] ? 'pass' : 'warn',
            'detail'   => match (true) {
                $richTypes !== []                => 'Qualifies for: ' . implode(', ', $richTypes),
                $structured->blockCount > 0      => "{$structured->blockCount} JSON-LD block(s), but none qualify for rich results",
                default                          => 'No JSON-LD schema found',
            },
            'rich_result_types' => $richTypes,
        ];
        $this->lastPage['rich_result_types'] = $richTypes;

        // "Why it matters" / "Check with" for every check.
        return array_map(fn ($check) => $check + CheckGuide::for($check['name']), $checks);
    }

    /**
     * @param list<array{category: 'titles'|'structure'|'images'|'security'|'mobile'|'links'|'discoverability'|'social'|'richresults', name: string, status: string, detail: string}> $checks
     *
     * @return array{categoryScores: array<string, int|null>, counts: array{pass: int, warn: int, fail: int}}
     */
    public function scoreChecks(array $checks): array
    {
        // Only pass/warn/fail are scored (weights in config/website_checks.php);
        // 'info' — observational, "Not measured", "not applicable" — never lowers a score.
        $weight     = config('website_checks.status_weights', ['pass' => 1.0, 'warn' => 0.5, 'fail' => 0.0]);
        $counts     = ['pass' => 0, 'warn' => 0, 'fail' => 0];
        $byCategory = [];

        foreach ($checks as $check) {
            if (array_key_exists($check['status'], $weight)) {
                $counts[$check['status']] = ($counts[$check['status']] ?? 0) + 1;
            }
            $byCategory[$check['category']][] = $check;
        }

        $categoryScores = [];
        foreach ($byCategory as $category => $categoryChecks) {
            $scored = array_filter($categoryChecks, fn ($c) => array_key_exists($c['status'], $weight));
            if ($scored === []) {
                $categoryScores[$category] = null;
                continue;
            }
            $sum = array_sum(array_map(fn ($c) => $weight[$c['status']], $scored));
            $categoryScores[$category] = (int) round(($sum / count($scored)) * 100);
        }

        return ['categoryScores' => $categoryScores, 'counts' => $counts];
    }

    /**
     * Overall score: the weighted average of the categories that have a score
     * (config website_checks.category_weights); unscored categories are left out.
     *
     * @param array<string, int|null> $categoryScores
     */
    public function overallScore(array $categoryScores): int
    {
        $weights = config('website_checks.category_weights', []);
        $total = $weightSum = 0.0;
        foreach ($categoryScores as $category => $score) {
            if ($score === null) {
                continue;
            }
            $w = (float) ($weights[$category] ?? 1.0);
            $total     += $score * $w;
            $weightSum += $w;
        }

        return $weightSum > 0 ? (int) round($total / $weightSum) : 0;
    }

    public function grade(int $score): string
    {
        foreach (config('website_checks.grades', ['A' => 90, 'B' => 80, 'C' => 70, 'D' => 60, 'F' => 0]) as $grade => $minimum) {
            if ($score >= $minimum) {
                return $grade;
            }
        }

        return 'F';
    }

    /**
     * Turns the free-tier page checks into one chat-friendly report.
     *
     * @param list<array{category: 'titles'|'structure'|'images'|'security'|'mobile'|'links'|'discoverability'|'social'|'richresults', name: string, status: string, detail: string}> $checks
     */
    public function summarizeFull(string $url, array $checks): string
    {
        // How much each check matters when it isn't passing (config website_checks.check_severity).
        $checks = array_map(fn ($c) => $c + ['severity' => config('website_checks.check_severity.' . $c['name'], 'minor')], $checks);

        $scored         = $this->scoreChecks($checks);
        $categoryScores = $scored['categoryScores'];
        $counts         = $scored['counts'];

        $overall = $this->overallScore($categoryScores);
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
     * Fallback advice for a failing or warning check, keyed by check name —
     * used when SeoRecommendationService (OpenAI) can't provide a
     * site-specific fix. A string applies to both statuses;
     * ['fail' => ..., 'warn' => ...] gives status-specific advice.
     */
    private const RECOMMENDATIONS = [
        'Title tag' => [
            'fail' => 'Add a <title> tag: your main keyword plus your brand, 30–60 characters, e.g. "Family Dental Care in Perth | BrightSmile".',
            'warn' => 'Rewrite the title to 30–60 characters so Google shows it in full: main keyword first, brand last.',
        ],
        'Meta description' => [
            'fail' => 'Add a meta description of 120–160 characters that says what the page offers and ends with a reason to click.',
            'warn' => 'Adjust the meta description to 120–160 characters so it isn\'t cut off or padded out in search results.',
        ],
        'H1 heading'                 => 'Use exactly one H1 that states what the page is about; turn any other H1s into H2s.',
        'Heading order'              => 'Keep headings in order (H1 → H2 → H3) without skipping levels, so search engines and screen readers follow the structure.',
        'Word count'                 => 'Add more useful text (aim for 300+ words): what you offer, who it\'s for, and answers to common questions.',
        'Image alt text'             => 'Give every meaningful image a short alt text describing it, e.g. alt="Customer using our app on a phone".',
        'HTTPS'                      => 'Serve the whole site over HTTPS with a valid SSL certificate and redirect all http:// pages to https://.',
        'Mixed content'              => 'Load every image, script and stylesheet over https://; mixed content triggers browser warnings and hurts trust.',
        'Mobile viewport'            => 'Add <meta name="viewport" content="width=device-width, initial-scale=1"> so the page scales properly on phones.',
        'Internal vs external links' => 'Link to your own key pages (services, contact, FAQs) from this page so visitors and search engines can reach them.',
        'Generic anchor text'        => 'Replace link text like "click here" or "read more" with words that describe the destination, e.g. "view our ring collection".',
        'robots.txt'                 => 'Add a robots.txt at the site root that allows crawling and points to your sitemap (Sitemap: https://yoursite/sitemap.xml).',
        'sitemap.xml'                => 'Publish a sitemap.xml listing your important pages and submit it in Google Search Console.',
        'Canonical tag'              => 'Add <link rel="canonical"> pointing to this page\'s preferred URL to avoid duplicate-content issues.',
        'Open Graph tags'            => 'Add og:title, og:description and og:image so links to this page look good when shared on Facebook and LinkedIn.',
        'Twitter Card tags'          => 'Add twitter:card (summary_large_image), twitter:title and twitter:image for a rich preview on X/Twitter.',
        'Structured data'            => 'Add JSON-LD structured data (Organization or LocalBusiness, plus FAQPage, Product, BreadcrumbList or AggregateRating where relevant) to qualify for rich results.',
        'Indexing (noindex)'              => 'Remove "noindex" from the robots meta tag and the X-Robots-Tag header (unless you deliberately want this page out of Google).',
        'Content without JavaScript'      => 'Render the main content in the HTML on the server (server-side rendering or pre-rendering) so crawlers see it without running JavaScript.',
        'Largest Contentful Paint (LCP)'  => 'Speed up the main content: compress and correctly size the hero image, preload it, and cut render-blocking CSS/JS. Aim for 2.5 s or less.',
        'Interaction to Next Paint (INP)' => 'Make taps and clicks respond faster: break up long JavaScript tasks and defer non-essential scripts. Aim for 200 ms or less.',
        'Cumulative Layout Shift (CLS)'   => 'Stop content jumping: set width/height on images and embeds and reserve space for banners and ads. Aim for 0.1 or less.',
        'Published / updated date'        => 'Show the published and last-updated date on the page and add datePublished / dateModified to the Article markup.',
        'Author information'              => 'Show who wrote the content (name, role, link to a bio) and add author to the Article markup.',
        'About / Contact / Privacy pages' => 'Link to your About, Contact and Privacy Policy pages from every page, usually in the footer.',
        'Business address & phone'        => 'Show your business address and a clickable phone number (tel: link), and add them to Organization/LocalBusiness markup.',
        'Review / rating schema'          => 'If you show genuine customer reviews, mark them up with Review / AggregateRating schema.',
        'FAQ / question headings'         => 'Add an FAQ section, or phrase key headings as the questions your customers ask.',
        'Short direct answers'            => 'Put a 20–60 word direct answer right under each question heading, then add detail below it.',
        'FAQPage & Organization schema'   => 'Add Organization markup with sameAs links to your official profiles, and FAQPage markup for your FAQ section.',
        'Broken links'                    => 'Fix or remove the broken links listed: point them at the page\'s new address (or add a 301 redirect on your own site).',
        'Redirects'                       => 'Link straight to the final address and collapse redirect chains into one 301; make every http:// address forward to https://.',
        'Readability'                     => 'Use shorter sentences (15–20 words) and everyday words, and break long paragraphs up with subheadings and lists.',
        'Language'                        => 'Set the page language on the html tag, e.g. <html lang="en">, matching the language the page is written in.',
        'Lighthouse Performance'          => 'Work through the Opportunities in PageSpeed Insights: compress images, remove unused JavaScript and CSS, and cache static files.',
        'Lighthouse Accessibility'        => 'Fix the items PageSpeed Insights lists under Accessibility: colour contrast, alt text, form labels and button names.',
        'Lighthouse Best Practices'       => 'Fix the items listed under Best Practices in PageSpeed Insights, such as console errors, insecure libraries and image aspect ratios.',
        'Pages with errors'               => 'Restore the pages that return errors, or 301-redirect them to the closest live page and remove them from your sitemap.',
        'Duplicate titles'                => 'Give every page its own title that names what is on that page; shared titles make pages compete with each other.',
        'Pages missing meta descriptions' => 'Write a 120–160 character meta description for each page listed, saying what that page offers.',
    ];

    /** Most recommendations listed on the "Site Overview" card. */
    private const MAX_TOP_RECOMMENDATIONS = 3;

    /**
     * Structured version of summarizeFull() — same data, shaped for a UI
     * dashboard (per-category pie-chart cards) instead of a text blob.
     * With $withRecommendations (Premium Mira only), every fail/warn check
     * gets a `recommendation`: site-specific from OpenAI when available,
     * otherwise the fixed RECOMMENDATIONS advice. Without it (the free Ask
     * Mira) there are no recommendations and no OpenAI call.
     *
     * @param list<array{category: 'titles'|'structure'|'images'|'security'|'mobile'|'links'|'discoverability'|'social'|'richresults', name: string, status: string, detail: string}> $checks
     *
     * @return array{
     *   url: string, overall: int, grade: string,
     *   counts: array{pass: int, warn: int, fail: int},
     *   categories: list<array{key: string, label: string, score: int|null, checks?: array, subscores?: array, metrics?: array}>,
     *   recommendations_source: 'ai'|'standard'|'none',
     *   top_recommendations: list<array{name: string, status: string, recommendation: string}>,
     * }
     */
    public function buildReport(string $url, array $checks, bool $withRecommendations = false): array
    {
        $url    = self::cleanUrl($url);
        $source = 'none';

        if ($withRecommendations) {
            // Fixed advice first, then let OpenAI replace it with site-specific
            // fixes. Any check OpenAI doesn't answer (or every check, if the
            // call fails or isn't configured) keeps the fixed advice.
            $checks = array_map(fn ($c) => $c + ['recommendation' => $this->recommendationFor($c)], $checks);
            $source = 'standard';

            $page   = ($this->lastPage['url'] ?? null) === $url ? $this->lastPage : null;
            $aiRecs = $this->recommender->recommend(
                $page,
                array_filter($checks, fn ($c) => in_array($c['status'], ['fail', 'warn'], true)),
                self::CATEGORIES
            );

            if ($aiRecs !== []) {
                foreach ($aiRecs as $i => $text) {
                    $checks[$i]['recommendation'] = $text;
                }
                $source = 'ai';
            }
        }

        // How much each check matters when it isn't passing (config website_checks.check_severity).
        $checks = array_map(fn ($c) => $c + ['severity' => config('website_checks.check_severity.' . $c['name'], 'minor')], $checks);

        $scored         = $this->scoreChecks($checks);
        $categoryScores = $scored['categoryScores'];
        $overall        = $this->overallScore($categoryScores);

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

        // Biggest problems first for the overview: every fail, then warns, in check order.
        $actionable = array_values(array_filter($checks, fn ($c) => ($c['recommendation'] ?? null) !== null));
        usort($actionable, fn ($a, $b) => ($a['status'] === 'fail' ? 0 : 1) <=> ($b['status'] === 'fail' ? 0 : 1));

        $page = ($this->lastPage['url'] ?? null) === $url ? $this->lastPage : null;

        return [
            'url'                 => $url,
            // Page <title>, for UIs that name the analysed site (e.g. Premium Mira's "Your website" card).
            'title'               => $page['title'] ?? '',
            // How the page would look as a Google search result.
            'serp_preview'        => SerpPreview::build($url, $page['title'] ?? '', $page['description'] ?? '', $page['text'] ?? ''),
            // Rich results the page's markup qualifies for (Product, Review stars, FAQ, Breadcrumb, Organization).
            'rich_result_types'   => $page['rich_result_types'] ?? [],
            // Open Graph title / description / image ('' when missing), for share previews.
            'open_graph'          => $page['open_graph'] ?? ['title' => '', 'description' => '', 'image' => ''],
            // KPIs a page scan can't measure — never estimated.
            'needs_connected_data' => array_map(
                fn ($k) => $k + ['status' => 'Not measured — connect ' . $k['tool']],
                (array) config('website_checks.connected_kpis', [])
            ),
            // Google's pipeline stages grouping the categories, and the weights behind
            // the scores (so a page can project a score as fixes are ticked off).
            'stages'              => array_map(
                fn ($st) => ['key' => $st['key'], 'label' => $st['label'], 'question' => $st['question'], 'categories' => $st['categories']],
                (array) config('website_checks.stages', [])
            ),
            'scoring'             => [
                'status_weights'   => config('website_checks.status_weights'),
                'category_weights' => config('website_checks.category_weights'),
            ],
            'overall'             => $overall,
            'grade'               => $this->grade($overall),
            'counts'              => $scored['counts'],
            'categories'          => $categories,
            'recommendations_source' => $source,   // 'ai' (OpenAI), 'standard' (fixed advice) or 'none' (free Ask Mira)
            'top_recommendations' => array_map(
                fn ($c) => ['name' => $c['name'], 'status' => $c['status'], 'recommendation' => $c['recommendation'],
                            'why' => $c['why'] ?? null, 'check_with' => $c['check_with'] ?? null],
                array_slice($actionable, 0, self::MAX_TOP_RECOMMENDATIONS)
            ),
        ];
    }

    /** The fix for a fail/warn check, or null when it passed (or has no advice yet). */
    public function recommendationFor(array $check): ?string
    {
        if (!in_array($check['status'], ['fail', 'warn'], true)) {
            return null;
        }

        $advice = self::RECOMMENDATIONS[$check['name']] ?? null;

        return is_array($advice) ? ($advice[$check['status']] ?? null) : $advice;
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
        // Bot protection (e.g. Cloudflare) sometimes blocks, rate-limits or
        // stalls a request for a moment, so a blocked or failed request gets
        // one more try as an ordinary browser before the site counts as unreachable.
        $attempts = [self::USER_AGENT, self::BROWSER_USER_AGENT];
        $response = null;
        $error    = null;

        foreach ($attempts as $i => $userAgent) {
            try {
                $response = Http::withHeaders([
                    'User-Agent'      => $userAgent,
                    'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ])->timeout(20)->get($url);
                $error = null;
            } catch (\Throwable $e) {
                $response = null;
                $error    = $e->getMessage();
            }

            $blocked = $response === null || in_array($response->status(), [403, 429, 503], true);
            if (!$blocked) {
                return $response;
            }
            if ($i < count($attempts) - 1) {
                usleep(800_000);
            }
        }

        Log::warning('Website analysis: could not fetch page', [
            'url'    => $url,
            'status' => $response?->status(),
            'error'  => $error,
        ]);

        return $response;
    }

    /** @return string[] H1–H3 texts, e.g. "H2: Our services", at most 25. */
    private function pageHeadings(\DOMXPath $xpath): array
    {
        $headings = [];
        foreach ($xpath->query('//h1|//h2|//h3') as $h) {
            $text = trim(preg_replace('/\s+/u', ' ', $h->textContent));
            if ($text !== '' && count($headings) < 25) {
                $headings[] = strtoupper($h->nodeName) . ': ' . $text;
            }
        }

        return $headings;
    }

    /** Visible body text (no script/style), whitespace-collapsed, first ~3000 characters. */
    private function pageText(\DOMXPath $xpath): string
    {
        $parts = [];
        foreach ($xpath->query('//body//text()[not(ancestor::script) and not(ancestor::style) and not(ancestor::noscript)]') as $node) {
            $parts[] = $node->nodeValue;
        }

        return mb_substr(trim(preg_replace('/\s+/u', ' ', implode(' ', $parts))), 0, 3000);
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
