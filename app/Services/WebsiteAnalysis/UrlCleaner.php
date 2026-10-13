<?php

namespace App\Services\WebsiteAnalysis;

/**
 * Removes tracking parameters (utm_*, gclid, fbclid, srsltid, …; list in
 * config('website_checks.tracking_parameters')) from a URL, so they're neither
 * analysed nor shown. Everything else in the URL is kept as-is.
 */
class UrlCleaner
{
    public static function clean(string $url): string
    {
        $url   = trim($url);
        $parts = parse_url($url);

        if ($parts === false || !isset($parts['query']) || $parts['query'] === '') {
            return $url;
        }

        $patterns = (array) config('website_checks.tracking_parameters', []);

        $kept = array_filter(explode('&', $parts['query']), function ($pair) use ($patterns) {
            $name = strtolower(urldecode(explode('=', $pair, 2)[0]));
            foreach ($patterns as $pattern) {
                $pattern = strtolower($pattern);
                if (str_ends_with($pattern, '*') ? str_starts_with($name, rtrim($pattern, '*')) : $name === $pattern) {
                    return false;
                }
            }

            return $pair !== '';
        });

        $base = strtok($url, '?#');
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return $base . ($kept === [] ? '' : '?' . implode('&', $kept)) . $fragment;
    }
}
