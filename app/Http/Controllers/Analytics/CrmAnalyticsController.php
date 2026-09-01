<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CrmAnalyticsController extends Controller
{
    public function index() { return redirect()->route('client.reports.crm.overview'); }

    public function overview()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $days = $this->getDaysFromPeriod($period);

        $data = [
            'has_data'         => $this->hasCrmData($client),
            'total_contacts'   => $this->getTotalContacts($client),
            'total_deals'      => $this->getTotalDeals($client, $days),
            'pipeline_value'   => $this->getPipelineValue($client),
            'win_rate'         => $this->getWinRate($client, $days),
            'connected_count'  => $this->getConnectedCount($client),
        ];

        $stages = $this->getPipelineByStage();
        $dealTrend = $this->getDealTrend($days);

        return view('client.reports.crm.overview', compact('data', 'period', 'stages', 'dealTrend'));
    }

    public function pipeline()
    {
        $client = Auth::guard('client')->user();
        $data = ['has_data' => $this->hasCrmData($client), 'synced' => $this->hasSynced()];
        $stages = $this->getPipelineByStage();
        return view('client.reports.crm.pipeline', compact('data', 'stages'));
    }

    public function deals()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $data = ['has_data' => $this->hasCrmData($client), 'synced' => $this->hasSynced()];
        $deals = $this->getDeals();
        return view('client.reports.crm.deals', compact('data', 'period', 'deals'));
    }

    public function contacts()
    {
        $client = Auth::guard('client')->user();
        $data = ['has_data' => $this->hasCrmData($client), 'total_contacts' => $this->getTotalContacts($client), 'synced' => $this->hasSynced()];
        $contacts = $this->getContacts();
        return view('client.reports.crm.contacts', compact('data', 'contacts'));
    }

    public function activities()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $data = ['has_data' => $this->hasCrmData($client), 'synced' => $this->hasSynced()];
        $activities = $this->getActivityFeed();
        return view('client.reports.crm.activities', compact('data', 'period', 'activities'));
    }

    public function forecast()
    {
        $client = Auth::guard('client')->user();
        $data = ['has_data' => $this->hasCrmData($client), 'synced' => $this->hasSynced()];
        $forecast = $this->getForecast();
        return view('client.reports.crm.forecast', compact('data', 'forecast'));
    }

    public function getData(Request $request, string $metric)
    {
        return response()->json(['metric' => $metric, 'data' => []]);
    }

    public function export(Request $request, string $format)
    {
        return response()->json(['message' => 'Export not yet implemented']);
    }

    private function getDaysFromPeriod(string $period): int
    {
        return match($period) { '7d' => 7, '30d' => 30, '90d' => 90, '1y' => 365, default => 30 };
    }

    /**
     * Connections are stored per-provider in `crm_integrations` (see
     * CrmConnectionController), not per-client — there's no client_id on
     * that table, so this reflects whether the platform has ANY connected
     * CRM, matching how CrmConnectionController itself treats connections.
     */
    private function hasCrmData($client): bool
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('crm_integrations')) return false;
            return DB::table('crm_integrations')->where('status', 'connected')->exists();
        } catch (\Exception $e) { return false; }
    }

    private function getTotalContacts($client)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('crm_contacts')) return 0;
            return DB::table('crm_contacts')->count();
        } catch (\Exception $e) { return 0; }
    }

    private function getTotalDeals($client, $days)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('crm_deals')) return 0;
            return DB::table('crm_deals')
                ->where('created_at', '>=', now()->subDays($days))->count();
        } catch (\Exception $e) { return 0; }
    }

    private function getPipelineValue($client)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('crm_deals')) return 0;
            return DB::table('crm_deals')->sum('value') ?? 0;
        } catch (\Exception $e) { return 0; }
    }

    private function getWinRate($client, $days)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('crm_deals')) return 0;
            $total = DB::table('crm_deals')
                ->where('created_at', '>=', now()->subDays($days))->count();
            $won = DB::table('crm_deals')
                ->where('status', 'won')
                ->where('created_at', '>=', now()->subDays($days))->count();
            return $total > 0 ? round(($won / $total) * 100, 2) : 0;
        } catch (\Exception $e) { return 0; }
    }

    private function getConnectedCount($client)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('crm_integrations')) return 0;
            return DB::table('crm_integrations')->where('status', 'connected')->count();
        } catch (\Exception $e) { return 0; }
    }

    /**
     * Distinguishes "never synced" from "synced, and genuinely has nothing"
     * so empty-state copy doesn't tell someone to sync when a sync already
     * ran and their CRM just has zero records of that type.
     */
    private function hasSynced(): bool
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('crm_integrations')) return false;
            return DB::table('crm_integrations')
                ->where('status', 'connected')
                ->whereNotNull('last_sync_at')
                ->exists();
        } catch (\Exception $e) { return false; }
    }

    private function getContacts()
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('crm_contacts')) return collect();
            return DB::table('crm_contacts')->orderByDesc('last_activity_at')->limit(200)->get();
        } catch (\Exception $e) { return collect(); }
    }

    private function getDeals()
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('crm_deals')) return collect();
            return DB::table('crm_deals')->orderByDesc('updated_at')->limit(200)->get();
        } catch (\Exception $e) { return collect(); }
    }

    /**
     * Deal counts and total value grouped by the provider's own pipeline
     * stage string (e.g. HubSpot's `dealstage` id) — no attempt to map
     * those ids to human labels since that mapping is per-portal and would
     * need a separate pipelines API call to resolve honestly.
     */
    private function getPipelineByStage()
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('crm_deals')) return collect();

            return DB::table('crm_deals')
                ->selectRaw('COALESCE(stage, \'(no stage)\') as stage, COUNT(*) as deal_count, SUM(value) as total_value')
                ->groupBy('stage')
                ->orderByDesc('total_value')
                ->get();
        } catch (\Exception $e) { return collect(); }
    }

    /**
     * A real activity feed built from what's actually synced — contact and
     * deal updates, ordered by their own timestamps — rather than a
     * separate engagements/events sync, which would need HubSpot scopes
     * beyond what's already been granted (contacts + deals).
     */
    private function getActivityFeed()
    {
        try {
            $events = collect();

            if (DB::getSchemaBuilder()->hasTable('crm_contacts')) {
                foreach (DB::table('crm_contacts')->orderByDesc('updated_at')->limit(50)->get() as $c) {
                    $name = trim(($c->first_name ?? '') . ' ' . ($c->last_name ?? '')) ?: ($c->email ?? 'Unknown contact');
                    $events->push((object) [
                        'type'      => 'contact',
                        'label'     => "Contact updated: {$name}",
                        'meta'      => $c->company ?? $c->email ?? null,
                        'provider'  => $c->provider,
                        'timestamp' => $c->last_activity_at ?? $c->updated_at,
                    ]);
                }
            }

            if (DB::getSchemaBuilder()->hasTable('crm_deals')) {
                foreach (DB::table('crm_deals')->orderByDesc('updated_at')->limit(50)->get() as $d) {
                    $events->push((object) [
                        'type'      => 'deal',
                        'label'     => "Deal updated: {$d->name}",
                        'meta'      => '$' . number_format((float) $d->value) . ' · ' . ($d->status ?? 'open'),
                        'provider'  => $d->provider,
                        'timestamp' => $d->updated_at,
                    ]);
                }
            }

            return $events
                ->filter(fn ($e) => !empty($e->timestamp))
                ->sortByDesc(fn ($e) => strtotime($e->timestamp))
                ->take(60)
                ->values();
        } catch (\Exception $e) { return collect(); }
    }

    /**
     * Projected value of currently-open deals, bucketed by the month their
     * close_date falls in. No probability weighting by stage — that would
     * need the HubSpot pipelines API to resolve stage → win-likelihood
     * honestly, so this is a straight open-pipeline total, not a
     * fabricated confidence-adjusted number.
     */
    private function getForecast()
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('crm_deals')) {
                return ['open_value' => 0, 'open_count' => 0, 'by_month' => collect()];
            }

            $open = DB::table('crm_deals')->where('status', 'open');

            $byMonth = (clone $open)
                ->whereNotNull('close_date')
                ->selectRaw("DATE_FORMAT(close_date, '%Y-%m') as month, COUNT(*) as deal_count, SUM(value) as total_value")
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            return [
                'open_value' => (float) (clone $open)->sum('value'),
                'open_count' => (clone $open)->count(),
                'by_month'   => $byMonth,
            ];
        } catch (\Exception $e) {
            return ['open_value' => 0, 'open_count' => 0, 'by_month' => collect()];
        }
    }

    /**
     * Deal volume/value over time, bucketed by each deal's real HubSpot
     * creation date (raw_data.createdAt), not our own sync timestamp —
     * bucketed in PHP rather than raw SQL since ISO-8601-with-milliseconds
     * parsing is fragile across MySQL versions.
     */
    private function getDealTrend(int $days)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('crm_deals')) return collect();

            $cutoff = now()->subDays($days);
            $groupByMonth = $days > 60;
            $buckets = [];

            foreach (DB::table('crm_deals')->get() as $deal) {
                $raw = json_decode($deal->raw_data ?? '{}', true);
                $createdAt = $raw['createdAt'] ?? $deal->created_at;

                try {
                    $date = \Carbon\Carbon::parse($createdAt);
                } catch (\Exception $e) {
                    continue;
                }

                if ($date->lt($cutoff)) continue;

                $key = $groupByMonth ? $date->format('Y-m') : $date->format('Y-m-d');
                if (!isset($buckets[$key])) {
                    $buckets[$key] = (object) ['bucket' => $key, 'deal_count' => 0, 'total_value' => 0.0];
                }
                $buckets[$key]->deal_count++;
                $buckets[$key]->total_value += (float) $deal->value;
            }

            ksort($buckets);
            return collect(array_values($buckets));
        } catch (\Exception $e) { return collect(); }
    }
}
