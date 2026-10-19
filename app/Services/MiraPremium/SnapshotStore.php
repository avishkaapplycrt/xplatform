<?php

namespace App\Services\MiraPremium;

use App\Models\MiraPremium\MiraProject;
use App\Models\MiraPremium\MiraSeoSnapshot;

/**
 * Provider responses stored per project and kind (mira_seo_snapshots).
 * remember() returns the latest snapshot younger than cache_hours, or runs
 * the fetch (one or more paid provider calls), stores and returns it — so
 * every paid call happens at most once per project / kind / day, whichever
 * panel asks first. Older rows are kept as history.
 */
class SnapshotStore
{
    /** @return array{payload: array, fetched_at: string} */
    public function remember(MiraProject $project, string $kind, bool $refresh, callable $fetch): array
    {
        $fresh = $refresh ? null : MiraSeoSnapshot::where('project_id', $project->id)->where('kind', $kind)
            ->where('fetched_at', '>=', now()->subHours((int) config('mira_premium.dataforseo.cache_hours', 24)))
            ->latest('fetched_at')->first();

        if ($fresh === null) {
            $fresh = MiraSeoSnapshot::create([
                'project_id' => $project->id, 'kind' => mb_substr($kind, 0, 20), 'provider' => 'dataforseo',
                'payload' => $fetch(), 'fetched_at' => now(),
            ]);
        }

        return ['payload' => $fresh->payload, 'fetched_at' => $fresh->fetched_at->toIso8601String()];
    }
}
