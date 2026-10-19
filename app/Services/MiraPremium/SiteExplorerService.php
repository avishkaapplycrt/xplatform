<?php

namespace App\Services\MiraPremium;

use App\Models\MiraPremium\MiraProject;
use App\Models\MiraPremium\MiraVisibilityAnswer;
use App\Models\MiraPremium\MiraVisibilityRun;

/**
 * The Site Explorer reports of Mira Premium (modelled on Ahrefs Site
 * Explorer; data from DataForSEO through SnapshotStore, so each paid call
 * happens at most once per project / kind / day):
 *
 *   performance  monthly organic traffic, keywords, value, paid traffic,
 *                referring domains and backlinks, with year-over-year and
 *                Google update markers
 *   positions    keywords per position band, month by month
 *   countries    organic traffic share across the configured markets
 *   pages        top pages by organic traffic, plus AI citations per page
 *   competitors  organic competitors (shared keywords) and a monthly traffic
 *                race against the project's tracked competitors
 *   insights     traffic by search intent, branded vs non-branded, and SERP
 *                features the site owns
 *   paid         keywords the site bids on, with ad URLs
 *   backlinks    followed vs nofollow, new vs lost per month, the authority
 *                distribution of linking domains, referring domains, anchors
 */
class SiteExplorerService
{
    private const INTENTS = ['informational', 'commercial', 'transactional', 'navigational'];

    public function __construct(private DataForSeoClient $client, private SnapshotStore $store, private SeoDataService $seo) {}

    // ------------------------------------------------------------ performance

    public function performance(MiraProject $project, bool $refresh = false): array
    {
        if (!$this->client->isConfigured()) {
            return $this->seo->notConnected('performance');
        }

        $search    = $this->seo->searchSnapshot($project, $refresh);
        $authority = $this->seo->authoritySnapshot($project, $refresh);
        $links     = $this->seo->monthly($authority['payload']['history'] ?? [], fn ($i) => [
            'referring_domains' => $i['referring_domains'] ?? null,
            'backlinks'         => $i['backlinks'] ?? null,
        ]);

        $months = [];
        foreach ($this->seo->searchHistory($search['payload']) as $p) {
            $months[$p['date']] = array_intersect_key($p, array_flip(['date', 'organic_traffic', 'organic_keywords', 'organic_value', 'paid_traffic']));
        }
        foreach ($links as $p) {
            $months[$p['date']] = ($months[$p['date']] ?? ['date' => $p['date']]) + $p;
        }
        ksort($months);

        $series = array_values(array_map(fn ($m) => $m + [
            'organic_traffic' => null, 'organic_keywords' => null, 'organic_value' => null, 'paid_traffic' => null,
            'referring_domains' => null, 'backlinks' => null,
        ], $months));

        // Year over year: each month next to the same month a year earlier.
        $byDate = array_column($series, null, 'date');
        foreach ($series as &$m) {
            $ly = sprintf('%04d-%s', (int) substr($m['date'], 0, 4) - 1, substr($m['date'], 5, 2));
            $m['last_year'] = isset($byDate[$ly]) ? array_intersect_key($byDate[$ly], array_flip(['organic_traffic', 'organic_keywords', 'referring_domains', 'backlinks'])) : null;
        }
        unset($m);

        $first = $series[0]['date'] ?? null;

        return [
            'connected'  => true,
            'sandbox'    => $this->client->isSandbox(),
            'fetched_at' => $search['fetched_at'],
            'series'     => $series,
            'notes'      => array_values(array_filter((array) config('mira_premium.google_updates'), fn ($n) => $first && substr($n['date'], 0, 7) >= $first)),
        ];
    }

    public function positions(MiraProject $project, bool $refresh = false): array
    {
        if (!$this->client->isConfigured()) {
            return $this->seo->notConnected('positions');
        }

        $snap    = $this->seo->searchSnapshot($project, $refresh);
        $history = array_values(array_filter($this->seo->searchHistory($snap['payload']), fn ($p) => $p['positions'] !== null));
        $current = $snap['payload']['overview']['organic'] ?? [];

        return [
            'connected'  => true,
            'sandbox'    => $this->client->isSandbox(),
            'fetched_at' => $snap['fetched_at'],
            'bands'      => ['1-3', '4-10', '11-20', '21-50', '51-100'],
            'current'    => $current ? SeoDataService::bands($current) : null,
            'movement'   => ['new' => $current['is_new'] ?? null, 'up' => $current['is_up'] ?? null, 'down' => $current['is_down'] ?? null, 'lost' => $current['is_lost'] ?? null],
            'history'    => array_map(fn ($p) => ['date' => $p['date'], 'positions' => $p['positions']], $history),
        ];
    }

    // -------------------------------------------------------------- countries

    public function countries(MiraProject $project, bool $refresh = false): array
    {
        if (!$this->client->isConfigured()) {
            return $this->seo->notConnected('countries');
        }

        $markets = (array) config('mira_premium.dataforseo.markets');
        $snap = $this->store->remember($project, 'countries', $refresh, function () use ($project, $markets) {
            $out = [];
            foreach ($markets as $m) {
                try {
                    $r = $this->client->live('dataforseo_labs/google/domain_rank_overview/live', [
                        'target' => $project->domain, 'location_code' => $m['code'], 'language_code' => $m['language'],
                    ]);
                    $out[$m['iso']] = $r['items'][0]['metrics']['organic'] ?? null;
                } catch (MiraApiException) {
                    $out[$m['iso']] = null;    // one market failing doesn't sink the report
                }
            }

            return $out;
        });

        $rows = [];
        foreach ($markets as $m) {
            $o = $snap['payload'][$m['iso']] ?? null;
            $rows[] = [
                'iso' => $m['iso'], 'country' => $m['country'],
                'traffic'  => $o ? (int) round($o['etv'] ?? 0) : 0,
                'keywords' => $o ? (int) ($o['count'] ?? 0) : 0,
                'value'    => $o ? round($o['estimated_paid_traffic_cost'] ?? 0, 2) : 0,
                'top3'     => $o ? (int) (($o['pos_1'] ?? 0) + ($o['pos_2_3'] ?? 0)) : 0,
            ];
        }
        $total = array_sum(array_column($rows, 'traffic'));
        foreach ($rows as &$r) {
            $r['share'] = $total ? round($r['traffic'] / $total * 100, 1) : 0;
        }
        unset($r);
        usort($rows, fn ($a, $b) => $b['traffic'] <=> $a['traffic'] ?: $b['keywords'] <=> $a['keywords']);

        return [
            'connected' => true, 'sandbox' => $this->client->isSandbox(), 'fetched_at' => $snap['fetched_at'],
            'total_traffic' => $total, 'markets' => count($markets), 'countries' => $rows,
            'note' => 'Share across the ' . count($markets) . ' markets measured, not all countries.',
        ];
    }

    // ------------------------------------------------------------------ pages

    public function pages(MiraProject $project, bool $refresh = false): array
    {
        if (!$this->client->isConfigured()) {
            return $this->seo->notConnected('pages');
        }

        $loc  = $this->seo->location();
        $snap = $this->store->remember($project, 'pages', $refresh, fn () => $this->client->live('dataforseo_labs/google/relevant_pages/live', ['target' => $project->domain] + $loc + [
            'limit' => (int) config('mira_premium.dataforseo.top_pages', 20), 'order_by' => ['metrics.organic.etv,desc'],
        ])['items'] ?? []);

        $ai    = $this->aiCitationsByPath($project);
        $total = array_sum(array_map(fn ($i) => $i['metrics']['organic']['etv'] ?? 0, $snap['payload']));

        return [
            'connected' => true, 'sandbox' => $this->client->isSandbox(), 'fetched_at' => $snap['fetched_at'],
            'pages' => array_map(function ($i) use ($ai, $total) {
                $o    = $i['metrics']['organic'] ?? [];
                $path = rtrim((string) parse_url((string) ($i['page_address'] ?? ''), PHP_URL_PATH), '/') ?: '/';

                return [
                    'url'       => $i['page_address'] ?? null,
                    'path'      => $path,
                    'traffic'   => (int) round($o['etv'] ?? 0),
                    'share'     => $total ? round(($o['etv'] ?? 0) / $total * 100, 1) : 0,
                    'keywords'  => (int) ($o['count'] ?? 0),
                    'top3'      => (int) (($o['pos_1'] ?? 0) + ($o['pos_2_3'] ?? 0)),
                    'value'     => round($o['estimated_paid_traffic_cost'] ?? 0, 2),
                    'ai_citations' => $ai[$path] ?? 0,
                ];
            }, $snap['payload']),
        ];
    }

    // ------------------------------------------------------------ competitors

    public function competitors(MiraProject $project, bool $refresh = false): array
    {
        if (!$this->client->isConfigured()) {
            return $this->seo->notConnected('competitors');
        }

        $loc  = $this->seo->location();
        $snap = $this->store->remember($project, 'competitors', $refresh, fn () => $this->client->live('dataforseo_labs/google/competitors_domain/live', ['target' => $project->domain] + $loc + [
            'limit' => (int) config('mira_premium.dataforseo.organic_competitors', 10) + 1, 'exclude_top_domains' => true,
        ])['items'] ?? []);

        $own   = $this->seo->searchSnapshot($project, $refresh)['payload']['overview']['organic'] ?? [];
        $ownKw = (int) ($own['count'] ?? 0);
        $rows  = [];
        foreach ($snap['payload'] as $i) {
            $domain = preg_replace('/^www\./', '', (string) ($i['domain'] ?? ''));
            if ($domain === '' || $domain === $project->domain) {
                continue;
            }
            $common = (int) ($i['intersections'] ?? 0);
            $rows[] = [
                'domain'        => $domain,
                'common'        => $common,
                'overlap'       => $ownKw ? round($common / $ownKw * 100, 1) : null,
                'avg_position'  => isset($i['avg_position']) ? round($i['avg_position'], 1) : null,
                'traffic'       => (int) round($i['full_domain_metrics']['organic']['etv'] ?? 0),
                'keywords'      => (int) ($i['full_domain_metrics']['organic']['count'] ?? 0),
                'tracked'       => in_array($domain, array_column($project->competitorList(), 'domain'), true),
            ];
        }

        // Traffic race: the project vs each tracked competitor with a domain, month by month.
        $race = [['domain' => $project->domain, 'you' => true,
            'series' => array_map(fn ($p) => ['date' => $p['date'], 'traffic' => $p['organic_traffic']], $this->seo->searchHistory($this->seo->searchSnapshot($project, $refresh)['payload']))]];
        foreach (array_slice(array_filter($project->competitorList(), fn ($c) => !empty($c['domain'])), 0, 5) as $c) {
            $h = $this->store->remember($project, 'race:' . substr(md5($c['domain']), 0, 12), $refresh,
                fn () => $this->client->live('dataforseo_labs/google/historical_rank_overview/live', ['target' => $c['domain']] + $loc)['items'] ?? []);
            $race[] = ['domain' => $c['domain'], 'name' => $c['name'], 'you' => false,
                'series' => array_map(fn ($p) => ['date' => $p['date'], 'traffic' => $p['organic_traffic']], $this->seo->searchHistory(['history' => $h['payload']]))];
        }

        return [
            'connected' => true, 'sandbox' => $this->client->isSandbox(), 'fetched_at' => $snap['fetched_at'],
            'own_keywords' => $ownKw, 'competitors' => array_slice($rows, 0, (int) config('mira_premium.dataforseo.organic_competitors', 10)),
            'race' => $race,
        ];
    }

    // --------------------------------------------------------------- insights

    public function insights(MiraProject $project, bool $refresh = false): array
    {
        if (!$this->client->isConfigured()) {
            return $this->seo->notConnected('insights');
        }

        $snap  = $this->seo->searchSnapshot($project, $refresh);
        $items = $snap['payload']['keywords'] ?? [];
        $brand = array_map('mb_strtolower', array_merge($project->brandTerms(), [explode('.', $project->domain)[0]]));

        $intent  = array_fill_keys(self::INTENTS, ['keywords' => 0, 'traffic' => 0]);
        $branded = ['branded' => ['keywords' => 0, 'traffic' => 0], 'non_branded' => ['keywords' => 0, 'traffic' => 0]];
        $features = [];
        $seen = [];

        foreach ($items as $i) {
            $kw   = mb_strtolower((string) data_get($i, 'keyword_data.keyword', ''));
            $type = (string) data_get($i, 'ranked_serp_element.serp_item.type', 'organic');
            $etv  = (float) data_get($i, 'ranked_serp_element.serp_item.etv', 0);

            if ($type !== 'organic') {
                $features[$type] = ($features[$type] ?? 0) + 1;
            }
            if (isset($seen[$kw])) {
                continue;     // one keyword can rank as organic and as a feature; count it once below
            }
            $seen[$kw] = true;

            $main = data_get($i, 'keyword_data.search_intent_info.main_intent');
            if (isset($intent[$main])) {
                $intent[$main]['keywords']++;
                $intent[$main]['traffic'] += $etv;
            }

            $isBrand = (bool) array_filter($brand, fn ($b) => mb_strlen($b) > 2 && str_contains($kw, $b));
            $bucket = $isBrand ? 'branded' : 'non_branded';
            $branded[$bucket]['keywords']++;
            $branded[$bucket]['traffic'] += $etv;
        }

        $intentTotal  = array_sum(array_column($intent, 'traffic'));
        $brandedTotal = $branded['branded']['traffic'] + $branded['non_branded']['traffic'];
        $labels = ['featured_snippet' => 'Featured snippet', 'local_pack' => 'Local pack', 'ai_overview_reference' => 'AI Overview citation'];

        return [
            'connected' => true, 'sandbox' => $this->client->isSandbox(), 'fetched_at' => $snap['fetched_at'],
            'sample'    => count($seen),
            'intent'    => array_map(fn ($k) => ['intent' => $k, 'keywords' => $intent[$k]['keywords'], 'traffic' => (int) round($intent[$k]['traffic']),
                'share' => $intentTotal ? round($intent[$k]['traffic'] / $intentTotal * 100, 1) : 0], self::INTENTS),
            'branded'   => array_map(fn ($b) => ['keywords' => $b['keywords'], 'traffic' => (int) round($b['traffic']),
                'share' => $brandedTotal ? round($b['traffic'] / $brandedTotal * 100, 1) : 0], $branded),
            'serp_features' => array_map(fn ($k) => ['type' => $k, 'label' => $labels[$k], 'owned' => $features[$k] ?? 0], array_keys($labels)),
            'note' => 'Based on the site\'s top ' . count($seen) . ' keywords by traffic.',
        ];
    }

    // ------------------------------------------------------------------- paid

    public function paid(MiraProject $project, bool $refresh = false): array
    {
        if (!$this->client->isConfigured()) {
            return $this->seo->notConnected('paid');
        }

        $loc  = $this->seo->location();
        $snap = $this->store->remember($project, 'paid', $refresh, fn () => $this->client->live('dataforseo_labs/google/ranked_keywords/live', ['target' => $project->domain] + $loc + [
            'limit' => 20, 'item_types' => ['paid'], 'order_by' => ['keyword_data.keyword_info.search_volume,desc'],
        ])['items'] ?? []);

        return [
            'connected' => true, 'sandbox' => $this->client->isSandbox(), 'fetched_at' => $snap['fetched_at'],
            'keywords' => array_map(fn ($i) => [
                'keyword'  => data_get($i, 'keyword_data.keyword'),
                'volume'   => data_get($i, 'keyword_data.keyword_info.search_volume'),
                'cpc'      => data_get($i, 'keyword_data.keyword_info.cpc'),
                'position' => data_get($i, 'ranked_serp_element.serp_item.rank_group'),
                'title'    => data_get($i, 'ranked_serp_element.serp_item.title'),
                'url'      => data_get($i, 'ranked_serp_element.serp_item.url'),
            ], $snap['payload']),
        ];
    }

    // -------------------------------------------------------------- backlinks

    public function backlinks(MiraProject $project, bool $refresh = false): array
    {
        if (!$this->client->isConfigured()) {
            return $this->seo->notConnected('backlinks');
        }

        $auth = $this->seo->authoritySnapshot($project, $refresh);
        $snap = $this->store->remember($project, 'backlinks', $refresh, fn () => [
            'domains' => $this->client->live('backlinks/referring_domains/live', [
                'target' => $project->domain, 'rank_scale' => 'one_hundred',
                'limit' => (int) config('mira_premium.dataforseo.referring_domains', 300), 'order_by' => ['rank,desc'],
            ])['items'] ?? [],
            'anchors' => $this->client->live('backlinks/anchors/live', [
                'target' => $project->domain, 'rank_scale' => 'one_hundred',
                'limit' => (int) config('mira_premium.dataforseo.anchors', 30), 'order_by' => ['referring_domains,desc'],
            ])['items'] ?? [],
        ]);

        $s       = $auth['payload']['summary'] ?? [];
        $domains = $snap['payload']['domains'] ?? [];
        $history = $this->seo->monthly($auth['payload']['history'] ?? [], fn ($i) => [
            'referring_domains' => $i['referring_domains'] ?? null, 'backlinks' => $i['backlinks'] ?? null,
            'new_referring_domains' => $i['new_referring_domains'] ?? 0, 'lost_referring_domains' => $i['lost_referring_domains'] ?? 0,
            'new_backlinks' => $i['new_backlinks'] ?? 0, 'lost_backlinks' => $i['lost_backlinks'] ?? 0,
        ]);

        // Authority distribution of linking domains: 0–9, 10–19 … 90–100.
        $dist = array_fill(0, 10, 0);
        foreach ($domains as $d) {
            $dist[min(9, intdiv((int) ($d['rank'] ?? 0), 10))]++;
        }

        $rd   = (int) ($s['referring_domains'] ?? 0);
        $rdNf = (int) ($s['referring_domains_nofollow'] ?? 0);
        $anchorTotal = array_sum(array_map(fn ($a) => (int) ($a['referring_domains'] ?? 0), $snap['payload']['anchors'] ?? []));

        return [
            'connected' => true, 'sandbox' => $this->client->isSandbox(), 'fetched_at' => $snap['fetched_at'],
            'summary' => [
                'score'             => $s['rank'] ?? null,
                'referring_domains' => $rd,
                'followed_domains'  => max(0, $rd - $rdNf),
                'backlinks'         => $s['backlinks'] ?? null,
                'referring_ips'     => $s['referring_ips'] ?? null,
                'broken_backlinks'  => $s['broken_backlinks'] ?? null,
                'spam_score'        => $s['backlinks_spam_score'] ?? null,
            ],
            'follow'       => ['followed' => max(0, $rd - $rdNf), 'nofollow' => $rdNf],
            'history'      => $history,
            'distribution' => array_map(fn ($i) => ['band' => ($i * 10) . '–' . ($i === 9 ? 100 : $i * 10 + 9), 'domains' => $dist[$i]], range(0, 9)),
            'sampled'      => count($domains),
            'domains'      => array_map(fn ($d) => [
                'domain' => $d['domain'] ?? null, 'authority' => $d['rank'] ?? null, 'backlinks' => $d['backlinks'] ?? null,
                'first_seen' => $d['first_seen'] ?? null, 'lost_date' => $d['lost_date'] ?? null,
                'nofollow' => ($d['referring_pages_nofollow'] ?? 0) > 0 && ($d['referring_pages_nofollow'] ?? 0) >= ($d['referring_pages'] ?? 0),
            ], array_slice($domains, 0, 50)),
            'anchors'      => array_map(fn ($a) => [
                'anchor' => ($a['anchor'] ?? '') === '' ? '(empty / image)' : $a['anchor'],
                'referring_domains' => (int) ($a['referring_domains'] ?? 0), 'backlinks' => (int) ($a['backlinks'] ?? 0),
                'share' => $anchorTotal ? round(($a['referring_domains'] ?? 0) / $anchorTotal * 100, 1) : 0,
                'first_seen' => $a['first_seen'] ?? null,
            ], $snap['payload']['anchors'] ?? []),
            'tlds'  => array_slice((array) ($s['referring_links_tld'] ?? []), 0, 8, true),
            'types' => (array) ($s['referring_links_types'] ?? []),
        ];
    }

    // ---------------------------------------------------------------- helpers

    /** path => number of AI answers (all runs) citing that page of the project. */
    private function aiCitationsByPath(MiraProject $project): array
    {
        $runIds = MiraVisibilityRun::where('project_id', $project->id)->where('status', 'done')->pluck('id');
        $out = [];
        foreach (MiraVisibilityAnswer::whereIn('run_id', $runIds)->whereNotNull('cited_urls')->pluck('cited_urls') as $urls) {
            foreach (array_unique(array_map(fn ($u) => $this->ownPath($u, $project), (array) $urls)) as $path) {
                if ($path !== null) {
                    $out[$path] = ($out[$path] ?? 0) + 1;
                }
            }
        }

        return $out;
    }

    private function ownPath(string $url, MiraProject $project): ?string
    {
        $host = preg_replace('/^www\./', '', strtolower((string) parse_url($url, PHP_URL_HOST)));
        if ($host !== $project->domain && !str_ends_with($host, '.' . $project->domain)) {
            return null;
        }

        return rtrim((string) parse_url($url, PHP_URL_PATH), '/') ?: '/';
    }
}
