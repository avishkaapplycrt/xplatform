<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'mailchimp' => [
        'client_id'     => env('MAILCHIMP_CLIENT_ID'),
        'client_secret' => env('MAILCHIMP_CLIENT_SECRET'),
        'redirect'      => env('APP_URL') . '/app/email-connections/mailchimp/callback',
    ],

    'facebook' => [
        'client_id'     => env('FACEBOOK_APP_ID'),
        'client_secret' => env('FACEBOOK_APP_SECRET'),
        'redirect'      => env('FACEBOOK_REDIRECT_URI'),
    ],

    'instagram' => [
        'client_id'     => env('INSTAGRAM_APP_ID'),
        'client_secret' => env('INSTAGRAM_APP_SECRET'),
        'redirect'      => env('INSTAGRAM_REDIRECT_URI'),
    ],

    'linkedin' => [
        'client_id'     => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        'redirect'      => env('LINKEDIN_REDIRECT_URI'),
    ],

    'slack' => [
        'client_id'     => env('SLACK_CLIENT_ID'),
        'client_secret' => env('SLACK_CLIENT_SECRET'),
    ],

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model'   => env('ANTHROPIC_MODEL', 'claude-opus-5'),
    ],

    'pagespeed' => [
        'api_key' => env('PAGESPEED_API_KEY'),
    ],

];
