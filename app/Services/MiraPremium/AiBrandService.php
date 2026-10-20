<?php

namespace App\Services\MiraPremium;

use App\Models\MiraPremium\MiraProject;
use App\Models\MiraPremium\MiraVisibilityAnswer;
use App\Models\MiraPremium\MiraVisibilityRun;

/**
 * Brand-Radar-style AI visibility report for a project, from its visibility
 * runs (VisibilityEngine). Definitions follow the industry's:
 *
 *   mention   the brand's name appears in an AI answer's text
 *   citation  the answer links to one of the brand's pages as a source
 *
 * Modeled: answers come from one model, sampled from the project's prompt set.
 */
class AiBrandService
{
    public function report(MiraProject $project): array
    {
        $runs = MiraVisibilityRun::where('project_id', $project->id)->where('status', 'done')->orderBy('finished_at')->get();
        if ($runs->isEmpty()) {
            return ['has_runs' => false];
        }

        $latest   = $runs->last();
        $answers  = MiraVisibilityAnswer::whereIn('run_id', $runs->pluck('id'))->whereNull('error')->get();
        $byRun    = $answers->groupBy('run_id');
        $lastSet  = $byRun->get($latest->id, collect());
        $brand    = $project->brandTerms()[0];
        $tracked  = array_merge([$brand], array_column($project->competitorList(), 'name'));

        // Mentions vs citations, by month (last 6 months with data).
        $monthly = [];
        foreach ($runs as $r) {
            $m = $r->finished_at->format('Y-m');
            $monthly[$m] ??= ['month' => $m, 'mentions' => 0, 'citations' => 0, 'answers' => 0];
            $monthly[$m]['mentions']  += $r->mentions;
            $monthly[$m]['citations'] += $r->citations;
            $monthly[$m]['answers']   += $r->answered_count;
        }

        // Each tracked brand's mentions per run (the competitor comparison chart).
        $brandsOverTime = array_map(fn ($name) => [
            'name' => $name, 'you' => $name === $brand,
            'series' => $runs->map(fn ($r) => [
                'date' => $r->finished_at->toIso8601String(),
                'mentions' => $byRun->get($r->id, collect())->filter(fn ($a) => in_array($name, (array) $a->brands_mentioned, true))->count(),
            ])->values()->all(),
        ], $tracked);

        // Top-cited own pages (all runs): answers citing them, and average cited position.
        $pages = [];
        foreach ($answers as $a) {
            foreach (array_values((array) $a->cited_urls) as $i => $url) {
                $host = preg_replace('/^www\./', '', strtolower((string) parse_url($url, PHP_URL_HOST)));
                if ($host !== $project->domain && !str_ends_with($host, '.' . $project->domain)) {
                    continue;
                }
                $key = 'https://' . $host . (rtrim((string) parse_url($url, PHP_URL_PATH), '/') ?: '');
                $pages[$key] ??= ['url' => $key, 'cited_in' => 0, 'positions' => [], 'mentioned_in' => 0];
                $pages[$key]['cited_in']++;
                $pages[$key]['positions'][] = $i + 1;
                if ($a->brand_mentioned) {
                    $pages[$key]['mentioned_in']++;
                }
            }
        }
        $pages = array_values(array_map(fn ($p) => ['url' => $p['url'], 'cited_in' => $p['cited_in'], 'mentioned_in' => $p['mentioned_in'],
            'avg_position' => round(array_sum($p['positions']) / count($p['positions']), 1)], $pages));
        usort($pages, fn ($a, $b) => $b['cited_in'] <=> $a['cited_in']);

        // Domains cited in answers that mention the brand (latest run).
        $domains = [];
        foreach ($lastSet->where('brand_mentioned', true) as $a) {
            foreach ((array) $a->cited_domains as $d) {
                $domains[$d] = ($domains[$d] ?? 0) + 1;
            }
        }
        arsort($domains);

        // Topics: where AI names the brand most (latest run).
        $topics = $lastSet->groupBy(fn ($a) => $a->topic ?: 'General')->map(fn ($g, $t) => [
            'topic' => $t, 'answers' => $g->count(), 'mentions' => $g->where('brand_mentioned', true)->count(),
            'rate' => round($g->where('brand_mentioned', true)->count() / max(1, $g->count()) * 100, 1),
        ])->sortByDesc('rate')->values()->all();

        // Mention gap: prompts where a competitor is named but the brand isn't (latest run).
        $gaps = $lastSet->filter(fn ($a) => !$a->brand_mentioned && array_diff((array) $a->brands_mentioned, [$brand]))
            ->map(fn ($a) => ['prompt' => $a->prompt, 'topic' => $a->topic, 'competitors' => array_values(array_diff((array) $a->brands_mentioned, [$brand]))])
            ->values()->all();

        // Most mentioned brands (any brand the answers name), latest run.
        $found = [];
        foreach ($lastSet as $a) {
            foreach (array_unique((array) ($a->brands_found ?? [])) as $b) {
                $k = mb_strtolower($b);
                $found[$k] ??= ['name' => $b, 'answers' => 0];
                $found[$k]['answers']++;
            }
        }
        usort($found, fn ($a, $b) => $b['answers'] <=> $a['answers']);
        $lowerTracked = array_map('mb_strtolower', $tracked);
        $found = array_map(fn ($f) => $f + ['kind' => mb_strtolower($f['name']) === mb_strtolower($brand) ? 'you'
            : (in_array(mb_strtolower($f['name']), $lowerTracked, true) ? 'competitor' : 'other')], array_slice($found, 0, 12));

        $sent = $lastSet->whereNotNull('sentiment')->countBy('sentiment')->all();

        // By prompt tag (latest run): mention rate per tag, for topic / funnel / campaign reporting.
        $tagsById = \App\Models\MiraPremium\MiraPrompt::where('project_id', $project->id)->pluck('tags', 'id');
        $byTag = [];
        foreach ($lastSet as $a) {
            foreach ((array) ($tagsById[$a->prompt_id] ?? []) as $tag) {
                $byTag[$tag] ??= ['tag' => $tag, 'answers' => 0, 'mentions' => 0];
                $byTag[$tag]['answers']++;
                if ($a->brand_mentioned) {
                    $byTag[$tag]['mentions']++;
                }
            }
        }
        $byTag = array_values(array_map(fn ($t) => $t + ['rate' => round($t['mentions'] / max(1, $t['answers']) * 100, 1)], $byTag));
        usort($byTag, fn ($a, $b) => $b['answers'] <=> $a['answers']);

        return [
            'has_runs'  => true,
            'sample'    => $runs->contains(fn ($r) => $r->trigger === 'sample'),   // artisan mira:sample-data
            'brand'     => $brand,
            'model'     => $latest->model,
            'latest'    => ['date' => $latest->finished_at->toIso8601String(), 'answers' => $latest->answered_count,
                            'mentions' => $latest->mentions, 'citations' => $latest->citations, 'share' => $latest->share_of_voice],
            'monthly'   => array_slice(array_values($monthly), -6),
            'brands_over_time' => $brandsOverTime,
            'cited_pages'   => array_slice($pages, 0, 10),
            'cited_domains' => array_map(fn ($d, $n) => ['domain' => $d, 'answers' => $n,
                'kind' => $d === $project->domain || str_ends_with($d, '.' . $project->domain) ? 'you' : 'other'], array_keys(array_slice($domains, 0, 10, true)), array_slice($domains, 0, 10, true)),
            'topics'    => $topics,
            'gaps'      => array_slice($gaps, 0, 10),
            'most_mentioned' => $found,
            'sentiment' => ['positive' => $sent['positive'] ?? 0, 'neutral' => $sent['neutral'] ?? 0, 'negative' => $sent['negative'] ?? 0,
                            'analyzed' => array_sum($sent)],
            'impressions' => $latest->summary['impressions'] ?? null,
            'impressions_trend' => $runs->map(fn ($r) => ['date' => $r->finished_at->toIso8601String(),
                'brand' => $r->summary['impressions']['brand'] ?? null, 'share' => $r->summary['impressions']['share'] ?? null])->values()->all(),
            'tags'      => $byTag,
            'schedule'  => ['frequency' => $project->visibility_schedule ?? 'off', 'next_run_at' => $project->next_visibility_run_at?->toIso8601String(),
                            'last_trigger' => $latest->trigger ?? 'manual'],
        ];
    }
}
