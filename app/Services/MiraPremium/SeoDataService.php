<?php

namespace App\Services\MiraPremium;

use App\Models\MiraPremium\MiraProject;

/**
 * The Authority (backlink profile) and Search (organic / paid) panels,
 * from DataForSEO. Provider responses are stored as snapshots
 * (mira_seo_snapshots) and reused for cache_hours, so each project costs at
 * most one set of calls per window. Period deltas come from the provider's
 * monthly history: the value at the end of the window vs at its start.
 *
 * Naming is deliberate: the 0–100 score is DataForSEO's link-based rank,
 * shown as "Authority score" — never as Ahrefs' "Domain Rating".
 */
class SeoDataService
{
    public function __construct(private DataForSeoClient $client, private SnapshotStore $store) {}

    public function isConnected(): bool
    {
        return $this->client->isConfigured();
    }

    /** Home-market location for DataForSEO Labs calls. */
    public function location(): array
    {
        return ['location_code' => (int) config('mira_premium.dataforseo.location_code'), 'language_code' => (string) config('mira_premium.dataforseo.language_code')];
    }

    /** The raw authority snapshot (summary + monthly history), for other services. */
    public function authoritySnapshot(MiraProject $project, bool $refresh = false): array
    {
        return $this->snapshot($project, 'authority', $refresh, function () use ($project) {
            $summary = $this->client->live('backlinks/summary/live', [
                'target' => $project->domain, 'rank_scale' => 'one_hundred', 'internal_list_limit' => 10,
            ]);
            $history = $this->client->live('backlinks/history/live', [
                'target' => $project->domain, 'rank_scale' => 'one_hundred',
                'date_from' => now()->subMonths(24)->startOfMonth()->toDateString(),
            ]);

            return ['summary' => $summary, 'history' => $history['items'] ?? []];
        });
    }

    /** The raw search snapshot (rank overview, monthly history, ranked keywords), for other services. */
    public function searchSnapshot(MiraProject $project, bool $refresh = false): array
    {
        $loc = $this->location();

        return $this->snapshot($project, 'search', $refresh, function () use ($project, $loc) {
            $overview = $this->client->live('dataforseo_labs/google/domain_rank_overview/live', ['target' => $project->domain] + $loc);
            $history  = $this->client->live('dataforseo_labs/google/historical_rank_overview/live', ['target' => $project->domain] + $loc);
            $keywords = $this->client->live('dataforseo_labs/google/ranked_keywords/live', ['target' => $project->domain] + $loc + [
                'limit'      => (int) config('mira_premium.dataforseo.analysis_keywords', 100),
                'item_types' => ['organic', 'featured_snippet', 'local_pack', 'ai_overview_reference'],
                'order_by'   => ['ranked_serp_element.serp_item.etv,desc'],
            ]);

            return ['overview' => $overview['items'][0]['metrics'] ?? [], 'history' => $history['items'] ?? [], 'keywords' => $keywords['items'] ?? []];
        });
    }

    /** Monthly search history points (organic + paid + position bands), oldest first. */
    public function searchHistory(array $snapPayload): array
    {
        return $this->monthly(array_map(function ($i) {
            $i['date'] = sprintf('%04d-%02d-01', $i['year'] ?? 0, $i['month'] ?? 0);

            return $i;
        }, $snapPayload['history'] ?? []), function ($i) {
            $o = $i['metrics']['organic'] ?? null;

            return [
                'organic_keywords' => $o['count'] ?? null,
                'organic_traffic'  => isset($o['etv']) ? (int) round($o['etv']) : null,
                'organic_value'    => isset($o['estimated_paid_traffic_cost']) ? round($o['estimated_paid_traffic_cost'], 2) : null,
                'top3'             => $o ? ($o['pos_1'] ?? 0) + ($o['pos_2_3'] ?? 0) : null,
                'paid_keywords'    => $i['metrics']['paid']['count'] ?? null,
                'paid_traffic'     => isset($i['metrics']['paid']['etv']) ? (int) round($i['metrics']['paid']['etv']) : null,
                'positions'        => $o ? self::bands($o) : null,
            ];
        });
    }

    /** Position bands Ahrefs uses: 1–3, 4–10, 11–20, 21–50, 51–100. */
    public static function bands(array $o): array
    {
        return [
            '1-3'    => (int) (($o['pos_1'] ?? 0) + ($o['pos_2_3'] ?? 0)),
            '4-10'   => (int) ($o['pos_4_10'] ?? 0),
            '11-20'  => (int) ($o['pos_11_20'] ?? 0),
            '21-50'  => (int) (($o['pos_21_30'] ?? 0) + ($o['pos_31_40'] ?? 0) + ($o['pos_41_50'] ?? 0)),
            '51-100' => (int) (($o['pos_51_60'] ?? 0) + ($o['pos_61_70'] ?? 0) + ($o['pos_71_80'] ?? 0) + ($o['pos_81_90'] ?? 0) + ($o['pos_91_100'] ?? 0)),
        ];
    }

    public function authority(MiraProject $project, Period $period, bool $refresh = false): array
    {
        if (!$this->client->isConfigured()) {
            return $this->notConnected('authority');
        }

        $snap = $this->authoritySnapshot($project, $refresh);

        $s       = $snap['payload']['summary'] ?? [];
        $history = $this->monthly($snap['payload']['history'] ?? [], fn ($i) => [
            'rank'                   => $i['rank'] ?? null,
            'backlinks'              => $i['backlinks'] ?? null,
            'referring_domains'      => $i['referring_domains'] ?? null,
            'new_backlinks'          => $i['new_backlinks'] ?? 0,
            'lost_backlinks'         => $i['lost_backlinks'] ?? 0,
            'new_referring_domains'  => $i['new_referring_domains'] ?? 0,
            'lost_referring_domains' => $i['lost_referring_domains'] ?? 0,
        ]);
        $before = $this->pointAt($history, $period);
        // Months after the comparison base, so new − lost lines up with the change shown.
        $inWindow = array_values(array_filter($history, fn ($p) => $p['date'] > ($before['date'] ?? '0000-00')));

        $score = $s['rank'] ?? null;
        $backlinks = $s['backlinks'] ?? null;
        $domains = $s['referring_domains'] ?? null;

        return [
            'connected'   => true,
            'sandbox'     => $this->client->isSandbox(),
            'fetched_at'  => $snap['fetched_at'],
            'score'       => ['value' => $score, 'delta' => Period::delta($score, $before['rank'] ?? null)],
            'backlinks'   => ['value' => $backlinks, 'delta' => Period::delta($backlinks, $before['backlinks'] ?? null),
                              'new' => array_sum(array_column($inWindow, 'new_backlinks')), 'lost' => array_sum(array_column($inWindow, 'lost_backlinks'))],
            'referring_domains' => ['value' => $domains, 'delta' => Period::delta($domains, $before['referring_domains'] ?? null),
                              'new' => array_sum(array_column($inWindow, 'new_referring_domains')), 'lost' => array_sum(array_column($inWindow, 'lost_referring_domains')),
                              'main' => $s['referring_main_domains'] ?? null],
            'broken_backlinks' => $s['broken_backlinks'] ?? null,
            'spam_score'  => $s['backlinks_spam_score'] ?? null,
            'first_seen'  => $s['first_seen'] ?? null,
            'link_types'  => $s['referring_links_types'] ?? [],
            'top_tlds'    => array_slice((array) ($s['referring_links_tld'] ?? []), 0, 6, true),
            'history'     => $history,
        ];
    }

    public function search(MiraProject $project, Period $period, bool $refresh = false): array
    {
        if (!$this->client->isConfigured()) {
            return $this->notConnected('search');
        }

        $loc     = $this->location();
        $snap    = $this->searchSnapshot($project, $refresh);
        $m       = $snap['payload']['overview'] ?? [];
        $org     = $m['organic'] ?? [];
        $paid    = $m['paid'] ?? [];
        $history = $this->searchHistory($snap['payload']);
        $before = $this->pointAt($history, $period);

        $kw      = fn ($k) => isset($org[$k]) ? (int) $org[$k] : null;
        $top3    = $org ? ($org['pos_1'] ?? 0) + ($org['pos_2_3'] ?? 0) : null;
        $traffic = isset($org['etv']) ? (int) round($org['etv']) : null;
        $value   = isset($org['estimated_paid_traffic_cost']) ? round($org['estimated_paid_traffic_cost'], 2) : null;
        $pTraf   = isset($paid['etv']) ? (int) round($paid['etv']) : null;

        return [
            'connected'  => true,
            'sandbox'    => $this->client->isSandbox(),
            'fetched_at' => $snap['fetched_at'],
            'location'   => $loc,
            'organic' => [
                'keywords' => ['value' => $kw('count'), 'delta' => Period::delta($kw('count'), $before['organic_keywords'] ?? null)],
                'traffic'  => ['value' => $traffic, 'delta' => Period::delta($traffic, $before['organic_traffic'] ?? null)],
                'value'    => ['value' => $value, 'delta' => Period::delta($value, $before['organic_value'] ?? null)],
                'top3'     => ['value' => $top3, 'delta' => Period::delta($top3, $before['top3'] ?? null)],
                'movement' => ['new' => $kw('is_new'), 'up' => $kw('is_up'), 'down' => $kw('is_down'), 'lost' => $kw('is_lost')],
                'positions' => [
                    ['band' => '1–3',    'count' => $top3 ?? 0],
                    ['band' => '4–10',   'count' => (int) ($org['pos_4_10'] ?? 0)],
                    ['band' => '11–20',  'count' => (int) ($org['pos_11_20'] ?? 0)],
                    ['band' => '21–50',  'count' => (int) (($org['pos_21_30'] ?? 0) + ($org['pos_31_40'] ?? 0) + ($org['pos_41_50'] ?? 0))],
                    ['band' => '51–100', 'count' => (int) (($org['pos_51_60'] ?? 0) + ($org['pos_61_70'] ?? 0) + ($org['pos_71_80'] ?? 0) + ($org['pos_81_90'] ?? 0) + ($org['pos_91_100'] ?? 0))],
                ],
            ],
            'paid' => [
                'keywords' => ['value' => isset($paid['count']) ? (int) $paid['count'] : null, 'delta' => Period::delta(isset($paid['count']) ? (int) $paid['count'] : null, $before['paid_keywords'] ?? null)],
                'traffic'  => ['value' => $pTraf, 'delta' => Period::delta($pTraf, $before['paid_traffic'] ?? null)],
                'cost'     => isset($paid['estimated_paid_traffic_cost']) ? round($paid['estimated_paid_traffic_cost'], 2) : null,
            ],
            'top_keywords' => array_map(fn ($i) => [
                'keyword'  => data_get($i, 'keyword_data.keyword'),
                'volume'   => data_get($i, 'keyword_data.keyword_info.search_volume'),
                'cpc'      => data_get($i, 'keyword_data.keyword_info.cpc'),
                'position' => data_get($i, 'ranked_serp_element.serp_item.rank_group'),
                'traffic'  => ($t = data_get($i, 'ranked_serp_element.serp_item.etv')) !== null ? (int) round($t) : null,
                'url'      => data_get($i, 'ranked_serp_element.serp_item.url'),
            ], array_slice(array_values(array_filter($snap['payload']['keywords'] ?? [],
                fn ($i) => data_get($i, 'ranked_serp_element.serp_item.type', 'organic') === 'organic')), 0, (int) config('mira_premium.dataforseo.top_keywords', 20))),
            'history' => $history,
        ];
    }

    // -------------------------------------------------------------- helpers

    private function snapshot(MiraProject $project, string $kind, bool $refresh, callable $fetch): array
    {
        return $this->store->remember($project, $kind, $refresh, $fetch);
    }

    /** Provider history -> [{date: "Y-m", ...fields}] oldest first, one point per month. */
    public function monthly(array $items, callable $map): array
    {
        $points = [];
        foreach ($items as $i) {
            if (empty($i['date'])) {
                continue;
            }
            $points[substr((string) $i['date'], 0, 7)] = ['date' => substr((string) $i['date'], 0, 7)] + $map($i);
        }
        ksort($points);

        return array_values($points);
    }

    /** The monthly point in force at the start of the window (the comparison base). */
    public function pointAt(array $history, Period $period): ?array
    {
        $at = null;
        foreach ($history as $p) {
            if ($p['date'] <= $period->start->format('Y-m')) {
                $at = $p;
            }
        }

        return $at;
    }

    public function notConnected(string $panel): array
    {
        $needs = [
            'authority'   => 'Backlinks, referring domains and the authority score',
            'backlinks'   => 'Referring domains, anchors, new and lost links and the authority distribution',
            'search'      => 'Organic keywords, traffic, traffic value and paid search',
            'performance' => 'The performance history of traffic, keywords and links',
            'positions'   => 'Keyword position history',
            'countries'   => 'Traffic share by country',
            'pages'       => 'Top pages by organic traffic',
            'competitors' => 'Organic competitors and the traffic comparison',
            'insights'    => 'Search intent, branded traffic and SERP features',
            'paid'        => 'Paid keywords and ads',
            'links'       => 'Individual backlinks, most linked pages and broken backlinks',
            'trends'      => 'Search intent and SERP feature history',
        ];

        return [
            'connected' => false,
            'provider'  => 'DataForSEO',
            'message'   => ($needs[$panel] ?? 'This report') . ' need an SEO data provider.',
            'setup'     => 'Add DATAFORSEO_LOGIN and DATAFORSEO_PASSWORD to .env (DATAFORSEO_SANDBOX=true for free test data).',
        ];
    }
}
