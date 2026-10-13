<?php

namespace Tests\Feature;

use App\Services\WebsiteAnalysis\SerpPreview;
use App\Services\WebsiteAnalysis\UrlCleaner;
use App\Services\WebsiteAnalyzerService;
use App\Services\WebsiteComparisonService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The "Analyse my website" / "Compare with a competitor" checks
 * (WebsiteAnalyzerService + App\Services\WebsiteAnalysis). Every page,
 * robots.txt, llms.txt and PageSpeed Insights response is faked — no network.
 */
class WebsiteAnalysisChecksTest extends TestCase
{
    private const SITE = 'https://site.test/';

    // ---------------------------------------------------------------- helpers

    /**
     * Fakes https://site.test (and optionally PageSpeed Insights), then
     * analyses it. Returns [checks keyed by name, report].
     */
    private function analyse(string $body, array $options = []): array
    {
        $this->fakeSite('site.test', $body, $options);

        $analyzer = new WebsiteAnalyzerService($options['api_key'] ?? null);
        $url      = $options['url'] ?? self::SITE;
        $checks   = $analyzer->analyzeSeoAndTechnical($url);
        $report   = $analyzer->buildReport($url, $checks);

        return [array_column($checks, null, 'name'), $report];
    }

    private function fakeSite(string $host, string $body, array $options = []): void
    {
        // A fresh fake client each time: Http::fake() adds to earlier fakes
        // (first match wins), so a second analysis in one test would get the
        // first page otherwise.
        Http::swap(new \Illuminate\Http\Client\Factory());

        $fakes = [
            "https://{$host}/robots.txt"  => isset($options['robots'])
                ? Http::response($options['robots'], 200, ['Content-Type' => 'text/plain'])
                : Http::response('', 404),
            "https://{$host}/sitemap.xml" => Http::response('', 404),
            "https://{$host}/llms.txt"    => isset($options['llms'])
                ? Http::response($options['llms'], 200, ['Content-Type' => 'text/plain'])
                : Http::response('Not found', 404, ['Content-Type' => 'text/html']),
            'https://www.googleapis.com/*' => $options['psi'] ?? Http::response(['error' => ['message' => 'boom']], 500),
            "https://{$host}/*"           => Http::response(
                $this->page($body, $options['head'] ?? ''),
                200,
                ['Content-Type' => 'text/html'] + ($options['headers'] ?? [])
            ),
        ];
        Http::fake($fakes);
    }

    private function page(string $body, string $head = ''): string
    {
        return "<!doctype html><html lang=\"en\"><head><title>Test page title for the site</title>{$head}</head><body>{$body}</body></html>";
    }

    /** About 200 words of plain body text, so the page isn't mistaken for a JavaScript shell. */
    private function prose(): string
    {
        return '<p>' . str_repeat('We help local businesses understand their customers with clear reports. ', 20) . '</p>';
    }

    // ------------------------------------------------------------ URL cleaning

    public function test_tracking_parameters_are_stripped(): void
    {
        $this->assertSame(
            'https://a.com/p?id=7#top',
            UrlCleaner::clean('https://a.com/p?utm_source=x&utm_medium=y&gclid=1&fbclid=2&srsltid=3&id=7#top')
        );
        $this->assertSame('https://a.com/p', UrlCleaner::clean('https://a.com/p?utm_campaign=x&srsltid=AfmB'));
        $this->assertSame('https://a.com/p?q=1', UrlCleaner::clean('https://a.com/p?q=1'));
    }

    public function test_analysis_uses_and_displays_the_cleaned_url(): void
    {
        [, $report] = $this->analyse($this->prose(), ['url' => 'https://site.test/?utm_source=google&srsltid=abc']);

        $this->assertSame('https://site.test/', $report['url']);
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'srsltid') || str_contains($request->url(), 'utm_'));
    }

    // ---------------------------------------------------- Crawling & Indexing

    public function test_noindex_in_meta_robots_fails(): void
    {
        [$checks] = $this->analyse($this->prose(), ['head' => '<meta name="robots" content="noindex, follow">']);

        $this->assertSame('fail', $checks['Indexing (noindex)']['status']);
        $this->assertStringContainsString('robots meta tag', $checks['Indexing (noindex)']['detail']);
    }

    public function test_noindex_in_x_robots_tag_header_fails(): void
    {
        [$checks] = $this->analyse($this->prose(), ['headers' => ['X-Robots-Tag' => 'noindex']]);

        $this->assertSame('fail', $checks['Indexing (noindex)']['status']);
        $this->assertStringContainsString('X-Robots-Tag', $checks['Indexing (noindex)']['detail']);
    }

    public function test_indexable_page_passes(): void
    {
        [$checks] = $this->analyse($this->prose());

        $this->assertSame('pass', $checks['Indexing (noindex)']['status']);
    }

    public function test_javascript_shell_is_flagged(): void
    {
        [$checks] = $this->analyse('<div id="root"></div><script src="/app.js"></script><noscript>You need to enable JavaScript to run this app.</noscript>');

        $this->assertSame('warn', $checks['Content without JavaScript']['status']);
        $this->assertStringContainsString('JavaScript', $checks['Content without JavaScript']['detail']);
    }

    public function test_content_in_raw_html_passes_the_javascript_check(): void
    {
        [$checks] = $this->analyse($this->prose());

        $this->assertSame('pass', $checks['Content without JavaScript']['status']);
    }

    // ------------------------------------------------------------- Page Speed

    public function test_core_web_vitals_from_field_data(): void
    {
        $psi = Http::response(['loadingExperience' => ['metrics' => [
            'LARGEST_CONTENTFUL_PAINT_MS'   => ['percentile' => 2100],
            'INTERACTION_TO_NEXT_PAINT'     => ['percentile' => 450],
            'CUMULATIVE_LAYOUT_SHIFT_SCORE' => ['percentile' => 30],   // ×100 → 0.30
        ]]]);
        [$checks, $report] = $this->analyse($this->prose(), ['api_key' => 'test-key', 'psi' => $psi]);

        $this->assertSame('pass', $checks['Largest Contentful Paint (LCP)']['status']);
        $this->assertStringContainsString('2.1 s', $checks['Largest Contentful Paint (LCP)']['detail']);
        $this->assertStringContainsString('real users', $checks['Largest Contentful Paint (LCP)']['detail']);
        $this->assertSame('warn', $checks['Interaction to Next Paint (INP)']['status']);   // 450 ms: between 200 and 500
        $this->assertSame('fail', $checks['Cumulative Layout Shift (CLS)']['status']);    // 0.30 > 0.25
        $this->assertNotNull(collect($report['categories'])->firstWhere('key', 'speed')['score']);
        // Raw numbers for the Core Web Vitals chart.
        $this->assertEquals(2100, $checks['Largest Contentful Paint (LCP)']['value']);
        $this->assertSame('ms', $checks['Largest Contentful Paint (LCP)']['unit']);
        $this->assertEquals(0.3, $checks['Cumulative Layout Shift (CLS)']['value']);
        $this->assertEquals(['good' => 0.1, 'poor' => 0.25], $checks['Cumulative Layout Shift (CLS)']['limits']);
    }

    public function test_core_web_vitals_fall_back_to_lab_data(): void
    {
        $psi = Http::response(['lighthouseResult' => ['audits' => [
            'largest-contentful-paint' => ['numericValue' => 3000],
            'cumulative-layout-shift'  => ['numericValue' => 0.05],
        ]]]);
        [$checks] = $this->analyse($this->prose(), ['api_key' => 'test-key', 'psi' => $psi]);

        $this->assertSame('warn', $checks['Largest Contentful Paint (LCP)']['status']);
        $this->assertStringContainsString('lab test', $checks['Largest Contentful Paint (LCP)']['detail']);
        $this->assertSame('pass', $checks['Cumulative Layout Shift (CLS)']['status']);
        $this->assertSame('info', $checks['Interaction to Next Paint (INP)']['status']);   // no lab equivalent
        $this->assertStringContainsString('Not measured', $checks['Interaction to Next Paint (INP)']['detail']);
    }

    public function test_pagespeed_failure_is_not_measured_and_not_scored(): void
    {
        [$checks, $report] = $this->analyse($this->prose(), ['api_key' => 'test-key']);   // fake returns HTTP 500

        foreach (['Largest Contentful Paint (LCP)', 'Interaction to Next Paint (INP)', 'Cumulative Layout Shift (CLS)'] as $name) {
            $this->assertSame('info', $checks[$name]['status']);
            $this->assertStringContainsString('Not measured', $checks[$name]['detail']);
        }
        $this->assertNull(collect($report['categories'])->firstWhere('key', 'speed')['score']);
    }

    public function test_missing_api_key_is_not_measured(): void
    {
        [$checks] = $this->analyse($this->prose());

        $this->assertSame('info', $checks['Largest Contentful Paint (LCP)']['status']);
        $this->assertStringContainsString('PAGESPEED_API_KEY', $checks['Largest Contentful Paint (LCP)']['detail']);
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'googleapis.com'));
    }

    // -------------------------------------------------------------- Scoring

    public function test_not_measured_checks_do_not_lower_the_score(): void
    {
        $analyzer = new WebsiteAnalyzerService();
        $scored = $analyzer->scoreChecks([
            ['category' => 'speed', 'name' => 'LCP', 'status' => 'info', 'detail' => 'Not measured'],
            ['category' => 'titles', 'name' => 'Title tag', 'status' => 'pass', 'detail' => 'ok'],
            ['category' => 'ai', 'name' => 'AI crawler access', 'status' => 'info', 'detail' => 'Blocked: GPTBot'],
            ['category' => 'ai', 'name' => 'FAQ / question headings', 'status' => 'pass', 'detail' => 'ok'],
        ]);

        $this->assertNull($scored['categoryScores']['speed']);
        $this->assertSame(100, $scored['categoryScores']['ai']);
        $this->assertSame(100, $analyzer->overallScore($scored['categoryScores']));
    }

    public function test_weights_and_thresholds_come_from_config(): void
    {
        config(['website_checks.title_length' => ['min' => 10, 'max' => 60]]);
        [$checks] = $this->analyse($this->prose());
        $this->assertSame('pass', $checks['Title tag']['status']);   // "Test page title for the site" = 28 chars

        config(['website_checks.status_weights' => ['pass' => 1.0, 'warn' => 0.0, 'fail' => 0.0]]);
        $scored = (new WebsiteAnalyzerService())->scoreChecks([
            ['category' => 'titles', 'name' => 'a', 'status' => 'pass', 'detail' => ''],
            ['category' => 'titles', 'name' => 'b', 'status' => 'warn', 'detail' => ''],
        ]);
        $this->assertSame(50, $scored['categoryScores']['titles']);
    }

    // ------------------------------------------- Content Freshness & Trust

    public function test_article_date_and_author_are_checked(): void
    {
        $head = '<meta property="og:type" content="article"><meta property="article:published_time" content="2026-09-01T10:00:00Z"><meta name="author" content="Jane Doe">';
        [$checks] = $this->analyse($this->prose(), ['head' => $head]);

        $this->assertSame('pass', $checks['Published / updated date']['status']);
        $this->assertStringContainsString('2026-09-01', $checks['Published / updated date']['detail']);
        $this->assertSame('pass', $checks['Author information']['status']);
    }

    public function test_date_and_author_are_not_applicable_on_non_article_pages(): void
    {
        [$checks] = $this->analyse($this->prose());

        $this->assertSame('info', $checks['Published / updated date']['status']);
        $this->assertSame('info', $checks['Author information']['status']);
    }

    public function test_article_without_date_or_author_warns(): void
    {
        [$checks] = $this->analyse($this->prose(), ['head' => '<meta property="og:type" content="article">']);

        $this->assertSame('warn', $checks['Published / updated date']['status']);
        $this->assertSame('warn', $checks['Author information']['status']);
    }

    public function test_about_contact_privacy_links(): void
    {
        [$checks] = $this->analyse($this->prose() . '<a href="/about-us">About</a><a href="/contact">Contact</a><a href="/privacy-policy">Privacy</a>');
        $this->assertSame('pass', $checks['About / Contact / Privacy pages']['status']);

        [$checks] = $this->analyse($this->prose() . '<a href="/about-us">About</a>');
        $this->assertSame('warn', $checks['About / Contact / Privacy pages']['status']);
        $this->assertStringContainsString('Contact, Privacy', $checks['About / Contact / Privacy pages']['detail']);
    }

    public function test_address_and_phone(): void
    {
        [$checks] = $this->analyse($this->prose() . '<address>1 Main St, Perth</address><a href="tel:+61800000000">Call us</a>');
        $this->assertSame('pass', $checks['Business address & phone']['status']);

        [$checks] = $this->analyse($this->prose() . '<a href="tel:+61800000000">Call us</a>');
        $this->assertSame('warn', $checks['Business address & phone']['status']);
        $this->assertStringContainsString('no address', $checks['Business address & phone']['detail']);
    }

    public function test_review_rating_schema(): void
    {
        $schema = '<script type="application/ld+json">{"@type":"Product","name":"Ring","aggregateRating":{"@type":"AggregateRating","ratingValue":"4.8","reviewCount":"20"}}</script>';
        [$checks] = $this->analyse($this->prose() . $schema);
        $this->assertSame('pass', $checks['Review / rating schema']['status']);

        [$checks] = $this->analyse($this->prose());
        $this->assertSame('warn', $checks['Review / rating schema']['status']);
    }

    // ------------------------------------------------ AI Visibility (AEO/GEO)

    public function test_blocked_ai_crawlers_are_reported_as_a_choice_not_scored(): void
    {
        $robots = "User-agent: GPTBot\nDisallow: /\n\nUser-agent: CCBot\nDisallow: /\n\nUser-agent: *\nAllow: /\n";
        [$checks] = $this->analyse($this->prose(), ['robots' => $robots]);

        $check = $checks['AI crawler access'];
        $this->assertSame('info', $check['status']);
        $this->assertStringContainsString('GPTBot, CCBot', $check['detail']);
        $this->assertStringContainsString('choice to review', $check['detail']);
        $this->assertSame('blocked', $check['crawlers']['GPTBot']);
        $this->assertSame('allowed', $check['crawlers']['ClaudeBot']);
    }

    public function test_wildcard_disallow_blocks_every_ai_crawler(): void
    {
        [$checks] = $this->analyse($this->prose(), ['robots' => "User-agent: *\nDisallow: /\n"]);

        $this->assertSame(['blocked'], array_values(array_unique($checks['AI crawler access']['crawlers'])));
    }

    public function test_question_headings_and_short_answers(): void
    {
        $answer = str_repeat('word ', 30);
        [$checks] = $this->analyse($this->prose() . "<h2>What do you sell?</h2><p>{$answer}</p><h2>Where are you based?</h2><p>Perth.</p>");

        $this->assertSame('pass', $checks['FAQ / question headings']['status']);
        $this->assertSame('pass', $checks['Short direct answers']['status']);
        $this->assertStringContainsString('1 of 2', $checks['Short direct answers']['detail']);
    }

    public function test_question_headings_without_short_answers_warn(): void
    {
        [$checks] = $this->analyse($this->prose() . '<h2>What do you sell?</h2><p>Rings.</p><h2>Where are you?</h2><p>Perth.</p>');

        $this->assertSame('warn', $checks['Short direct answers']['status']);
    }

    public function test_no_question_headings(): void
    {
        [$checks] = $this->analyse($this->prose());

        $this->assertSame('warn', $checks['FAQ / question headings']['status']);
        $this->assertSame('info', $checks['Short direct answers']['status']);   // nothing to answer
    }

    public function test_faq_section_passes_without_question_headings(): void
    {
        [$checks] = $this->analyse($this->prose() . '<section id="faq"><h2>Frequently asked questions</h2></section>');

        $this->assertSame('pass', $checks['FAQ / question headings']['status']);
    }

    public function test_faqpage_and_organization_schema_with_same_as(): void
    {
        $full = '<script type="application/ld+json">{"@context":"https://schema.org","@graph":[{"@type":"Organization","name":"X","sameAs":["https://www.linkedin.com/company/x"]},{"@type":"FAQPage","mainEntity":[]}]}</script>';
        [$checks] = $this->analyse($this->prose() . $full);
        $this->assertSame('pass', $checks['FAQPage & Organization schema']['status']);

        [$checks] = $this->analyse($this->prose() . '<script type="application/ld+json">{"@type":"Organization","name":"X"}</script>');
        $this->assertSame('warn', $checks['FAQPage & Organization schema']['status']);
        $this->assertStringContainsString('sameAs profiles ✗', $checks['FAQPage & Organization schema']['detail']);

        [$checks] = $this->analyse($this->prose());
        $this->assertSame('fail', $checks['FAQPage & Organization schema']['status']);
    }

    public function test_llms_txt_is_optional_information_only(): void
    {
        [$checks] = $this->analyse($this->prose(), ['llms' => "# Site\n> Summary"]);
        $this->assertSame('info', $checks['llms.txt']['status']);
        $this->assertStringStartsWith('Found', $checks['llms.txt']['detail']);

        [$checks] = $this->analyse($this->prose());
        $this->assertSame('info', $checks['llms.txt']['status']);
        $this->assertStringContainsString('optional', $checks['llms.txt']['detail']);
    }

    // ------------------------------------------------------ Rich results

    public function test_rich_result_types_are_listed(): void
    {
        $schema = '<script type="application/ld+json">[{"@type":"Product","name":"Ring","aggregateRating":{"@type":"AggregateRating","ratingValue":"5","reviewCount":"3"}},'
                . '{"@type":"FAQPage"},{"@type":"BreadcrumbList","itemListElement":[]},{"@type":"JewelryStore","name":"X"}]</script>';
        [$checks, $report] = $this->analyse($this->prose() . $schema);

        $expected = ['Product', 'Review stars', 'FAQ', 'Breadcrumb', 'Organization'];
        $this->assertSame('Qualifies for: ' . implode(', ', $expected), $checks['Structured data']['detail']);
        $this->assertSame($expected, $report['rich_result_types']);
    }

    public function test_json_ld_without_eligible_types_warns(): void
    {
        [$checks] = $this->analyse($this->prose() . '<script type="application/ld+json">{"@type":"WebPage","name":"X"}</script>');

        $this->assertSame('warn', $checks['Structured data']['status']);
        $this->assertStringContainsString('none qualify', $checks['Structured data']['detail']);
    }

    // ---------------------------------------------------- Google preview

    public function test_serp_preview_truncates_like_google(): void
    {
        $preview = SerpPreview::build(
            'https://www.example.com/blog/my-post?id=1',
            'A very long page title that goes on and on well beyond sixty characters in total',
            str_repeat('Helpful description words. ', 12)
        );

        $this->assertTrue($preview['title_truncated']);
        $this->assertLessThanOrEqual(61, mb_strlen($preview['title']));
        $this->assertStringEndsWith('…', $preview['title']);
        $this->assertSame('example.com › blog › my-post', $preview['display_url']);
        $this->assertTrue($preview['description_truncated']);
        $this->assertLessThanOrEqual(161, mb_strlen($preview['description']));
        $this->assertSame('meta', $preview['description_source']);
    }

    public function test_serp_preview_falls_back_to_page_text_without_a_meta_description(): void
    {
        [, $report] = $this->analyse($this->prose());

        $this->assertSame('page_text', $report['serp_preview']['description_source']);
        $this->assertSame('Test page title for the site', $report['serp_preview']['title']);
    }

    // ------------------------------------------- Guide lines & connected data

    public function test_every_check_has_why_it_matters_and_check_with(): void
    {
        [$checks] = $this->analyse($this->prose());

        foreach ($checks as $name => $check) {
            $this->assertNotEmpty($check['why'] ?? null, "No 'why' for {$name}");
            $this->assertNotEmpty($check['check_with'] ?? null, "No 'check_with' for {$name}");
        }
    }

    public function test_needs_connected_data_is_never_a_number(): void
    {
        [, $report] = $this->analyse($this->prose());

        $kpis = array_column($report['needs_connected_data'], 'kpi');
        foreach (['Organic sessions', 'Engaged session rate', 'Conversion rate', 'Index coverage', 'CTR by position', 'Referring domains / backlinks', 'AI Overview inclusion', 'AI referral traffic'] as $kpi) {
            $this->assertContains($kpi, $kpis);
        }
        foreach ($report['needs_connected_data'] as $row) {
            // Only the KPI, the tool and "Not measured" — no value field, no estimate.
            $this->assertSame(['kpi', 'tool', 'status'], array_keys($row));
            $this->assertSame('Not measured — connect ' . $row['tool'], $row['status']);
        }
    }

    // ------------------------------------------------------------ Bug fixes

    public function test_heading_order_never_mentions_h0(): void
    {
        [$checks] = $this->analyse($this->prose() . '<h5>Small heading</h5><h6>Smaller</h6>');
        $this->assertSame('No H1 found; first heading is H5', $checks['Heading order']['detail']);

        [$checks] = $this->analyse($this->prose() . '<h1>Title</h1><h3>Skipped</h3>');
        $this->assertSame('Heading order skips a level (H1 → H3)', $checks['Heading order']['detail']);
        $this->assertStringNotContainsStringIgnoringCase('h0', $checks['Heading order']['detail']);
    }

    public function test_where_youre_ahead_shows_descriptive_anchor_text(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake([
            'https://www.googleapis.com/*' => Http::response([], 500),
            'https://*/robots.txt'         => Http::response('', 404),
            'https://*/sitemap.xml'        => Http::response('', 404),
            'https://*/llms.txt'           => Http::response('', 404),
            'https://mine.test/*'          => Http::response($this->page($this->prose() . '<a href="/rings">View our rings</a>')),
            'https://rival.test/*'         => Http::response($this->page($this->prose() . '<a href="/rings">click here</a>')),
        ]);

        $comparison = (new WebsiteComparisonService())->compare('https://mine.test/', 'https://rival.test/?gclid=1');
        $strength = collect($comparison['strengths'])->firstWhere('name', 'Generic anchor text');

        $this->assertNotNull($strength);
        $this->assertSame('Descriptive anchor text', $strength['label']);
        $this->assertSame('https://rival.test/', $comparison['competitor']['url']);   // tracking parameter stripped
        $this->assertArrayHasKey('needs_connected_data', $comparison);
    }

    public function test_comparison_lists_every_check_with_stages_and_site_details(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake([
            'https://www.googleapis.com/*' => Http::response([], 500),
            'https://mine.test/robots.txt' => Http::response("User-agent: GPTBot
Disallow: /
"),
            'https://*/robots.txt'         => Http::response('', 404),
            'https://*/sitemap.xml'        => Http::response('', 404),
            'https://*/llms.txt'           => Http::response('', 404),
            'https://mine.test/*'          => Http::response($this->page($this->prose(), '<meta property="og:title" content="Mine OG"><meta property="og:image" content="/share.jpg">')),
            'https://rival.test/*'         => Http::response($this->page($this->prose())),
        ]);

        $comparison = (new WebsiteComparisonService())->compare('https://mine.test/', 'https://rival.test/');

        // Every check, including ones both sites pass — not just gaps and strengths.
        $names = array_column($comparison['checks'], 'name');
        $this->assertContains('HTTPS', $names);
        $this->assertGreaterThan(count($comparison['gaps']) + count($comparison['strengths']), count($comparison['checks']));
        $https = collect($comparison['checks'])->firstWhere('name', 'HTTPS');
        $this->assertSame('pass', $https['you_status']);
        $this->assertSame('pass', $https['them_status']);
        $this->assertSame('critical', $https['severity']);
        $meta = collect($comparison['checks'])->firstWhere('name', 'Meta description');
        $this->assertNotEmpty($meta['recommendation']);   // you're not passing, so the fix is included

        // Stages cover every category exactly once.
        $stageCats = array_merge(...array_column($comparison['stages'], 'categories'));
        $this->assertEqualsCanonicalizing(array_column($comparison['you']['categories'], 'key'), $stageCats);
        $this->assertSame(config('website_checks.status_weights'), $comparison['scoring']['status_weights']);

        // Site details for the spotlights.
        $this->assertSame('Mine OG', $comparison['you']['open_graph']['title']);
        $this->assertSame('/share.jpg', $comparison['you']['open_graph']['image']);
        $this->assertSame('blocked', $comparison['you']['ai_crawlers']['GPTBot']);
        $this->assertSame('allowed', $comparison['competitor']['ai_crawlers']['GPTBot']);
        $this->assertCount(3, $comparison['you']['vitals']);
        $this->assertSame('info', $comparison['you']['vitals'][0]['status']);   // PageSpeed failed: not measured
    }

    // ---------------------------------------------------- API compatibility

    public function test_existing_report_fields_are_kept(): void
    {
        [, $report] = $this->analyse($this->prose());

        foreach (['url', 'title', 'overall', 'grade', 'counts', 'categories', 'recommendations_source', 'top_recommendations'] as $key) {
            $this->assertArrayHasKey($key, $report);
        }
        $this->assertSame(['pass', 'warn', 'fail'], array_keys($report['counts']));
        foreach (['titles', 'structure', 'images', 'security', 'mobile', 'links', 'discoverability', 'social', 'richresults',
                  'crawl', 'speed', 'trust', 'ai'] as $key) {
            $this->assertNotNull(collect($report['categories'])->firstWhere('key', $key), "Missing category {$key}");
        }
    }
}
