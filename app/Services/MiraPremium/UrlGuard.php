<?php

namespace App\Services\MiraPremium;

use App\Services\WebsiteAnalyzerService;

/**
 * Normalises a URL a person typed ("acme.com" -> "https://acme.com/") and
 * refuses anything that isn't a public http(s) site: other schemes, missing
 * hosts, and hosts resolving to private, loopback, link-local or reserved
 * addresses — so a scan can't be pointed at the server's own network.
 */
class UrlGuard
{
    /** @var callable(string): string[] host -> resolved IPs (injectable for tests) */
    private $resolve;

    public function __construct(?callable $resolve = null)
    {
        $this->resolve = $resolve ?? fn (string $host) => array_merge(
            gethostbynamel($host) ?: [],
            array_column(@dns_get_record($host, DNS_AAAA) ?: [], 'ipv6')
        );
    }

    public function normalize(?string $input, string $field = 'url'): string
    {
        $url = trim((string) $input);
        if ($url === '') {
            throw new MiraApiException('InvalidUrl', 'Enter a website address, for example https://yourwebsite.com.', ['field' => $field]);
        }

        if (!preg_match('#^[a-z][a-z0-9+.-]*://#i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }

        $parts = parse_url($url);
        $host  = strtolower($parts['host'] ?? '');

        if (!in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            || $host === '' || mb_strlen($url) > 2048
            || (!filter_var($host, FILTER_VALIDATE_IP) && !preg_match('/^([a-z0-9-]+\.)+[a-z]{2,63}$/', $host))) {
            throw new MiraApiException('InvalidUrl', "That doesn't look like a website address. Try https://yourwebsite.com.", ['field' => $field]);
        }

        if (!$this->isPublicHost($host)) {
            throw new MiraApiException('InvalidUrl', 'That address is not a public website.', ['field' => $field]);
        }

        if (!isset($parts['path'])) {
            $url .= '/';
        }

        return WebsiteAnalyzerService::cleanUrl($url);
    }

    public function domainOf(string $url): string
    {
        return preg_replace('/^www\./', '', strtolower((string) parse_url($url, PHP_URL_HOST)));
    }

    private function isPublicHost(string $host): bool
    {
        $host = trim($host, '[]');
        $ips  = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : ($this->resolve)($host);

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
}
