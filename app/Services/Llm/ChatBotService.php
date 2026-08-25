<?php

namespace App\Services\Llm;

use App\Models\Client;
use App\Services\WebsiteAnalyzerException;
use App\Services\WebsiteAnalyzerService;

/**
 * The assistant behind the Chat Bot page.
 *
 * Holds the three things that make a chat feel like *this* product's chat —
 * the persona, the per-client context, and how much history to carry — and
 * leaves transport, retries and error mapping to AnthropicClient.
 *
 * History is passed in and returned rather than read from the session here,
 * so the service stays free of request state and can be exercised directly.
 */
class ChatBotService
{
    /** Turns kept in context. One exchange is two turns, so this is 12 exchanges. */
    private const MAX_HISTORY_TURNS = 24;

    private AnthropicClient $client;
    private WebsiteAnalyzerService $analyzer;

    public function __construct(?AnthropicClient $client = null, ?WebsiteAnalyzerService $analyzer = null)
    {
        $this->client   = $client ?? new AnthropicClient();
        $this->analyzer = $analyzer ?? new WebsiteAnalyzerService();
    }

    public function isConfigured(): bool
    {
        return $this->client->isConfigured();
    }

    /**
     * Answer one user message in the context of the conversation so far.
     *
     * A submitted website URL always triggers the free-tier
     * WebsiteAnalyzerService checks — checked before the LLM/local-fallback split, so the analysis keeps
     * working the same way regardless of whether
     * ANTHROPIC_API_KEY is set. Otherwise falls back to answerLocally() when
     * no API key is configured, so the page is useful the moment it's
     * installed rather than sitting disabled until someone adds a key. The
     * fallback only sees the current message, not the history — it's
     * pattern matching, not a conversation.
     *
     * @param string      $message The new user turn.
     * @param array       $history Prior turns: [['role' => 'user'|'assistant', 'content' => string], ...]
     * @param Client|null $client  The signed-in client, used to ground answers in their setup.
     *
     * @return string The assistant's reply.
     *
     * @throws AnthropicException
     * @throws AnthropicRefusedException
     */
    public function reply(string $message, array $history = [], ?Client $client = null): string
    {
        if ($this->wantsDeeperAnalysis($message)) {
            return $this->upgradeMessage();
        }

        $url = $this->extractWebsiteUrl($message);
        if ($url !== null) {
            return $this->runAnalysis($url);
        }

        if (!$this->isConfigured()) {
            return $this->answerLocally($message, $client);
        }

        $messages   = $this->trim($history);
        $messages[] = ['role' => 'user', 'content' => $message];

        return $this->client->chat($this->systemPrompt($client), $messages);
    }

    /**
     * Pulls a website URL out of a message. Requires an explicit http:// or
     * https:// scheme rather than guessing at bare domains, matching the
     * same simple rule used on the public chat page.
     */
    private function extractWebsiteUrl(string $message): ?string
    {
        return preg_match('#https?://\S+#i', $message, $m) ? rtrim($m[0], '.,;:!?)') : null;
    }

    /**
     * Detects requests for capabilities the free tier doesn't cover — a full
     * multi-page site crawl, or paid-API-based scoring like Lighthouse/page
     * speed. Checked before the single-URL free analysis so these get an
     * honest "that needs the paid plan" answer instead of being silently
     * treated as a normal question or a single-page check.
     */
    private function wantsDeeperAnalysis(string $message): bool
    {
        $text = strtolower($message);

        $patterns = [
            'entire website', 'entire site', 'whole website', 'whole site',
            'every page', 'all pages', 'full website', 'full site',
            'site-wide', 'sitewide', 'crawl my site', 'crawl the site', 'crawl my website',
            'page speed', 'pagespeed', 'lighthouse', 'performance score',
            'core web vitals', 'how fast is my site', 'site speed',
        ];

        foreach ($patterns as $needle) {
            if (str_contains($text, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function upgradeMessage(): string
    {
        return "A full multi-page site crawl and speed/performance scoring (Lighthouse, Core Web Vitals) are part of the paid plan — the free check covers a single page's titles, content structure, images, security, mobile-friendliness, links, discoverability, social tags and rich-results eligibility. Upgrade for full-site and speed analysis: " . route('pricing');
    }

    /** Runs the free-tier WebsiteAnalyzerService checks and turns them into chat text. */
    private function runAnalysis(string $url): string
    {
        try {
            $checks = $this->analyzer->analyzeSeoAndTechnical($url);
        } catch (WebsiteAnalyzerException $e) {
            return $e->getMessage();
        }

        return $this->analyzer->summarizeFull($url, $checks)
            . "\n\nIf this site isn't already connected, add it under Website Connections and I'll start scoring visitors on it too.";
    }

    /**
     * Keyword-matched answers over the platform's own knowledge base and the
     * signed-in client's real numbers. No network call, so it works with no
     * API key configured — this is what runs until one is added.
     *
     * Ordered most-specific first: a message naming one score or segment
     * matches that case before falling through to the generic "what are
     * scores" case.
     */
    private function answerLocally(string $message, ?Client $client): string
    {
        $text = strtolower(trim($message));

        if ($text === '') {
            return "Ask me something about your scores, segments, data sources or the pipeline.";
        }

        if (preg_match('/\b(hi|hello|hey)\b/', $text)) {
            return "Hi! I can walk you through your behavioral scores, segments, the L1–L8 pipeline, and what's connected to your account. What would you like to know?";
        }

        if (str_contains($text, 'website url')) {
            return "Sure — paste your website URL below (starting with http:// or https://) and I'll take a look.";
        }

        if (str_contains($text, 'churn') && str_contains($text, 'month')) {
            return "Month-3 churn usually comes from one of two places in your data: rising frustration_score (errors and failed flows piling up after the honeymoon period) or falling engagement_score once the initial excitement fades. Sort the Decision Centre (L4) by churn_score, then check those two scores' trend over the last 90 days to find the actual cause.";
        }

        if (str_contains($text, 'call') && (str_contains($text, 'first') || str_contains($text, 'today') || str_contains($text, 'priorit'))) {
            if ($client) {
                $top = $client->behavioralProfiles()->orderByDesc('churn_score')->limit(3)->get();
                if ($top->isNotEmpty()) {
                    $names = $top->map(fn ($p) => ($p->name ?: $p->email) . " (churn score {$p->churn_score})")->implode(', ');
                    return "Your highest-risk customers right now: {$names}. Call these first — full ranked list on the Decision Centre (L4).";
                }
            }
            return "Sort the Decision Centre (L4) by churn_score, highest first — that's your call list for today. You don't have any customer profiles yet, so there's nothing to rank until a data source is connected.";
        }

        if (str_contains($text, 'win back') || str_contains($text, 'gone quiet') || (str_contains($text, 'quiet') && str_contains($text, 'customer'))) {
            if ($client) {
                $dormantCount = $client->behavioralProfiles()->where('segment', 'dormant')->count();
                if ($dormantCount > 0) {
                    return "You have {$dormantCount} dormant customer" . ($dormantCount === 1 ? '' : 's') . " right now. Sort by reactivation_potential on the Decision Centre (L4) — the highest scorers are most likely to respond. A personal check-in or discount works best for high-value accounts; a simple \"we miss you\" email is usually enough for the rest.";
                }
            }
            return "Reactivation_potential (0–100, 14-day window) flags who's most likely to respond to a nudge once a customer goes dormant. Check the Decision Centre (L4) — highest scorers first, then reach out with a check-in or a small incentive.";
        }

        if (str_contains($text, 'discount') && (str_contains($text, 'unhappy') || str_contains($text, 'offer'))) {
            return "It depends on their trust_score, not how they're feeling in the moment — the platform's own decision logic (L5) offers a discount below 65 trust_score (about a 1.38× conversion lift) and holds full price above it. Check their trust_score on the Decision Centre first; an unhappy customer with a high trust_score usually needs a fix, not a discount.";
        }

        if (str_contains($text, 'crm') && str_contains($text, 'phone')) {
            return "CRM connects in one click — pick your provider on the CRM Connections page and authorize. Phone/call data comes in through the L1 data collection layer rather than a dedicated self-serve connector today; if you need a specific phone system wired in, flag it to your account team.";
        }

        $scores = [
            'intent'         => "Intent score (0–100, 30-day window) — how much purchase intent a user is showing right now, from things like product views and pricing-page visits.",
            'engagement'     => "Engagement score (0–100, 30-day window) — how broadly a user is interacting with your product across features, not just how often.",
            'buying readiness' => "Buying readiness score (0–100, 30-day window) — how close a user is to actually purchasing.",
            'churn'          => "Churn score (0–100, 90-day window) — the user's risk of cancelling.",
            'loyalty'        => "Loyalty score (0–100, 90-day window) — long-term retention behavior.",
            'trust'          => "Trust score (0–100, 90-day window) — how consistent a user's behavior has been. Below 65 and the platform's decision logic offers a discount (it lifts conversion ~1.38×); above 65, full price.",
            'frustration'    => "Frustration score (0–100, 90-day window) — errors and failed flows the user has hit.",
            'dropoff'        => "Dropoff risk score (0–100, 90-day window) — likelihood of mid-funnel abandonment.",
            'reactivation'   => "Reactivation potential score (0–100, 14-day window) — how likely a dormant user is to come back if nudged.",
        ];
        foreach ($scores as $needle => $answer) {
            if (str_contains($text, $needle)) {
                return $answer . " You'll find live values on the Decision Centre (L4).";
            }
        }
        if (str_contains($text, 'score')) {
            return "There are nine behavioral scores, each 0–100: intent, engagement, buying readiness, churn, loyalty, trust, frustration, dropoff risk and reactivation potential. Ask me about any one of them, or open the Decision Centre (L4) to see them per user.";
        }

        $segments = [
            'champion' => "Champion — your best users: high engagement and loyalty.",
            'loyal'    => "Loyal — consistent, retained users who aren't yet champions.",
            'at risk'  => "At risk — elevated churn signals; usually the target for retention campaigns.",
            'dormant'  => "Dormant — inactive users; the reactivation-potential score flags which ones are worth a nudge.",
        ];
        foreach ($segments as $needle => $answer) {
            if (str_contains($text, $needle)) {
                return $answer . " Segments live on the Decision Centre (L4).";
            }
        }
        if (str_contains($text, 'segment')) {
            return "Users are sorted into five segments: champion, loyal, at risk, dormant and new. Ask me about a specific one, or open the Decision Centre (L4).";
        }

        if (preg_match('/\bl[1-8]\b/', $text) || str_contains($text, 'layer') || str_contains($text, 'pipeline')) {
            return "The pipeline runs eight layers: L1 data collection, L2 signal processing, L3 behavioral modeling, L4 the Decision Centre (per-user scores and segment), L5 decision scenarios (recommended actions), L6 campaign orchestration, L7 attribution, and L8 reporting.";
        }

        if (str_contains($text, 'source') || str_contains($text, 'connect') || str_contains($text, 'website') || str_contains($text, 'crm')) {
            if ($client) {
                $connected = $client->activeDataSources()->count();
                $total     = $client->dataSources()->count();

                return $connected > 0
                    ? "You have {$connected} connected data source" . ($connected === 1 ? '' : 's') . " out of {$total} set up. Manage them under Data Sources."
                    : "You don't have any data sources connected yet — the platform needs at least one (website, email, CRM, social, chat support or payment gateway) to start scoring users. Go to Data Sources to connect one.";
            }

            return "You can connect website, email, CRM, social, chat support and payment-gateway sources under Data Sources.";
        }

        if (str_contains($text, 'plan') || str_contains($text, 'industry') || str_contains($text, 'my account') || str_contains($text, 'my company')) {
            if ($client) {
                return sprintf(
                    "%s is on the %s plan, industry: %s, status: %s.",
                    $client->company_name ?? 'Your account',
                    $client->plan ? ucfirst($client->plan) : 'unspecified',
                    $client->industry->name ?? 'unspecified',
                    $client->status ?? 'unspecified',
                );
            }
        }

        if (str_contains($text, 'help') || str_contains($text, 'what can you') || str_contains($text, 'what do you')) {
            return "I can explain the nine behavioral scores, the five segments, the L1–L8 pipeline, and what's connected to your account. Ask about any of those — for example \"what's the trust score\" or \"how many sources do I have connected\".";
        }

        return "I didn't catch a match for that. I can answer questions about your behavioral scores (intent, engagement, churn, trust, etc.), segments (champion, loyal, at risk, dormant), the L1–L8 pipeline, or your connected data sources. For open-ended questions, add ANTHROPIC_API_KEY to .env to enable the full AI assistant.";
    }

    /**
     * Drop the oldest turns once the history outgrows the window.
     *
     * Trims from the front and then discards a leading assistant turn, because
     * the API requires the first message in a conversation to be from the user.
     */
    public function trim(array $history): array
    {
        $messages = array_slice($history, -self::MAX_HISTORY_TURNS);

        while ($messages !== [] && ($messages[0]['role'] ?? null) !== 'user') {
            array_shift($messages);
        }

        return array_values($messages);
    }

    /**
     * The persona. Two notes on the wording:
     *
     * - The conciseness paragraph is deliberate. This model writes long by
     *   default, and length is tuned by prompting — lowering `effort` changes
     *   how much it thinks, not how much it says.
     * - The scope paragraph keeps it from wandering into unrelated territory
     *   or inventing figures it cannot see, since it has no tools or database
     *   access from this page.
     */
    private function systemPrompt(?Client $client): string
    {
        $company  = $client->company_name ?? 'the client';
        $industry = $client->industry->name ?? 'unspecified';
        $plan     = $client->plan ?? 'unspecified';

        return <<<PROMPT
        You are the assistant inside X Platforms, a behavioral analytics platform. Businesses connect their data sources and the platform scores user behavior to recommend the right action at the right time.

        You are talking to someone at {$company}. Their industry is {$industry} and their plan is {$plan}.

        What the platform does, so you can answer questions about it:
        - It ingests events from websites, email, CRM, social, chat support and payment gateways.
        - It computes nine behavioral scores per user, each from 0 to 100: intent, engagement, buying readiness, churn, loyalty, trust, frustration, dropoff risk and reactivation potential.
        - It sorts users into segments: champion, loyal, at risk, dormant and new.
        - It runs an eight-layer pipeline, L1 through L8: data collection, signal processing, behavioral modeling, the decision centre, decision scenarios, campaign orchestration, attribution and reporting.

        Answer questions about the platform, how to interpret the scores and segments, and what to do with them. Help with connecting data sources and reading the reports.

        A message containing an actual website URL is intercepted before it reaches you and handled by a separate free-tier website analysis tool — you will never see one, so there's nothing to do there.

        Keep replies short and focused. Lead with the answer, then add only the detail that changes what the reader would do next. Skip preamble and restatement of the question.

        You cannot see this client's actual numbers, dashboards or connected accounts from here, and you have no tools to look them up. When a question needs their real data, say so plainly and point them at the page that shows it rather than guessing at a figure. If a question falls outside this platform, say it is outside what you can help with here.
        PROMPT;
    }
}
