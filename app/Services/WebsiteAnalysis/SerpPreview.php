<?php

namespace App\Services\WebsiteAnalysis;

/**
 * How a page would look as a Google search result: title link, URL
 * breadcrumb (domain › path › …) and description, truncated at a word
 * boundary like Google does (limits: config website_checks.serp_preview).
 * Without a meta description Google usually shows page text instead, so the
 * preview falls back to the start of the page text and says so.
 */
class SerpPreview
{
    /**
     * @return array{title: string, title_truncated: bool, display_url: string, url: string,
     *               description: string, description_truncated: bool, description_source: 'meta'|'page_text'|'none'}
     */
    public static function build(string $url, string $title, string $description, string $pageText = ''): array
    {
        $limits = config('website_checks.serp_preview', ['title_max' => 60, 'description_max' => 160]);

        $source = $description !== '' ? 'meta' : ($pageText !== '' ? 'page_text' : 'none');
        $text   = $description !== '' ? $description : $pageText;

        [$shownTitle, $titleCut] = self::truncate($title !== '' ? $title : (parse_url($url, PHP_URL_HOST) ?: $url), $limits['title_max']);
        [$shownDesc, $descCut]   = self::truncate($text, $limits['description_max']);

        return [
            'title'                 => $shownTitle,
            'title_truncated'       => $titleCut,
            'display_url'           => self::displayUrl($url),
            'url'                   => $url,
            'description'           => $shownDesc,
            'description_truncated' => $descCut,
            'description_source'    => $source,
        ];
    }

    /** "https://www.example.com/blog/post?x=1" → "example.com › blog › post" */
    public static function displayUrl(string $url): string
    {
        $host     = preg_replace('/^www\./i', '', (string) parse_url($url, PHP_URL_HOST));
        $segments = array_values(array_filter(explode('/', (string) parse_url($url, PHP_URL_PATH)), fn ($s) => $s !== ''));

        return implode(' › ', array_merge([$host], array_map('rawurldecode', $segments)));
    }

    /** @return array{0: string, 1: bool} text cut at the last whole word within $max characters, plus "…" */
    private static function truncate(string $text, int $max): array
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text));
        if (mb_strlen($text) <= $max) {
            return [$text, false];
        }

        $cut   = mb_substr($text, 0, $max);
        $space = mb_strrpos($cut, ' ');
        if ($space !== false && $space > $max * 0.6) {
            $cut = mb_substr($cut, 0, $space);
        }

        return [rtrim($cut, " ,.;:-–—|") . '…', true];
    }
}
