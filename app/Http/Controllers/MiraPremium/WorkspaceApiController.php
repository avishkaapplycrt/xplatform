<?php

namespace App\Http\Controllers\MiraPremium;

use App\Http\Controllers\Controller;
use App\Models\MiraPremium\MiraEvent;
use App\Models\MiraPremium\MiraMessage;
use App\Models\MiraPremium\MiraProject;
use App\Models\MiraPremium\MiraPrompt;
use App\Models\MiraPremium\MiraScan;
use App\Services\MiraPremium\AiResponsesService;
use App\Services\MiraPremium\Entitlement;
use App\Services\MiraPremium\MiraApiException;
use App\Services\MiraPremium\Period;
use App\Services\MiraPremium\ScanService;
use App\Services\MiraPremium\SeoDataService;
use App\Services\MiraPremium\TrafficService;
use App\Services\MiraPremium\Turnstile;
use App\Services\MiraPremium\UrlGuard;
use App\Services\MiraPremium\UsageMeter;
use App\Services\MiraPremium\VisibilityEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * JSON API behind the Mira Premium workspace (/mira-premium). Every endpoint
 * resolves the requester's Entitlement on the server; errors are the tagged
 * MiraApiException set ({"error": {"code", "message", ...}}), rendered by
 * bootstrap/app.php's exception handler for routes under mira-premium/api.
 *
 *   GET    me                                 subscription, usage, projects
 *   POST   scans                              phase 1: submit → signed handle
 *   POST   scans/report                       phase 2: handle → state / report
 *   GET    scans/{id}                         reopen an owned scan
 *   POST   projects                           create   PATCH projects/{id}  DELETE projects/{id}
 *   GET    projects/{id}/history              scans of the project, with deltas
 *   GET    projects/{id}/messages             saved Ask Mira conversation
 *   GET    projects/{id}/visibility           AI visibility report + prompt set
 *   POST   projects/{id}/prompts              add      POST projects/{id}/prompts/suggest
 *   PATCH  prompts/{id}  DELETE prompts/{id}
 *   POST   projects/{id}/visibility/runs      start a run
 *   GET    projects/{id}/panels/{panel}       report panel: ai | authority | search | traffic (period-compared),
 *                                            performance | positions | countries | pages | competitors |
 *                                            insights | paid | backlinks (Site Explorer) | ai_brand (Brand Radar)
 *                                            for ?period=7d|30d|90d|12m vs the previous window
 *   POST   event / error                      first-party analytics and client error reports
 */
class WorkspaceApiController extends Controller
{
    public function __construct(
        private UsageMeter $meter,
        private ScanService $scans,
        private VisibilityEngine $visibility,
        private UrlGuard $urls,
        private Turnstile $turnstile,
    ) {}

    /**
     * The workspace page. The same payload as me() is inlined as a small JSON
     * island so the first paint needs no extra request.
     */
    public function page(Request $request)
    {
        return view('mira-premium', [
            'boot'           => $this->bootData(Entitlement::forRequest($request)),
            'premiumLive'    => (new \App\Services\Llm\PremiumMiraService())->isConfigured(),
            // "Try asking" questions from askmirap_predefined_prompts, by category (seo / aeo / geo).
            'sidebarPrompts' => \App\Models\AskMiraPremiumPrompt::active()->placement('sidebar')->ordered()
                ->get(['category', 'question'])
                ->groupBy(fn ($p) => $p->category ?: 'other')
                ->map(fn ($group) => $group->pluck('question')),
            // For the shared Ask Mira scripts (lead form, report renderer).
            'miraLive'       => (new \App\Services\Llm\MarketingChatBotService())->isConfigured(),
            'loggedIn'       => auth('client')->check(),
            'chatQuestionsByIndustry' => [],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($this->bootData(Entitlement::forRequest($request)));
    }

    private function bootData(Entitlement $who): array
    {
        return [
            'subscription' => $who->toArray(),
            'usage'        => $this->meter->snapshot($who),
            'caps'         => [
                'projects'    => $who->limit('projects')[0],
                'prompts'     => $who->limit('prompts')[0],
                'competitors' => $who->limit('competitors')[0],
            ],
            'projects'     => $who->client ? $this->projectList($who) : [],
            'recent_scans' => MiraScan::where('subject', $who->subject)->whereNull('project_id')
                ->latest('id')->limit(5)->get()->map(fn ($s) => $this->scans->summarize($s))->all(),
            'turnstile_site_key' => $who->isPremium() ? null : $this->turnstile->siteKey(),
        ];
    }

    // -------------------------------------------------------------------- scans

    public function submitScan(Request $request): JsonResponse
    {
        $who = Entitlement::forRequest($request);
        $in  = $this->input($request, [
            'url'             => 'required|string|max:2048',
            'competitor_url'  => 'nullable|string|max:2048',
            'project_id'      => 'nullable|integer',
            'turnstile_token' => 'nullable|string|max:4096',
            'fresh'           => 'nullable|boolean',   // "Re-run audit": skip the recent-result reuse
        ]);

        if (!$who->isPremium()) {
            $this->turnstile->verify($in['turnstile_token'] ?? null, $request->ip());
        }

        return response()->json($this->scans->submit($who, $in), 202);
    }

    public function scanReport(Request $request): JsonResponse
    {
        $in = $this->input($request, ['handle' => 'required|string|max:1024']);

        return response()->json($this->scans->report(Entitlement::forRequest($request), $in['handle']));
    }

    public function showScan(Request $request, int $id): JsonResponse
    {
        return response()->json($this->scans->owned(Entitlement::forRequest($request), $id));
    }

    // ------------------------------------------------------------ rank tracking

    /** Tracked keywords with today's positions, trend, visibility and share of voice. */
    public function ranks(Request $request, int $id): JsonResponse
    {
        $who = Entitlement::forRequest($request);
        $who->require('tracked_keywords', 'Rank tracking');

        return response()->json(['ranks' => app(\App\Services\MiraPremium\RankTracker::class)->report($who, $this->ownedProject($who, $id))]);
    }

    /** Adds keywords (one per line or comma-separated) and checks them straight away. */
    public function addRankKeywords(Request $request, int $id): JsonResponse
    {
        $who     = Entitlement::forRequest($request);
        $project = $this->ownedProject($who, $id);
        $in      = $this->input($request, ['keywords' => 'required|string|max:5000']);
        $tracker = app(\App\Services\MiraPremium\RankTracker::class);

        $added = $tracker->add($who, $project, $in['keywords']);

        return response()->json(['added' => $added, 'ranks' => $tracker->report($who, $project)], 201);
    }

    public function deleteRankKeyword(Request $request, int $id, int $keywordId): JsonResponse
    {
        $who     = Entitlement::forRequest($request);
        $project = $this->ownedProject($who, $id);
        $tracker = app(\App\Services\MiraPremium\RankTracker::class);
        $tracker->remove($project, $keywordId);

        return response()->json(['ranks' => $tracker->report($who, $project)]);
    }

    /** "Check now": re-checks keywords not checked in the last hour. */
    public function checkRanks(Request $request, int $id): JsonResponse
    {
        $who = Entitlement::forRequest($request);
        $who->require('tracked_keywords', 'Rank tracking');
        $project = $this->ownedProject($who, $id);
        $tracker = app(\App\Services\MiraPremium\RankTracker::class);
        $checked = $tracker->check($project);

        return response()->json(['checked' => $checked, 'ranks' => $tracker->report($who, $project)]);
    }

    /** A read-only link to a finished scan, for "Share report" and "Download PDF". */
    public function shareScan(Request $request, int $id): JsonResponse
    {
        return response()->json($this->scans->shareUrl(Entitlement::forRequest($request), $id));
    }

    /** The shared report page (signed link from shareScan). ?print=1 opens the print dialog for "Download PDF". */
    public function sharedReport(Request $request, string $uuid)
    {
        $report = $this->scans->sharedReport($uuid);
        abort_if($report === null, 404);

        return view('ask-mira-report', ['report' => $report, 'premium' => true, 'autoPrint' => $request->boolean('print')]);
    }

    // ----------------------------------------------------------------- projects

    public function createProject(Request $request): JsonResponse
    {
        $who = Entitlement::forRequest($request);
        $who->require('projects', 'Saved projects');
        if ($who->client === null) {
            throw new MiraApiException('NotEntitled', 'Sign in to save projects.', ['upgrade_url' => config('mira_premium.upgrade_url')]);
        }

        $in     = $this->input($request, ['url' => 'required|string|max:2048', 'name' => 'nullable|string|max:120']);
        $url    = $this->urls->normalize($in['url']);
        $domain = $this->urls->domainOf($url);

        [$limit] = $who->limit('projects');
        $existing = MiraProject::where('client_id', $who->client->id)->where('domain', $domain)->first();
        if ($existing !== null) {
            return response()->json(['project' => $this->projectData($existing)]);
        }
        if (MiraProject::where('client_id', $who->client->id)->count() >= $limit) {
            throw new MiraApiException('LimitReached', "Your plan includes {$limit} project" . ($limit === 1 ? '' : 's') . '. Remove one or upgrade.', [
                'upgrade_url' => config('mira_premium.upgrade_url'),
            ]);
        }

        $name    = trim((string) ($in['name'] ?? '')) ?: ucfirst(explode('.', $domain)[0]);
        $project = MiraProject::create([
            'client_id'   => $who->client->id,
            'name'        => $name,
            'url'         => $url,
            'domain'      => $domain,
            'brand_terms' => [$name],
            'competitors' => [],
        ]);

        return response()->json(['project' => $this->projectData($project)], 201);
    }

    public function updateProject(Request $request, int $id): JsonResponse
    {
        $who     = Entitlement::forRequest($request);
        $project = $this->ownedProject($who, $id);

        $in = $this->input($request, [
            'name'                 => 'sometimes|string|min:1|max:120',
            'brand_terms'          => 'sometimes|array|max:8',
            'brand_terms.*'        => 'string|min:2|max:60',
            'competitors'          => 'sometimes|array',
            'competitors.*.name'   => 'required_with:competitors|string|min:2|max:80',
            'competitors.*.domain' => 'nullable|string|max:255',
        ]);

        if (array_key_exists('competitors', $in)) {
            [$cap] = $who->limit('competitors');
            if (count($in['competitors']) > $cap) {
                throw new MiraApiException('LimitReached', "Your plan tracks up to {$cap} competitor" . ($cap === 1 ? '' : 's') . ' per project.', [
                    'upgrade_url' => config('mira_premium.upgrade_url'),
                ]);
            }
            $in['competitors'] = array_values(array_map(function ($c) {
                $domain = trim((string) ($c['domain'] ?? ''));
                if ($domain !== '') {
                    $domain = $this->urls->domainOf(preg_match('#^https?://#i', $domain) ? $domain : 'https://' . $domain);
                }

                return ['name' => trim($c['name']), 'domain' => $domain];
            }, $in['competitors']));
        }
        if (array_key_exists('brand_terms', $in)) {
            $in['brand_terms'] = array_values(array_unique(array_filter(array_map('trim', $in['brand_terms']))));
        }

        $project->update($in);

        return response()->json(['project' => $this->projectData($project->fresh())]);
    }

    public function deleteProject(Request $request, int $id): JsonResponse
    {
        $project = $this->ownedProject(Entitlement::forRequest($request), $id);

        // Scans stay in the person's history; the project's own data goes.
        MiraScan::where('project_id', $project->id)->update(['project_id' => null]);
        MiraMessage::where('project_id', $project->id)->delete();
        foreach ($project->visibilityRuns as $run) {
            $run->answers()->delete();
            $run->delete();
        }
        $project->prompts()->delete();
        $project->delete();

        return response()->json(['status' => 'deleted']);
    }

    public function history(Request $request, int $id): JsonResponse
    {
        $project = $this->ownedProject(Entitlement::forRequest($request), $id);

        $scans = MiraScan::where('project_id', $project->id)->latest('id')->limit(40)->get();

        return response()->json([
            'scans'    => $scans->map(fn ($s) => $this->scans->summarize($s))->all(),
            'timeline' => $scans->where('status', 'done')->reverse()->values()->map(fn ($s) => [
                'date'         => $s->finished_at?->toIso8601String(),
                'overall'      => $s->overall,
                'stage_scores' => $s->stage_scores,
                'open_issues'  => $s->issue_counts ? $s->issue_counts['critical'] + $s->issue_counts['important'] + $s->issue_counts['minor'] : null,
            ])->all(),
        ]);
    }

    public function messages(Request $request, int $id): JsonResponse
    {
        $who     = Entitlement::forRequest($request);
        $project = $this->ownedProject($who, $id);

        $messages = MiraMessage::where('client_id', $who->client->id)->where('project_id', $project->id)
            ->orderByDesc('id')->limit(40)->get()->reverse()->values();

        return response()->json(['messages' => $messages->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])->all()]);
    }

    // ---------------------------------------------------------- overview panels

    /**
     * One overview panel for the window ending now vs the window before it.
     * Panels are fetched separately so a slow provider never holds up the rest.
     * ?refresh=1 re-fetches provider data (Authority / Search) before the cache expires.
     */
    public function panel(Request $request, int $id, string $panel): JsonResponse
    {
        $who     = Entitlement::forRequest($request);
        $project = $this->ownedProject($who, $id);
        $period  = Period::fromKey($request->query('period'));
        $refresh = $request->boolean('refresh');

        $data = match ($panel) {
            'ai'        => app(AiResponsesService::class)->responses($project, $period),
            'authority' => app(SeoDataService::class)->authority($project, $period, $refresh),
            'search'    => app(SeoDataService::class)->search($project, $period, $refresh),
            'traffic'   => app(TrafficService::class)->traffic($project, $period),
            'performance', 'positions', 'countries', 'pages', 'competitors', 'insights', 'paid', 'backlinks', 'difficulty'
                        => app(\App\Services\MiraPremium\SiteExplorerService::class)->{$panel}($project, $refresh),
            'ai_brand'  => app(\App\Services\MiraPremium\AiBrandService::class)->report($project),
            default     => throw new MiraApiException('NotFound', 'Unknown panel.'),
        };

        // Sample data mode: tag every provider-built panel so it can't be read as real.
        if ($panel !== 'ai' && $panel !== 'ai_brand' && $panel !== 'traffic' && ($data['connected'] ?? false)
            && app(\App\Services\MiraPremium\DataForSeoClient::class)->isDemo()) {
            $data['demo'] = true;
        }

        return response()->json(['panel' => $panel, 'period' => $period->toArray(), 'data' => $data]);
    }

    // --------------------------------------------------------------- visibility

    public function visibility(Request $request, int $id): JsonResponse
    {
        $who     = Entitlement::forRequest($request);
        $project = $this->ownedProject($who, $id);

        return response()->json([
            'prompts' => $project->prompts()->orderBy('id')->get()->map(fn ($p) => $this->promptData($p))->all(),
            'report'  => $this->visibility->report($project),
            'caps'    => ['prompts' => $who->limit('prompts')[0], 'prompts_per_run' => (int) config('mira_premium.visibility.prompts_per_run')],
        ]);
    }

    public function addPrompt(Request $request, int $id): JsonResponse
    {
        $who     = Entitlement::forRequest($request);
        $project = $this->ownedProject($who, $id);
        $in      = $this->input($request, ['prompt' => 'required|string|max:300', 'topic' => 'nullable|string|max:40']);

        return response()->json(['prompt' => $this->promptData($this->visibility->addPrompt($who, $project, $in['prompt'], $in['topic'] ?? null))], 201);
    }

    public function suggestPrompts(Request $request, int $id): JsonResponse
    {
        $who     = Entitlement::forRequest($request);
        $project = $this->ownedProject($who, $id);

        return response()->json(['added' => array_map(fn ($p) => $this->promptData($p), $this->visibility->suggestPrompts($who, $project))]);
    }

    public function updatePrompt(Request $request, int $id): JsonResponse
    {
        $prompt = $this->ownedPrompt(Entitlement::forRequest($request), $id);
        $in     = $this->input($request, ['is_active' => 'required|boolean']);
        $prompt->update(['is_active' => (bool) $in['is_active']]);

        return response()->json(['prompt' => $this->promptData($prompt)]);
    }

    public function deletePrompt(Request $request, int $id): JsonResponse
    {
        $this->ownedPrompt(Entitlement::forRequest($request), $id)->delete();

        return response()->json(['status' => 'deleted']);
    }

    public function startRun(Request $request, int $id): JsonResponse
    {
        $who     = Entitlement::forRequest($request);
        $project = $this->ownedProject($who, $id);

        return response()->json(['run' => $this->visibility->runSummary($this->visibility->startRun($who, $project))], 202);
    }

    // ---------------------------------------------------------------- telemetry

    /** First-party analytics: cookieless, allow-listed event names, tiny payload (sendBeacon, text/plain). */
    public function event(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $name = is_array($data) ? (string) ($data['n'] ?? '') : '';

        if (in_array($name, (array) config('mira_premium.events'), true)) {
            $who   = Entitlement::forRequest($request);
            $props = is_array($data['p'] ?? null) ? array_slice($data['p'], 0, 12, true) : null;
            MiraEvent::create([
                'name'       => $name,
                'subject'    => $who->subject,
                'tier'       => $who->tier,
                'props'      => $props ? array_map(fn ($v) => is_scalar($v) ? mb_substr((string) $v, 0, 200) : null, $props) : null,
                'url'        => mb_substr((string) ($data['u'] ?? ''), 0, 2048) ?: null,
                'referrer'   => mb_substr((string) ($data['r'] ?? ''), 0, 2048) ?: null,
                'created_at' => now(),
            ]);
        }

        return response()->json(null, 204);
    }

    /** Errors the page's API client caught — logged, then the page re-throws them. */
    public function clientError(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (is_array($data)) {
            Log::warning('Mira Premium client error', [
                'message' => mb_substr((string) ($data['message'] ?? ''), 0, 500),
                'stack'   => mb_substr((string) ($data['stack'] ?? ''), 0, 2000),
                'url'     => mb_substr((string) ($data['url'] ?? ''), 0, 500),
            ]);
        }

        return response()->json(null, 204);
    }

    // ------------------------------------------------------------------ helpers

    private function input(Request $request, array $rules): array
    {
        $validator = validator($request->all(), $rules);
        if ($validator->fails()) {
            $field = array_key_first($validator->errors()->toArray());
            throw new MiraApiException('InvalidInput', $validator->errors()->first(), ['field' => $field]);
        }

        return $validator->validated();
    }

    private function ownedProject(Entitlement $who, int $id): MiraProject
    {
        if ($who->client === null) {
            throw new MiraApiException('NotEntitled', 'Sign in to use saved projects.', ['upgrade_url' => config('mira_premium.upgrade_url')]);
        }

        $project = MiraProject::where('id', $id)->where('client_id', $who->client->id)->first();
        if ($project === null) {
            throw new MiraApiException('NotFound', 'That project was not found.');
        }

        // A lapsed plan keeps read access to nothing premium.
        $who->require('projects', 'Saved projects');

        return $project;
    }

    private function ownedPrompt(Entitlement $who, int $id): MiraPrompt
    {
        $prompt = MiraPrompt::find($id);
        if ($prompt === null) {
            throw new MiraApiException('NotFound', 'That prompt was not found.');
        }
        $this->ownedProject($who, $prompt->project_id);

        return $prompt;
    }

    private function projectList(Entitlement $who): array
    {
        return MiraProject::where('client_id', $who->client->id)->orderBy('name')->get()
            ->map(fn ($p) => $this->projectData($p))->all();
    }

    private function projectData(MiraProject $p): array
    {
        $latest = MiraScan::where('project_id', $p->id)->where('status', 'done')->latest('id')->first();
        $run    = $p->visibilityRuns()->where('status', 'done')->latest('id')->first();

        return [
            'id'              => $p->id,
            'name'            => $p->name,
            'url'             => $p->url,
            'domain'          => $p->domain,
            'brand_terms'     => $p->brandTerms(),
            'competitors'     => $p->competitorList(),
            'last_scanned_at' => $p->last_scanned_at?->toIso8601String(),
            'latest_scan'     => $latest ? $this->scans->summarize($latest) : null,
            'visibility'      => $run ? $this->visibility->runSummary($run) : null,
            'prompts_count'   => $p->prompts()->count(),
        ];
    }

    private function promptData(MiraPrompt $p): array
    {
        return ['id' => $p->id, 'prompt' => $p->prompt, 'topic' => $p->topic, 'source' => $p->source, 'is_active' => $p->is_active];
    }
}
