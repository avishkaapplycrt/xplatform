<?php

namespace App\Services\WebsiteAnalysis;

/**
 * "Why it matters" and "Check with" lines for every website check, shown on
 * issue cards (competitor comparison) and with recommendations. Added to each
 * check as `why` and `check_with` by WebsiteAnalyzerService.
 */
class CheckGuide
{
    /** check name => [why it matters, where to check it] */
    private const GUIDE = [
        'Title tag'                       => ['The title is the blue link in Google results — it drives whether people click.', 'Google Search Console (Performance → Queries)'],
        'Meta description'                => ['Google often shows it under your link; a clear one lifts click-through.', 'Google Search Console (Performance → Pages)'],
        'H1 heading'                      => ['One clear H1 tells search engines and readers what the page is about.', 'Browser DevTools or a site crawler (e.g. Screaming Frog)'],
        'Heading order'                   => ['Logical headings help Google and screen readers understand the page structure.', 'Browser DevTools or the WAVE accessibility checker'],
        'Word count'                      => ['Thin pages rarely rank or get quoted; useful detail answers more searches.', 'Google Search Console (Performance → Pages)'],
        'Image alt text'                  => ['Alt text makes images findable in Google Images and readable for screen readers.', 'Lighthouse in PageSpeed Insights (Accessibility)'],
        'HTTPS'                           => ['Browsers warn on non-HTTPS pages and Google uses HTTPS as a ranking signal.', 'Google Search Console (HTTPS report)'],
        'Mixed content'                   => ['Insecure files on a secure page trigger browser warnings and broken padlocks.', 'Browser DevTools (Console) or PageSpeed Insights'],
        'Mobile viewport'                 => ['Google indexes the mobile version of your site first.', 'PageSpeed Insights (mobile)'],
        'Internal vs external links'      => ['Internal links help Google discover pages and spread ranking signals.', 'Google Search Console (Links report)'],
        'Generic anchor text'             => ['Descriptive link text tells Google and users what the linked page is about.', 'A site crawler (e.g. Screaming Frog)'],
        'robots.txt'                      => ['It tells search engines and AI crawlers what they may crawl.', 'Google Search Console (robots.txt report)'],
        'sitemap.xml'                     => ['A sitemap helps Google find and refresh all your important pages.', 'Google Search Console (Sitemaps)'],
        'Canonical tag'                   => ['It stops duplicate URLs from splitting your rankings.', 'Google Search Console (URL Inspection)'],
        'Indexing (noindex)'              => ['A noindex tag removes the page from Google results entirely.', 'Google Search Console (URL Inspection)'],
        'Content without JavaScript'      => ['Content built by JavaScript can be indexed late or missed, and most AI crawlers can\'t run JavaScript.', 'Google Search Console (URL Inspection → View crawled page)'],
        'Largest Contentful Paint (LCP)'  => ['How fast the main content appears — a Core Web Vital that affects rankings and bounce rate.', 'PageSpeed Insights'],
        'Interaction to Next Paint (INP)' => ['How quickly the page responds to taps and clicks — a Core Web Vital.', 'PageSpeed Insights'],
        'Cumulative Layout Shift (CLS)'   => ['Content jumping around frustrates users — a Core Web Vital.', 'PageSpeed Insights'],
        'Published / updated date'        => ['Visible dates signal fresh, maintained content to readers, Google and AI answers.', 'Rich Results Test (Article markup)'],
        'Author information'              => ['Named authors support E-E-A-T (experience, expertise, authority, trust).', 'Rich Results Test (Article markup)'],
        'About / Contact / Privacy pages' => ['Easy-to-find company and policy pages are basic trust signals for users and Google.', 'A site crawler (e.g. Screaming Frog)'],
        'Business address & phone'        => ['Consistent contact details build trust and help local search.', 'Google Business Profile'],
        'Review / rating schema'          => ['Rating markup can earn review stars in results and is a trust signal for AI answers.', 'Rich Results Test'],
        'AI crawler access'               => ['Blocked AI crawlers can\'t read your site, so ChatGPT, Claude or Perplexity can\'t cite it.', 'Your robots.txt file'],
        'FAQ / question headings'         => ['Question-style headings match how people search and ask AI assistants.', 'Google Search Console (Performance → Queries)'],
        'Short direct answers'            => ['A 20–60 word answer under a question is what featured snippets and AI answers quote.', 'Google search for your target question'],
        'FAQPage & Organization schema'   => ['Tells Google and AI who you are (sameAs links your profiles) and marks your Q&A content.', 'Rich Results Test or the Schema Markup Validator'],
        'llms.txt'                        => ['An optional summary file for AI tools; few read it yet, so it\'s a low priority.', 'Your site root (/llms.txt)'],
        'Open Graph tags'                 => ['Controls how your link looks when shared on Facebook and LinkedIn.', 'Facebook Sharing Debugger'],
        'Twitter Card tags'               => ['Controls how your link looks when shared on X/Twitter.', 'An X/Twitter card preview tool'],
        'Structured data'                 => ['Markup makes pages eligible for rich results (stars, FAQs, breadcrumbs) that stand out.', 'Rich Results Test'],
        'Broken links'                    => ['Dead links waste visitors\' clicks and tell Google the page isn\'t maintained.', 'Google Search Console (Pages → Not found) or a site crawler'],
        'Redirects'                       => ['Each extra hop slows the page and can lose ranking signals along the way.', 'httpstatus.io or browser DevTools (Network)'],
        'Readability'                     => ['Plain text keeps visitors reading and is easier for AI answers to quote.', 'Hemingway Editor or a Flesch reading-ease checker'],
        'Language'                        => ['The lang attribute tells Google and screen readers which language the page is in.', 'Browser DevTools (Elements → <html>)'],
        'Lighthouse Performance'          => ['Google\'s own lab score for how fast the page loads and responds.', 'PageSpeed Insights or Chrome DevTools (Lighthouse)'],
        'Lighthouse Accessibility'        => ['Pages everyone can use reach more people and avoid legal risk.', 'PageSpeed Insights or Chrome DevTools (Lighthouse)'],
        'Lighthouse Best Practices'       => ['Security and code-quality problems that browsers flag.', 'PageSpeed Insights or Chrome DevTools (Lighthouse)'],
        'Pages with errors'               => ['Pages that error drop out of Google and lose the links pointing at them.', 'Google Search Console (Pages)'],
        'Duplicate titles'                => ['Pages with the same title compete with each other in search results.', 'Google Search Console or a site crawler (e.g. Screaming Frog)'],
        'Pages missing meta descriptions' => ['Without one, Google picks text from the page, often a menu or cookie notice.', 'A site crawler (e.g. Screaming Frog)'],
        'Pages kept out of Google'        => ['noindex or a canonical pointing elsewhere keeps a page out of search; check it is on purpose.', 'Google Search Console (URL Inspection)'],
    ];

    /** @return array{why: string|null, check_with: string|null} */
    public static function for(string $checkName): array
    {
        $entry = self::GUIDE[$checkName] ?? null;

        return ['why' => $entry[0] ?? null, 'check_with' => $entry[1] ?? null];
    }
}
