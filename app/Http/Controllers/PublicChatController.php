<?php

namespace App\Http\Controllers;

use App\Models\AskMiraVisitor;
use App\Models\ChatBot;
use App\Models\ChatBotUser;
use App\Models\Industry;
use App\Services\AskMiraReportStore;
use App\Services\Llm\AnthropicException;
use App\Services\Llm\AnthropicRefusedException;
use App\Services\Llm\MarketingChatBotService;
use App\Services\WebsiteAnalyzerException;
use App\Services\WebsiteComparisonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Backend for the public "Chat with AI" widget on the marketing site.
 * Unauthenticated by design — visitors haven't signed up yet — so the route
 * is rate-limited (see routes/web.php) rather than gated behind a login.
 *
 * History lives in the session under its own key, kept separate from the
 * signed-in client chatbot's history so the two personas never mix if the
 * same browser session later logs in.
 *
 * A visitor can still be signed in as a client while browsing the public
 * site (e.g. a second tab). When that's the case, every exchange is also
 * persisted to chat_bots — this is the only place that decision is made;
 * MarketingChatBotService only uses the login state to word its reply.
 *
 * The chat UI itself is embedded directly on the homepage (welcome.blade.php,
 * #ask-mira section) rather than served from a route here — this controller
 * is just the send/reset API the JS on that page calls.
 */
class PublicChatController extends Controller
{
    private const SESSION_KEY = 'public_chat_history';

    /**
     * Competitor comparison "how to fix" advice on the free homepage:
     *   true  (for now) — the fixes are sent, and clicking "Try Premium Mira"
     *         on the blurred section reveals them in place.
     *   false — the fixes are never sent (a blur alone leaves them readable in
     *         the page) and the button links to /mira-premium instead.
     */
    private const COMPARISON_FIXES_UNLOCKABLE = true;

    /** Cookie holding the visitor's askmira_visitors.visitor_id (kept for a year). */
    private const VISITOR_COOKIE         = 'askmira_vid';
    private const VISITOR_COOKIE_MINUTES = 60 * 24 * 365;

    public function send(Request $request, MarketingChatBotService $bot): JsonResponse
    {
        $validated = $request->validate([
            'message'     => 'required|string|max:4000',
            'industry_id' => 'nullable|integer|exists:industries,id',
            'premium'     => 'sometimes|boolean',
        ]);

        $history  = $request->session()->get(self::SESSION_KEY, []);
        $client   = auth('client')->user();
        $industry = isset($validated['industry_id'])
            ? Industry::find($validated['industry_id'])?->name
            : null;

        try {
            $result = $bot->reply($validated['message'], $history, $client, $industry, $this->premium($request, $validated));
        } catch (AnthropicRefusedException $e) {
            Log::info('Public chat request declined', ['message' => $e->getMessage()]);

            return response()->json([
                'error' => 'I declined to answer that one. Try rephrasing it.',
            ], 422);
        } catch (AnthropicException $e) {
            Log::error('Public chat request failed', ['message' => $e->getMessage()]);

            return response()->json([
                'error' => 'The assistant is unavailable right now. Please try again in a moment.',
            ], 502);
        }

        $reply  = $result['reply'];
        $report = $result['report'];

        if ($client !== null) {
            ChatBot::create([
                'question' => $validated['message'],
                'answer'   => $reply,
                'user_id'  => $client->id,
            ]);
        }

        $history[] = ['role' => 'user', 'content' => $validated['message']];
        $history[] = ['role' => 'assistant', 'content' => $reply];

        $request->session()->put(self::SESSION_KEY, $bot->trim($history));

        // The report opens on its own page (route mira.report).
        if ($report !== null) {
            $report['report_url'] = app(AskMiraReportStore::class)->store('analysis', $report, $this->premium($request, $validated));
        }

        return response()->json(['reply' => $reply, 'report' => $report, 'suggestions' => $result['suggestions'] ?? [], 'cta' => $result['cta'] ?? null]);
    }

    /**
     * "Compare with a competitor" in the homepage Analyse tab: checks both
     * sites and returns what to fix to catch up (WebsiteComparisonService).
     * Visitors who aren't logged in give their name and email first and are
     * saved as a lead, like analyzeWithLead().
     */
    public function compare(Request $request, WebsiteComparisonService $comparer): JsonResponse
    {
        $client = auth('client')->user();

        $validated = $request->validate([
            'url'            => 'required|url|max:2048',
            'competitor_url' => 'required|url|max:2048|different:url',
            'premium'        => 'sometimes|boolean',
            'name'           => [$client ? 'nullable' : 'required', 'string', 'max:255'],
            'email'          => [$client ? 'nullable' : 'required', 'email', 'max:255'],
        ], [
            'competitor_url.different' => "Your competitor's website must be different from yours.",
        ]);

        if ($client === null) {
            ChatBotUser::create([
                'name'        => $validated['name'],
                'email'       => $validated['email'],
                'website_url' => $validated['url'],
            ]);
            $this->recordVisitor($request, $validated['name'], $validated['email'], $validated['url']);
        }

        // Two full site checks, each with a PageSpeed Insights call, can take a while.
        set_time_limit(180);

        try {
            $comparison = $comparer->compare($validated['url'], $validated['competitor_url']);
        } catch (WebsiteAnalyzerException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        // The "how to fix" advice sits behind a blurred "Try Premium Mira" card
        // on the homepage. See COMPARISON_FIXES_UNLOCKABLE.
        // Premium Mira (premium flag, set by its page) always gets the full,
        // open report.
        $premium = $this->premium($request, $validated);
        $comparison['locked'] = !$premium;
        if (!$premium && !self::COMPARISON_FIXES_UNLOCKABLE) {
            $comparison['gaps'] = array_map(
                fn ($gap) => array_diff_key($gap, ['recommendation' => true]),
                $comparison['gaps']
            );
            $comparison['checks'] = array_map(
                fn ($check) => array_diff_key($check, ['recommendation' => true]),
                $comparison['checks']
            );
        }

        // The comparison opens on its own page (route mira.report).
        $comparison['report_url'] = app(AskMiraReportStore::class)->store('comparison', $comparison, $premium);

        return response()->json([
            'reply'      => $comparer->replyFor($comparison),
            'comparison' => $comparison,
        ]);
    }

    public function analyzeWithLead(Request $request, MarketingChatBotService $bot): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|max:255',
            'url'         => 'required|url|max:2048',
            'industry_id' => 'nullable|integer|exists:industries,id',
            'premium'     => 'sometimes|boolean',
        ]);

        ChatBotUser::create([
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'website_url' => $validated['url'],
        ]);
        $this->recordVisitor($request, $validated['name'], $validated['email'], $validated['url']);

        $history  = $request->session()->get(self::SESSION_KEY, []);
        $client   = auth('client')->user();
        $industry = isset($validated['industry_id'])
            ? Industry::find($validated['industry_id'])?->name
            : null;

        try {
            $result = $bot->reply($validated['url'], $history, $client, $industry, $this->premium($request, $validated));
        } catch (AnthropicRefusedException $e) {
            Log::info('Public chat request declined', ['message' => $e->getMessage()]);

            return response()->json([
                'error' => 'I declined to answer that one. Try rephrasing it.',
            ], 422);
        } catch (AnthropicException $e) {
            Log::error('Public chat request failed', ['message' => $e->getMessage()]);

            return response()->json([
                'error' => 'The assistant is unavailable right now. Please try again in a moment.',
            ], 502);
        }

        $reply  = $result['reply'];
        $report = $result['report'];

        if ($client !== null) {
            ChatBot::create([
                'question' => $validated['url'],
                'answer'   => $reply,
                'user_id'  => $client->id,
            ]);
        }

        $history[] = ['role' => 'user', 'content' => $validated['url']];
        $history[] = ['role' => 'assistant', 'content' => $reply];

        $request->session()->put(self::SESSION_KEY, $bot->trim($history));

        // The report opens on its own page (route mira.report).
        if ($report !== null) {
            $report['report_url'] = app(AskMiraReportStore::class)->store('analysis', $report, $this->premium($request, $validated));
        }

        return response()->json(['reply' => $reply, 'report' => $report, 'suggestions' => $result['suggestions'] ?? []]);
    }

    /**
     * Saves the visitor from the "Before we analyze your site" form to
     * askmira_visitors. One row per browser: visitor_id lives in a long-lived
     * cookie, and a repeat visitor's row is updated with their latest name,
     * email, website and date. Device comes from the user agent. Never blocks
     * the analysis — failures are logged.
     */
    private function recordVisitor(Request $request, string $name, string $email, string $website): void
    {
        try {
            $visitorId = $request->cookie(self::VISITOR_COOKIE);
            if (!is_string($visitorId) || !preg_match('/^[0-9a-f-]{36}$/', $visitorId)) {
                $visitorId = (string) Str::uuid();
            }

            AskMiraVisitor::updateOrCreate(
                ['visitor_id' => $visitorId],
                [
                    'name'    => $name,
                    'email'   => $email,
                    'website' => mb_substr($website, 0, 2048),
                    'date'    => now(),
                    'device'  => $this->deviceFrom((string) $request->userAgent()),
                ]
            );

            Cookie::queue(self::VISITOR_COOKIE, $visitorId, self::VISITOR_COOKIE_MINUTES);
        } catch (\Throwable $e) {
            Log::warning('Could not save Ask Mira visitor', ['message' => $e->getMessage()]);
        }
    }

    /**
     * The visitor's operating system from their user agent, for
     * askmira_visitors.device: Android / iOS / Windows / Mac / Linux / Other /
     * Unknown. Order matters — Android user agents also say "Linux", and iOS
     * ones say "like Mac OS X". Recent iPads present themselves as a Mac, so
     * they're recorded as Mac (indistinguishable on the server).
     */
    private function deviceFrom(string $userAgent): string
    {
        return match (true) {
            $userAgent === ''                                          => 'Unknown',
            (bool) preg_match('/iphone|ipad|ipod/i', $userAgent)       => 'iOS',
            (bool) preg_match('/android/i', $userAgent)                => 'Android',
            (bool) preg_match('/windows/i', $userAgent)                => 'Windows',
            (bool) preg_match('/macintosh|mac os x/i', $userAgent)     => 'Mac',
            (bool) preg_match('/cros/i', $userAgent)                   => 'Other',   // ChromeOS
            (bool) preg_match('/linux|x11/i', $userAgent)              => 'Linux',
            default                                                    => 'Other',
        };
    }

    /**
     * Premium depth (AI fixes, the unlocked comparison) only when the page asks
     * for it AND the requester's plan includes it — decided on the server by
     * Mira Premium's Entitlement, never by the request's "premium" flag alone.
     */
    private function premium(Request $request, array $validated): bool
    {
        return (bool) ($validated['premium'] ?? false)
            && \App\Services\MiraPremium\Entitlement::forRequest($request)->isPremium();
    }

    public function reset(Request $request): JsonResponse
    {
        $request->session()->forget(self::SESSION_KEY);

        return response()->json(['status' => 'cleared']);
    }
}
