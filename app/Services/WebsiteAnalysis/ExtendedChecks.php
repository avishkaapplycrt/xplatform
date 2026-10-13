<?php

namespace App\Services\WebsiteAnalysis;

use Illuminate\Http\Client\Response;

/**
 * The newer "Analyse my website" / "Compare with a competitor" check
 * categories, built from one page fetch (plus robots.txt and llms.txt, which
 * the analyzer fetches):
 *
 * - crawl  Crawling & Indexing: noindex (meta robots + X-Robots-Tag), content
 *          present without JavaScript.
 * - trust  Content Freshness & Trust: published/updated date and author (on
 *          article pages), About/Contact/Privacy links, address & phone,
 *          review/rating markup.
 * - ai     AI Visibility (AEO/GEO): AI crawler access in robots.txt (a choice,
 *          reported as 'info'), FAQ / question headings, short direct answers,
 *          FAQPage + Organization (sameAs) schema, llms.txt (optional, 'info').
 *
 * Thresholds come from config('website_checks'). 'info' checks are never
 * scored (see WebsiteAnalyzerService::scoreChecks).
 */
class ExtendedChecks
{
    /** @return list<array> Crawling & Indexing checks */
    public function crawl(Response $response, \DOMXPath $xpath): array
    {
        $checks = [];

        // noindex in <meta name="robots|googlebot"> or the X-Robots-Tag header.
        $metaNoindex = false;
        foreach ($xpath->query("//meta[translate(@name,'ROBTSGL','robtsgl')='robots' or translate(@name,'GOLEBT','golebt')='googlebot']") as $meta) {
            if (stripos($meta->getAttribute('content'), 'noindex') !== false) {
                $metaNoindex = true;
            }
        }
        $headerNoindex = false;
        foreach ((array) $response->header('X-Robots-Tag') as $value) {
            if (stripos((string) $value, 'noindex') !== false) {
                $headerNoindex = true;
            }
        }
        $checks[] = [
            'category' => 'crawl', 'name' => 'Indexing (noindex)',
            'status'   => $metaNoindex || $headerNoindex ? 'fail' : 'pass',
            'detail'   => match (true) {
                $metaNoindex && $headerNoindex => 'Blocked from search results: noindex in the robots meta tag and X-Robots-Tag header',
                $metaNoindex                   => 'Blocked from search results: noindex in the robots meta tag',
                $headerNoindex                 => 'Blocked from search results: noindex in the X-Robots-Tag header',
                default                        => 'Indexable — no noindex found',
            },
        ];

        // Main content in the raw HTML, or built by JavaScript after load?
        $words   = $this->visibleWordCount($xpath);
        $minimum = (int) config('website_checks.js_rendering_min_words', 150);
        $jsShell = $xpath->query("//div[@id='root' or @id='app' or @id='__next' or @id='__nuxt'][not(normalize-space())] | //*[@ng-app]")->length > 0
            || preg_match('/enable javascript|javascript is (required|disabled)|requires javascript/i', implode(' ', array_map(
                fn ($n) => $n->textContent, iterator_to_array($xpath->query('//noscript'))
            )));
        $checks[] = [
            'category' => 'crawl', 'name' => 'Content without JavaScript',
            'status'   => $words >= $minimum ? 'pass' : 'warn',
            'detail'   => $words >= $minimum
                ? "{$words} words of content in the raw HTML"
                : "Only {$words} words in the raw HTML" . ($jsShell ? ' — the main content appears to load with JavaScript' : ''),
        ];

        return $checks;
    }

    /** @return list<array> Content Freshness & Trust checks */
    public function trust(\DOMXPath $xpath, StructuredData $data): array
    {
        $checks    = [];
        $isArticle = $data->hasArticle()
            || strtolower((string) $this->attr($xpath, "//meta[@property='og:type']", 'content')) === 'article';

        $date = $this->attr($xpath, "//meta[@property='article:modified_time' or @property='og:updated_time']", 'content')
            ?? $this->attr($xpath, "//meta[@property='article:published_time']", 'content')
            ?? $data->latestDate()
            ?? $this->attr($xpath, "//meta[@name='date' or @name='last-modified' or @name='dcterms.modified' or @name='dcterms.date']", 'content')
            ?? $this->attr($xpath, '//time[@datetime]', 'datetime');
        $checks[] = [
            'category' => 'trust', 'name' => 'Published / updated date',
            'status'   => $date !== null ? 'pass' : ($isArticle ? 'warn' : 'info'),
            'detail'   => $date !== null
                ? 'Dated ' . substr($date, 0, 10)
                : ($isArticle ? 'No published or updated date found' : 'Not applicable — not an article page (no date found)'),
        ];

        $hasAuthor = $data->hasAuthor()
            || $xpath->query("//meta[@name='author'][normalize-space(@content)] | //a[@rel='author'] | //*[@itemprop='author'] | //*[@rel='author']")->length > 0;
        $checks[] = [
            'category' => 'trust', 'name' => 'Author information',
            'status'   => $hasAuthor ? 'pass' : ($isArticle ? 'warn' : 'info'),
            'detail'   => $hasAuthor
                ? 'Author shown'
                : ($isArticle ? 'No author information found' : 'Not applicable — not an article page (no author found)'),
        ];

        $found = [];
        foreach ($xpath->query('//a[@href]') as $a) {
            $haystack = strtolower($a->getAttribute('href') . ' ' . $a->textContent);
            foreach (['About' => 'about', 'Contact' => 'contact', 'Privacy' => 'privacy'] as $label => $needle) {
                if (str_contains($haystack, $needle)) {
                    $found[$label] = true;
                }
            }
        }
        $missing = array_diff(['About', 'Contact', 'Privacy'], array_keys($found));
        $checks[] = [
            'category' => 'trust', 'name' => 'About / Contact / Privacy pages',
            'status'   => $missing === [] ? 'pass' : 'warn',
            'detail'   => $missing === [] ? 'Links to About, Contact and Privacy found' : 'No link found to: ' . implode(', ', $missing),
        ];

        $hasPhone   = $data->hasTelephone() || $xpath->query("//a[starts-with(@href,'tel:')] | //*[@itemprop='telephone']")->length > 0;
        $hasAddress = $data->hasAddress() || $xpath->query("//address | //*[@itemprop='address' or @itemprop='streetAddress']")->length > 0;
        $checks[] = [
            'category' => 'trust', 'name' => 'Business address & phone',
            'status'   => $hasPhone && $hasAddress ? 'pass' : 'warn',
            'detail'   => match (true) {
                $hasPhone && $hasAddress => 'Address and phone number shown',
                $hasPhone                => 'Phone number shown, no address found',
                $hasAddress              => 'Address shown, no phone number found',
                default                  => 'No address or phone number found',
            },
        ];

        $checks[] = [
            'category' => 'trust', 'name' => 'Review / rating schema',
            'status'   => $data->hasRating() ? 'pass' : 'warn',
            'detail'   => $data->hasRating() ? 'Review or AggregateRating markup found' : 'No Review or AggregateRating markup',
        ];

        return $checks;
    }

    /**
     * @param string|null $robotsTxt robots.txt body, or null if there isn't one
     * @param bool        $hasLlms   whether /llms.txt exists
     *
     * @return list<array> AI Visibility (AEO/GEO) checks
     */
    public function ai(\DOMXPath $xpath, StructuredData $data, ?string $robotsTxt, bool $hasLlms): array
    {
        $checks = [];

        $access  = $this->aiCrawlerAccess($robotsTxt);
        $blocked = array_keys(array_filter($access, fn ($s) => $s === 'blocked'));
        $checks[] = [
            'category' => 'ai', 'name' => 'AI crawler access',
            'status'   => 'info',   // blocking AI crawlers is a business choice, not an error
            'detail'   => $blocked === []
                ? 'All ' . count($access) . ' AI crawlers allowed in robots.txt'
                : 'Blocked in robots.txt: ' . implode(', ', $blocked) . ' — a choice to review',
            'crawlers' => $access,
        ];

        // Question-style headings and a FAQ section.
        $questionHeadings = [];
        $hasFaqSection    = false;
        foreach ($xpath->query('//h1|//h2|//h3|//h4|//h5|//h6') as $h) {
            $text = trim(preg_replace('/\s+/u', ' ', $h->textContent));
            if (str_ends_with($text, '?')) {
                $questionHeadings[] = $h;
            }
            if (preg_match('/\bfaqs?\b|frequently asked/i', $text)) {
                $hasFaqSection = true;
            }
        }
        $hasFaqSection = $hasFaqSection || $xpath->query("//*[contains(translate(@id,'FAQ','faq'),'faq') or contains(translate(@class,'FAQ','faq'),'faq')]")->length > 0;
        $count   = count($questionHeadings);
        $minimum = (int) config('website_checks.min_question_headings', 2);
        $checks[] = [
            'category' => 'ai', 'name' => 'FAQ / question headings',
            'status'   => $hasFaqSection || $count >= $minimum ? 'pass' : 'warn',
            'detail'   => ($hasFaqSection ? 'FAQ section found' : 'No FAQ section') . " · {$count} question-style heading" . ($count === 1 ? '' : 's'),
        ];

        // A short, direct answer right under each question heading.
        $range = config('website_checks.short_answer_words', ['min' => 20, 'max' => 60]);
        $answered = 0;
        foreach ($questionHeadings as $h) {
            $answer = $this->paragraphAfter($h);
            $words  = $answer ? str_word_count(trim($answer->textContent)) : 0;
            if ($words >= $range['min'] && $words <= $range['max']) {
                $answered++;
            }
        }
        $checks[] = [
            'category' => 'ai', 'name' => 'Short direct answers',
            'status'   => $count === 0 ? 'info' : ($answered > 0 ? 'pass' : 'warn'),
            'detail'   => $count === 0
                ? 'Not applicable — no question headings to answer'
                : "{$answered} of {$count} question headings followed by a {$range['min']}–{$range['max']} word answer",
        ];

        $hasFaqSchema = $data->hasType('FAQPage');
        $hasOrg       = $data->organizations() !== [];
        $hasSameAs    = $data->organizationHasSameAs();
        $checks[] = [
            'category' => 'ai', 'name' => 'FAQPage & Organization schema',
            'status'   => $hasFaqSchema && $hasOrg && $hasSameAs ? 'pass' : (($hasFaqSchema || $hasOrg) ? 'warn' : 'fail'),
            'detail'   => 'FAQPage ' . ($hasFaqSchema ? '✓' : '✗')
                . ' · Organization ' . ($hasOrg ? '✓' : '✗')
                . ' · sameAs profiles ' . ($hasSameAs ? '✓' : '✗'),
        ];

        $checks[] = [
            'category' => 'ai', 'name' => 'llms.txt',
            'status'   => 'info',   // optional; few AI tools read it yet
            'detail'   => $hasLlms ? 'Found (optional)' : 'Not found (optional — few AI tools read it yet)',
        ];

        return $checks;
    }

    /**
     * Each configured AI crawler's access per robots.txt: 'blocked' when
     * "Disallow: /" applies to it (its own group, else the * group), else
     * 'allowed'.
     *
     * @return array<string, 'allowed'|'blocked'>
     */
    public function aiCrawlerAccess(?string $robotsTxt): array
    {
        $groups = [];
        $agents = [];
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
                    $agents = [];
                    $inRules = false;
                }
                $agents[] = strtolower($value);
            } elseif ($field === 'disallow' || $field === 'allow') {
                $inRules = true;
                foreach ($agents as $agent) {
                    $groups[$agent][] = [$field, $value];
                }
            }
        }

        $access = [];
        foreach ((array) config('website_checks.ai_crawlers', []) as $bot) {
            $rules = $groups[strtolower($bot)] ?? $groups['*'] ?? [];
            $access[$bot] = array_filter($rules, fn ($r) => $r[0] === 'disallow' && $r[1] === '/') !== [] ? 'blocked' : 'allowed';
        }

        return $access;
    }

    /** Words of visible text (no script/style/noscript/template) in the raw HTML. */
    private function visibleWordCount(\DOMXPath $xpath): int
    {
        $parts = [];
        foreach ($xpath->query('//body//text()[not(ancestor::script) and not(ancestor::style) and not(ancestor::noscript) and not(ancestor::template)]') as $node) {
            $parts[] = $node->nodeValue;
        }

        return str_word_count(preg_replace('/\s+/u', ' ', implode(' ', $parts)));
    }

    /** First paragraph after a heading (handles wrapped headings and answers inside a div). */
    private function paragraphAfter(\DOMElement $heading): ?\DOMElement
    {
        $node = $heading;
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

    private function attr(\DOMXPath $xpath, string $query, string $attribute): ?string
    {
        $value = trim((string) $xpath->query($query)->item(0)?->getAttribute($attribute));

        return $value === '' ? null : $value;
    }
}
