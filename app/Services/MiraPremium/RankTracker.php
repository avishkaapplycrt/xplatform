<?php

namespace App\Services\MiraPremium;

use App\Models\MiraPremium\MiraProject;
use App\Models\MiraPremium\MiraRankCheck;
use App\Models\MiraPremium\MiraTrackedKeyword;
use Illuminate\Support\Facades\Log;

/**
 * Rank tracking: the keywords a project follows, their Google position once a
 * day (DataForSEO SERP API, top 100), and the numbers built from them:
 *
 * - Visibility: Σ CTR(position) ÷ (keywords × CTR(1)), 0–100 %. 100 % means
 *   position 1 for every tracked keyword.
 * - Share of voice: each domain's estimated clicks (volume × CTR at its
 *   position) ÷ all tracked domains' estimated clicks, across the same keywords.
 *
 * The CTR curve and limits are in config/mira_premium.php (rank_tracking).
 * Daily checks run from the scheduler (mira:track-ranks); "Check now" re-checks
 * keywords not checked in the last hour.
 */
class RankTracker
{
    public function __construct(private DataForSeoClient $client, private SeoDataService $seo) {}

    // ---------------------------------------------------------------- keywords

    /**
     * Adds keywords (one per line or comma-separated), fetches their volume,
     * difficulty and intent, then checks their position straight away.
     *
     * @return int how many were added
     */
    public function add(Entitlement $who, MiraProject $project, string $input): int
    {
        $who->require('tracked_keywords', 'Rank tracking');
        $this->requireProvider();

        $words = array_values(array_unique(array_filter(array_map(
            fn ($k) => mb_strtolower(trim(preg_replace('/\s+/u', ' ', $k))),
            preg_split('/[\r\n,]+/u', $input)
        ), fn ($k) => mb_strlen($k) >= 2 && mb_strlen($k) <= 200)));
        if ($words === []) {
            throw new MiraApiException('InvalidInput', 'Enter at least one keyword (2–200 characters).', ['field' => 'keywords']);
        }

        $existing = $project->trackedKeywords()->pluck('keyword')->all();
        $new      = array_values(array_diff($words, $existing));
        if ($new === []) {
            throw new MiraApiException('InvalidInput', 'Those keywords are already tracked.', ['field' => 'keywords']);
        }

        [$cap] = $who->limit('tracked_keywords');
        if (count($existing) + count($new) > $cap) {
            throw new MiraApiException('LimitReached', "Your plan tracks up to {$cap} keywords per project. Remove some or upgrade.", [
                'upgrade_url' => config('mira_premium.upgrade_url'),
            ]);
        }

        $facts = $this->keywordFacts($new);
        foreach ($new as $kw) {
            $project->trackedKeywords()->create(['keyword' => $kw] + ($facts[$kw] ?? []));
        }

        $this->check($project, $new);

        return count($new);
    }

    public function remove(MiraProject $project, int $keywordId): void
    {
        $deleted = $project->trackedKeywords()->where('id', $keywordId)->delete();
        if (!$deleted) {
            throw new MiraApiException('NotFound', 'That keyword is not tracked.');
        }
    }

    /**
     * Checks positions. $only limits it to these keywords (just added); otherwise
     * every keyword not checked within min_recheck_minutes ($daily: not checked today).
     *
     * @return int keywords checked
     */
    public function check(MiraProject $project, ?array $only = null, bool $daily = false): int
    {
        $this->requireProvider();

        $q = $project->trackedKeywords();
        if ($only !== null) {
            $q->whereIn('keyword', $only);
        } elseif ($daily) {
            $q->where(fn ($w) => $w->whereNull('last_checked_at')->orWhere('last_checked_at', '<', now()->startOfDay()));
        } else {
            $q->where(fn ($w) => $w->whereNull('last_checked_at')
                ->orWhere('last_checked_at', '<', now()->subMinutes((int) config('mira_premium.rank_tracking.min_recheck_minutes', 60))));
        }

        $checked = 0;
        foreach ($q->get() as $kw) {
            try {
                $this->checkKeyword($project, $kw);
                $checked++;
            } catch (MiraApiException $e) {
                Log::warning('Rank check failed', ['project' => $project->id, 'keyword' => $kw->keyword, 'message' => $e->getMessage()]);
            }
        }

        return $checked;
    }

    private function checkKeyword(MiraProject $project, MiraTrackedKeyword $kw): void
    {
        $loc  = $this->seo->location();
        $rivals = array_values(array_filter(array_map(fn ($c) => preg_replace('/^www\./', '', strtolower(trim((string) ($c['domain'] ?? '')))), $project->competitorList())));
        $res = $this->client->live('serp/google/organic/live/advanced', [
            'keyword' => $kw->keyword, 'depth' => (int) config('mira_premium.rank_tracking.depth', 100),
            // tag: DataForSEO's free-text task label (max 255); sample data reads the domains from it
            'tag'     => mb_substr(implode('|', array_merge([$project->domain], $rivals)), 0, 255),
        ] + $loc);

        $items    = $res['items'] ?? [];
        $organic  = array_values(array_filter($items, fn ($i) => ($i['type'] ?? '') === 'organic'));
        $find     = function (string $site) use ($organic) {
            foreach ($organic as $i) {
                $d = preg_replace('/^www\./', '', strtolower((string) ($i['domain'] ?? parse_url((string) ($i['url'] ?? ''), PHP_URL_HOST))));
                if ($site !== '' && ($d === $site || str_ends_with($d, '.' . $site))) {
                    return [(int) ($i['rank_group'] ?? 0) ?: null, $i['url'] ?? null];
                }
            }

            return [null, null];
        };

        [$position, $url] = $find($project->domain);
        $competitors = [];
        foreach ($project->competitorList() as $c) {
            $d = preg_replace('/^www\./', '', strtolower(trim((string) ($c['domain'] ?? ''))));
            if ($d !== '') {
                $competitors[$d] = $find($d)[0];
            }
        }

        MiraRankCheck::updateOrCreate(
            ['tracked_keyword_id' => $kw->id, 'checked_on' => now()->toDateString()],
            [
                'position'      => $position,
                'url'           => $url,
                'competitors'   => $competitors,
                'serp_features' => array_values(array_unique(array_filter(array_map(fn ($i) => $i['type'] ?? null, $items), fn ($t) => $t && $t !== 'organic'))),
            ]
        );
        $kw->update(['last_checked_at' => now()]);
    }

    /** @return array<string, array{volume: ?int, difficulty: ?int, intent: ?string}> */
    private function keywordFacts(array $keywords): array
    {
        try {
            $res = $this->client->live('dataforseo_labs/google/keyword_overview/live', ['keywords' => $keywords] + $this->seo->location());
        } catch (MiraApiException $e) {
            return [];   // tracking still works; facts fill in on a later add
        }

        $out = [];
        foreach ($res['items'] ?? [] as $i) {
            $k = mb_strtolower((string) ($i['keyword'] ?? ''));
            $out[$k] = [
                'volume'     => data_get($i, 'keyword_info.search_volume'),
                'difficulty' => data_get($i, 'keyword_properties.keyword_difficulty'),
                'intent'     => data_get($i, 'search_intent_info.main_intent'),
            ];
        }

        return $out;
    }

    // ------------------------------------------------------------------ report

    public function report(Entitlement $who, MiraProject $project): array
    {
        if (!$this->client->isConfigured()) {
            return $this->seo->notConnected('ranks');
        }

        $days     = (int) config('mira_premium.rank_tracking.history_days', 30);
        $since    = now()->subDays($days - 1)->startOfDay();
        $keywords = $project->trackedKeywords()->orderBy('keyword')->get();
        $checks   = MiraRankCheck::whereIn('tracked_keyword_id', $keywords->pluck('id'))
            ->where('checked_on', '>=', $since->toDateString())->orderBy('checked_on')->get()
            ->groupBy('tracked_keyword_id');

        $rows = $keywords->map(function ($kw) use ($checks) {
            $mine   = ($checks[$kw->id] ?? collect())->values();
            $latest = $mine->last();
            $prev   = $mine->count() > 1 ? $mine[$mine->count() - 2] : null;
            $weekAgo = $mine->first(fn ($c) => $c->checked_on->gte(now()->subDays(7)->startOfDay()));
            $positions = $mine->pluck('position')->filter();

            return [
                'id'         => $kw->id,
                'keyword'    => $kw->keyword,
                'volume'     => $kw->volume,
                'difficulty' => $kw->difficulty,
                'band'       => self::band($kw->difficulty),
                'intent'     => $kw->intent,
                'position'   => $latest?->position,
                'url'        => $latest?->url,
                // No earlier check = no change yet (not "entered the top 100").
                'change_1d'  => $prev ? self::change($prev->position, $latest?->position) : null,
                'change_7d'  => $weekAgo && $weekAgo->id !== $latest?->id ? self::change($weekAgo->position, $latest?->position) : null,
                'best'       => $positions->isNotEmpty() ? $positions->min() : null,
                'serp_features' => $latest?->serp_features ?? [],
                'competitors'   => $latest?->competitors ?? [],
                'history'    => $mine->map(fn ($c) => ['date' => $c->checked_on->toDateString(), 'position' => $c->position])->all(),
                'checked_at' => $kw->last_checked_at?->toIso8601String(),
            ];
        })->values();

        // Latest position per keyword for every domain (you + competitors).
        $domains = array_merge([$project->domain], array_values(array_filter(array_map(
            fn ($c) => preg_replace('/^www\./', '', strtolower(trim((string) ($c['domain'] ?? '')))), $project->competitorList()))));
        $latestPos = $rows->map(fn ($r) => ['volume' => (int) ($r['volume'] ?? 0)] + array_merge($r['competitors'], [$project->domain => $r['position']]))->all();

        // Daily visibility trend for you and each competitor.
        $trend = [];
        foreach ($checks->flatten() as $c) {
            $trend[$c->checked_on->toDateString()][] = array_merge($c->competitors ?? [], [$project->domain => $c->position]);
        }
        ksort($trend);
        $n = max(1, $keywords->count());
        $trendOut = [];
        foreach ($trend as $date => $dayRows) {
            $point = ['date' => $date];
            foreach ($domains as $d) {
                $point[$d] = round(array_sum(array_map(fn ($r) => $this->ctr($r[$d] ?? null), $dayRows)) / ($n * $this->ctr(1)) * 100, 1);
            }
            $trendOut[] = $point;
        }

        $positions = $rows->pluck('position')->filter();
        [$cap] = $who->limit('tracked_keywords');

        return [
            'connected'  => true,
            'sandbox'    => $this->client->isSandbox(),
            'demo'       => $this->client->isDemo(),
            'fetched_at' => $keywords->max('last_checked_at')?->toIso8601String(),
            'limit'      => $cap,
            'location'   => $this->seo->location(),
            'domain'     => $project->domain,
            'domains'    => $domains,
            'summary'    => [
                'tracked'      => $keywords->count(),
                'ranking'      => $positions->count(),
                'top3'         => $positions->filter(fn ($p) => $p <= 3)->count(),
                'top10'        => $positions->filter(fn ($p) => $p <= 10)->count(),
                'avg_position' => $positions->isNotEmpty() ? round($positions->avg(), 1) : null,
                'improved'     => $rows->filter(fn ($r) => ($r['change_1d'] ?? 0) > 0)->count(),
                'declined'     => $rows->filter(fn ($r) => ($r['change_1d'] ?? 0) < 0)->count(),
                'visibility'   => round($rows->sum(fn ($r) => $this->ctr($r['position'])) / ($n * $this->ctr(1)) * 100, 1),
            ],
            'share_of_voice' => $this->shareOfVoice($domains, $latestPos),
            'trend'      => $trendOut,
            'keywords'   => $rows->all(),
            'bands'      => self::bands(),
            'suggestions' => $this->suggestions($project, $keywords->pluck('keyword')->all()),
        ];
    }

    /** @return list<array{domain: string, share: float, clicks: int}> estimated clicks share per domain */
    private function shareOfVoice(array $domains, array $rows): array
    {
        $clicks = array_fill_keys($domains, 0.0);
        foreach ($rows as $r) {
            foreach ($domains as $d) {
                $clicks[$d] += max(1, $r['volume']) * $this->ctr($r[$d] ?? null);
            }
        }
        $total = array_sum($clicks);

        return array_map(fn ($d) => ['domain' => $d, 'share' => $total > 0 ? round($clicks[$d] / $total * 100, 1) : 0.0, 'clicks' => (int) round($clicks[$d])], $domains);
    }

    /** Top organic keywords the site already ranks for, not tracked yet — one click to add. */
    private function suggestions(MiraProject $project, array $tracked): array
    {
        try {
            $items = $this->seo->searchSnapshot($project)['payload']['keywords'] ?? [];
        } catch (MiraApiException $e) {
            return [];
        }

        $out = [];
        foreach ($items as $i) {
            $k = mb_strtolower((string) data_get($i, 'keyword_data.keyword', ''));
            if ($k !== '' && !in_array($k, $tracked, true) && !in_array($k, $out, true)
                && data_get($i, 'ranked_serp_element.serp_item.type', 'organic') === 'organic') {
                $out[] = $k;
            }
            if (count($out) >= 8) {
                break;
            }
        }

        return $out;
    }

    // ----------------------------------------------------------------- helpers

    private function ctr(?int $position): float
    {
        if ($position === null || $position < 1) {
            return 0.0;
        }
        $curve = (array) config('mira_premium.rank_tracking.ctr');

        return (float) ($curve[$position] ?? ($position <= 20 ? config('mira_premium.rank_tracking.ctr_11_20', 0.01) : 0));
    }

    /** Positive = moved up (e.g. 8 → 5 is +3). Entering the top 100 counts from 101. */
    private static function change(?int $before, ?int $now): ?int
    {
        if ($before === null && $now === null) {
            return null;
        }

        return ($before ?? 101) - ($now ?? 101);
    }

    /** Keyword difficulty bands: [min, label] from config, highest first match. */
    public static function bands(): array
    {
        return (array) config('mira_premium.keyword_difficulty_bands');
    }

    public static function band(?int $kd): ?string
    {
        if ($kd === null) {
            return null;
        }
        $label = null;
        foreach (self::bands() as $b) {
            if ($kd >= $b['min']) {
                $label = $b['label'];
            }
        }

        return $label;
    }

    private function requireProvider(): void
    {
        if (!$this->client->isConfigured()) {
            throw new MiraApiException('Unavailable', 'Rank tracking needs DataForSEO. Add DATAFORSEO_LOGIN and DATAFORSEO_PASSWORD to .env.');
        }
    }
}
