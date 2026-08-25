<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\BusinessInsight;
use App\Services\BusinessIntelligenceService;
use App\Services\Llm\AnthropicException;
use App\Services\Llm\AnthropicRefusedException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GrowthReportController extends Controller
{
    /** Minimum time between on-demand regenerations — each one is a billed API call. */
    private const REGENERATE_COOLDOWN_MINUTES = 60;

    public function __construct(private BusinessIntelligenceService $insights) {}

    public function index() { return redirect()->route('client.reports.growth.business-health'); }

    public function businessHealth()
    {
        $client   = Auth::guard('client')->user();
        $snapshot = $client->businessHealthSnapshot;

        $data = [
            'has_snapshot'  => (bool) $snapshot,
            'has_data'      => $this->insights->hasEnoughData($client),
            'health_score'  => $snapshot->health_score ?? null,
            'summary'       => $snapshot->summary ?? null,
            'strengths'     => $snapshot->strengths ?? [],
            'weaknesses'    => $snapshot->weaknesses ?? [],
            'opportunities' => $snapshot->opportunities ?? [],
            'generated_at'  => $snapshot?->generated_at,
        ];

        return view('client.reports.growth.business-health', compact('data'));
    }

    public function crossChannel()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $data = ['has_data' => $this->insights->hasEnoughData($client)];
        return view('client.reports.growth.cross-channel', compact('data', 'period'));
    }

    public function recommendations()
    {
        $client = Auth::guard('client')->user();

        $recommendations = BusinessInsight::where('client_id', $client->id)
            ->orderByRaw("field(impact_level, 'critical', 'high', 'medium', 'low')")
            ->orderByDesc('confidence_score')
            ->get()
            ->map(fn($insight) => [
                'priority' => in_array($insight->impact_level, ['critical', 'high']) ? 'high'
                    : ($insight->impact_level === 'medium' ? 'medium' : 'low'),
                'title'   => $insight->title,
                'message' => $insight->recommendation,
            ])
            ->all();

        $data = [
            'has_data'        => $this->insights->hasEnoughData($client),
            'recommendations' => $recommendations,
        ];

        return view('client.reports.growth.recommendations', compact('data'));
    }

    public function benchmarks()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $data = ['has_data' => $this->insights->hasEnoughData($client)];
        return view('client.reports.growth.benchmarks', compact('data', 'period'));
    }

    public function trends()
    {
        $client = Auth::guard('client')->user();
        $data = ['has_data' => $this->insights->hasEnoughData($client)];
        return view('client.reports.growth.trends', compact('data'));
    }

    public function getData(Request $request, string $metric)
    {
        return response()->json(['metric' => $metric, 'data' => []]);
    }

    public function export(Request $request, string $format)
    {
        return response()->json(['message' => 'Export not yet implemented']);
    }

    /**
     * On-demand regeneration, triggered from the dashboard. Rate-limited
     * server-side (not just by a disabled button) since this is a billed,
     * multi-second external API call — a client refreshing the page shouldn't
     * be able to trigger a fresh one every time.
     */
    public function generate(Request $request)
    {
        $client = Auth::guard('client')->user();

        $snapshot = $client->businessHealthSnapshot;
        if ($snapshot && $snapshot->generated_at->gt(now()->subMinutes(self::REGENERATE_COOLDOWN_MINUTES))) {
            $wait = self::REGENERATE_COOLDOWN_MINUTES - $snapshot->generated_at->diffInMinutes(now());
            return back()->with('error', "Already regenerated recently. Try again in {$wait} minute(s).");
        }

        if (!$this->insights->hasEnoughData($client)) {
            return back()->with('error', 'Connect a data source first — there\'s nothing to analyze yet.');
        }

        try {
            $this->insights->generate($client);
            return back()->with('success', 'Business health report regenerated.');
        } catch (AnthropicRefusedException $e) {
            return back()->with('error', 'The AI declined to process this request. This is usually a false positive — try again shortly.');
        } catch (AnthropicException $e) {
            report($e);
            return back()->with('error', 'Could not generate the report right now. Please try again later.');
        }
    }

}
