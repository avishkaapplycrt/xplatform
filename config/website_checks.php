<?php

/*
|--------------------------------------------------------------------------
| Website analysis — weights and thresholds
|--------------------------------------------------------------------------
| Every number the "Analyse my website" / "Compare with a competitor" checks
| use to decide pass / warn / fail, and how results roll up into scores.
| Read by App\Services\WebsiteAnalyzerService and the classes in
| App\Services\WebsiteAnalysis.
|
| Scoring: each check is pass / warn / fail / info. 'info' covers anything
| observational, "Not measured" or "not applicable" — it never lowers a score.
| A category's score is the average of its scored checks (status weights
| below); a category with no scored checks has no score and is left out of
| the overall score, which is the category_weights-weighted average.
*/

return [

    // How much each status counts toward a category score (0–1).
    'status_weights' => [
        'pass' => 1.0,
        'warn' => 0.5,
        'fail' => 0.0,
    ],

    // Relative weight of each category in the overall score (missing = 1.0).
    'category_weights' => [
        'titles'          => 1.0,
        'structure'       => 1.0,
        'images'          => 1.0,
        'security'        => 1.0,
        'mobile'          => 1.0,
        'links'           => 1.0,
        'discoverability' => 1.0,
        'crawl'           => 1.0,
        'speed'           => 1.0,
        'trust'           => 1.0,
        'ai'              => 1.0,
        'social'          => 1.0,
        'richresults'     => 1.0,
        'conversion'      => 1.0,
    ],

    // Minimum overall score for each grade, highest first.
    'grades' => ['A' => 90, 'B' => 80, 'C' => 70, 'D' => 60, 'F' => 0],

    // Page titles & descriptions (characters).
    'title_length'       => ['min' => 30, 'max' => 60],
    'description_length' => ['min' => 120, 'max' => 160],

    // Crawling & indexing: fewer words than this in the raw HTML suggests the
    // main content is rendered by JavaScript (most AI crawlers can't run it).
    'js_rendering_min_words' => 150,

    // AI visibility: a "short direct answer" is the paragraph right under a
    // question heading, between these word counts (the featured-snippet range).
    'short_answer_words' => ['min' => 20, 'max' => 60],
    // Question-style headings needed to pass "FAQ / question headings" without a FAQ section.
    'min_question_headings' => 2,

    // AI crawlers checked in robots.txt (blocking is reported as a choice, not scored).
    'ai_crawlers' => ['GPTBot', 'OAI-SearchBot', 'ClaudeBot', 'PerplexityBot', 'Google-Extended', 'CCBot'],

    // Page speed: Google PageSpeed Insights (mobile), API key from PAGESPEED_API_KEY.
    // Core Web Vitals "good" / "poor" limits: <= good passes, <= poor warns, above fails.
    'pagespeed' => [
        'timeout_seconds' => 45,
        'lcp_ms'          => ['good' => 2500, 'poor' => 4000],
        'inp_ms'          => ['good' => 200,  'poor' => 500],
        'cls'             => ['good' => 0.1,  'poor' => 0.25],
    ],

    // Google search result preview truncation (characters).
    'serp_preview' => ['title_max' => 60, 'description_max' => 160],

    // Query parameters stripped from URLs before analysing and displaying them.
    // Entries ending in "*" are prefixes.
    'tracking_parameters' => ['utm_*', 'gclid', 'fbclid', 'srsltid', 'msclkid', 'dclid', 'gbraid', 'wbraid', 'mc_cid', 'mc_eid', '_ga'],

    // Google's pipeline stages for the comparison report, in order, and the
    // categories each one groups. A stage's score averages its scored categories.
    'stages' => [
        ['key' => 'crawl',      'label' => 'Crawl',         'question' => 'Can Google find your pages?',           'categories' => ['discoverability', 'crawl']],
        ['key' => 'index',      'label' => 'Index',         'question' => 'Will Google store the right version?',  'categories' => ['richresults']],
        ['key' => 'rank',       'label' => 'Rank',          'question' => 'Is the content strong enough to rank?', 'categories' => ['structure', 'images', 'links', 'trust', 'security']],
        ['key' => 'serve',      'label' => 'Serve',         'question' => 'How does your link look when shown?',   'categories' => ['titles', 'social']],
        ['key' => 'experience', 'label' => 'Experience',    'question' => 'Is it fast, mobile-friendly and easy to act on?', 'categories' => ['mobile', 'speed', 'conversion']],
        ['key' => 'ai',         'label' => 'AI Visibility', 'question' => 'Can AI assistants find and quote you?', 'categories' => ['ai']],
    ],

    // How much each check matters when it isn't passing: critical / important
    // / minor (missing = minor). Orders the comparison's fix list.
    'check_severity' => [
        'Title tag'                       => 'critical',
        'Meta description'                => 'critical',
        'Indexing (noindex)'              => 'critical',
        'HTTPS'                           => 'critical',
        'Mobile viewport'                 => 'critical',
        'Largest Contentful Paint (LCP)'  => 'critical',
        'Content without JavaScript'      => 'critical',
        'H1 heading'                      => 'important',
        'Image alt text'                  => 'important',
        'robots.txt'                      => 'important',
        'sitemap.xml'                     => 'important',
        'Canonical tag'                   => 'important',
        'Open Graph tags'                 => 'important',
        'Structured data'                 => 'important',
        'Interaction to Next Paint (INP)' => 'important',
        'Cumulative Layout Shift (CLS)'   => 'important',
        'Business address & phone'        => 'important',
        'FAQ / question headings'         => 'important',
        'FAQPage & Organization schema'   => 'important',
        'Mixed content'                   => 'important',
        'Broken links'                    => 'important',
        'Pages with errors'               => 'critical',
        'Lighthouse Performance'          => 'important',
        'Duplicate titles'                => 'important',
        'Redirects'                       => 'minor',
        'Readability'                     => 'minor',
        'Language'                        => 'minor',
        'Lighthouse Accessibility'        => 'minor',
        'Lighthouse Best Practices'       => 'minor',
        'Pages missing meta descriptions' => 'important',
        'Calls to action'                 => 'important',
        'Contact options'                 => 'important',
        'Form labels'                     => 'minor',
        'Trust proof'                     => 'minor',
        'hreflang tags'                   => 'minor',
        'Consistent phone number'         => 'minor',
    ],

    // Broken links: how many of the page's links are tested, and how long each may take.
    'broken_links' => ['max_links' => 30, 'timeout_seconds' => 8],

    // Redirects followed before the chain counts as too long.
    'redirects' => ['max_hops' => 5],

    // Flesch reading ease: good at or above 'good', poor below 'poor'; scored from this many words of paragraph text.
    'readability' => ['good' => 60, 'poor' => 30, 'min_words' => 100],

    // "Pages analysed" (Mira Premium site audits): pages read per scan, from the sitemap or the page's links.
    'crawl' => ['max_pages' => 25, 'timeout_seconds' => 12],

    // KPIs a page scan can't measure — shown as "Not measured — connect <tool>".
    // Never filled with estimates.
    'connected_kpis' => [
        ['kpi' => 'Organic sessions',               'tool' => 'GA4'],
        ['kpi' => 'Engaged session rate',           'tool' => 'GA4'],
        ['kpi' => 'Conversion rate',                'tool' => 'GA4'],
        ['kpi' => 'Index coverage',                 'tool' => 'Google Search Console'],
        ['kpi' => 'CTR by position',                'tool' => 'Google Search Console'],
        ['kpi' => 'Referring domains / backlinks',  'tool' => 'SEMrush or Ahrefs'],
        ['kpi' => 'AI Overview inclusion',          'tool' => 'Google Search Console or an AI visibility tracker'],
        ['kpi' => 'AI referral traffic',            'tool' => 'GA4'],
    ],
];
