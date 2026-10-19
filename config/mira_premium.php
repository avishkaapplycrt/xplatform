<?php

/*
|--------------------------------------------------------------------------
| Mira Premium — plans, limits and engine settings
|--------------------------------------------------------------------------
| Read by App\Services\MiraPremium\*. Every rule that decides who gets
| Premium, how much they can use, and how the scan / AI visibility engines
| behave lives here, the same way config/website_checks.php holds the
| analysis thresholds.
|
| Who gets which tier (App\Services\MiraPremium\Entitlement):
|   - a signed-in client (auth guard "client") whose clients.plan is listed
|     under 'plans', with subscription_ends_at empty or in the future
|     -> that plan, status "active";
|   - a client on any plan with trial_ends_at in the future
|     -> their paid plan, or 'trial' on the free plan, status "active_trial";
|   - everyone else (guests, free plan, expired, suspended / cancelled)
|     -> 'preview': a working taste of the product, depth locked.
|
| Limits: [count, period]. period is 'day' or 'month' (calendar, app
| timezone) for metered usage, or null for a standing cap (projects,
| prompts, competitors). 0 means "not included in this tier".
*/

return [

    'plans' => [
        'preview' => [
            'label'  => 'Preview',
            'limits' => [
                'scans'           => [2, 'day'],
                'chat_messages'   => [6, 'day'],
                'visibility_runs' => [0, 'month'],
                'projects'        => [0, null],
                'prompts'         => [0, null],
                'competitors'     => [1, null],
            ],
            // Preview reports skip the AI-written, site-specific fixes.
            'ai_recommendations' => false,
        ],
        'trial' => [
            'label'  => 'Premium trial',
            'limits' => [
                'scans'           => [20, 'month'],
                'chat_messages'   => [60, 'day'],
                'visibility_runs' => [3, 'month'],
                'projects'        => [1, null],
                'prompts'         => [10, null],
                'competitors'     => [2, null],
            ],
            'ai_recommendations' => true,
        ],
        'starter' => [
            'label'  => 'Starter',
            'limits' => [
                'scans'           => [60, 'month'],
                'chat_messages'   => [150, 'day'],
                'visibility_runs' => [8, 'month'],
                'projects'        => [2, null],
                'prompts'         => [20, null],
                'competitors'     => [3, null],
            ],
            'ai_recommendations' => true,
        ],
        'professional' => [
            'label'  => 'Professional',
            'limits' => [
                'scans'           => [300, 'month'],
                'chat_messages'   => [600, 'day'],
                'visibility_runs' => [40, 'month'],
                'projects'        => [10, null],
                'prompts'         => [75, null],
                'competitors'     => [5, null],
            ],
            'ai_recommendations' => true,
        ],
        'enterprise' => [
            'label'  => 'Enterprise',
            'limits' => [
                'scans'           => [3000, 'month'],
                'chat_messages'   => [5000, 'day'],
                'visibility_runs' => [400, 'month'],
                'projects'        => [50, null],
                'prompts'         => [300, null],
                'competitors'     => [10, null],
            ],
            'ai_recommendations' => true,
        ],
    ],

    // Development: give everyone (guests and every account) this tier, e.g.
    // MIRA_UNLOCK_ALL=professional, to try every Premium feature locally.
    // Ignored when APP_ENV=production. Remove the env line to restore the real rules.
    'unlock_all' => env('MIRA_UNLOCK_ALL'),

    // Where upgrade buttons send people until self-serve billing exists.
    'upgrade_url'    => env('MIRA_UPGRADE_URL', '/pricing'),
    // Billing portal for subscribers (Stripe customer portal or similar). Null hides "Manage plan".
    'management_url' => env('MIRA_MANAGEMENT_URL'),

    // Two-phase scan protocol: a submitted scan returns an HMAC-signed handle
    // that is valid for this long; the report is fetched with the handle.
    'handle_ttl_minutes' => 60,

    // A finished scan of the same URL (and competitor) by the same person is
    // reused for this long instead of running — and charging — a new one.
    'scan_reuse_minutes' => 30,

    // How long a "Share report" link keeps working.
    'share_days' => 30,

    // A scan stuck in queued/running for longer than this is marked failed.
    'scan_timeout_minutes' => 6,

    // How background work runs: 'after_response' (in the same PHP process once
    // the response is sent — no worker needed) or 'queue' (the app's queue
    // connection; run `php artisan queue:work`).
    'dispatch' => env('MIRA_DISPATCH', 'after_response'),

    // Cloudflare Turnstile proof-of-human for Preview scans. Off until both keys are set.
    'turnstile' => [
        'site_key'   => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
        'verify_url' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
    ],

    // AI visibility engine.
    'visibility' => [
        // Answers are generated by this model; reports name it so the estimate is honest about its source.
        'model'            => env('MIRA_VISIBILITY_MODEL', env('OPENAI_MODEL', 'gpt-4.1-mini')),
        'max_tokens'       => 700,
        'temperature'      => 0.4,
        // Prompts answered per run (the rest of the set waits for the next run).
        'prompts_per_run'  => 25,
        // Prompts suggested when a project's set is generated.
        'seed_prompts'     => 10,
        // Domains never counted as "cited" (the model's own boilerplate links).
        'ignore_domains'   => ['example.com', 'openai.com'],
        // After each answer, one small extra call classifies how the brand is
        // described (sentiment) and lists every brand named ("most mentioned brands").
        'analyze_answers'  => env('MIRA_VISIBILITY_ANALYZE', true),
        // Topic labels a prompt can be filed under.
        'topics'           => ['Discovery', 'Comparison', 'Pricing', 'Trust', 'How-to', 'Local'],
    ],

    // Overview comparison windows: key => days. Every panel compares the
    // window ending now with the same-length window before it.
    'periods' => ['7d' => 7, '30d' => 30, '90d' => 90, '12m' => 365],
    'default_period' => '30d',

    // DataForSEO (https://dataforseo.com) powers the Authority and Search panels.
    // Unset = those panels show "Not measured — connect DataForSEO". sandbox=true
    // uses sandbox.dataforseo.com: free, same response shape, dummy numbers.
    'dataforseo' => [
        'login'         => env('DATAFORSEO_LOGIN'),
        'password'      => env('DATAFORSEO_PASSWORD'),
        'sandbox'       => (bool) env('DATAFORSEO_SANDBOX', false),
        // Sample data mode: realistic generated data in DataForSEO's shape so every
        // chart can be seen before an account exists. Tagged "Sample data" on every
        // panel; ignored in production and as soon as a real login is set.
        'demo'          => (bool) env('MIRA_DEMO_DATA', false),
        'location_code' => (int) env('DATAFORSEO_LOCATION_CODE', 2036),   // 2036 = Australia (home market)
        'language_code' => env('DATAFORSEO_LANGUAGE_CODE', 'en'),
        // A snapshot is reused for this long before the provider is called (and paid) again.
        'cache_hours'   => 24,
        'top_keywords'  => 20,
        // Ranked keywords pulled per refresh for intent / branded / SERP-feature analysis.
        'analysis_keywords' => 100,
        'referring_domains' => 300,
        'anchors'           => 30,
        'top_pages'         => 20,
        'organic_competitors' => 10,
        // "Traffic share by country": one domain_rank_overview call per market per refresh.
        'markets' => [
            ['code' => 2036, 'country' => 'Australia',      'iso' => 'AU', 'language' => 'en'],
            ['code' => 2840, 'country' => 'United States',  'iso' => 'US', 'language' => 'en'],
            ['code' => 2826, 'country' => 'United Kingdom', 'iso' => 'GB', 'language' => 'en'],
            ['code' => 2554, 'country' => 'New Zealand',    'iso' => 'NZ', 'language' => 'en'],
            ['code' => 2124, 'country' => 'Canada',         'iso' => 'CA', 'language' => 'en'],
            ['code' => 2356, 'country' => 'India',          'iso' => 'IN', 'language' => 'en'],
            ['code' => 2702, 'country' => 'Singapore',      'iso' => 'SG', 'language' => 'en'],
            ['code' => 2276, 'country' => 'Germany',        'iso' => 'DE', 'language' => 'de'],
            ['code' => 2372, 'country' => 'Ireland',        'iso' => 'IE', 'language' => 'en'],
            ['code' => 2710, 'country' => 'South Africa',   'iso' => 'ZA', 'language' => 'en'],
        ],
    ],

    // Markers on the Performance chart (Ahrefs-style "chart notes"). Confirmed
    // Google ranking updates; add new ones here as Google announces them.
    'google_updates' => [
        ['date' => '2024-03-05', 'name' => 'March 2024 core update'],
        ['date' => '2024-08-15', 'name' => 'August 2024 core update'],
        ['date' => '2024-11-11', 'name' => 'November 2024 core update'],
        ['date' => '2024-12-12', 'name' => 'December 2024 core update'],
        ['date' => '2025-03-13', 'name' => 'March 2025 core update'],
        ['date' => '2025-06-30', 'name' => 'June 2025 core update'],
    ],

    // First-party product analytics: event names the page may send.
    'events' => [
        'page_view', 'scan_submit', 'scan_done', 'scan_failed', 'report_open', 'chat_send',
        'upgrade_click', 'project_create', 'prompt_add', 'prompts_suggest', 'visibility_run',
        'tab_open', 'quota_hit', 'locked_view',
    ],
];
