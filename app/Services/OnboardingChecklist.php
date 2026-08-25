<?php

namespace App\Services;

use App\Models\Client;
use App\Models\CrmConnection;
use App\Models\EmailConnection;
use App\Models\WebsiteConnection;
use Illuminate\Support\Facades\DB;

/**
 * Builds the "Finish setup" checklist shown on the client dashboard.
 *
 * Registration only asks for an account and an industry; everything else is
 * seeded from industry defaults (see IndustryDefaults) and surfaced here so the
 * client can review it in context, once they can actually see what it affects.
 */
class OnboardingChecklist
{
    public function for(Client $client): array
    {
        $items = [
            [
                'key'         => 'industry',
                'label'       => 'Choose your industry',
                'description' => $client->industry?->name ?? 'Not selected',
                'done'        => (bool) $client->industry_id,
                'url'         => route('client.industry'),
                'cta'         => 'Change',
            ],
            [
                'key'         => 'layers',
                'label'       => 'Enable intelligence layers',
                'description' => $this->countLabel($client->analysisLayers()->count(), 'layer'),
                'done'        => $client->analysisLayers()->exists(),
                'url'         => route('client.setup.layers'),
                'cta'         => 'Review',
            ],
            [
                'key'         => 'micro-signals',
                'label'       => 'Review behavioral micro-signals',
                'description' => $this->countLabel($this->microSignalCount($client), 'signal'),
                'done'        => $this->microSignalCount($client) > 0,
                'url'         => route('client.setup.micro-signals'),
                'cta'         => 'Review',
            ],
            [
                'key'         => 'predictions',
                'label'       => 'Choose predictive models',
                'description' => $this->countLabel($client->predictions()->count(), 'model'),
                'done'        => $client->predictions()->exists(),
                'url'         => route('client.setup.predictions'),
                'cta'         => 'Review',
            ],
            [
                'key'         => 'actions',
                'label'       => 'Configure automated actions',
                'description' => $this->countLabel($client->actions()->count(), 'action'),
                'done'        => $client->actions()->exists(),
                'url'         => route('client.setup.actions'),
                'cta'         => 'Review',
            ],
            [
                'key'         => 'connection',
                'label'       => 'Connect your first data source',
                'description' => $this->connectionDescription($client),
                'done'        => $this->hasConnection($client),
                'url'         => route('client.website-connections'),
                'cta'         => 'Connect',
            ],
        ];

        $done = collect($items)->where('done', true)->count();

        return [
            'items'     => $items,
            'done'      => $done,
            'total'     => count($items),
            'complete'  => $done === count($items),
            'dismissed' => (bool) $client->onboarding_dismissed_at,
        ];
    }

    /**
     * Whether the client has a live integration sending data. This is the only
     * checklist item industry defaults cannot satisfy — it needs real
     * credentials from the client.
     */
    private function hasConnection(Client $client): bool
    {
        return WebsiteConnection::where('client_id', $client->id)->exists()
            || EmailConnection::where('client_id', $client->id)->exists()
            || CrmConnection::where('client_id', $client->id)->exists()
            || $client->dataSources()->exists();
    }

    private function connectionDescription(Client $client): string
    {
        return $this->hasConnection($client)
            ? 'Receiving data'
            : 'No data source connected yet';
    }

    private function microSignalCount(Client $client): int
    {
        $configId = $client->analyticsConfig?->id;

        if (!$configId) {
            return 0;
        }

        return DB::table('analytics_config_micro_signal')
            ->where('analytics_config_id', $configId)
            ->count();
    }

    private function countLabel(int $count, string $noun): string
    {
        if ($count === 0) {
            return 'None selected';
        }

        return $count . ' ' . $noun . ($count === 1 ? '' : 's') . ' enabled';
    }
}
