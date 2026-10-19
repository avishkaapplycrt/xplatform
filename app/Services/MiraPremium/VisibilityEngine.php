<?php

namespace App\Services\MiraPremium;

use App\Jobs\RunMiraVisibility;
use App\Models\MiraPremium\MiraProject;
use App\Models\MiraPremium\MiraPrompt;
use App\Models\MiraPremium\MiraScan;
use App\Models\MiraPremium\MiraVisibilityAnswer;
use App\Models\MiraPremium\MiraVisibilityRun;
use App\Services\Llm\OpenAiClient;
use App\Services\Llm\OpenAiException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * AI visibility for a project: a prompt set (the questions customers ask an
 * AI assistant) is answered by an LLM on demand, and each answer is parsed
 * for the brand, its competitors, and the URLs / domains it cites. A run's
 * answers roll up into mentions, citations, share of voice, top cited
 * domains and per-topic coverage; runs over time give the timeline and the
 * "new mentions this cycle" delta.
 *
 * Honest by design: answers come from one model without live web access, are
 * non-deterministic, and are sampled from the prompt set — so every number is
 * a modeled estimate of visibility, and the report says so.
 */
class VisibilityEngine
{
    private OpenAiClient $llm;

    // The client is built here, not injected: the container would build one with the
    // default chat model instead of the configured visibility model.
    public function __construct(private UsageMeter $meter)
    {
        $this->llm = new OpenAiClient(null, (string) config('mira_premium.visibility.model'));
    }

    // ------------------------------------------------------------------ prompts

    public function addPrompt(Entitlement $who, MiraProject $project, string $text, ?string $topic, string $source = 'user'): MiraPrompt
    {
        $who->require('prompts', 'AI visibility tracking');
        $text = trim(preg_replace('/\s+/', ' ', $text));

        if (mb_strlen($text) < 8 || mb_strlen($text) > 300) {
            throw new MiraApiException('InvalidInput', 'A prompt should be a full question of 8–300 characters.', ['field' => 'prompt']);
        }

        if ($project->prompts()->whereRaw('LOWER(prompt) = ?', [mb_strtolower($text)])->exists()) {
            throw new MiraApiException('InvalidInput', 'That prompt is already in the set.', ['field' => 'prompt']);
        }

        $this->assertRoom($who, $project, 1);

        return $project->prompts()->create([
            'prompt' => $text,
            'topic'  => $this->topic($topic),
            'source' => $source,
        ]);
    }

    /**
     * Suggests prompts real customers would ask an AI assistant about this
     * kind of business — unbranded, so they measure whether the brand comes up
     * on its own. Uses the site's latest scan for context; falls back to
     * templates when the model is unavailable.
     *
     * @return MiraPrompt[] the prompts added
     */
    public function suggestPrompts(Entitlement $who, MiraProject $project): array
    {
        $who->require('prompts', 'AI visibility tracking');

        [$limit] = $who->limit('prompts');
        $room    = min((int) config('mira_premium.visibility.seed_prompts', 10), $limit - $project->prompts()->count());
        if ($room <= 0) {
            throw new MiraApiException('LimitReached', "Your prompt set is full ({$limit} prompts on your plan). Remove some or upgrade.", [
                'upgrade_url' => config('mira_premium.upgrade_url'),
            ]);
        }

        $existing = $project->prompts()->pluck('prompt')->map(fn ($p) => mb_strtolower($p))->all();
        $added    = [];

        foreach ($this->generatePrompts($project, $room + 5) as $row) {
            if (count($added) >= $room || in_array(mb_strtolower($row['prompt']), $existing, true)) {
                continue;
            }
            $existing[] = mb_strtolower($row['prompt']);
            $added[]    = $project->prompts()->create(['prompt' => $row['prompt'], 'topic' => $this->topic($row['topic']), 'source' => 'seed']);
        }

        return $added;
    }

    /** @return list<array{prompt: string, topic: ?string}> */
    private function generatePrompts(MiraProject $project, int $count): array
    {
        $scan    = MiraScan::where('project_id', $project->id)->where('status', 'done')->latest('id')->first();
        $report  = $scan?->reportData();
        $title   = $report['title'] ?? ($report['you']['name'] ?? '');
        $snippet = $report['serp_preview']['description'] ?? ($report['you']['serp_preview']['description'] ?? '');
        $topics  = implode(', ', (array) config('mira_premium.visibility.topics'));

        if ($this->llm->isConfigured()) {
            try {
                $raw = $this->llm->chat(
                    "You design AI-visibility prompt sets. Reply with JSON only: {\"prompts\":[{\"prompt\":\"...\",\"topic\":\"...\"}]}.",
                    "Business: {$project->name} ({$project->domain}).\nPage title: {$title}\nDescription: {$snippet}\n\n"
                    . "Write {$count} questions a potential customer would type into ChatGPT, Gemini or Perplexity when looking for what this business offers. "
                    . "Do NOT name the business or its domain — the point is to see whether AI brings it up unprompted. "
                    . "Mix intents (finding options, comparing, pricing, trust, how-to, local where relevant). Each under 18 words. "
                    . "topic must be one of: {$topics}.",
                    ['max_tokens' => 900, 'temperature' => 0.5]
                );
                $json = json_decode(trim(preg_replace('/^```(json)?|```$/m', '', $raw)), true);
                $rows = array_values(array_filter(array_map(
                    fn ($r) => is_array($r) && is_string($r['prompt'] ?? null) && mb_strlen(trim($r['prompt'])) >= 8
                        ? ['prompt' => mb_substr(trim($r['prompt']), 0, 300), 'topic' => $r['topic'] ?? null] : null,
                    (array) ($json['prompts'] ?? [])
                )));
                if ($rows !== []) {
                    return $rows;
                }
            } catch (OpenAiException $e) {
                Log::warning('Mira visibility prompt suggestions failed', ['message' => $e->getMessage()]);
            }
        }

        $what = $title !== '' ? Str::limit(preg_replace('/\s*[|\-–—:].*$/u', '', $title), 60, '') : $project->domain;

        return [
            ['prompt' => "What are the best companies for {$what}?", 'topic' => 'Discovery'],
            ['prompt' => "Which {$what} providers do experts recommend?", 'topic' => 'Discovery'],
            ['prompt' => "How do I choose a good {$what} provider?", 'topic' => 'How-to'],
            ['prompt' => "How much does {$what} usually cost?", 'topic' => 'Pricing'],
            ['prompt' => "What are the most trusted brands for {$what}?", 'topic' => 'Trust'],
            ['prompt' => "Compare the top {$what} options available today", 'topic' => 'Comparison'],
            ['prompt' => "What should I avoid when buying {$what}?", 'topic' => 'Trust'],
            ['prompt' => "Who offers the best value for {$what}?", 'topic' => 'Pricing'],
        ];
    }

    // ---------------------------------------------------------------------- runs

    public function startRun(Entitlement $who, MiraProject $project): MiraVisibilityRun
    {
        $who->require('visibility_runs', 'AI visibility tracking');

        if (!$this->llm->isConfigured()) {
            throw new MiraApiException('Unavailable', 'The AI visibility engine is not configured right now.');
        }

        $active = $project->prompts()->where('is_active', true)->count();
        if ($active === 0) {
            throw new MiraApiException('InvalidInput', 'Add or suggest some prompts first — they are what the engine asks.');
        }

        $running = $project->visibilityRuns()->whereIn('status', ['queued', 'running'])
            ->where('created_at', '>=', now()->subMinutes(15))->first();
        if ($running !== null) {
            return $running;
        }

        $this->meter->consume($who, 'visibility_runs');

        $run = $project->visibilityRuns()->create([
            'uuid'          => (string) Str::uuid(),
            'status'        => 'queued',
            'model'         => (string) config('mira_premium.visibility.model'),
            'prompts_count' => min($active, (int) config('mira_premium.visibility.prompts_per_run', 25)),
        ]);

        if (config('mira_premium.dispatch') === 'queue') {
            RunMiraVisibility::dispatch($run->id, $who->subject, $who->limit('visibility_runs')[1] ?? 'month');
        } else {
            RunMiraVisibility::dispatchAfterResponse($run->id, $who->subject, $who->limit('visibility_runs')[1] ?? 'month');
        }

        return $run;
    }

    /** Answers every prompt in the run, parses the answers and aggregates them. Called by RunMiraVisibility. */
    public function execute(MiraVisibilityRun $run, string $subject, string $period): void
    {
        if ($run->status !== 'queued') {
            return;
        }

        $project = MiraProject::find($run->project_id);
        if ($project === null) {
            $run->update(['status' => 'failed', 'error_message' => 'Project removed.']);

            return;
        }

        $run->update(['status' => 'running', 'started_at' => now()]);
        @set_time_limit(600);

        $prompts = $project->prompts()->where('is_active', true)->orderBy('id')
            ->limit((int) config('mira_premium.visibility.prompts_per_run', 25))->get();

        foreach ($prompts as $prompt) {
            $row = ['run_id' => $run->id, 'prompt_id' => $prompt->id, 'prompt' => $prompt->prompt, 'topic' => $prompt->topic];

            try {
                $answer = $this->llm->chat(
                    'You are a helpful AI assistant. Answer the question concretely, as you normally would for a member of the public. '
                    . 'Where relevant, name specific companies, products, services or websites, and include the URLs of sources you would point to.',
                    $prompt->prompt,
                    ['max_tokens' => (int) config('mira_premium.visibility.max_tokens', 700), 'temperature' => (float) config('mira_premium.visibility.temperature', 0.4), 'timeout' => 45, 'attempts' => 2]
                );
                $parsed = $this->parse($answer, $project);
                MiraVisibilityAnswer::create($row + ['answer' => $answer] + $parsed + $this->analyze($answer, $project, $parsed['brand_mentioned']));
            } catch (\Throwable $e) {
                MiraVisibilityAnswer::create($row + ['error' => mb_substr($e->getMessage(), 0, 300)]);
            }
        }

        $answers = $run->answers()->whereNull('error')->get();

        if ($answers->isEmpty()) {
            $run->update(['status' => 'failed', 'error_message' => 'The AI engine did not answer any prompt. This run was not counted.', 'finished_at' => now()]);
            $this->meter->refund($subject, 'visibility_runs', $period);

            return;
        }

        $summary = $this->aggregate($answers->all(), $project);

        $run->update([
            'status'         => 'done',
            'answered_count' => $answers->count(),
            'mentions'       => $summary['brand']['mentions'],
            'citations'      => $summary['brand']['citations'],
            'share_of_voice' => $summary['brand']['share'],
            'summary'        => $summary,
            'finished_at'    => now(),
        ]);
    }

    // ------------------------------------------------------------------ parsing

    /**
     * One answer: which tracked brands it names (in order of first mention),
     * where the project's brand ranks among them, and which URLs / domains it
     * cites (and whether one is the brand's own).
     */
    public function parse(string $answer, MiraProject $project): array
    {
        $found = [];
        foreach ($this->entities($project) as $entity) {
            $pos = $this->firstMention($answer, $entity);
            if ($pos !== null) {
                $found[] = ['name' => $entity['name'], 'pos' => $pos, 'is_brand' => $entity['is_brand']];
            }
        }
        usort($found, fn ($a, $b) => $a['pos'] <=> $b['pos']);

        $brandIndex = array_search(true, array_column($found, 'is_brand'), true);
        [$urls, $domains] = $this->citations($answer);

        // A tracked brand's own domain written out in the answer is a citation
        // too, whatever its TLD (the bare-domain pattern only knows common ones).
        foreach ($this->entities($project) as $entity) {
            $d = $entity['domain'];
            if ($d !== '' && !in_array($d, $domains, true)
                && preg_match('/(?<![\p{L}\p{N}.-])(?:www\.)?' . preg_quote($d, '/') . '(?![\p{L}\p{N}-])/iu', $answer)) {
                $domains[] = $d;
            }
        }

        return [
            'brand_mentioned'  => $brandIndex !== false,
            'brand_position'   => $brandIndex !== false ? $brandIndex + 1 : null,
            'brands_mentioned' => array_column($found, 'name'),
            'cited_urls'       => $urls,
            'cited_domains'    => $domains,
            'brand_cited'      => (bool) array_filter($domains, fn ($d) => $this->sameSite($d, $project->domain)),
            // Where the brand's own page sits among the cited sources (1 = first link).
            'cited_position'   => $this->citedPosition($urls, $project->domain),
        ];
    }

    /** @return array{0: string[], 1: string[]} cited URLs and their domains (plus bare domains named in text) */
    public function citations(string $text): array
    {
        preg_match_all('#https?://[^\s<>()\[\]"\'`]+#i', $text, $m);
        $urls = array_values(array_unique(array_map(fn ($u) => rtrim($u, '.,;:!?*_'), $m[0])));

        $domains = [];
        foreach ($urls as $u) {
            $host = strtolower((string) parse_url($u, PHP_URL_HOST));
            if ($host !== '') {
                $domains[] = preg_replace('/^www\./', '', $host);
            }
        }

        $stripped = preg_replace('#https?://\S+#i', ' ', $text);
        preg_match_all('/\b((?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+(?:com|org|net|io|ai|co|app|dev|edu|gov|uk|in|au|ca|de|fr|es|it|nl|se|us|info|biz|me))\b/i', $stripped, $bare);
        foreach ($bare[1] as $d) {
            $domains[] = preg_replace('/^www\./', '', strtolower($d));
        }

        $ignore  = (array) config('mira_premium.visibility.ignore_domains', []);
        $domains = array_values(array_unique(array_filter($domains, fn ($d) => !in_array($d, $ignore, true))));

        return [$urls, $domains];
    }

    // -------------------------------------------------------------- aggregation

    /** @param MiraVisibilityAnswer[] $answers */
    public function aggregate(array $answers, MiraProject $project): array
    {
        $entities = $this->entities($project);
        $total    = count($answers);
        $counts   = array_fill_keys(array_column($entities, 'name'), 0);
        $domains  = $topics = [];
        $positions = [];
        $citations = 0;

        foreach ($answers as $a) {
            foreach ((array) $a->brands_mentioned as $name) {
                if (isset($counts[$name])) {
                    $counts[$name]++;
                }
            }
            if ($a->brand_position) {
                $positions[] = $a->brand_position;
            }
            if ($a->brand_cited) {
                $citations++;
            }
            foreach ((array) $a->cited_domains as $d) {
                $domains[$d] = ($domains[$d] ?? 0) + 1;
            }

            $t = $a->topic ?: 'General';
            $topics[$t] ??= ['topic' => $t, 'prompts' => 0, 'mentions' => 0];
            $topics[$t]['prompts']++;
            if ($a->brand_mentioned) {
                $topics[$t]['mentions']++;
            }
        }

        $allMentions = array_sum($counts);
        $brandName   = $entities[0]['name'];

        $entityRows = array_map(fn ($e) => [
            'name'     => $e['name'],
            'domain'   => $e['domain'],
            'is_brand' => $e['is_brand'],
            'mentions' => $counts[$e['name']],
            'rate'     => $total ? round($counts[$e['name']] / $total * 100, 1) : 0,
            'share'    => $allMentions ? round($counts[$e['name']] / $allMentions * 100, 1) : null,
        ], $entities);

        arsort($domains);
        $competitorDomains = array_filter(array_column($entities, 'domain'));
        $topDomains = [];
        foreach (array_slice($domains, 0, 10, true) as $d => $n) {
            $kind = $this->sameSite($d, $project->domain) ? 'you'
                : (array_filter($competitorDomains, fn ($cd) => $cd !== $project->domain && $this->sameSite($d, $cd)) ? 'competitor' : 'other');
            $topDomains[] = ['domain' => $d, 'count' => $n, 'kind' => $kind];
        }

        $topicRows = array_values(array_map(fn ($t) => $t + ['rate' => round($t['mentions'] / max(1, $t['prompts']) * 100, 1)], $topics));
        usort($topicRows, fn ($a, $b) => $b['rate'] <=> $a['rate'] ?: $b['prompts'] <=> $a['prompts']);

        return [
            'brand' => [
                'name'         => $brandName,
                'mentions'     => $counts[$brandName],
                'citations'    => $citations,
                'rate'         => $total ? round($counts[$brandName] / $total * 100, 1) : 0,
                'share'        => $allMentions ? round($counts[$brandName] / $allMentions * 100, 2) : null,
                'avg_position' => $positions ? round(array_sum($positions) / count($positions), 1) : null,
            ],
            'answered'    => $total,
            'entities'    => $entityRows,
            'top_domains' => $topDomains,
            'topics'      => $topicRows,
            'modeled'     => true,
        ];
    }

    /** Latest finished run, its answers, the timeline, and any run in progress. */
    public function report(MiraProject $project): array
    {
        $runs   = $project->visibilityRuns()->latest('id')->limit(12)->get();
        $done   = $runs->where('status', 'done')->values();
        $latest = $done->first();
        $prev   = $done->get(1);
        $active = $runs->first(fn ($r) => in_array($r->status, ['queued', 'running'], true));

        return [
            'model'    => (string) config('mira_premium.visibility.model'),
            'active'   => $active ? $this->runSummary($active) : null,
            'latest'   => $latest ? $this->runSummary($latest) + [
                'summary'       => $latest->summary,
                'new_mentions'  => $prev ? $latest->mentions - $prev->mentions : null,
                'share_change'  => $prev && $prev->share_of_voice !== null && $latest->share_of_voice !== null
                    ? round($latest->share_of_voice - $prev->share_of_voice, 1) : null,
                'answers'       => $latest->answers()->orderBy('id')->get()->map(fn ($a) => [
                    'prompt'           => $a->prompt,
                    'topic'            => $a->topic,
                    'brand_mentioned'  => $a->brand_mentioned,
                    'brand_position'   => $a->brand_position,
                    'brand_cited'      => $a->brand_cited,
                    'brands_mentioned' => $a->brands_mentioned ?? [],
                    'cited_domains'    => array_slice($a->cited_domains ?? [], 0, 6),
                    'answer'           => $a->answer,
                    'error'            => $a->error ? 'No answer this run.' : null,
                ])->all(),
            ] : null,
            'timeline' => $done->reverse()->values()->map(fn ($r) => [
                'date'     => $r->finished_at?->toIso8601String(),
                'mentions' => $r->mentions,
                'answered' => $r->answered_count,
                'share'    => $r->share_of_voice,
            ])->all(),
        ];
    }

    public function runSummary(MiraVisibilityRun $run): array
    {
        return [
            'id'             => $run->id,
            'uuid'           => $run->uuid,
            'status'         => $run->status,
            'model'          => $run->model,
            'prompts_count'  => $run->prompts_count,
            'answered_count' => $run->answered_count,
            'progress'       => $run->status === 'running' ? $run->answers()->count() : null,
            'mentions'       => $run->mentions,
            'citations'      => $run->citations,
            'share_of_voice' => $run->share_of_voice,
            'error'          => $run->error_message,
            'created_at'     => $run->created_at?->toIso8601String(),
            'finished_at'    => $run->finished_at?->toIso8601String(),
        ];
    }

    /**
     * One small classification call per answer: how the brand is described
     * (only when it is named) and every brand / product the answer names.
     * Never fails the run — on any error the fields stay empty.
     */
    public function analyze(string $answer, MiraProject $project, bool $mentioned): array
    {
        if (!config('mira_premium.visibility.analyze_answers') || !$this->llm->isConfigured()) {
            return [];
        }

        try {
            $raw = $this->llm->chat(
                'You label AI answers. Reply with JSON only: {"sentiment":"positive|neutral|negative|null","brands":["..."]}.',
                "Brand: {$project->brandTerms()[0]} ({$project->domain}).\n"
                . "sentiment: how the answer describes this brand — null if it isn't named.\n"
                . "brands: every company, product or website brand the answer names, as written, max 15.\n\nANSWER:\n" . mb_substr($answer, 0, 6000),
                ['max_tokens' => 250, 'temperature' => 0, 'timeout' => 30, 'attempts' => 2]
            );
            $json = json_decode(trim(preg_replace('/^```(json)?|```$/m', '', $raw)), true);
        } catch (\Throwable) {
            return [];
        }

        $sentiment = in_array($json['sentiment'] ?? null, ['positive', 'neutral', 'negative'], true) && $mentioned ? $json['sentiment'] : null;
        $brands    = array_values(array_unique(array_filter(array_map(
            fn ($b) => is_string($b) ? mb_substr(trim($b), 0, 60) : null, array_slice((array) ($json['brands'] ?? []), 0, 15)
        ))));

        return ['sentiment' => $sentiment, 'brands_found' => $brands];
    }

    // ------------------------------------------------------------------ helpers

    private function citedPosition(array $urls, string $domain): ?int
    {
        foreach (array_values($urls) as $i => $u) {
            $host = preg_replace('/^www\./', '', strtolower((string) parse_url($u, PHP_URL_HOST)));
            if ($this->sameSite($host, $domain)) {
                return $i + 1;
            }
        }

        return null;
    }

    /** The brand first, then competitors: each with the terms that count as a mention. */
    private function entities(MiraProject $project): array
    {
        $list = [[
            'name'     => $project->brandTerms()[0],
            'terms'    => $project->brandTerms(),
            'domain'   => $project->domain,
            'is_brand' => true,
        ]];

        foreach ($project->competitorList() as $c) {
            $domain = preg_replace('/^www\./', '', strtolower(trim($c['domain'] ?? '')));
            $list[] = ['name' => trim($c['name']), 'terms' => [trim($c['name'])], 'domain' => $domain, 'is_brand' => false];
        }

        return $list;
    }

    private function firstMention(string $text, array $entity): ?int
    {
        $best = null;
        $needles = $entity['terms'];
        if ($entity['domain'] !== '') {
            $needles[] = $entity['domain'];
        }

        foreach ($needles as $term) {
            if (mb_strlen($term) < 2) {
                continue;
            }
            if (preg_match('/(?<![\p{L}\p{N}])' . preg_quote($term, '/') . '(?![\p{L}\p{N}])/iu', $text, $m, PREG_OFFSET_CAPTURE)) {
                $best = $best === null ? $m[0][1] : min($best, $m[0][1]);
            }
        }

        return $best;
    }

    private function sameSite(string $domain, string $site): bool
    {
        return $site !== '' && ($domain === $site || str_ends_with($domain, '.' . $site));
    }

    private function topic(?string $topic): ?string
    {
        $allowed = (array) config('mira_premium.visibility.topics');
        foreach ($allowed as $t) {
            if (strcasecmp($t, (string) $topic) === 0) {
                return $t;
            }
        }

        return null;
    }

    private function assertRoom(Entitlement $who, MiraProject $project, int $adding): void
    {
        [$limit] = $who->limit('prompts');
        if ($project->prompts()->count() + $adding > $limit) {
            throw new MiraApiException('LimitReached', "Your plan tracks up to {$limit} prompts per project.", [
                'upgrade_url' => config('mira_premium.upgrade_url'),
            ]);
        }
    }
}
