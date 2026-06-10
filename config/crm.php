<?php

return [
    'hubspot' => [
        'client_id'     => env('HUBSPOT_CLIENT_ID'),
        'client_secret' => env('HUBSPOT_CLIENT_SECRET'),
        'redirect_uri'  => env('HUBSPOT_REDIRECT_URI', env('APP_URL') . '/app/crm/hubspot/callback'),
    ],

    'salesforce' => [
        'client_id'     => env('SALESFORCE_CLIENT_ID'),
        'client_secret' => env('SALESFORCE_CLIENT_SECRET'),
        'redirect_uri'  => env('SALESFORCE_REDIRECT_URI', env('APP_URL') . '/app/crm/salesforce/callback'),
    ],
];
