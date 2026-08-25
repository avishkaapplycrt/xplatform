<?php

return [
    'webhook_url' => env('ANALYTICS_WEBHOOK_URL', 'https://your-analytics-platform.com/api/v1'),
    'api_key' => env('ANALYTICS_API_KEY'),
    'site_id' => env('ANALYTICS_SITE_ID'),
    'enabled' => env('ANALYTICS_ENABLED', true),
];