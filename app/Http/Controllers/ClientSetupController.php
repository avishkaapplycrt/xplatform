<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\AnalysisLayer;
use App\Models\DataSource;
use App\Models\Industry;
use App\Models\MicroSignalCategory;
use App\Services\IndustryDefaults;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * The configuration that used to be steps 3–7 of registration.
 *
 * These are now optional, authenticated pages reachable from the dashboard
 * checklist. Each one is standalone — it saves and returns to the dashboard
 * rather than chaining into the next screen — and every one already has a
 * sensible default applied by IndustryDefaults, so nothing here is required
 * to get a working account.
 */
class ClientSetupController extends Controller
{
    public function __construct(private IndustryDefaults $defaults) {}

    // ─────────────────────────────────────────────────────────────────────────
    // Intelligence layers
    // ─────────────────────────────────────────────────────────────────────────

    public function layers()
    {
        $client = auth('client')->user();

        return view('client.setup.layers', [
            'layers'         => AnalysisLayer::where('is_active', 1)->orderBy('sort_order')->get(),
            'selectedLayers' => $client->analysisLayers()->pluck('analysis_layers.id')
                ->map(fn($id) => (int) $id)->toArray(),
        ]);
    }

    public function updateLayers(Request $request)
    {
        $request->validate([
            'layers'   => ['required', 'array', 'min:1'],
            'layers.*' => ['exists:analysis_layers,id'],
        ], [
            'layers.required' => 'Select at least one layer.',
            'layers.min'      => 'Select at least one layer.',
        ]);

        // L1 feeds every other layer — without it the pipeline has no input.
        $l1Id = AnalysisLayer::where('code', 'L1')->value('id');
        $selected = array_map('intval', $request->layers);

        if ($l1Id && !in_array((int) $l1Id, $selected, true)) {
            $selected[] = (int) $l1Id;
        }

        auth('client')->user()->analysisLayers()->sync($selected);

        return $this->done('Intelligence layers updated.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Data sources — every layer on one page, no per-layer wizard
    // ─────────────────────────────────────────────────────────────────────────

    public function dataSources()
    {
        $client = auth('client')->user();

        $layerIds = $client->analysisLayers()->pluck('analysis_layers.id');

        $sourcesByLayer = DataSource::whereIn('analysis_layer_id', $layerIds)
            ->get()
            ->groupBy('analysis_layer_id');

        $layers = AnalysisLayer::whereIn('id', $layerIds)
            ->orderBy('sort_order')
            ->get()
            ->filter(fn($layer) => $sourcesByLayer->has($layer->id));

        return view('client.setup.data-sources', [
            'layers'         => $layers,
            'sourcesByLayer' => $sourcesByLayer,
            'selected'       => $client->selectedSources()->pluck('data_source_id')
                ->map(fn($id) => (int) $id)->toArray(),
        ]);
    }

    public function updateDataSources(Request $request)
    {
        $request->validate([
            'data_sources'   => ['nullable', 'array'],
            'data_sources.*' => ['exists:data_sources,id'],
        ]);

        auth('client')->user()->selectedSources()->sync($request->input('data_sources', []));

        return $this->done('Data sources updated.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Micro-signals
    // ─────────────────────────────────────────────────────────────────────────

    public function microSignals()
    {
        $client   = auth('client')->user();
        $industry = Industry::findOrFail($client->industry_id);
        $config   = $this->defaults->analyticsConfig($client, $industry);

        $categories = MicroSignalCategory::with(['microSignals' => function ($q) use ($industry) {
            $q->where('is_active', true)
              ->where(fn($q2) => $q2->whereNull('industry_id')->orWhere('industry_id', $industry->id))
              ->orderBy('sort_order');
        }])->orderBy('sort_order')->get();

        return view('client.setup.micro-signals', [
            'industry'        => $industry,
            'categories'      => $categories,
            'selectedSignals' => $config->microSignals()->pluck('micro_signals.id')
                ->map(fn($id) => (int) $id)->toArray(),
        ]);
    }

    public function updateMicroSignals(Request $request)
    {
        $request->validate([
            'signals'   => ['nullable', 'array'],
            'signals.*' => ['integer', 'exists:micro_signals,id'],
        ]);

        $client   = auth('client')->user();
        $industry = Industry::findOrFail($client->industry_id);

        $this->defaults->analyticsConfig($client, $industry)
            ->microSignals()
            ->sync($request->input('signals', []));

        return $this->done('Micro-signals updated.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Predictive models
    // ─────────────────────────────────────────────────────────────────────────

    public function predictions()
    {
        $client   = auth('client')->user();
        $industry = Industry::with('predictionModels')->findOrFail($client->industry_id);

        return view('client.setup.predictions', [
            'industry'    => $industry,
            'predictions' => $industry->predictionModels,
            'selectedIds' => $client->predictions()
                ->wherePivot('industry_id', $industry->id)
                ->pluck('predictions.id')
                ->map(fn($id) => (int) $id)->toArray(),
        ]);
    }

    public function updatePredictions(Request $request)
    {
        $client   = auth('client')->user();
        $industry = Industry::with('predictionModels')->findOrFail($client->industry_id);
        $validIds = $industry->predictionModels->pluck('id')->all();

        $request->validate([
            'predictions'   => ['nullable', 'array'],
            'predictions.*' => ['integer', 'in:' . implode(',', $validIds ?: [0])],
        ]);

        DB::transaction(function () use ($request, $client, $industry) {
            DB::table('client_predictions')
                ->where('client_id', $client->id)
                ->where('industry_id', $industry->id)
                ->delete();

            $rows = collect($request->input('predictions', []))->map(fn($id) => [
                'client_id'     => $client->id,
                'industry_id'   => $industry->id,
                'prediction_id' => $id,
                'created_at'    => now(),
                'updated_at'    => now(),
            ])->all();

            if ($rows) {
                DB::table('client_predictions')->insert($rows);
            }
        });

        return $this->done('Predictive models updated.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Automated actions
    // ─────────────────────────────────────────────────────────────────────────

    public function actions()
    {
        $client = auth('client')->user();

        return view('client.setup.actions', [
            'actions'     => Action::where('is_active', true)->orderBy('sort_order')->get(),
            'selectedIds' => $client->actions()->pluck('actions.id')
                ->map(fn($id) => (int) $id)->toArray(),
        ]);
    }

    public function updateActions(Request $request)
    {
        $request->validate([
            'actions'   => ['nullable', 'array'],
            'actions.*' => ['integer', 'exists:actions,id'],
        ]);

        auth('client')->user()->actions()->sync($request->input('actions', []));

        return $this->done('Automated actions updated.');
    }

    private function done(string $message)
    {
        return redirect()->route('client.dashboard')->with('success', $message);
    }
}
