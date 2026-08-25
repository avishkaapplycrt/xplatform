<?php
// app/Services/GeoLocationService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class GeoLocationService
{
    protected string $dbPath;
    
    public function __construct()
    {
        $this->dbPath = storage_path('app/GeoLite2-City.mmdb');
    }
    
    /**
     * Lookup IP geolocation
     */
    public function lookup(string $ip): array
    {
        // Don't lookup private IPs
        if ($this->isPrivateIp($ip)) {
            return ['country_code' => 'LO', 'country_name' => 'Local'];
        }
        
        return Cache::remember("geo:{$ip}", 86400, function() use ($ip) {
            try {
                // Option 1: Use MaxMind GeoLite2 (recommended for production)
                if (extension_loaded('geoip2') && file_exists($this->dbPath)) {
                    return $this->lookupMaxMind($ip);
                }
                
                // Option 2: Use ipapi.co (free tier: 1000/day)
                return $this->lookupApi($ip);
                
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('GeoIP lookup failed', ['error' => $e->getMessage()]);
                return ['country_code' => null, 'country_name' => null];
            }
        });
    }
    
    protected function lookupMaxMind(string $ip): array
    {
        try {
            $reader = new \GeoIp2\Database\Reader($this->dbPath);
            $record = $reader->city($ip);
            
            return [
                'country_code' => $record->country->isoCode,
                'country_name' => $record->country->name,
                'city' => $record->city->name,
                'region' => $record->mostSpecificSubdivision->name,
                'latitude' => $record->location->latitude,
                'longitude' => $record->location->longitude
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('MaxMind lookup failed', ['error' => $e->getMessage()]);
            return ['country_code' => null, 'country_name' => null];
        }
    }
    
    protected function lookupApi(string $ip): array
    {
        try {
            $response = Http::timeout(2)->get("https://ipapi.co/{$ip}/json/");
            
            if ($response->successful() && !$response->json('error')) {
                return [
                    'country_code' => $response->json('country_code'),
                    'country_name' => $response->json('country_name'),
                    'city' => $response->json('city'),
                    'region' => $response->json('region'),
                    'latitude' => $response->json('latitude'),
                    'longitude' => $response->json('longitude')
                ];
            }
            
            return ['country_code' => null, 'country_name' => null];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('API lookup failed', ['error' => $e->getMessage()]);
            return ['country_code' => null, 'country_name' => null];
        }
    }
    
    protected function isPrivateIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }
}