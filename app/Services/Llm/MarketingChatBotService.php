<?php

namespace App\Services\Llm;

use App\Models\Client;
use App\Services\WebsiteAnalyzerException;
use App\Services\WebsiteAnalyzerService;

/**
 * The assistant behind the public "Ask Mira" page — separate from
 * ChatBotService, which answers signed-in clients from inside the
 * dashboard. This one serves the public /chat page, which anyone can reach
 * without logging in — but a client can also be signed in while browsing it
 * (e.g. a second tab), so some questions ("which of my customers should I
 * call") need real account data and are gated behind that login state.
 * The free-tier website-analysis flow is NOT gated — anyone can submit a
 * URL and get a report without signing up. Shares AnthropicClient for
 * transport; the persona and fallback content are entirely different, so
 * it's its own file rather than an extra mode bolted onto the client-facing
 * service.
 *
 * Persisting the exchange (question/answer/user) when the visitor is signed
 * in is a controller-level concern, not this service's — see
 * PublicChatController::send(). This class only decides what to say.
 */
class MarketingChatBotService
{
    /** Turns kept in context. One exchange is two turns. */
    private const MAX_HISTORY_TURNS = 20;

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
     * A submitted website URL always triggers the free-tier WebsiteAnalyzerService
     * checks, no login required — checked before the LLM/local-fallback split,
     * so it keeps working the same way regardless of whether ANTHROPIC_API_KEY
     * is set.
     *
     * Returns both a short chat reply and, for a URL analysis, the full
     * structured report — the chat bubble shows only the short text; the
     * caller renders the report as a dashboard rather than a wall of text.
     * Every other branch returns the same shape with report: null, so
     * callers don't need to branch on which kind of answer this was.
     *
     * @param string      $message  The new user turn.
     * @param array       $history  Prior turns: [['role' => 'user'|'assistant', 'content' => string], ...]
     * @param Client|null $client   The signed-in client, or null if the visitor isn't logged in.
     *                              Gates account-specific questions ("which customers should I
     *                              call") behind a signup prompt, and grounds them in real data
     *                              once signed in. Does NOT gate website-URL analysis.
     * @param string|null $industry Industry picked from the sidebar dropdown, if any — only
     *                              affects the LLM system prompt, not the local fallback text.
     *
     * @return array{reply: string, report: array|null}
     *
     * @throws AnthropicException
     * @throws AnthropicRefusedException
     */
    public function reply(string $message, array $history = [], ?Client $client = null, ?string $industry = null): array
    {
        if ($this->wantsDeeperAnalysis($message)) {
            return ['reply' => $this->upgradeMessage(), 'report' => null];
        }

        $url = $this->extractWebsiteUrl($message);
        if ($url !== null) {
            return $this->runAnalysis($url);
        }

        if (!$this->isConfigured()) {
            return ['reply' => $this->answerLocally($message, $client), 'report' => null];
        }

        $messages   = $this->trim($history);
        $messages[] = ['role' => 'user', 'content' => $message];

        return ['reply' => $this->client->chat($this->systemPrompt($client, $industry), $messages), 'report' => null];
    }

    public function trim(array $history): array
    {
        $messages = array_slice($history, -self::MAX_HISTORY_TURNS);

        while ($messages !== [] && ($messages[0]['role'] ?? null) !== 'user') {
            array_shift($messages);
        }

        return array_values($messages);
    }

    /**
     * Pulls a website URL out of a message. Deliberately simple: requires an
     * explicit http:// or https:// scheme rather than guessing at bare
     * domains ("example.com"), which is a much easier way to misfire on
     * ordinary text than to actually help.
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

    /** Runs the free-tier WebsiteAnalyzerService checks and returns a chat reply plus the structured report. */
    /** @return array{reply: string, report: array|null} */
    private function runAnalysis(string $url): array
    {
        try {
            $checks = $this->analyzer->analyzeSeoAndTechnical($url);
        } catch (WebsiteAnalyzerException $e) {
            return ['reply' => $e->getMessage(), 'report' => null];
        }

        $report = $this->analyzer->buildReport($url, $checks);

        return [
            'reply'  => "I checked {$url} — {$report['overall']}/100 (Grade {$report['grade']}). Full breakdown is in the report below.",
            'report' => $report,
        ];
    }

    private function signupPrompt(string $reason): string
    {
        return "{$reason} — sign up free: " . route('client.register') . " Already have an account? Log in at " . route('client.login');
    }

    /**
     * Keyword-matched answers grounded in the actual pricing page copy, so
     * the widget is useful the moment it ships rather than sitting behind an
     * API key. Ordered most-specific first — a submitted website URL always
     * wins, regardless of what else the message says.
     *
     * Questions that need the visitor's own customer data (call list,
     * dormant win-back) are gated: signed out gets a signup prompt, signed
     * in gets a real answer from their behavioral profiles.
     */
    private function answerLocally(string $message, ?Client $client): string
    {
        $text = strtolower(trim($message));

        if ($text === '') {
            return "Ask me something, or click one of the questions below.";
        }

        if (preg_match('/\b(hi|hello|hey)\b/', $text)) {
            return "Hi! I'm Mira. Ask me one of the questions below, or tell me what you'd like to know.";
        }

        if (str_contains($text, 'website url') || str_contains($text, 'enter your website') || str_contains($text, 'analyze') || str_contains($text, 'analyse')
            || str_contains($text, 'site overview') || str_contains($text, 'competitor')) {
            return "Sure — paste your website URL below (starting with http:// or https://) and I'll take a look.";
        }

        if (str_contains($text, 'churn') && str_contains($text, 'month')) {
            return "Month-3 churn usually comes from one of two places in the data: rising frustration_score (errors and failed flows piling up after the honeymoon period) or falling engagement_score once the initial excitement fades. Once your account is connected, sort the Decision Centre by churn_score and check those two scores' 90-day trend to find the actual cause.";
        }

        if (str_contains($text, 'call') && (str_contains($text, 'first') || str_contains($text, 'today') || str_contains($text, 'priorit'))) {
            if ($client === null) {
                return $this->signupPrompt("That's specific to your own customers, so you'll need an account first");
            }
            $top = $client->behavioralProfiles()->orderByDesc('churn_score')->limit(3)->get();
            if ($top->isNotEmpty()) {
                $names = $top->map(fn ($p) => ($p->name ?: $p->email) . " (churn score {$p->churn_score})")->implode(', ');
                return "Your highest-risk customers right now: {$names}. Call these first — full ranked list on the Decision Centre (L4).";
            }
            return "Sort the Decision Centre (L4) by churn_score, highest first — that's your call list for today. You don't have any customer profiles yet, so there's nothing to rank until a data source is connected.";
        }

        if (str_contains($text, 'win back') || str_contains($text, 'gone quiet') || (str_contains($text, 'quiet') && str_contains($text, 'customer'))) {
            if ($client === null) {
                return $this->signupPrompt("That depends on your own dormant customers, so you'll need an account first");
            }
            $dormantCount = $client->behavioralProfiles()->where('segment', 'dormant')->count();
            if ($dormantCount > 0) {
                return "You have {$dormantCount} dormant customer" . ($dormantCount === 1 ? '' : 's') . " right now. Sort by reactivation_potential on the Decision Centre (L4) — the highest scorers are most likely to respond. A personal check-in or discount works best for high-value accounts; a simple \"we miss you\" email is usually enough for the rest.";
            }
            return "Reactivation_potential (0–100, 14-day window) flags who's most likely to respond to a nudge once a customer goes dormant. Check the Decision Centre (L4) once you have live data — highest scorers first.";
        }

        if (str_contains($text, 'discount') && (str_contains($text, 'unhappy') || str_contains($text, 'offer'))) {
            return "It depends on their trust_score, not how they're feeling in the moment — the platform's own decision logic offers a discount below 65 trust_score (about a 1.38× conversion lift) and holds full price above it. An unhappy customer with a high trust_score usually needs a fix, not a discount.";
        }

        if (str_contains($text, 'crm') && str_contains($text, 'phone')) {
            if ($client === null) {
                return "CRM connects in one click once you have an account. Phone/call data comes in through data collection rather than a dedicated connector. "
                    . $this->signupPrompt("Sign up free to connect yours");
            }
            return "CRM connects in one click — pick your provider on the CRM Connections page and authorize. Phone/call data comes in through the L1 data collection layer rather than a dedicated self-serve connector today; if you need a specific phone system wired in, flag it to your account team.";
        }

        if (str_contains($text, 'score')) {
            return "There are nine behavioral scores, each 0–100: intent, engagement, buying readiness, churn, loyalty, trust, frustration, dropoff risk and reactivation potential.";
        }

        if (str_contains($text, 'enterprise')) {
            return "Enterprise is custom-quoted based on your customer volume, data sources, industry models and SLA needs — it includes dedicated infrastructure and a named Customer Success Manager. Use the \"Request Enterprise Quote\" option on the Pricing page, or Book a Demo and we'll scope it with you.";
        }
        if (str_contains($text, 'growth')) {
            return "Growth is \$1,499/mo (\$1,199/mo billed annually) — everything in Starter, plus more data sources, industry models and profile volume. Full breakdown on the Pricing page.";
        }
        if (str_contains($text, 'starter')) {
            return "Starter is \$499/mo (\$399/mo billed annually) — 3 data source integrations, real-time scoring and segmentation, and standard dashboards. Full breakdown on the Pricing page.";
        }
        if (str_contains($text, 'annual') || str_contains($text, 'yearly')) {
            return "Annual billing is charged upfront for 12 months and saves 20% versus monthly — Starter drops to \$399/mo, Growth to \$1,199/mo. You can switch to annual at any renewal.";
        }
        if (str_contains($text, 'price') || str_contains($text, 'pricing') || str_contains($text, 'cost') || str_contains($text, 'plan')) {
            return "Three plans: Starter at \$499/mo, Growth at \$1,499/mo, and custom-quoted Enterprise. Every plan includes a 30-day proof of concept with your own data, no credit card required. See the Pricing page for the full comparison.";
        }

        if (str_contains($text, 'trial') || str_contains($text, 'free') || str_contains($text, 'get started') || str_contains($text, 'sign up') || str_contains($text, 'signup') || str_contains($text, 'demo')) {
            return "Every plan starts with a free 30-day proof of concept on your own data, no credit card required. Create your account here: "
                . route('client.register') . " Already have one? Log in at " . route('client.login');
        }

        if (str_contains($text, 'industr')) {
            return "X Platforms serves 15 industries, each with its own pre-trained behavioral models and playbooks. See the Industries page for the full list and how the models are tuned per vertical.";
        }

        if (str_contains($text, 'source') || str_contains($text, 'connect') || str_contains($text, 'integrat')) {
            return "The platform connects websites, email, CRM, social media and call-centre data, plus payment gateways and chat support. Connectors are one-click with no code required — most customers are fully connected within 2 hours of signing up.";
        }

        if (preg_match('/\b(layer|pipeline|how does|8.?layer|8 ai)\b/', $text)) {
            return "X Platforms runs an 8-layer AI pipeline: it ingests data from every customer touchpoint, unifies identities, maps behavioral patterns, detects anomalies, predicts churn/purchase/CLV, generates strategies, and executes automated actions — learning continuously as it goes.";
        }

        if (str_contains($text, 'what') && (str_contains($text, 'you do') || str_contains($text, 'this') || str_contains($text, 'x platform'))) {
            return "X Platforms connects your business data and tells you which customers are about to buy, churn, or need attention — so you can act before it's too late.";
        }

        return "I can tell you what X Platforms does, how much it costs, or analyze your website — just ask, or click a question below.";
    }

    private function systemPrompt(?Client $client, ?string $industry = null): string
    {
        $signupUrl   = route('client.register');
        $loginUrl    = route('client.login');
        $loginStatus = $client === null
            ? "The visitor is NOT signed in."
            : "The visitor IS currently signed in to X Platforms as {$client->company_name}.";
        $industryLine = $industry !== null
            ? "The visitor selected \"{$industry}\" as their industry — tailor examples to it where relevant."
            : '';

        return <<<PROMPT
        You are Mira, the assistant on the X Platforms marketing website, talking to a visitor who is not yet a customer. X Platforms is a behavioral analytics platform: businesses connect their data sources and the platform scores user behavior to recommend the right action at the right time.

        What to know about the product, so you can answer questions accurately:
        - It ingests events from websites, email, CRM, social, call centres, chat support and payment gateways, with one-click, no-code connectors.
        - It runs an eight-layer pipeline: data ingestion, identity unification, behavioral pattern mapping, anomaly detection, predictions (churn, purchase likelihood, lifetime value), strategy generation, automated action execution, and continuous learning.
        - It serves 15 industries with pre-trained industry models.
        - Plans: Starter at $499/mo ($399/mo billed annually), Growth at $1,499/mo ($1,199/mo billed annually), and custom-quoted Enterprise with dedicated infrastructure and a named Customer Success Manager.
        - Every plan includes a 30-day proof of concept using the customer's own data, no credit card required, and no charge if they're not satisfied.
        - Nine behavioral scores drive it, each 0–100: intent, engagement, buying readiness, churn, loyalty, trust, frustration, dropoff risk, reactivation potential. Trust below 65 triggers a discount offer in the platform's own decision logic (about a 1.38× conversion lift); above 65 stays full price.

        Answer questions about the product, pricing, industries served, and getting started. Keep it simple and easy to follow — avoid jargon. {$industryLine}

        {$loginStatus} Some questions are about the visitor's OWN customers (who to call first, winning back dormant customers, connecting their CRM). You have no access to any specific company's account or data — if the visitor is signed in, explain the general approach (Decision Centre, churn_score, reactivation_potential) without inventing specific customer names or numbers. If they are NOT signed in, tell them plainly they'll need an account first and give the signup URL: {$signupUrl} (login: {$loginUrl}).

        A message containing an actual website URL is intercepted before it reaches you and handled by a separate real analysis tool — you will never see one, so there's nothing to do there.

        Whenever a visitor asks how to get started or sign up, give the same signup URL: {$signupUrl} — and mention they can log in at {$loginUrl} if they already have an account. Never invent or guess a URL.

        If a visitor asks for a site overview or their competitor details without pasting a URL yet, ask them to paste their website URL (starting with http:// or https://) so the real analysis tool can run.

        Keep replies short — two or three sentences unless the question genuinely needs more. Lead with the answer.

        If asked something outside X Platforms, say plainly that it's outside what you can help with here.
        PROMPT;
    }
}
