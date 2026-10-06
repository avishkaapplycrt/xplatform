<?php

namespace App\Services;

use App\Services\Llm\OpenAiClient;
use Illuminate\Support\Facades\Log;

/**
 * Writes site-specific fixes for the failing/warning checks in a
 * WebsiteAnalyzerService report, using OpenAI — e.g. a ready-to-paste meta
 * description built from the page's real content, instead of generic advice.
 *
 * One call per analysis, kept short (20s, no retries) because the visitor is
 * waiting on the report. Never throws: on any problem it returns [] and
 * WebsiteAnalyzerService keeps its fixed RECOMMENDATIONS for those checks.
 */
class SeoRecommendationService
{
    private const TIMEOUT_SECONDS = 20;

    /** Longest recommendation accepted from the model, in characters. */
    private const MAX_LENGTH = 400;

    public function __construct(private ?OpenAiClient $client = null)
    {
        $this->client ??= new OpenAiClient();
    }

    /**
     * @param array|null $page       ['url', 'title', 'description', 'headings', 'text'] of the checked page, if known.
     * @param array      $checks     fail/warn checks, keyed by their index in the report's check list.
     * @param array      $categories category key => display label.
     *
     * @return array<int, string> check index => recommendation; [] when unavailable.
     */
    public function recommend(?array $page, array $checks, array $categories): array
    {
        if ($checks === [] || !$this->client->isConfigured()) {
            return [];
        }

        $list = [];
        foreach ($checks as $i => $c) {
            $list[] = [
                'id'       => $i,
                'section'  => $categories[$c['category']] ?? $c['category'],
                'check'    => $c['name'],
                'status'   => $c['status'],
                'finding'  => $c['detail'],
            ];
        }

        try {
            $raw = $this->client->chat($this->systemPrompt(), $this->userPrompt($page, $list), [
                'max_tokens'  => 1500,
                'temperature' => 0.3,
                'timeout'     => self::TIMEOUT_SECONDS,
                'attempts'    => 1,
            ]);
        } catch (\Throwable $e) {
            Log::warning('SEO recommendations from OpenAI failed; using fixed advice', ['message' => $e->getMessage()]);
            return [];
        }

        return $this->parse($raw, array_keys($checks));
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
You are an SEO consultant. A website's page has been checked automatically; you get the page's content and the list of checks that failed or need work. For each check, write one specific, actionable fix for THIS website.

Rules:
- Use the page's real content: its business, products, place and wording. Where it helps, give ready-to-use text, e.g. the exact new title or meta description, an example alt text, or a heading rewritten.
- Respect the limits in the finding (e.g. a title of 30-60 characters, a meta description of 120-160 characters) and make any text you propose meet them.
- One or two sentences per fix, at most about 60 words, plain text, no Markdown.
- Never invent facts about the business that aren't supported by the page content. If the content doesn't say enough, give a clear generic fix instead.

Reply with JSON only, no code fences, in exactly this shape:
{"recommendations": {"<id>": "<fix>", ...}}
using the ids given in the checks list.
PROMPT;
    }

    private function userPrompt(?array $page, array $list): string
    {
        $pageBlock = $page === null
            ? '(page content not available)'
            : implode("\n", [
                'URL: ' . $page['url'],
                'Title: ' . ($page['title'] !== '' ? $page['title'] : '(missing)'),
                'Meta description: ' . ($page['description'] !== '' ? $page['description'] : '(missing)'),
                'Headings:',
                $page['headings'] ? implode("\n", $page['headings']) : '(none)',
                'Page text (truncated):',
                $page['text'] !== '' ? $page['text'] : '(no visible text)',
            ]);

        return "PAGE\n{$pageBlock}\n\nCHECKS TO FIX\n" . json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param int[] $validIds
     *
     * @return array<int, string>
     */
    private function parse(string $raw, array $validIds): array
    {
        // Tolerate a stray ```json fence despite the instructions.
        $json = trim(preg_replace('/^```(?:json)?\s*|\s*```$/', '', trim($raw)));
        $data = json_decode($json, true);

        if (!is_array($data['recommendations'] ?? null)) {
            Log::warning('SEO recommendations from OpenAI were not valid JSON; using fixed advice');
            return [];
        }

        $out = [];
        foreach ($data['recommendations'] as $id => $text) {
            $id = (int) $id;
            if (in_array($id, $validIds, true) && is_string($text) && trim($text) !== '') {
                $out[$id] = mb_substr(trim($text), 0, self::MAX_LENGTH);
            }
        }

        return $out;
    }
}
