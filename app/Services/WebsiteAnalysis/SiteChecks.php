<?php

namespace App\Services\WebsiteAnalysis;

use App\Services\MiraPremium\UrlGuard;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

/**
 * Checks that need more than the page's own HTML: following its links
 * (broken links), following its redirects, and reading its text
 * (readability, language). Limits live in config/website_checks.php.
 *
 * Every URL fetched here comes from someone else's page, so each one goes
 * through UrlGuard::isPublicUrl() first: a page can't point the analyzer at
 * the server's own network.
 */
class SiteChecks
{
    private const USER_AGENT = 'Mozilla/5.0 (compatible; XPlatformsAnalyzer/1.0; +https://xplatforms.example)';

    /** Statuses that mean "the site refused a bot", not "the page is gone". */
    private const UNVERIFIED = [401, 403, 429];

    private UrlGuard $guard;

    /** @var array<string, bool> host => public, so each host is resolved once per scan */
    private array $publicHosts = [];

    public function __construct(?UrlGuard $guard = null)
    {
        $this->guard = $guard ?? new UrlGuard();
    }

    // ------------------------------------------------------------ broken links

    public function brokenLinks(\DOMXPath $xpath, string $pageUrl): array
    {
        $max   = (int) config('website_checks.broken_links.max_links', 30);
        $links = [];
        foreach ($xpath->query('//a[@href]') as $a) {
            $abs = self::absoluteUrl($pageUrl, trim($a->getAttribute('href')));
            if ($abs !== null && !isset($links[$abs]) && $abs !== $pageUrl && $this->isPublic($abs)) {
                $links[$abs] = true;
            }
            if (count($links) >= $max) {
                break;
            }
        }
        $links = array_keys($links);

        if ($links === []) {
            return $this->check('links', 'Broken links', 'info', 'No links to test');
        }

        $status = $this->statuses($links, 'head');
        // Many servers answer HEAD wrongly (404 or 405 for a page that loads), so
        // anything that didn't succeed is asked again with a normal GET.
        $retry = array_values(array_filter($links, fn ($u) => $status[$u] === null || $status[$u] >= 400));
        if ($retry !== []) {
            $status = array_merge($status, $this->statuses($retry, 'get'));
        }

        $broken = [];
        foreach ($links as $u) {
            $s = $status[$u];
            if ($s === null || ($s >= 400 && !in_array($s, self::UNVERIFIED, true))) {
                $broken[] = ['url' => $u, 'status' => $s];
            }
        }

        $tested = count($links);
        $count  = count($broken);
        $sample = implode(', ', array_map(
            fn ($b) => self::shortUrl($b['url']) . ' (' . ($b['status'] ?? 'unreachable') . ')',
            array_slice($broken, 0, 3)
        ));

        return $this->check('links', 'Broken links', match (true) {
            $count === 0 => 'pass',
            $count <= 2  => 'warn',
            default      => 'fail',
        }, $count === 0
            ? "All {$tested} links tested work"
            : "{$count} of {$tested} links broken: {$sample}" . ($count > 3 ? ' and ' . ($count - 3) . ' more' : '')
        ) + ['broken' => $broken, 'tested' => $tested];
    }

    // -------------------------------------------------------------- redirects

    /** Follows the page's redirect hops one by one, and checks http:// sends people to https://. */
    public function redirects(string $url): array
    {
        $maxHops = (int) config('website_checks.redirects.max_hops', 5);
        $chain   = [$url];
        $current = $url;
        $loop    = false;

        for ($i = 0; $i <= $maxHops; $i++) {
            $res = $this->hop($current);
            if ($res === null || $res->status() < 300 || $res->status() >= 400 || !$res->header('Location')) {
                break;
            }
            $next = self::absoluteUrl($current, $res->header('Location'));
            if ($next === null || !$this->isPublic($next)) {
                break;
            }
            if (in_array($next, $chain, true)) {
                $loop = true;
                break;
            }
            $chain[] = $current = $next;
        }

        $hops = count($chain) - 1;

        // Does the http:// address forward to https://?
        $httpsNote = '';
        $httpsOk   = true;
        if (str_starts_with(strtolower($url), 'https://')) {
            $httpUrl = 'http://' . substr($url, 8);
            $res     = $this->hop($httpUrl);
            if ($res !== null) {
                $to      = (string) $res->header('Location');
                $httpsOk = $res->status() >= 300 && $res->status() < 400 && str_starts_with(strtolower($to), 'https://');
                $httpsNote = $httpsOk ? ' · http:// forwards to https://' : ' · http:// does not forward to https://';
            }
        }

        $path = implode(' → ', array_map([self::class, 'shortUrl'], $chain));

        [$status, $detail] = match (true) {
            $loop             => ['fail', "Redirect loop: {$path}"],
            $hops > $maxHops  => ['fail', "More than {$maxHops} redirects: {$path}"],
            $hops >= 2        => ['warn', "Redirect chain of {$hops} hops: {$path}"],
            $hops === 1       => ['pass', "1 redirect: {$path}"],
            default           => ['pass', 'No redirects'],
        };
        if (!$httpsOk && $status === 'pass') {
            $status = 'warn';
        }

        return $this->check('crawl', 'Redirects', $status, $detail . $httpsNote) + ['chain' => $chain];
    }

    // ------------------------------------------------------------ readability

    /** Flesch Reading Ease of the page's paragraph text (English only). */
    public function readability(\DOMXPath $xpath, ?string $language): array
    {
        $text = trim(preg_replace('/\s+/u', ' ', implode('. ', array_map(
            fn ($n) => trim($n->textContent),
            iterator_to_array($xpath->query('//p | //li[string-length(normalize-space()) > 60]'))
        ))));
        $words = preg_split('/[^\p{L}\']+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        if ($language !== null && $language !== 'en') {
            return $this->check('structure', 'Readability', 'info', 'Not scored — the reading-ease formula is for English text');
        }
        if (count($words) < (int) config('website_checks.readability.min_words', 100)) {
            return $this->check('structure', 'Readability', 'info', 'Not enough paragraph text to score (' . count($words) . ' words)');
        }

        $sentences = max(1, preg_match_all('/[.!?]+(\s|$)/u', $text));
        $syllables = array_sum(array_map([self::class, 'syllables'], $words));
        $score     = (int) round(206.835 - 1.015 * (count($words) / $sentences) - 84.6 * ($syllables / count($words)));
        $score     = max(0, min(100, $score));

        $limits = config('website_checks.readability', ['good' => 60, 'poor' => 30]);
        $label  = match (true) {
            $score >= 80 => 'easy',
            $score >= 60 => 'plain English',
            $score >= 50 => 'fairly difficult',
            $score >= 30 => 'difficult',
            default      => 'very difficult',
        };

        return $this->check('structure', 'Readability', match (true) {
            $score >= $limits['good'] => 'pass',
            $score >= $limits['poor'] => 'warn',
            default                   => 'fail',
        }, "Flesch reading ease {$score} ({$label}; aim for {$limits['good']}+)") + ['value' => $score];
    }

    // ----------------------------------------------------- conversion & trust

    /**
     * Can a visitor act on this page? Calls to action, ways to get in touch,
     * labelled form fields and visible proof (reviews, clients, guarantees).
     * Mira Premium audits only (ScanService).
     */
    public function conversion(\DOMXPath $xpath, StructuredData $structured): array
    {
        $checks = [];

        // Calls to action: buttons and links whose text asks for an action.
        $actions = '/\b(book|buy|order|sign ?up|get started|start|try|join|register|subscribe|contact us|call( us| now)?|enquire|enquiry|get a quote|quote|demo|apply|download|shop|enrol|enroll|free trial|request)\b/i';
        $ctas = [];
        foreach ($xpath->query('//a | //button | //input[@type="submit" or @type="button"]') as $el) {
            $text = trim(preg_replace('/\s+/u', ' ', $el->nodeName === 'input' ? $el->getAttribute('value') : $el->textContent));
            if ($text !== '' && mb_strlen($text) <= 40 && preg_match($actions, $text)) {
                $ctas[mb_strtolower($text)] = $text;
            }
        }
        $checks[] = $this->check('conversion', 'Calls to action', $ctas ? 'pass' : 'warn', $ctas
            ? count($ctas) . ' call(s) to action, e.g. "' . implode('", "', array_slice(array_values($ctas), 0, 3)) . '"'
            : 'No clear call to action (e.g. "Book a demo", "Get started", "Contact us")');

        // Ways to get in touch.
        $ways = array_filter([
            'phone link'   => $xpath->query('//a[starts-with(@href,"tel:")]')->length > 0,
            'email link'   => $xpath->query('//a[starts-with(@href,"mailto:")]')->length > 0,
            'WhatsApp'     => $xpath->query('//a[contains(@href,"wa.me") or contains(@href,"api.whatsapp.com") or starts-with(@href,"whatsapp:")]')->length > 0,
            'contact page' => $xpath->query('//a[contains(translate(@href,"CONTACT","contact"),"contact") or contains(translate(normalize-space(.),"CONTACT","contact"),"contact")]')->length > 0,
            'form'         => $xpath->query('//form[.//input[@type="email" or @type="tel"] or .//textarea]')->length > 0,
        ]);
        $checks[] = $this->check('conversion', 'Contact options', match (true) {
            count($ways) >= 2 => 'pass',
            count($ways) === 1 => 'warn',
            default => 'fail',
        }, $ways ? 'Found: ' . implode(', ', array_keys($ways)) : 'No phone, email, WhatsApp, contact page or enquiry form found');

        // Form fields people can understand (and screen readers can announce).
        $fields = $xpath->query('//form//input[not(@type="hidden" or @type="submit" or @type="button" or @type="image" or @type="reset")] | //form//select | //form//textarea');
        $unlabelled = 0;
        foreach ($fields as $f) {
            $id = $f->getAttribute('id');
            $labelled = ($id !== '' && $xpath->query('//label[@for="' . str_replace('"', '', $id) . '"]')->length > 0)
                || $xpath->query('ancestor::label', $f)->length > 0
                || trim($f->getAttribute('aria-label') . $f->getAttribute('aria-labelledby') . $f->getAttribute('title') . $f->getAttribute('placeholder')) !== '';
            if (!$labelled) {
                $unlabelled++;
            }
        }
        $checks[] = $this->check('conversion', 'Form labels', match (true) {
            $fields->length === 0 => 'info',
            $unlabelled === 0     => 'pass',
            default               => 'warn',
        }, match (true) {
            $fields->length === 0 => 'No forms on this page',
            $unlabelled === 0     => "All {$fields->length} form field(s) are labelled",
            default               => "{$unlabelled} of {$fields->length} form field(s) have no label",
        });

        // Proof that others trust the business.
        $text  = mb_strtolower(implode(' ', array_map(fn ($n) => $n->textContent, iterator_to_array($xpath->query('//body')))));
        $proof = array_values(array_filter([
            preg_match('/\b(testimonials?|what (our )?(customers|clients|students) say)\b/u', $text) ? 'testimonials' : null,
            preg_match('/\b(reviews?|rated|ratings?|stars?)\b/u', $text) || $structured->hasRating() ? 'reviews or ratings' : null,
            preg_match('/\b(trusted by|our clients|case stud(y|ies)|success stor(y|ies))\b/u', $text) ? 'clients or case studies' : null,
            preg_match('/\b(guarantee|certified|accredited|award)/u', $text) ? 'guarantees or accreditations' : null,
        ]));
        $checks[] = $this->check('conversion', 'Trust proof', $proof ? 'pass' : 'warn', $proof
            ? 'Shows ' . implode(', ', $proof)
            : 'No testimonials, reviews, client logos or guarantees found on this page');

        return $checks;
    }

    // ---------------------------------------------------------------- hreflang

    /** Language versions: valid codes, absolute links, and a link back to this page. */
    public function hreflang(\DOMXPath $xpath, string $pageUrl): array
    {
        $tags = $xpath->query('//link[@rel="alternate"][@hreflang]');
        if ($tags->length === 0) {
            return $this->check('discoverability', 'hreflang tags', 'info', 'No hreflang tags (only needed when the site has other language or country versions)');
        }

        $problems = [];
        $codes    = [];
        $self     = false;
        $norm     = fn ($u) => rtrim(preg_replace('#^https?://(www\.)?#i', '', strtolower((string) $u)), '/');
        foreach ($tags as $t) {
            $code = trim($t->getAttribute('hreflang'));
            $href = trim($t->getAttribute('href'));
            if (!preg_match('/^(x-default|[a-z]{2,3}(-[a-z]{2}|-[a-z]{4}(-[a-z]{2})?)?)$/i', $code)) {
                $problems[] = "invalid code \"{$code}\"";
            }
            if (!preg_match('#^https?://#i', $href)) {
                $problems[] = "relative link for {$code}";
            }
            if (isset($codes[strtolower($code)])) {
                $problems[] = "{$code} listed twice";
            }
            $codes[strtolower($code)] = true;
            $self = $self || $norm($href) === $norm($pageUrl);
        }
        if (!$self) {
            $problems[] = 'no tag pointing back to this page';
        }

        return $this->check('discoverability', 'hreflang tags', $problems ? 'warn' : 'pass', $problems
            ? count($codes) . ' language version(s); ' . implode('; ', array_slice(array_unique($problems), 0, 3))
            : count($codes) . ' language version(s), all valid' . (isset($codes['x-default']) ? ' (with x-default)' : ''));
    }

    // --------------------------------------------------------------- language

    /** The page's declared <html lang> against the language its text is written in. */
    public function language(\DOMXPath $xpath, string $text): array
    {
        $declared = strtolower(trim($xpath->query('//html/@lang')->item(0)?->nodeValue ?? ''));
        $detected = self::detectLanguage($text);
        $names    = self::LANGUAGE_NAMES;
        $seen     = $detected ? ($names[$detected] ?? $detected) . ' detected' : 'Language not detected';

        if ($declared === '') {
            return $this->check('structure', 'Language', 'warn', "{$seen} · no lang attribute on <html>") + ['detected' => $detected];
        }

        $base = explode('-', $declared)[0];
        if ($detected !== null && $base !== $detected) {
            return $this->check('structure', 'Language', 'warn', "{$seen}, but <html lang=\"{$declared}\"> says otherwise") + ['detected' => $detected];
        }

        return $this->check('structure', 'Language', 'pass', "{$seen} · lang=\"{$declared}\"") + ['detected' => $detected ?? $base];
    }

    /** ISO 639-1 code of the text's language from common words, or null when unsure. */
    public static function detectLanguage(string $text): ?string
    {
        $words = array_count_values(preg_split('/[^\p{L}]+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY));
        $best  = null;
        $top   = 0;
        foreach (self::STOPWORDS as $lang => $list) {
            $hits = 0;
            foreach ($list as $w) {
                $hits += $words[$w] ?? 0;
            }
            if ($hits > $top) {
                [$best, $top] = [$lang, $hits];
            }
        }

        return $top >= 5 ? $best : null;
    }

    private const STOPWORDS = [
        'en' => ['the', 'and', 'you', 'your', 'with', 'for', 'are', 'this', 'that', 'our', 'from', 'have', 'will', 'can'],
        'es' => ['el', 'los', 'las', 'del', 'para', 'con', 'una', 'por', 'que', 'nuestro', 'más', 'como'],
        'fr' => ['le', 'les', 'des', 'une', 'pour', 'avec', 'vous', 'nous', 'est', 'dans', 'sur', 'votre'],
        'de' => ['der', 'die', 'das', 'und', 'mit', 'für', 'sie', 'ist', 'nicht', 'ein', 'eine', 'auf'],
        'it' => ['il', 'gli', 'della', 'per', 'con', 'una', 'che', 'sono', 'nostro', 'anche', 'più'],
        'pt' => ['os', 'as', 'para', 'com', 'uma', 'não', 'você', 'nosso', 'mais', 'pelo', 'são'],
        'nl' => ['de', 'het', 'een', 'en', 'van', 'voor', 'met', 'zijn', 'niet', 'ook', 'uw', 'wij'],
    ];

    private const LANGUAGE_NAMES = ['en' => 'English', 'es' => 'Spanish', 'fr' => 'French', 'de' => 'German', 'it' => 'Italian', 'pt' => 'Portuguese', 'nl' => 'Dutch'];

    // ---------------------------------------------------------------- helpers

    /** Absolute http(s) URL for a link on $base, without its #fragment; null for mailto:, tel:, javascript:, # … */
    public static function absoluteUrl(string $base, string $href): ?string
    {
        $href = trim(html_entity_decode($href, ENT_QUOTES | ENT_HTML5));
        if ($href === '' || $href[0] === '#' || preg_match('/^(mailto|tel|javascript|data|sms|whatsapp):/i', $href)) {
            return null;
        }
        $href = preg_replace('/#.*$/', '', $href);

        if (preg_match('#^https?://#i', $href)) {
            return $href;
        }
        $b = parse_url($base);
        if (empty($b['host'])) {
            return null;
        }
        $scheme = $b['scheme'] ?? 'https';
        $origin = $scheme . '://' . $b['host'] . (isset($b['port']) ? ':' . $b['port'] : '');

        if (str_starts_with($href, '//')) {
            return $scheme . ':' . $href;
        }
        if (preg_match('/^[a-z][a-z0-9+.-]*:/i', $href)) {
            return null;   // another scheme
        }
        if ($href[0] === '/') {
            return $origin . self::dotSegments($href);
        }
        if ($href[0] === '?') {
            return $origin . ($b['path'] ?? '/') . $href;
        }
        $dir = preg_replace('#/[^/]*$#', '/', $b['path'] ?? '/');

        return $origin . self::dotSegments($dir . $href);
    }

    private static function dotSegments(string $path): string
    {
        [$p, $q] = array_pad(explode('?', $path, 2), 2, null);
        $out = [];
        foreach (explode('/', $p) as $seg) {
            if ($seg === '..') {
                array_pop($out);
            } elseif ($seg !== '.') {
                $out[] = $seg;
            }
        }
        $p = implode('/', $out);

        return ($p === '' || $p[0] !== '/' ? '/' . ltrim($p, '/') : $p) . ($q !== null ? '?' . $q : '');
    }

    /** "acme.com/pricing" — host plus path, for details. */
    public static function shortUrl(string $url): string
    {
        $p = parse_url($url);

        return preg_replace('/^www\./', '', $p['host'] ?? '') . rtrim($p['path'] ?? '', '/') . (isset($p['query']) ? '?…' : '');
    }

    private static function syllables(string $word): int
    {
        $w = preg_replace('/[^a-z]/', '', strtolower($word));
        if ($w === '') {
            return 0;
        }
        if (strlen($w) <= 3) {
            return 1;
        }
        $w = preg_replace('/(?:[^laeiouy]es|ed|[^laeiouy]e)$/', '', $w);
        $w = preg_replace('/^y/', '', $w);

        return max(1, preg_match_all('/[aeiouy]{1,2}/', $w));
    }

    private function isPublic(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return $this->publicHosts[$host] ??= $this->guard->isPublicUrl($url);
    }

    /** @return array<string, int|null> url => HTTP status (null when unreachable), fetched in parallel */
    private function statuses(array $urls, string $method): array
    {
        $timeout = (int) config('website_checks.broken_links.timeout_seconds', 8);
        $out     = [];
        foreach (array_chunk($urls, 10) as $chunk) {
            $responses = Http::pool(fn (Pool $pool) => array_map(
                fn ($u) => $pool->as($u)->withHeaders(['User-Agent' => self::USER_AGENT])
                    ->connectTimeout(5)->timeout($timeout)->{$method}($u),
                $chunk
            ));
            foreach ($chunk as $u) {
                $r = $responses[$u] ?? null;
                $out[$u] = $r instanceof \Illuminate\Http\Client\Response ? $r->status() : null;
            }
        }

        return $out;
    }

    /** One request without following redirects: HEAD, or GET when the server refuses HEAD. */
    private function hop(string $url): ?\Illuminate\Http\Client\Response
    {
        $res = $this->fetch($url, 'head', false);

        return $res === null || $res->status() >= 400
            ? ($this->fetch($url, 'get', false) ?? $res)
            : $res;
    }

    private function fetch(string $url, string $method, bool $follow): ?\Illuminate\Http\Client\Response
    {
        try {
            return Http::withHeaders(['User-Agent' => self::USER_AGENT])
                ->withOptions(['allow_redirects' => $follow])
                ->connectTimeout(5)->timeout(10)->{$method}($url);
        } catch (\Throwable) {
            return null;
        }
    }

    private function check(string $category, string $name, string $status, string $detail): array
    {
        return ['category' => $category, 'name' => $name, 'status' => $status, 'detail' => $detail];
    }
}
