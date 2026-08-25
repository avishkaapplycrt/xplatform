<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DataSourceSeeder extends Seeder
{
    public function run(): void
    {
        $layers = DB::table('analysis_layers')->pluck('id', 'name');

        $allSources = [
            'Data Collection' => [
                ['name' => 'Website Tracking',  'subtitle' => 'JS SDK for pages, clicks, forms'],
                ['name' => 'Mobile App',         'subtitle' => 'iOS and Android SDK'],
                ['name' => 'CRM System',         'subtitle' => 'Salesforce, HubSpot, Pipedrive'],
                ['name' => 'Payment Gateway',    'subtitle' => 'Stripe, Razorpay, PayPal'],
                ['name' => 'Email Platform',     'subtitle' => 'SendGrid, Mailchimp, Brevo'],
                ['name' => 'Support System',     'subtitle' => 'Zendesk, Freshdesk, Intercom'],
                ['name' => 'Ad Platforms',       'subtitle' => 'Google Ads, Meta, LinkedIn'],
                ['name' => 'POS System',         'subtitle' => 'Square, Shopify POS'],
            ],
            'Identity Resolution' => [
                ['name' => 'Customer ID Stitching',      'subtitle' => 'JS SDK for pages, clicks, forms'],
                ['name' => 'Session Linking',             'subtitle' => 'iOS and Android SDK'],
                ['name' => 'Device Matching',             'subtitle' => 'Salesforce, HubSpot, Pipedrive'],
                ['name' => 'Account / Profile Merging',   'subtitle' => 'Stripe, Razorpay, PayPal'],
                ['name' => 'Anonymous-to-Known Mapping',  'subtitle' => 'SendGrid, Mailchimp, Brevo'],
            ],
            'Data Processing' => [
                ['name' => 'Event Ingestion Pipeline', 'subtitle' => 'JS SDK for pages, clicks, forms'],
                ['name' => 'Schema Validation',        'subtitle' => 'iOS and Android SDK'],
                ['name' => 'Deduplication Engine',     'subtitle' => 'Salesforce, HubSpot, Pipedrive'],
                ['name' => 'Enrichment Pipeline',      'subtitle' => 'Stripe, Razorpay, PayPal'],
                ['name' => 'Feature Store',            'subtitle' => 'SendGrid, Mailchimp, Brevo'],
                ['name' => 'Data Quality Monitor',     'subtitle' => 'SendGrid, Mailchimp, Brevo'],
                ['name' => 'Batch Job Monitor',        'subtitle' => 'SendGrid, Mailchimp, Brevo'],
                ['name' => 'Storage Layer Monitor',    'subtitle' => 'SendGrid, Mailchimp, Brevo'],
            ],
            'Behavioral Intelligence' => [
                ['name' => 'Intent Score Engine',           'subtitle' => 'JS SDK for pages, clicks, forms'],
                ['name' => 'Engagement Score Engine',       'subtitle' => 'iOS and Android SDK'],
                ['name' => 'Churn Score Engine',            'subtitle' => 'Salesforce, HubSpot, Pipedrive'],
                ['name' => 'Loyalty Score Engine',          'subtitle' => 'Stripe, Razorpay, PayPal'],
                ['name' => 'Trust Score Engine',            'subtitle' => 'SendGrid, Mailchimp, Brevo'],
                ['name' => 'Frustration Score Engine',      'subtitle' => 'SendGrid, Mailchimp, Brevo'],
                ['name' => 'Buying Readiness Engine',       'subtitle' => 'SendGrid, Mailchimp, Brevo'],
                ['name' => 'Drop-off Risk Engine',          'subtitle' => 'SendGrid, Mailchimp, Brevo'],
                ['name' => 'Reactivation Potential Engine', 'subtitle' => 'SendGrid, Mailchimp, Brevo'],
                ['name' => 'Behavioral Clustering Engine',  'subtitle' => 'SendGrid, Mailchimp, Brevo'],
            ],
            'AI/ML Predictions' => [
                ['name' => 'Churn Prediction Model',    'subtitle' => 'JS SDK for pages, clicks, forms'],
                ['name' => 'Purchase Propensity Model', 'subtitle' => 'iOS and Android SDK'],
                ['name' => 'LTV Forecasting Model',     'subtitle' => 'Salesforce, HubSpot, Pipedrive'],
                ['name' => 'Anomaly Detection Model',   'subtitle' => 'Stripe, Razorpay, PayPal'],
                ['name' => 'NLP Sentiment Model',       'subtitle' => 'SendGrid, Mailchimp, Brevo'],
                ['name' => 'LLM Explanation Engine',    'subtitle' => 'SendGrid, Mailchimp, Brevo'],
                ['name' => 'Model Drift Monitor',       'subtitle' => 'SendGrid, Mailchimp, Brevo'],
            ],
            'Decision & Actions' => [
                ['name' => 'Rule Engine',              'subtitle' => 'JS SDK for pages, clicks, forms'],
                ['name' => 'AI Recommendation Engine', 'subtitle' => 'iOS and Android SDK'],
                ['name' => 'Threshold-Based Triggers', 'subtitle' => 'Salesforce, HubSpot, Pipedrive'],
                ['name' => 'Workflow Automation',      'subtitle' => 'Stripe, Razorpay, PayPal'],
                ['name' => 'Human-in-Loop Approvals',  'subtitle' => 'SendGrid, Mailchimp, Brevo'],
                ['name' => 'Action Delivery Tracker',  'subtitle' => 'SendGrid, Mailchimp, Brevo'],
            ],
            'Application Layer' => [
                ['name' => 'Executive Dashboard Module',        'subtitle' => 'JS SDK for pages, clicks, forms'],
                ['name' => 'Customer 360° Profile Module',      'subtitle' => 'iOS and Android SDK'],
                ['name' => 'Audience Segmentation Studio',      'subtitle' => 'Salesforce, HubSpot, Pipedrive'],
                ['name' => 'Funnel & Journey Analytics Module', 'subtitle' => 'Stripe, Razorpay, PayPal'],
                ['name' => 'Prediction Center Module',          'subtitle' => 'SendGrid, Mailchimp, Brevo'],
                ['name' => 'Alerts & Anomaly Center',           'subtitle' => 'SendGrid, Mailchimp, Brevo'],
                ['name' => 'Campaign / Recommendation Center',  'subtitle' => 'SendGrid, Mailchimp, Brevo'],
                ['name' => 'Report Builder Module',             'subtitle' => 'SendGrid, Mailchimp, Brevo'],
                ['name' => 'API Management Panel',              'subtitle' => 'SendGrid, Mailchimp, Brevo'],
                ['name' => 'Admin + Tenant Settings',           'subtitle' => 'SendGrid, Mailchimp, Brevo'],
            ],
            'Governance & Security' => [
                ['name' => 'Role-Based Access Control (RBAC)', 'subtitle' => 'JS SDK for pages, clicks, forms'],
                ['name' => 'Audit Log Viewer',                 'subtitle' => 'iOS and Android SDK'],
                ['name' => 'Consent Management',               'subtitle' => 'Salesforce, HubSpot, Pipedrive'],
                ['name' => 'Data Deletion & Retention',        'subtitle' => 'Stripe, Razorpay, PayPal'],
                ['name' => 'Explainable AI (XAI) Logs',        'subtitle' => 'SendGrid, Mailchimp, Brevo'],
                ['name' => 'Model Monitoring & Drift',         'subtitle' => 'SendGrid, Mailchimp, Brevo'],
                ['name' => 'Encryption & Key Management',      'subtitle' => 'SendGrid, Mailchimp, Brevo'],
                ['name' => 'Compliance Framework Dashboard',   'subtitle' => 'SendGrid, Mailchimp, Brevo'],
                ['name' => 'Tenant Isolation Monitor',         'subtitle' => 'SendGrid, Mailchimp, Brevo'],
                ['name' => 'Privacy Controls Center',          'subtitle' => 'SendGrid, Mailchimp, Brevo'],
            ],
        ];

        foreach ($allSources as $layerName => $sources) {
            $layerId = $layers[$layerName] ?? null;
            if (!$layerId) {
                $this->command->warn("Layer not found: {$layerName} — skipping.");
                continue;
            }
            foreach ($sources as $source) {
                DB::table('data_sources')->updateOrInsert(
                    ['analysis_layer_id' => $layerId, 'name' => $source['name']],
                    ['subtitle' => $source['subtitle'], 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        $this->command->info('✅ DataSourceSeeder done — ' . DB::table('data_sources')->count() . ' rows inserted.');
    }
}