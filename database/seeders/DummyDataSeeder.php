<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientDataSource;
use App\Models\AnalyticsMetric;
use App\Models\BusinessInsight;
use App\Models\MasterAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create Master Admin if not exists
        MasterAdmin::firstOrCreate([
            'email' => 'admin@analytics-platform.com'
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        // Create sample clients
        $clients = [
            [
                'company_name' => 'TechCorp Solutions',
                'email' => 'admin@techcorp.com',
                'phone' => '+1-555-0123',
                'address' => '123 Innovation Drive, San Francisco, CA',
                'status' => 'active',
                'plan' => 'professional',
                'currency' => 'USD',
            ],
            [
                'company_name' => 'Global Retail Inc',
                'email' => 'manager@globalretail.com',
                'phone' => '+1-555-0456',
                'address' => '456 Commerce Street, New York, NY',
                'status' => 'active',
                'plan' => 'enterprise',
                'currency' => 'USD',
            ],
            [
                'company_name' => 'StartupXYZ',
                'email' => 'founder@startupxyz.com',
                'phone' => '+1-555-0789',
                'address' => '789 Startup Lane, Austin, TX',
                'status' => 'pending',
                'plan' => 'starter',
                'currency' => 'USD',
            ],
            [
                'company_name' => 'E-Shop Europe',
                'email' => 'contact@eshopeu.com',
                'phone' => '+44-20-7946-0958',
                'address' => '12 Oxford Street, London, UK',
                'status' => 'active',
                'plan' => 'professional',
                'currency' => 'EUR',
            ],
            [
                'company_name' => 'DataDriven Co',
                'email' => 'info@datadriven.co',
                'phone' => '+1-555-0321',
                'address' => '321 Analytics Blvd, Boston, MA',
                'status' => 'suspended',
                'plan' => 'starter',
                'currency' => 'USD',
            ],
            [
                'company_name' => 'CloudNine Services',
                'email' => 'hello@cloudnine.io',
                'phone' => '+1-555-0654',
                'address' => '654 Cloud Way, Seattle, WA',
                'status' => 'active',
                'plan' => 'enterprise',
                'currency' => 'USD',
            ],
        ];

        foreach ($clients as $clientData) {
            $client = Client::create([
                ...$clientData,
                'password' => Hash::make('password'),
                'trial_ends_at' => now()->addDays(rand(5, 30)),
                'subscription_ends_at' => now()->addMonths(rand(1, 12)),
                'limits' => ['max_data_sources' => 5, 'max_queries' => 1000],
                'settings' => ['notifications' => true, 'theme' => 'light'],
                'last_login_at' => now()->subDays(rand(0, 10)),
            ]);

            // Create data sources for active clients
            if (in_array($clientData['status'], ['active', 'suspended'])) {
                $this->createDataSources($client);
            }
        }

        $this->command->info('Dummy data created successfully!');
        $this->command->info('Login with: admin@analytics-platform.com / password');
    }

    private function createDataSources(Client $client): void
    {
        $sources = [
            [
                'connection_name' => 'Production Database',
                'db_type' => 'mysql',
                'host' => 'db.techcorp.com',
                'port' => 3306,
                'database_name' => 'techcorp_prod',
                'username' => 'analytics_user',
                'password' => 'secure_pass_123',
                'status' => 'connected',
                'schema_mapping' => [
                    'users_table' => 'users',
                    'orders_table' => 'orders',
                    'amount_column' => 'total_amount',
                ],
            ],
            [
                'connection_name' => 'Warehouse DB',
                'db_type' => 'postgresql',
                'host' => 'warehouse.globalretail.com',
                'port' => 5432,
                'database_name' => 'gr_warehouse',
                'username' => 'warehouse_reader',
                'password' => 'wh_pass_456',
                'status' => 'connected',
                'schema_mapping' => [
                    'users_table' => 'customers',
                    'orders_table' => 'transactions',
                ],
            ],
        ];

        foreach ($sources as $index => $sourceData) {
            if ($index === 0 || rand(0, 1)) {
                $source = $client->dataSources()->create([
                    ...$sourceData,
                    'last_tested_at' => now(),
                    'connected_at' => now()->subDays(rand(1, 30)),
                    'last_sync_at' => now()->subMinutes(rand(5, 60)),
                ]);

                $this->createMetrics($client, $source);
                $this->createInsights($client, $source);
            }
        }
    }

    private function createMetrics(Client $client, ClientDataSource $source): void
    {
        $metrics = ['active_users', 'revenue_today', 'orders_today', 'page_views', 'conversion_rate'];
        
        foreach ($metrics as $metricType) {
            for ($i = 30; $i >= 0; $i--) {
                $value = match($metricType) {
                    'active_users' => rand(100, 5000),
                    'revenue_today' => rand(1000, 50000),
                    'orders_today' => rand(10, 500),
                    'page_views' => rand(1000, 50000),
                    'conversion_rate' => rand(1, 15) / 10,
                    default => rand(1, 100),
                };

                AnalyticsMetric::create([
                    'client_id' => $client->id,
                    'data_source_id' => $source->id,
                    'metric_type' => $metricType,
                    'value' => $value,
                    'unit' => match($metricType) {
                        'revenue_today' => 'USD',
                        'conversion_rate' => 'percentage',
                        default => 'count',
                    },
                    'measured_at' => now()->subDays($i),
                    'granularity' => 'daily',
                ]);
            }
        }
    }

    private function createInsights(Client $client, ClientDataSource $source): void
    {
        $insights = [
            [
                'category' => 'growth',
                'title' => 'Revenue Surge Detected',
                'description' => 'Your revenue has increased by 23% compared to last month',
                'confidence_score' => 0.92,
                'impact_level' => 'high',
                'status' => 'new',
            ],
            [
                'category' => 'anomaly',
                'title' => 'Unusual Traffic Pattern',
                'description' => 'Traffic from mobile devices spiked 150% on Sunday',
                'confidence_score' => 0.85,
                'impact_level' => 'medium',
                'status' => 'viewed',
            ],
            [
                'category' => 'risk',
                'title' => 'Cart Abandonment High',
                'description' => 'Cart abandonment rate is 15% above industry average',
                'confidence_score' => 0.78,
                'impact_level' => 'high',
                'status' => 'actioned',
            ],
            [
                'category' => 'opportunity',
                'title' => 'Upsell Potential',
                'description' => '32% of customers show interest in premium features',
                'confidence_score' => 0.88,
                'impact_level' => 'medium',
                'status' => 'new',
            ],
        ];

        foreach ($insights as $insight) {
            BusinessInsight::create([
                'client_id' => $client->id,
                'data_source_id' => $source->id,
                ...$insight,
                'generated_at' => now()->subDays(rand(1, 7)),
            ]);
        }
    }
}