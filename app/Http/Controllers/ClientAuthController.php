<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DataSource;
use App\Models\AnalyticsConfig;
use App\Models\MicroSignalCategory;
use App\Models\Action;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Monarobase\CountryList\CountryListFacade as Countries;
use App\Models\Industry;
use App\Models\AnalysisLayer;
use Illuminate\Support\Facades\DB;
use App\Models\Prediction;

class ClientAuthController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /** Redirect to register start if the registration session is missing. */
    private function requireRegistrationSession()
    {
        if (!session('registration.client_id')) {
            return redirect()->route('client.register');
        }
        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Login
    // ─────────────────────────────────────────────────────────────────────────

    public function showLogin()
    {
        if (Auth::guard('client')->check()) {
            return redirect()->route('client.dashboard');
        }
        return view('client.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'string', 'email:rfc'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('client')->attempt(
            $request->only('email', 'password'),
            $request->boolean('remember')
        )) {
            $client = Auth::guard('client')->user();

            if (in_array($client->status, ['suspended', 'cancelled'])) {
                Auth::guard('client')->logout();
                return back()
                    ->with('error', 'Your account is ' . $client->status . '. Please contact support.')
                    ->withInput();
            }

            $request->session()->regenerate();
            $client->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            return redirect()->intended(route('client.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Step 1 — Company Info
    // ─────────────────────────────────────────────────────────────────────────

    public function showRegister()
    {
        // Only block if already logged in AND not in a registration flow
        if (Auth::guard('client')->check() && !session('registration.client_id')) {
            return redirect()->route('client.dashboard');
        }

        $countries = Countries::getList('en');
        return view('client.auth.register', compact('countries'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'size'         => ['required', 'string'],
            'country'      => ['required', 'string'],
        ]);

        $client = Client::create([
            'company_name' => $request->company_name,
            'name'         => $request->company_name,
            'size'         => $request->size,
            'country'      => $request->country,
            'email'        => 'pending_' . uniqid() . '@pending.com',
            'password'     => Hash::make(uniqid()),
            'status'       => 'pending',
        ]);

        session(['registration.client_id' => $client->id]);

        return redirect()->route('client.industry');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Step 2 — Industry
    // ─────────────────────────────────────────────────────────────────────────

    public function showIndustry()
    {
        if ($guard = $this->requireRegistrationSession()) return $guard;

        $industries = Industry::whereNull('client_id')->get();
        return view('client.auth.industry', compact('industries'));
    }

    public function storeIndustry(Request $request)
    {
        if ($guard = $this->requireRegistrationSession()) return $guard;

        $request->validate([
            'industry_id' => ['required', 'exists:industries,id'],
        ]);

        Client::findOrFail(session('registration.client_id'))->update([
            'industry_id' => $request->industry_id,
        ]);

        session(['registration.industry_id' => $request->industry_id]);

        return redirect()->route('client.register.analysis.layers');
    }

    // Kept for any seeded custom industry (route still exists, just no UI button)
    public function storeCustomIndustry(Request $request)
    {
        if ($guard = $this->requireRegistrationSession()) return $guard;

        $request->validate(['name' => ['required', 'string', 'max:255']]);

        $industry = Industry::create([
            'name'        => $request->name,
            'signals'     => [],
            'predictions' => [],
            'is_custom'   => true,
            'client_id'   => session('registration.client_id'),
        ]);

        return response()->json($industry);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Step 3 — Analysis Layers
    // ─────────────────────────────────────────────────────────────────────────

    public function showAnalysisLayers()
    {
        if ($guard = $this->requireRegistrationSession()) return $guard;

        if (!session('registration.industry_id')) {
            return redirect()->route('client.industry');
        }

        $layers         = AnalysisLayer::where('is_active', 1)->orderBy('sort_order')->get();
        $client         = Client::findOrFail(session('registration.client_id'));
        $selectedLayers = $client->analysisLayers()->pluck('analysis_layers.id')->toArray();

        return view('client.auth.analysis-layers', compact('layers', 'selectedLayers'));
    }

    public function storeAnalysisLayers(Request $request)
    {
        if ($guard = $this->requireRegistrationSession()) return $guard;

        $l1Id = AnalysisLayer::where('code', 'L1')->value('id');

        $request->validate([
            'layers'   => ['required', 'array', 'min:3'],
            'layers.*' => ['exists:analysis_layers,id'],
        ], [
            'layers.required' => 'Please select at least 3 layers.',
            'layers.min'      => 'Please select at least 3 layers.',
        ]);

        if ($l1Id && !in_array((string) $l1Id, array_map('strval', $request->layers))) {
            return back()->withErrors(['layers' => 'L1 (Data Collection) is required.'])->withInput();
        }

        $client = Client::findOrFail(session('registration.client_id'));
        $client->analysisLayers()->sync($request->layers);

        // Always sort by sort_order so the sequence is deterministic
        $orderedIds = AnalysisLayer::whereIn('id', $request->layers)
            ->orderBy('sort_order')
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        session(['selected_layers' => $orderedIds]);

        return redirect()->route('client.register.connect.data.source', [
            'layer_id' => $orderedIds[0],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Step 4 — Connect Data Source
    // ─────────────────────────────────────────────────────────────────────────

    public function showConnectDataSource(Request $request)
    {
        if ($guard = $this->requireRegistrationSession()) return $guard;

        $selectedLayerIds = session('selected_layers', []);

        if (empty($selectedLayerIds)) {
            return redirect()->route('client.register.analysis.layers');
        }

        // layer_id comes from query string on GET
        $layerId = (int) $request->query('layer_id', $selectedLayerIds[0]);

        if (!in_array($layerId, $selectedLayerIds)) {
            $layerId = $selectedLayerIds[0];
        }

        $position    = (int) array_search($layerId, $selectedLayerIds);
        $total       = count($selectedLayerIds);
        $isFirst     = $position === 0;
        $isLast      = $position === $total - 1;
        $prevLayerId = $isFirst ? null : $selectedLayerIds[$position - 1];

        $currentLayer       = AnalysisLayer::findOrFail($layerId);
        $sources            = DataSource::where('analysis_layer_id', $layerId)->get();
        $client             = Client::findOrFail(session('registration.client_id'));
        $previouslySelected = $client->selectedSources()->pluck('data_source_id')->map(fn($id) => (int) $id)->toArray();

        return view('client.auth.connect-data-source', compact(
            'sources', 'currentLayer', 'layerId', 'selectedLayerIds',
            'previouslySelected', 'isFirst', 'isLast', 'prevLayerId',
            'position', 'total'
        ));
    }

    public function storeConnectDataSource(Request $request)
    {
        if ($guard = $this->requireRegistrationSession()) return $guard;

        $selectedLayerIds = session('selected_layers', []);

        if (empty($selectedLayerIds)) {
            return redirect()->route('client.register.analysis.layers');
        }

        // layer_id comes from hidden field in the POST body
        $layerId = (int) $request->input('layer_id');

        if (!in_array($layerId, $selectedLayerIds)) {
            return redirect()->route('client.register.connect.data.source', [
                'layer_id' => $selectedLayerIds[0],
            ]);
        }

        $client = Client::findOrFail(session('registration.client_id'));

        // Require selection only if this layer has sources
        if (DataSource::where('analysis_layer_id', $layerId)->exists()) {
            $request->validate([
                'data_sources'   => ['required', 'array', 'min:1'],
                'data_sources.*' => ['exists:data_sources,id'],
            ], [
                'data_sources.required' => 'Please select at least one data source.',
                'data_sources.min'      => 'Please select at least one data source.',
            ]);

            $layerSourceIds = DataSource::where('analysis_layer_id', $layerId)->pluck('id');
            $client->selectedSources()->detach($layerSourceIds);
            $client->selectedSources()->attach($request->data_sources);
        }

        $position  = (int) array_search($layerId, $selectedLayerIds);
        $nextIndex = $position + 1;

        if ($nextIndex < count($selectedLayerIds)) {
            return redirect()->route('client.register.connect.data.source', [
                'layer_id' => $selectedLayerIds[$nextIndex],
            ]);
        }

        return redirect()->route('client.register.micro.signals');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Step 5 — Micro Signals
    // ─────────────────────────────────────────────────────────────────────────

    public function showMicroSignals()
    {
        if ($guard = $this->requireRegistrationSession()) return $guard;

        $client = Client::findOrFail(session('registration.client_id'));

        if (!$client->industry_id) {
            return redirect()->route('client.industry');
        }

        $industry = Industry::findOrFail($client->industry_id);

        $analyticsConfig = AnalyticsConfig::firstOrCreate(
            ['client_id' => $client->id],
            ['config_name' => 'Default Config', 'industry' => $industry->name, 'status' => 'draft']
        );

        $categories = MicroSignalCategory::with(['microSignals' => function ($q) use ($industry) {
            $q->where('is_active', true)
              ->where(fn($q2) => $q2->whereNull('industry')->orWhere('industry', $industry->name))
              ->orderBy('sort_order');
        }])->orderBy('sort_order')->get();

        $selectedSignals = $analyticsConfig->microSignals()->pluck('micro_signals.id')
            ->map(fn($id) => (int) $id)->toArray();

        return view('client.auth.micro-signals', compact('industry', 'categories', 'selectedSignals'));
    }

    public function storeMicroSignals(Request $request)
    {
        if ($guard = $this->requireRegistrationSession()) return $guard;

        $client = Client::findOrFail(session('registration.client_id'));

        $request->validate([
            'signals'   => ['required', 'array', 'min:1'],
            'signals.*' => ['integer', 'exists:micro_signals,id'],
        ], [
            'signals.required' => 'Please select at least one micro-signal from each category.',
            'signals.min'      => 'Please select at least one micro-signal from each category.',
        ]);

        // Per-category check: at least 1 per category that has signals
        $industry   = Industry::findOrFail($client->industry_id);
        $categories = MicroSignalCategory::with(['microSignals' => function ($q) use ($industry) {
            $q->where('is_active', true)
              ->where(fn($q2) => $q2->whereNull('industry')->orWhere('industry', $industry->name));
        }])->orderBy('sort_order')->get();

        $submitted = collect($request->signals)->map(fn($id) => (int) $id);

        foreach ($categories as $category) {
            if ($category->microSignals->isEmpty()) continue;
            $categoryIds = $category->microSignals->pluck('id');
            if ($submitted->intersect($categoryIds)->isEmpty()) {
                return back()->withInput()->withErrors([
                    'signals' => 'Please select at least one signal from "' . $category->name . '".',
                ]);
            }
        }

        $analyticsConfig = AnalyticsConfig::firstOrCreate(
            ['client_id' => $client->id],
            ['config_name' => 'Default Config', 'industry' => $industry->name, 'status' => 'draft']
        );

        $analyticsConfig->microSignals()->sync($request->signals);
        session(['selected_micro_signals' => $request->signals]);

        return redirect()->route('client.register.select.predictions');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Step 6 — Select Predictions
    // ─────────────────────────────────────────────────────────────────────────

    public function showSelectPredictions()
    {
        if ($guard = $this->requireRegistrationSession()) return $guard;

        $client = Client::findOrFail(session('registration.client_id'));

        if (!$client->industry_id) {
            return redirect()->route('client.industry');
        }

        $industry = Industry::with('predictionModels')
            ->findOrFail($client->industry_id);

        $selectedPredictionIds = $client->predictions()
            ->wherePivot('industry_id', $industry->id)
            ->pluck('predictions.id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        return view('client.auth.select-predictions', [
            'industry'              => $industry,
            'predictions'           => $industry->predictionModels,
            'selectedPredictionIds' => $selectedPredictionIds,
        ]);
    }

    public function storeSelectPredictions(Request $request)
    {
        if ($guard = $this->requireRegistrationSession()) return $guard;

        $client   = Client::findOrFail(session('registration.client_id'));
        $industry = Industry::with('predictionModels')->findOrFail($client->industry_id);

        $validIds = $industry->predictionModels->pluck('id')->toArray();

        $request->validate([
            'predictions'   => ['required', 'array', 'min:1'],
            'predictions.*' => ['required', 'integer', 'in:' . implode(',', $validIds)],
        ], [
            'predictions.required' => 'Please select at least one prediction.',
            'predictions.min'      => 'Please select at least one prediction.',
        ]);

        DB::table('client_predictions')
            ->where('client_id', $client->id)
            ->where('industry_id', $industry->id)
            ->delete();

        DB::table('client_predictions')->insert(
            collect($request->predictions)->map(fn($id) => [
                'client_id'     => $client->id,
                'industry_id'   => $industry->id,
                'prediction_id' => $id,
                'created_at'    => now(),
                'updated_at'    => now(),
            ])->all()
        );

        session(['selected_predictions' => $request->predictions]);

        return redirect()->route('client.register.configure.actions');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Step 7 — Configure Actions
    // ─────────────────────────────────────────────────────────────────────────

    public function showConfigureActions()
    {
        if ($guard = $this->requireRegistrationSession()) return $guard;

        $client            = Client::findOrFail(session('registration.client_id'));
        $actions           = Action::where('is_active', true)->orderBy('sort_order')->get();
        $selectedActionIds = $client->actions()->pluck('actions.id')->map(fn($id) => (int) $id)->toArray();

        return view('client.auth.configure-actions', compact('actions', 'selectedActionIds'));
    }

    public function storeConfigureActions(Request $request)
    {
        if ($guard = $this->requireRegistrationSession()) return $guard;

        $client = Client::findOrFail(session('registration.client_id'));

        $request->validate([
            'actions'   => ['required', 'array', 'min:1'],
            'actions.*' => ['integer', 'exists:actions,id'],
        ], [
            'actions.required' => 'Please select at least one action.',
            'actions.min'      => 'Please select at least one action.',
        ]);

        $client->actions()->sync($request->actions);

        // Clear registration session
        session()->forget([
            'registration.client_id',
            'registration.industry_id',
            'selected_layers',
            'selected_data_sources',
            'selected_micro_signals',
            'selected_predictions',
            'selected_actions',
        ]);

        // Auto-login and go straight to dashboard
        Auth::guard('client')->login($client);
        $request->session()->regenerate();

        return redirect()->route('client.dashboard')
            ->with('success', 'Welcome! Your company has been registered successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Logout
    // ─────────────────────────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        Auth::guard('client')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('client.login');
    }
}
