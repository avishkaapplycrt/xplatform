<?php

namespace App\Services\Llm;

use App\Models\AskMiraPremiumPrompt;
use App\Services\WebsiteAnalyzerException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * The assistant behind the Premium Mira page (/mira-premium). The visitor
 * pastes their website URL first; this service reads that site once and
 * then answers questions about it through OpenAI, from an Answer Engine
 * Optimization (AEO) point of view — how likely the site is to be picked as
 * the direct answer in Google featured snippets, "People also ask", voice
 * assistants and AI answer boxes.
 *
 * Separate from MarketingChatBotService (the free landing-page Mira), which
 * answers questions about X Platforms itself and runs on Anthropic.
 *
 * Reading a site = the page itself plus robots.txt from the same origin.
 * Every fetch goes through safeGet(), which refuses private,
 * loopback and reserved addresses (including on redirects) so a pasted URL
 * can't be used to reach the server's own network.
 */
class PremiumMiraService
{
    /** Turns kept in context. One exchange is two turns. */
    private const MAX_HISTORY_TURNS = 16;

    /** Page text sent to the model, in characters — roughly 2.5k tokens. */
    private const MAX_PAGE_TEXT = 10000;

    private const USER_AGENT = 'Mozilla/5.0 (compatible; XPlatformsMira/1.0)';

    /**
     * Crawlers behind the main answer engines, as named in robots.txt. A
     * blocked crawler means that engine can't use the site as an answer.
     */
    private const ANSWER_CRAWLERS = [
        'Googlebot' => 'Google featured snippets, People also ask, Google Assistant',
        'Bingbot'   => 'Bing answers, Copilot, Alexa',
        'Applebot'  => 'Siri and Spotlight',
    ];

    /** AI chatbot crawlers — only sent to the model, for GEO questions. */
    private const AI_CRAWLERS = [
        'GPTBot'          => 'OpenAI training',
        'OAI-SearchBot'   => 'ChatGPT search',
        'PerplexityBot'   => 'Perplexity',
        'ClaudeBot'       => 'Anthropic Claude',
        'Google-Extended' => 'Google Gemini',
    ];

    /**
     * schema.org types that say who the business is (GEO "entity" signal):
     * Organization, LocalBusiness and their common subtypes such as
     * JewelryStore, Dentist, Restaurant, Corporation.
     */
    private const ENTITY_SCHEMA_PATTERN = '/Organization|LocalBusiness|Corporation|Store$|Restaurant|Dentist|Physician|Attorney|Hotel|Person/i';

    /** Below this many words of HTML text, AI crawlers (which mostly don't run JavaScript) see little. */
    private const THIN_TEXT_WORDS = 150;

    /** Most clickable suggestions shown under the welcome reply. */
    private const MAX_SUGGESTIONS = 4;

    /** Follow-up questions generated with every answer, and the line that separates them from it. */
    private const MAX_FOLLOW_UPS   = 3;
    private const FOLLOW_UP_MARKER = '###FOLLOW-UPS###';

    /** Word range Google typically shows in a paragraph featured snippet. */
    private const SNIPPET_MIN_WORDS = 20;
    private const SNIPPET_MAX_WORDS = 60;

    /** schema.org types that mark content up as questions and answers or steps. */
    private const ANSWER_SCHEMA = ['FAQPage', 'QAPage', 'HowTo', 'Question', 'Answer', 'Speakable', 'SpeakableSpecification'];

    public function __construct(private ?OpenAiClient $client = null)
    {
        $this->client ??= new OpenAiClient();
    }

    public function isConfigured(): bool
    {
        return $this->client->isConfigured();
    }

    /**
     * Turns a message into a website URL if it is one: either contains an
     * explicit http(s):// URL, or is a single bare domain ("example.com").
     */
    public function extractUrl(string $message): ?string
    {
        if (preg_match('#https?://\S+#i', $message, $m)) {
            return rtrim($m[0], '.,;:!?)');
        }

        $trimmed = trim($message);
        if (preg_match('#^(www\.)?[a-z0-9-]+(\.[a-z0-9-]+)+(/\S*)?$#i', $trimmed)) {
            return 'https://' . $trimmed;
        }

        return null;
    }

    /**
     * Reads a website and returns what the chat needs to answer questions
     * about it: a short summary for the UI and a text snapshot for the model.
     *
     * @return array{url: string, title: string, facts: array, context: string}
     *
     * @throws WebsiteAnalyzerException the site can't be reached or isn't an HTML page
     */
    public function loadWebsite(string $url): array
    {
        $response = $this->safeGet($url);

        if ($response === null || !$response->successful()) {
            throw new WebsiteAnalyzerException("I couldn't open {$url}. Check the address is correct and the site is publicly accessible, then paste it again.");
        }

        if (!str_contains(strtolower($response->header('Content-Type')), 'html')) {
            throw new WebsiteAnalyzerException("{$url} didn't return a web page. Paste the address of your site's home page (or any HTML page).");
        }

        $xpath  = $this->parseHtml($response->body());
        $origin = $this->originOf($url);

        $title       = $this->clean($xpath->query('//title')->item(0)?->textContent ?? '');
        $description = $this->clean($this->meta($xpath, "//meta[@name='description']") ?? '');
        $metaRobots  = strtolower($this->meta($xpath, "//meta[@name='robots']") ?? '');
        $lang        = $xpath->query('//html/@lang')->item(0)?->nodeValue ?? '';
        $canonical   = $xpath->query("//link[@rel='canonical']/@href")->item(0)?->nodeValue ?? '';
        $ogTitle     = $this->meta($xpath, "//meta[@property='og:title']");

        $headings = [];
        foreach ($xpath->query('//h1|//h2|//h3') as $h) {
            $text = $this->clean($h->textContent);
            if ($text !== '' && count($headings) < 40) {
                $headings[] = strtoupper($h->nodeName) . ': ' . $text;
            }
        }
        // Question headings, and how many are directly followed by a
        // paragraph of featured-snippet length (the classic AEO pattern).
        $questionHeadings = 0;
        $snippetReady     = 0;
        foreach ($xpath->query('//h1|//h2|//h3|//h4') as $h) {
            if (!str_ends_with($this->clean($h->textContent), '?')) {
                continue;
            }
            $questionHeadings++;
            $answer = $this->answerAfter($h);
            if ($answer !== null) {
                $words = str_word_count($this->clean($answer->textContent));
                if ($words >= self::SNIPPET_MIN_WORDS && $words <= self::SNIPPET_MAX_WORDS) {
                    $snippetReady++;
                }
            }
        }

        $lists  = $xpath->query('//ul[li]|//ol[li]')->length;
        $tables = $xpath->query('//table')->length;

        $h1Count      = $xpath->query('//h1')->length;
        $schemaTypes  = $this->schemaTypes($xpath);
        $answerSchema = array_values(array_intersect($schemaTypes, self::ANSWER_SCHEMA));

        // Visible text: drop non-content elements, then collapse whitespace.
        foreach (iterator_to_array($xpath->query('//script|//style|//noscript|//svg|//template')) as $node) {
            $node->parentNode?->removeChild($node);
        }
        $bodyText  = $this->clean($xpath->query('//body')->item(0)?->textContent ?? '');
        $wordCount = str_word_count($bodyText);

        $robots  = $this->safeGet($origin . '/robots.txt');
        $robotsTxt = ($robots && $robots->successful() && !str_contains(strtolower($robots->header('Content-Type')), 'html'))
            ? $robots->body() : null;
        $crawlers = $this->crawlerAccess($robotsTxt);

        // GEO: an llms.txt summarising the site for AI models (a plain-text file, not an HTML 404 page).
        $llms    = $this->safeGet($origin . '/llms.txt');
        $hasLlms = $llms !== null && $llms->successful()
            && !str_contains(strtolower($llms->header('Content-Type')), 'html') && trim($llms->body()) !== '';

        $entitySchema = array_values(array_filter($schemaTypes, fn ($t) => preg_match(self::ENTITY_SCHEMA_PATTERN, $t)));

        $facts = [
            'title'             => $title,
            'h1_count'          => $h1Count,
            'llms_txt'          => $hasLlms,
            'entity_schema'     => $entitySchema,
            'meta_description'  => $description,
            'language'          => $lang,
            'canonical'         => $canonical,
            'open_graph'        => $ogTitle !== null,
            'meta_robots'       => $metaRobots,
            'word_count'        => $wordCount,
            'question_headings' => $questionHeadings,
            'snippet_ready'     => $snippetReady,
            'lists'             => $lists,
            'tables'            => $tables,
            'schema_types'      => $schemaTypes,
            'answer_schema'     => $answerSchema,
            'robots_txt'        => $robotsTxt !== null,
            'crawlers'          => $crawlers,
            'https'             => str_starts_with(strtolower($url), 'https://'),
        ];

        return [
            'url'     => $url,
            'title'   => $title !== '' ? $title : parse_url($url, PHP_URL_HOST),
            'facts'   => $facts,
            'context' => $this->buildContext($url, $facts, $headings, mb_substr($bodyText, 0, self::MAX_PAGE_TEXT)),
        ];
    }

    /** The first reply after a site is loaded: what was read, and the headline SEO, AEO and GEO signals. */
    public function welcomeFor(array $site): string
    {
        $f = $site['facts'];

        $blockedFor = fn (array $bots) => array_keys(array_filter(
            array_intersect_key($f['crawlers'], $bots),
            fn ($s) => $s === 'blocked'
        ));
        $searchBlocked = $blockedFor(self::ANSWER_CRAWLERS);
        $aiBlocked     = $blockedFor(self::AI_CRAWLERS);

        $titleLength = mb_strlen($f['title']);
        $descLength  = mb_strlen($f['meta_description']);

        $lines = [
            "Got it. I've read {$site['title']} ({$site['url']}).",
            '',
            'Quick SEO snapshot:',
            '- Title: ' . ($f['title'] === '' ? 'missing' : "{$titleLength} characters" . ($titleLength < 30 || $titleLength > 60 ? ' (aim for 30–60)' : '')),
            '- Meta description: ' . ($f['meta_description'] === '' ? 'missing' : "{$descLength} characters" . ($descLength < 120 || $descLength > 160 ? ' (aim for 120–160)' : '')),
            '- H1 heading: ' . match (true) {
                $f['h1_count'] === 1 => '1 found',
                $f['h1_count'] === 0 => 'none found',
                default              => "{$f['h1_count']} found (should be 1)",
            },
            '- Canonical tag: ' . ($f['canonical'] === '' ? 'missing' : 'present'),
            '- Indexing: ' . (str_contains($f['meta_robots'], 'noindex') ? 'blocked by a noindex tag' : 'allowed'),
            "- Content: {$f['word_count']} words",
            '',
            'Quick AEO snapshot:',
            "- Question headings: {$f['question_headings']} ({$f['snippet_ready']} followed by a snippet-length answer)",
            '- FAQ / HowTo / Q&A schema: ' . ($f['answer_schema'] === [] ? 'none found' : implode(', ', $f['answer_schema'])),
            "- Lists and tables: {$f['lists']} lists, {$f['tables']} tables",
            '- Search crawlers: ' . ($searchBlocked === [] ? 'Google, Bing and Apple can all crawl the site' : 'blocked for ' . implode(', ', $searchBlocked)),
            '',
            'Quick GEO snapshot:',
            '- AI crawlers: ' . ($aiBlocked === [] ? 'ChatGPT, Perplexity, Claude and Gemini can all read the site' : 'blocked for ' . implode(', ', $aiBlocked)),
            '- llms.txt: ' . ($f['llms_txt'] ? 'found' : 'not found'),
            '- Business identity schema (Organization / LocalBusiness): ' . ($f['entity_schema'] === [] ? 'none found' : implode(', ', array_slice($f['entity_schema'], 0, 3))),
            '- Text AI can read: ' . ($f['word_count'] < self::THIN_TEXT_WORDS
                ? "only {$f['word_count']} words (content may load with JavaScript, which most AI crawlers can't run)"
                : "{$f['word_count']} words"),
            '',
            'Pick a question below or ask your own:',
        ];

        return implode("\n", $lines);
    }

    /**
     * Clickable follow-up questions shown under the welcome reply, read from
     * askmirap_predefined_prompts (placement = suggestion). Rows whose
     * trigger matches what the snapshot found missing come first, then rows
     * with no trigger fill the remaining slots — each group in sort_order.
     * Rows with a trigger not listed in TRIGGERS are skipped.
     *
     * @return string[]
     */
    public function suggestionsFor(array $site): array
    {
        $triggers = $this->activeTriggers($site['facts']);

        $rows = AskMiraPremiumPrompt::active()->placement('suggestion')->ordered()->get(['question', 'trigger']);

        $specific = $rows->filter(fn ($r) => $r->trigger !== null && in_array($r->trigger, $triggers, true));
        $general  = $rows->filter(fn ($r) => $r->trigger === null);

        return $specific->concat($general)
            ->pluck('question')
            ->unique()
            ->take(self::MAX_SUGGESTIONS)
            ->values()
            ->all();
    }

    /**
     * Trigger names (askmirap_predefined_prompts.trigger) that apply to this
     * site. To add a new trigger, add a condition here and use its name in
     * the table.
     *
     * @return string[]
     */
    private function activeTriggers(array $f): array
    {
        $conditions = [
            'missing_meta_description' => $f['meta_description'] === '',
            'no_question_headings'     => $f['question_headings'] === 0,
            'no_snippet_answers'       => $f['question_headings'] > 0 && $f['snippet_ready'] === 0,
            'no_answer_schema'         => $f['answer_schema'] === [],
            'no_lists_or_tables'       => $f['lists'] === 0 && $f['tables'] === 0,
            'search_crawler_blocked'   => (bool) array_filter(array_intersect_key($f['crawlers'], self::ANSWER_CRAWLERS), fn ($s) => $s === 'blocked'),
            'ai_crawler_blocked'       => (bool) array_filter(array_intersect_key($f['crawlers'], self::AI_CRAWLERS), fn ($s) => $s === 'blocked'),
        ];

        return array_keys(array_filter($conditions));
    }

    /**
     * Answers a question about the loaded site, plus follow-up questions the
     * user might ask next. Both come from one OpenAI call: the model ends
     * its answer with FOLLOW_UP_MARKER and one question per line, which is
     * split off here so it never shows in the answer or the chat history.
     *
     * @param array $history Prior turns: [['role' => 'user'|'assistant', 'content' => string], ...]
     *
     * @return array{answer: string, suggestions: string[]}
     *
     * @throws OpenAiException
     */
    public function reply(string $question, array $site, array $history = []): array
    {
        $messages   = $this->trim($history);
        $messages[] = ['role' => 'user', 'content' => $question];

        $raw = $this->client->chatMessages($this->systemPrompt($site), $messages, [
            'max_tokens'  => 1000,
            'temperature' => 0.3,
        ]);

        return $this->splitFollowUps($raw);
    }

    /** @return array{answer: string, suggestions: string[]} */
    private function splitFollowUps(string $raw): array
    {
        $pos = strpos($raw, self::FOLLOW_UP_MARKER);

        if ($pos === false) {
            return ['answer' => trim($raw), 'suggestions' => []];
        }

        $suggestions = collect(preg_split('/\R/', substr($raw, $pos + strlen(self::FOLLOW_UP_MARKER))))
            // Strip any numbering or bullets the model adds anyway ("1. ", "- ").
            ->map(fn ($line) => trim(preg_replace('/^\s*(\d+[.)]|[-*•])\s*/u', '', $line)))
            ->filter(fn ($line) => $line !== '' && mb_strlen($line) <= 150)
            ->unique()
            ->take(self::MAX_FOLLOW_UPS)
            ->values()
            ->all();

        return ['answer' => trim(substr($raw, 0, $pos)), 'suggestions' => $suggestions];
    }

    public function trim(array $history): array
    {
        $messages = array_slice($history, -self::MAX_HISTORY_TURNS);

        while ($messages !== [] && ($messages[0]['role'] ?? null) !== 'user') {
            array_shift($messages);
        }

        return array_values($messages);
    }

    private function systemPrompt(array $site): string
    {
        $marker    = self::FOLLOW_UP_MARKER;
        $followUps = self::MAX_FOLLOW_UPS;

        return <<<PROMPT
You are Mira Premium, an Answer Engine Optimization (AEO) consultant from X Platforms. The user owns the website below and asks questions about it.

AEO means shaping a website so answer engines pick it as the direct answer to a question: Google featured snippets, "People also ask" boxes, knowledge panels, voice assistants (Google Assistant, Siri, Alexa) and AI answer boxes such as Google AI Overviews and Bing Copilot. AEO is your main focus.

You also answer SEO and GEO questions on their own terms:
- SEO (Search Engine Optimization): ranking in the normal search results. Title tags, meta descriptions, keyword targeting, heading structure, internal links, content depth, page speed, mobile-friendliness, HTTPS, canonical tags, indexability.
- GEO (Generative Engine Optimization): being mentioned, recommended and cited in AI chatbot answers (ChatGPT, Perplexity, Gemini, Claude). AI crawler access (GPTBot, OAI-SearchBot, PerplexityBot, ClaudeBot, Google-Extended), a clear statement of who the business is and what it offers, quotable facts and statistics, credentials and sources, and brand mentions on sites AI engines trust (reviews, directories, Wikipedia, press, forums).
When a question doesn't name one, answer from the AEO angle and add a short SEO or GEO note only where it genuinely helps.

Ground every answer in the website data below. Quote or reference the site's actual title, headings, text and signals. If the data doesn't cover something (for example other pages, traffic or rankings), say you can only see this page and give the best advice you can, clearly marked as general guidance. Never invent facts, numbers or pages about the site.

AEO factors to draw on where relevant:
- Question targeting: headings phrased as the real questions customers ask (who, what, how, why, how much, best, near me), matching how people type and speak them.
- Answer-first format: a direct 40-60 word answer paragraph immediately under each question heading, then the detail. This is the featured-snippet pattern.
- Snippet formats: numbered lists for steps, bullet lists for options, tables for comparisons and prices; short, self-contained sentences that make sense read aloud.
- FAQ sections on the page covering follow-up questions (People also ask).
- Structured data: FAQPage, HowTo, QAPage, Speakable, plus Organization, LocalBusiness, Product and Review for knowledge panels and rich results.
- Voice search: conversational long-tail phrasing, local details (address, opening hours, service area), fast mobile-friendly pages.
- Clarity and trust: plain definitions of what the business is and offers, accurate facts, author or company credentials, up-to-date information.
- Crawlability: Googlebot, Bingbot and Applebot not blocked in robots.txt; no noindex or nosnippet; key answers present in the HTML, not only loaded by JavaScript.

Style: plain text only, no Markdown (no #, no ** bold, no tables). Start with a direct 1-2 sentence answer, then give specific, prioritised actions as a numbered list ("1. ..."). Refer to the site's real content in your suggestions: rewrite a real heading as a question and write the 40-60 word answer that should sit under it. Keep it under about 250 words unless the user asks for more detail. If the question is unrelated to their website, SEO, AEO or GEO, briefly steer back to what you can help with.

Follow-up questions: after your answer, on a new line write exactly {$marker} and then exactly {$followUps} questions the user is likely to ask next, one per line, with no numbering or bullets. Write them in the user's voice ("How do I...", "Can you write..."), each under 12 words, specific to this website and to what you just answered, and different from questions already asked in this conversation. Mix angles: a deeper next step on this topic, a related SEO, AEO or GEO question, and a request for ready-to-use content (for example "Write the FAQ answers for me").

WEBSITE DATA
{$site['context']}
PROMPT;
    }

    private function buildContext(string $url, array $f, array $headings, string $text): string
    {
        $crawlers = [];
        foreach ($f['crawlers'] as $bot => $status) {
            $crawlers[] = "{$bot} ({$this->crawlerLabel($bot)}): {$status}";
        }

        return implode("\n", [
            "URL: {$url}",
            'Title: ' . ($f['title'] ?: '(missing)'),
            'Meta description: ' . ($f['meta_description'] ?: '(missing)'),
            'Language: ' . ($f['language'] ?: '(not declared)'),
            'Canonical: ' . ($f['canonical'] ?: '(none)'),
            'Meta robots: ' . ($f['meta_robots'] ?: '(none)'),
            'Open Graph tags: ' . ($f['open_graph'] ? 'present' : 'missing'),
            'HTTPS: ' . ($f['https'] ? 'yes' : 'no'),
            'Structured data (schema.org types): ' . ($f['schema_types'] ? implode(', ', $f['schema_types']) : 'none'),
            'Answer-type schema (FAQPage/QAPage/HowTo/Speakable): ' . ($f['answer_schema'] ? implode(', ', $f['answer_schema']) : 'none'),
            'Business identity schema (Organization/LocalBusiness and subtypes): ' . (($f['entity_schema'] ?? []) ? implode(', ', $f['entity_schema']) : 'none'),
            'H1 headings on page: ' . ($f['h1_count'] ?? 'unknown'),
            'llms.txt: ' . (($f['llms_txt'] ?? false) ? 'present' : 'not found'),
            'robots.txt: ' . ($f['robots_txt'] ? 'present' : 'not found'),
            'Answer-engine crawler access per robots.txt: ' . implode('; ', $crawlers),
            "Word count on page: {$f['word_count']}",
            "Question-style headings: {$f['question_headings']}",
            "Question headings followed by a " . self::SNIPPET_MIN_WORDS . '-' . self::SNIPPET_MAX_WORDS . " word answer paragraph: {$f['snippet_ready']}",
            "Lists: {$f['lists']}, tables: {$f['tables']}",
            '',
            'Headings:',
            $headings ? implode("\n", $headings) : '(none)',
            '',
            'Page text (may be truncated):',
            $text ?: '(no visible text; the page may render its content with JavaScript, so answer engines may not see it)',
        ]);
    }

    /**
     * The first paragraph after a heading. Handles the common layouts:
     * <h3>Q?</h3><p>A</p>, <h3>Q?</h3><div><p>A</p></div>, and headings
     * wrapped in their own element (<div><h3>Q?</h3></div><p>A</p>).
     */
    private function answerAfter(\DOMElement $heading): ?\DOMElement
    {
        $node = $heading;

        // Climb out of wrappers that contain nothing after the heading.
        for ($depth = 0; $node->nextElementSibling === null && $depth < 2; $depth++) {
            if (!$node->parentNode instanceof \DOMElement) {
                return null;
            }
            $node = $node->parentNode;
        }

        $next = $node->nextElementSibling;
        if ($next === null) {
            return null;
        }

        if (strtolower($next->nodeName) === 'p') {
            return $next;
        }

        $inner = $next->getElementsByTagName('p')->item(0);

        return $inner instanceof \DOMElement ? $inner : null;
    }

    private function crawlerLabel(string $bot): string
    {
        return (self::ANSWER_CRAWLERS + self::AI_CRAWLERS)[$bot] ?? $bot;
    }

    /**
     * Per answer-engine crawler: "blocked" (Disallow: / applies to it),
     * "restricted" (some paths disallowed) or "allowed". Uses the bot's own
     * group if robots.txt has one, otherwise the "*" group — same precedence
     * crawlers use.
     *
     * @return array<string, string>
     */
    private function crawlerAccess(?string $robotsTxt): array
    {
        $groups  = [];
        $agents  = [];
        $inRules = false;

        foreach (preg_split('/\R/', (string) $robotsTxt) as $line) {
            $line = trim(preg_replace('/#.*/', '', $line));
            if (!str_contains($line, ':')) {
                continue;
            }
            [$field, $value] = array_map('trim', explode(':', $line, 2));
            $field = strtolower($field);

            if ($field === 'user-agent') {
                if ($inRules) {
                    $agents  = [];
                    $inRules = false;
                }
                $agents[] = strtolower($value);
                continue;
            }

            if ($field === 'disallow' || $field === 'allow') {
                $inRules = true;
                foreach ($agents as $agent) {
                    $groups[$agent][] = [$field, $value];
                }
            }
        }

        $result = [];
        foreach (array_keys(self::ANSWER_CRAWLERS + self::AI_CRAWLERS) as $bot) {
            $rules = $groups[strtolower($bot)] ?? $groups['*'] ?? [];

            $disallowed = array_filter($rules, fn ($r) => $r[0] === 'disallow' && $r[1] !== '');
            $result[$bot] = match (true) {
                (bool) array_filter($disallowed, fn ($r) => $r[1] === '/') => 'blocked',
                $disallowed !== []                                       => 'restricted',
                default                                                  => 'allowed',
            };
        }

        return $result;
    }

    /** @return string[] schema.org @type values found in JSON-LD, de-duplicated. */
    private function schemaTypes(\DOMXPath $xpath): array
    {
        $types = [];

        $collect = function ($node) use (&$collect, &$types) {
            if (!is_array($node)) {
                return;
            }
            if (isset($node['@type'])) {
                foreach ((array) $node['@type'] as $type) {
                    if (is_string($type)) {
                        $types[] = $type;
                    }
                }
            }
            foreach ($node as $child) {
                $collect($child);
            }
        };

        foreach ($xpath->query("//script[@type='application/ld+json']") as $script) {
            $collect(json_decode($script->textContent, true));
        }

        return array_values(array_unique($types));
    }

    /**
     * GET that refuses non-http(s) URLs and hosts resolving to private,
     * loopback, link-local or reserved addresses, and re-checks every
     * redirect hop the same way. Returns null when refused or unreachable.
     */
    private function safeGet(string $url, int $maxRedirects = 3): ?Response
    {
        for ($hop = 0; $hop <= $maxRedirects; $hop++) {
            if (!$this->isPublicUrl($url)) {
                return null;
            }

            try {
                $response = Http::withHeaders(['User-Agent' => self::USER_AGENT])
                    ->withOptions(['allow_redirects' => false])
                    ->timeout(15)
                    ->get($url);
            } catch (\Throwable $e) {
                return null;
            }

            if (!$response->redirect()) {
                return $response;
            }

            $location = $response->header('Location');
            if ($location === '') {
                return $response;
            }
            $url = $this->resolveUrl($url, $location);
        }

        return null;
    }

    private function isPublicUrl(string $url): bool
    {
        $parts = parse_url($url);
        $host  = $parts['host'] ?? null;

        if (!in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true) || !$host) {
            return false;
        }

        $host = trim($host, '[]');
        $ips  = filter_var($host, FILTER_VALIDATE_IP)
            ? [$host]
            : array_merge(
                gethostbynamel($host) ?: [],
                array_column(@dns_get_record($host, DNS_AAAA) ?: [], 'ipv6')
            );

        if ($ips === []) {
            return false;
        }

        foreach ($ips as $ip) {
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return false;
            }
        }

        return true;
    }

    private function resolveUrl(string $base, string $location): string
    {
        if (preg_match('#^https?://#i', $location)) {
            return $location;
        }

        $origin = $this->originOf($base);

        if (str_starts_with($location, '//')) {
            return (parse_url($base, PHP_URL_SCHEME) ?: 'https') . ':' . $location;
        }

        if (str_starts_with($location, '/')) {
            return $origin . $location;
        }

        // Relative to the current path's directory ("/a/b" -> "/a/").
        $path = parse_url($base, PHP_URL_PATH) ?: '/';
        $dir  = substr($path, 0, strrpos($path, '/') + 1);

        return $origin . $dir . $location;
    }

    private function parseHtml(string $html): \DOMXPath
    {
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        // Force UTF-8 so non-English pages aren't mangled as Latin-1.
        $doc->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        return new \DOMXPath($doc);
    }

    private function meta(\DOMXPath $xpath, string $query): ?string
    {
        return $xpath->query($query)->item(0)?->getAttribute('content');
    }

    private function originOf(string $url): string
    {
        $parts = parse_url($url);

        return ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? '') . (isset($parts['port']) ? ':' . $parts['port'] : '');
    }

    private function clean(string $text): string
    {
        return trim(preg_replace('/\s+/u', ' ', $text));
    }
}
