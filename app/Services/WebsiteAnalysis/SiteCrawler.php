<?php

namespace App\Services\WebsiteAnalysis;

use App\Services\MiraPremium\UrlGuard;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * "Pages analysed": reads up to N pages of the same site (from its sitemap,
 * or failing that the links on the scanned page) and checks what only shows
 * across several pages: pages that error, pages kept out of Google, and
 * duplicate or missing titles and descriptions.
 *
 * Limits: config website_checks.crawl. Every URL goes through
 * UrlGuard::isPublicUrl() and must be on the scanned site's host.
 */
class SiteCrawler
{
    private const USER_AGENT = 'Mozilla/5.0 (compatible; XPlatformsAnalyzer/1.0; +https://xplatforms.example)';

    private UrlGuard $guard;

    public function __construct(?UrlGuard $guard = null)
    {
        $this->guard = $guard ?? new UrlGuard();
    }

    /**
     * @return array{site: array, checks: list<array>} `site` for the report's
     *         "Pages analysed" panel, `checks` merged into the scan's checks
     */
    public function crawl(string $url): array
    {
        $max  = (int) config('website_checks.crawl.max_pages', 25);
        $host = $this->bareHost($url);

        if (!$this->guard->isPublicUrl($url)) {
            return ['site' => null, 'checks' => []];
        }

        [$urls, $source] = $this->discover($url, $host, $max);
        $pages = $this->fetchPages($urls);

        // ------- summary
        $errors = $indexed = $notIndexed = 0;
        $titles = $missingDesc = $missingTitle = $multiH1 = [];
        foreach ($pages as &$p) {
            if ($p['status'] === null || $p['status'] >= 400) {
                $p['state'] = 'error';
                $errors++;
                continue;
            }
            if ($p['noindex'] || ($p['canonical'] && $this->normal($p['canonical']) !== $this->normal($p['url']))) {
                $p['state'] = 'not_indexed';
                $notIndexed++;
            } else {
                $p['state'] = 'indexed';
                $indexed++;
            }
            if ($p['title'] === '') {
                $missingTitle[] = $p['url'];
            } else {
                $titles[mb_strtolower($p['title'])][] = $p['url'];
            }
            if ($p['description'] === '') {
                $missingDesc[] = $p['url'];
            }
            if ($p['h1'] > 1) {
                $multiH1[] = $p['url'];
            }
        }
        unset($p);

        $duplicates = [];
        foreach ($titles as $group) {
            if (count($group) > 1) {
                $duplicates[] = ['title' => $this->titleOf($pages, $group[0]), 'urls' => $group];
            }
        }
        $okPages = count($pages) - $errors;
        $dupPages = array_sum(array_map(fn ($d) => count($d['urls']), $duplicates));

        $site = [
            'source'          => $source,           // 'sitemap' | 'links'
            'pages_analysed'  => count($pages),
            'indexed'         => $indexed,
            'not_indexed'     => $notIndexed,
            'errors'          => $errors,
            'duplicate_titles'     => $duplicates,
            'missing_titles'       => $missingTitle,
            'missing_descriptions' => $missingDesc,
            'multiple_h1'          => $multiH1,
            'pages'           => array_map(fn ($p) => [
                'url' => $p['url'], 'status' => $p['status'], 'state' => $p['state'],
                'title' => $p['title'], 'words' => $p['words'],
            ], $pages),
        ];

        // ------- checks (scored with the others)
        $checks = [
            [
                'category' => 'crawl', 'name' => 'Pages with errors',
                'status'   => $errors === 0 ? 'pass' : ($errors <= 2 ? 'warn' : 'fail'),
                'detail'   => $errors === 0
                    ? 'All ' . count($pages) . ' pages analysed load'
                    : "{$errors} of " . count($pages) . ' pages return an error: ' . $this->sample(array_column(array_filter($pages, fn ($p) => $p['state'] === 'error'), 'url')),
            ],
            [
                'category' => 'crawl', 'name' => 'Pages kept out of Google',
                'status'   => 'info',   // noindex / canonical elsewhere is often deliberate
                'detail'   => $notIndexed === 0
                    ? 'Every page analysed can be indexed'
                    : "{$notIndexed} page(s) have noindex or a canonical pointing elsewhere: " . $this->sample(array_column(array_filter($pages, fn ($p) => $p['state'] === 'not_indexed'), 'url')),
            ],
            [
                'category' => 'titles', 'name' => 'Duplicate titles',
                'status'   => $okPages === 0 ? 'info' : ($duplicates === [] ? 'pass' : 'warn'),
                'detail'   => $duplicates === []
                    ? "All {$okPages} pages have unique titles"
                    : "{$dupPages} pages share " . count($duplicates) . ' title(s), e.g. "' . mb_strimwidth($duplicates[0]['title'], 0, 60, '…') . '"',
            ],
            [
                'category' => 'titles', 'name' => 'Pages missing meta descriptions',
                'status'   => $okPages === 0 ? 'info' : ($missingDesc === [] ? 'pass' : 'warn'),
                'detail'   => $missingDesc === []
                    ? "All {$okPages} pages have a meta description"
                    : count($missingDesc) . " of {$okPages} pages have no meta description: " . $this->sample($missingDesc),
            ],
        ];

        return ['site' => $site, 'checks' => $checks];
    }

    /** @return array{0: list<string>, 1: 'sitemap'|'links'} the scanned URL first, then up to $max - 1 more pages */
    private function discover(string $url, string $host, int $max): array
    {
        $origin = $this->origin($url);
        $found  = [];

        // Sitemaps: from robots.txt, else /sitemap.xml; one level of sitemap index.
        $robots   = $this->get("{$origin}/robots.txt");
        $sitemaps = [];
        if ($robots?->successful()) {
            preg_match_all('/^\s*sitemap:\s*(\S+)/mi', $robots->body(), $m);
            $sitemaps = $m[1];
        }
        $sitemaps = $sitemaps ?: ["{$origin}/sitemap.xml"];

        $seenMaps = 0;
        while ($sitemaps && $seenMaps < 4 && count($found) < $max * 2) {
            $map = array_shift($sitemaps);
            $seenMaps++;
            if (!$this->sameSite($map, $host)) {
                continue;
            }
            $res = $this->get($map);
            if (!$res?->successful()) {
                continue;
            }
            preg_match_all('#<loc>\s*([^<\s]+)\s*</loc>#i', $res->body(), $m);
            $locs = array_map(fn ($l) => html_entity_decode($l, ENT_QUOTES | ENT_XML1), $m[1]);
            if (stripos($res->body(), '<sitemapindex') !== false) {
                array_push($sitemaps, ...$locs);
                continue;
            }
            foreach ($locs as $loc) {
                if ($this->sameSite($loc, $host)) {
                    $found[$this->normal($loc)] = $loc;
                }
            }
        }

        $source = 'sitemap';
        if ($found === []) {
            // No sitemap: the internal links on the scanned page.
            $source = 'links';
            $page   = $this->get($url);
            if ($page?->successful()) {
                preg_match_all('/<a\s[^>]*href=["\']([^"\']+)["\']/i', $page->body(), $m);
                foreach ($m[1] as $href) {
                    $abs = SiteChecks::absoluteUrl($url, $href);
                    if ($abs && $this->sameSite($abs, $host) && !preg_match('/\.(pdf|jpe?g|png|gif|webp|svg|zip|mp4|mp3|docx?|xlsx?)(\?|$)/i', $abs)) {
                        $found[$this->normal($abs)] = $abs;
                    }
                }
            }
        }

        unset($found[$this->normal($url)]);

        return [array_merge([$url], array_slice(array_values($found), 0, $max - 1)), $source];
    }

    /** @return list<array{url: string, status: int|null, noindex: bool, canonical: string|null, title: string, description: string, h1: int, words: int}> */
    private function fetchPages(array $urls): array
    {
        $timeout = (int) config('website_checks.crawl.timeout_seconds', 12);
        $pages   = [];

        foreach (array_chunk($urls, 8) as $chunk) {
            $responses = Http::pool(fn (Pool $pool) => array_map(
                fn ($u) => $pool->as($u)->withHeaders(['User-Agent' => self::USER_AGENT, 'Accept' => 'text/html,application/xhtml+xml'])
                    ->connectTimeout(5)->timeout($timeout)->get($u),
                $chunk
            ));
            foreach ($chunk as $u) {
                $r = $responses[$u] ?? null;
                $pages[] = $r instanceof Response ? $this->readPage($u, $r) : [
                    'url' => $u, 'status' => null, 'noindex' => false, 'canonical' => null,
                    'title' => '', 'description' => '', 'h1' => 0, 'words' => 0,
                ];
            }
        }

        return $pages;
    }

    private function readPage(string $url, Response $r): array
    {
        $page = ['url' => $url, 'status' => $r->status(), 'noindex' => false, 'canonical' => null, 'title' => '', 'description' => '', 'h1' => 0, 'words' => 0];
        if (!$r->successful() || !str_contains(strtolower((string) $r->header('Content-Type')), 'html')) {
            return $page;
        }

        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($r->body(), LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        $x = new \DOMXPath($doc);

        $robots = '';
        foreach ($x->query("//meta[translate(@name,'ROBTS','robts')='robots' or translate(@name,'GOLEBT','golebt')='googlebot']") as $m) {
            $robots .= ' ' . $m->getAttribute('content');
        }
        $robots .= ' ' . implode(' ', (array) $r->header('X-Robots-Tag'));

        $canonical = $x->query('//link[@rel="canonical"]/@href')->item(0)?->nodeValue;

        $page['noindex']     = stripos($robots, 'noindex') !== false;
        $page['canonical']   = $canonical ? SiteChecks::absoluteUrl($url, $canonical) : null;
        // Some sites double-encode ("&amp;amp;"), so decode what is left, as for Open Graph tags.
        $decode = fn (?string $v) => trim(html_entity_decode(preg_replace('/\s+/u', ' ', (string) $v), ENT_QUOTES | ENT_HTML5));
        $page['title']       = $decode($x->query('//title')->item(0)?->textContent);
        $page['description'] = $decode($x->query('//meta[@name="description"]/@content')->item(0)?->nodeValue);
        $page['h1']          = $x->query('//h1')->length;
        $page['words']       = str_word_count(preg_replace('/\s+/', ' ', implode(' ', array_map(
            fn ($n) => $n->nodeValue,
            iterator_to_array($x->query('//body//text()[not(ancestor::script) and not(ancestor::style)]'))
        ))));

        return $page;
    }

    private function get(string $url): ?Response
    {
        if (!$this->guard->isPublicUrl($url)) {
            return null;
        }
        try {
            return Http::withHeaders(['User-Agent' => self::USER_AGENT])->connectTimeout(5)->timeout(12)->get($url);
        } catch (\Throwable) {
            return null;
        }
    }

    private function sameSite(string $url, string $host): bool
    {
        return preg_match('#^https?://#i', $url) && $this->bareHost($url) === $host;
    }

    private function bareHost(string $url): string
    {
        return preg_replace('/^www\./', '', strtolower((string) parse_url($url, PHP_URL_HOST)));
    }

    private function origin(string $url): string
    {
        $p = parse_url($url);

        return ($p['scheme'] ?? 'https') . '://' . ($p['host'] ?? '');
    }

    /** Comparable form: no scheme, www, trailing slash or fragment. */
    private function normal(string $url): string
    {
        $p = parse_url($url);

        return $this->bareHost($url) . '/' . trim($p['path'] ?? '', '/') . (isset($p['query']) ? '?' . $p['query'] : '');
    }

    private function titleOf(array $pages, string $url): string
    {
        foreach ($pages as $p) {
            if ($p['url'] === $url) {
                return $p['title'];
            }
        }

        return '';
    }

    private function sample(array $urls): string
    {
        $shown = implode(', ', array_map([SiteChecks::class, 'shortUrl'], array_slice($urls, 0, 3)));

        return $shown . (count($urls) > 3 ? ' and ' . (count($urls) - 3) . ' more' : '');
    }
}
