<?php

namespace App\Services;

use App\Models\Action;
use App\Models\AnalysisLayer;
use App\Models\AnalyticsConfig;
use App\Models\Client;
use App\Models\Industry;
use App\Models\MicroSignal;
use Illuminate\Support\Facades\DB;

/**
 * Seeds a newly registered client with a working configuration derived from
 * their industry, so onboarding can end at "pick an industry" instead of
 * walking the client through five configuration screens they have no context
 * for yet. Everything seeded here is editable afterwards under /app/setup/*.
 *
 * Each relation is only seeded when it is currently empty, so re-running this
 * (e.g. the client changes industry later) never discards deliberate choices.
 */
class IndustryDefaults
{
    public function apply(Client $client, Industry $industry): void
    {
        DB::transaction(function () use ($client, $industry) {
            $this->seedLayers($client);
            $this->seedMicroSignals($client, $industry);
            $this->seedPredictions($client, $industry);
            $this->seedActions($client);
        });
    }

    /** Every active layer is enabled by default — the pipeline runs end to end. */
    private function seedLayers(Client $client): void
    {
        if ($client->analysisLayers()->exists()) {
            return;
        }

        $layerIds = AnalysisLayer::where('is_active', 1)
            ->orderBy('sort_order')
            ->pluck('id');

        if ($layerIds->isNotEmpty()) {
            $client->analysisLayers()->sync($layerIds);
        }
    }

    /**
     * Attach every active signal that is either generic (industry_id null) or
     * specific to this industry. The client prunes from there rather than
     * building the list up from nothing.
     */
    private function seedMicroSignals(Client $client, Industry $industry): void
    {
        $config = $this->analyticsConfig($client, $industry);

        if ($config->microSignals()->exists()) {
            return;
        }

        $signalIds = MicroSignal::where('is_active', true)
            ->where(fn($q) => $q->whereNull('industry_id')->orWhere('industry_id', $industry->id))
            ->orderBy('sort_order')
            ->pluck('id');

        if ($signalIds->isNotEmpty()) {
            $config->microSignals()->sync($signalIds);
        }
    }

    /** Prediction models are industry-scoped in the pivot, so seed per industry. */
    private function seedPredictions(Client $client, Industry $industry): void
    {
        $alreadySet = DB::table('client_predictions')
            ->where('client_id', $client->id)
            ->where('industry_id', $industry->id)
            ->exists();

        if ($alreadySet) {
            return;
        }

        $rows = $industry->predictionModels->map(fn($prediction) => [
            'client_id'     => $client->id,
            'industry_id'   => $industry->id,
            'prediction_id' => $prediction->id,
            'created_at'    => now(),
            'updated_at'    => now(),
        ])->all();

        if ($rows) {
            DB::table('client_predictions')->insert($rows);
        }
    }

    private function seedActions(Client $client): void
    {
        if ($client->actions()->exists()) {
            return;
        }

        $actionIds = Action::where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('id');

        if ($actionIds->isNotEmpty()) {
            $client->actions()->sync($actionIds);
        }
    }

    /** The client's AnalyticsConfig row, created on first use. */
    public function analyticsConfig(Client $client, Industry $industry): AnalyticsConfig
    {
        return AnalyticsConfig::firstOrCreate(
            ['client_id' => $client->id],
            [
                'config_name' => 'Default Config',
                'industry_id' => $industry->id,
                'status'      => 'draft',
            ]
        );
    }
}
