<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientDataSource;
use App\Models\AnalyticsMetric;
use App\Models\BusinessInsight;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Events\ClientAnalyticsUpdate;

class AnalyticsEngine
{
    private Client $client;
    private Collection $dataSources;

    public function forClient(Client $client): self
    {
        $this->client = $client;
        return $this;
    }

    public function fromSources($sources): self
    {
        $this->dataSources = is_array($sources) ? collect($sources) : $sources;
        return $this;
    }

    public function getRealtimeMetrics(array $metricTypes): array
    {
        $results = [];

        foreach ($this->dataSources as $source) {
            $connectionName = "client_{$this->client->id}_source_{$source->id}";

            try {
                config(["database.connections.{$connectionName}" => $source->getDynamicConnectionConfig()]);

                foreach ($metricTypes as $type) {
                    $value = $this->queryMetric($connectionName, $type, $source->schema_mapping);

                    $results[$type] = [
                        'value' => $value,
                        'source' => $source->connection_name,
                        'updated_at' => now()->toIso8601String(),
                    ];

                    $this->storeMetric($source, $type, $value, 'realtime');
                    
                    // Broadcast update
                    broadcast(new ClientAnalyticsUpdate($this->client->id, $type, [
                        'value' => $value,
                        'source' => $source->connection_name,
                    ]));
                }

                DB::disconnect($connectionName);

            } catch (\Exception $e) {
                \Log::error("Analytics query failed", [
                    'client_id' => $this->client->id,
                    'source_id' => $source->id,
                    'error' => $e->getMessage(),
                ]);

                $results[$type] = $this->getCachedMetric($source, $type);
            }
        }

        return $results;
    }

    private function queryMetric(string $connection, string $type, ?array $mapping): mixed
    {
        return match($type) {
            'active_users' => $this->queryActiveUsers($connection, $mapping),
            'revenue_today' => $this->queryRevenueToday($connection, $mapping),
            'orders_today' => $this->queryOrdersToday($connection, $mapping),
            default => throw new \InvalidArgumentException("Unknown metric: {$type}")
        };
    }

    private function queryActiveUsers(string $connection, ?array $mapping): int
    {
        $table = $mapping['users_table'] ?? 'users';
        $timestampCol = $mapping['last_active_column'] ?? 'last_active_at';

        return DB::connection($connection)
            ->table($table)
            ->where($timestampCol, '>=', now()->subMinutes(5))
            ->count();
    }

    private function queryRevenueToday(string $connection, ?array $mapping): float
    {
        $table = $mapping['orders_table'] ?? 'orders';
        $amountCol = $mapping['amount_column'] ?? 'total_amount';
        $dateCol = $mapping['date_column'] ?? 'created_at';

        return (float) DB::connection($connection)
            ->table($table)
            ->whereDate($dateCol, today())
            ->sum($amountCol);
    }

    private function queryOrdersToday(string $connection, ?array $mapping): int
    {
        $table = $mapping['orders_table'] ?? 'orders';
        $dateCol = $mapping['date_column'] ?? 'created_at';

        return DB::connection($connection)
            ->table($table)
            ->whereDate($dateCol, today())
            ->count();
    }

    private function storeMetric(ClientDataSource $source, string $type, $value, string $granularity): void
    {
        AnalyticsMetric::create([
            'client_id' => $this->client->id,
            'data_source_id' => $source->id,
            'metric_type' => $type,
            'value' => $value,
            'measured_at' => now(),
            'granularity' => $granularity,
        ]);
    }

    private function getCachedMetric(ClientDataSource $source, string $type): array
    {
        $lastMetric = AnalyticsMetric::where('client_id', $this->client->id)
            ->where('data_source_id', $source->id)
            ->where('metric_type', $type)
            ->latest()
            ->first();

        return [
            'value' => $lastMetric?->value ?? 0,
            'source' => $source->connection_name,
            'updated_at' => $lastMetric?->measured_at?->toIso8601String() ?? now()->toIso8601String(),
            'cached' => true,
        ];
    }

    public function generateInsights(): array
    {
        $metrics = AnalyticsMetric::where('client_id', $this->client->id)
            ->where('measured_at', '>=', now()->subDays(7))
            ->orderBy('measured_at')
            ->get()
            ->groupBy('metric_type');

        $insights = [];

        if (isset($metrics['revenue_today'])) {
            $trend = $this->calculateTrend($metrics['revenue_today']);
            
            if ($trend['change_percent'] > 15) {
                $insights[] = [
                    'category' => 'growth',
                    'title' => 'Revenue Surge Detected',
                    'description' => "Your revenue is up {$trend['change_percent']}% compared to last week",
                    'confidence_score' => 0.92,
                    'impact_level' => 'high',
                    'evidence' => $trend,
                ];
            }
        }

        foreach ($insights as $insightData) {
            BusinessInsight::create([
                'client_id' => $this->client->id,
                'data_source_id' => $this->dataSources->first()?->id,
                ...$insightData,
                'generated_at' => now(),
            ]);
        }

        return $insights;
    }

    private function calculateTrend(Collection $data): array
    {
        $values = $data->pluck('value')->toArray();
        $count = count($values);

        if ($count < 2) return ['change_percent' => 0];

        $firstHalf = array_slice($values, 0, (int)($count / 2));
        $secondHalf = array_slice($values, (int)($count / 2));

        $firstAvg = array_sum($firstHalf) / count($firstHalf);
        $secondAvg = array_sum($secondHalf) / count($secondHalf);

        $change = $firstAvg > 0 ? (($secondAvg - $firstAvg) / $firstAvg) * 100 : 0;

        return [
            'change_percent' => round($change, 1),
            'first_period_avg' => round($firstAvg, 2),
            'second_period_avg' => round($secondAvg, 2),
            'data_points' => $count,
        ];
    }

    public function getHistoricalData(int $days, string $granularity = 'daily'): array
    {
        return AnalyticsMetric::where('client_id', $this->client->id)
            ->where('measured_at', '>=', now()->subDays($days))
            ->where('granularity', $granularity)
            ->orderBy('measured_at')
            ->get()
            ->groupBy('metric_type')
            ->map(fn($group) => $group->map(fn($m) => [
                'date' => $m->measured_at->format('Y-m-d H:i'),
                'value' => $m->value,
            ]))
            ->toArray();
    }
}