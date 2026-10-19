<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Keeps Ask Mira "Analyse my website" and "Compare with a competitor"
 * results for a while, so each can be opened on its own page
 * (route mira.report) instead of under the chat widget. Each result gets an
 * unguessable id; nothing but the public-site analysis is stored.
 */
class AskMiraReportStore
{
    private const TTL_HOURS = 24;

    /**
     * @param 'analysis'|'comparison' $type
     * @param bool                    $premium Mira Premium's look (and its open, unlocked report)
     *
     * @return string the report page URL
     */
    public function store(string $type, array $data, bool $premium): string
    {
        $id = (string) Str::uuid();
        Cache::put($this->key($id), ['type' => $type, 'premium' => $premium, 'data' => $data], now()->addHours(self::TTL_HOURS));

        return route('mira.report', $id);
    }

    /** @return array{type: string, premium: bool, data: array}|null */
    public function find(string $id): ?array
    {
        return Str::isUuid($id) ? Cache::get($this->key($id)) : null;
    }

    private function key(string $id): string
    {
        return "askmira_report:{$id}";
    }
}
