<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AnalyticsReportController extends Controller
{
    public function index()
    {
        $client = Auth::guard('client')->user();
        try {
            $reports = DB::table('custom_reports')->where('client_id', $client->id)->latest()->paginate(10);
        } catch (\Exception $e) {
            $reports = collect();
        }
        return view('client.reports.custom.index', compact('reports'));
    }

    public function create()
    {
        $client = Auth::guard('client')->user();
        $availableMetrics = $this->getAvailableMetrics();
        $availableDimensions = $this->getAvailableDimensions();
        return view('client.reports.custom.create', compact('availableMetrics', 'availableDimensions'));
    }

    public function store(Request $request)
    {
        $client = Auth::guard('client')->user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'metrics' => 'required|array|min:1',
            'dimensions' => 'nullable|array',
            'date_range' => 'required|in:7d,30d,90d,1y,custom',
            'chart_type' => 'required|in:table,line,bar,pie,doughnut,area',
        ]);

        try {
            $id = DB::table('custom_reports')->insertGetId([
                'client_id' => $client->id,
                'name' => $validated['name'],
                'description' => $validated['description'],
                'metrics' => json_encode($validated['metrics']),
                'dimensions' => json_encode($validated['dimensions'] ?? []),
                'date_range' => $validated['date_range'],
                'chart_type' => $validated['chart_type'],
                'status' => 'active',
                'share_token' => Str::random(32),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return redirect()->route('client.reports.custom.index')->with('success', 'Report created.');
        } catch (\Exception $e) {
            return back()->with('error', 'Could not create report: ' . $e->getMessage());
        }
    }

    public function show($report)
    {
        $client = Auth::guard('client')->user();
        try {
            $report = DB::table('custom_reports')->where('id', $report)->where('client_id', $client->id)->first();
            if (!$report) abort(404);
            $report->metrics = json_decode($report->metrics ?? '[]', true);
            $report->dimensions = json_decode($report->dimensions ?? '[]', true);
        } catch (\Exception $e) {
            abort(404);
        }
        return view('client.reports.custom.show', compact('report'));
    }

    public function edit($report)
    {
        $client = Auth::guard('client')->user();
        try {
            $report = DB::table('custom_reports')->where('id', $report)->where('client_id', $client->id)->first();
            if (!$report) abort(404);
            $report->metrics = json_decode($report->metrics ?? '[]', true);
            $report->dimensions = json_decode($report->dimensions ?? '[]', true);
        } catch (\Exception $e) {
            abort(404);
        }
        $availableMetrics = $this->getAvailableMetrics();
        $availableDimensions = $this->getAvailableDimensions();
        return view('client.reports.custom.edit', compact('report', 'availableMetrics', 'availableDimensions'));
    }

    public function update(Request $request, $report)
    {
        $client = Auth::guard('client')->user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'metrics' => 'required|array|min:1',
            'dimensions' => 'nullable|array',
            'date_range' => 'required|in:7d,30d,90d,1y,custom',
            'chart_type' => 'required|in:table,line,bar,pie,doughnut,area',
        ]);

        try {
            DB::table('custom_reports')->where('id', $report)->where('client_id', $client->id)->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'metrics' => json_encode($validated['metrics']),
                'dimensions' => json_encode($validated['dimensions'] ?? []),
                'date_range' => $validated['date_range'],
                'chart_type' => $validated['chart_type'],
                'updated_at' => now(),
            ]);
            return redirect()->route('client.reports.custom.index')->with('success', 'Report updated.');
        } catch (\Exception $e) {
            return back()->with('error', 'Could not update report.');
        }
    }

    public function destroy($report)
    {
        $client = Auth::guard('client')->user();
        try {
            DB::table('custom_reports')->where('id', $report)->where('client_id', $client->id)->delete();
        } catch (\Exception $e) {}
        return redirect()->route('client.reports.custom.index')->with('success', 'Report deleted.');
    }

    public function export(Request $request, $report, string $format)
    {
        return response()->json(['message' => 'Export not yet implemented']);
    }

    public function schedule(Request $request, $report)
    {
        return response()->json(['message' => 'Scheduling not yet implemented']);
    }

    public function share(Request $request, $report)
    {
        return response()->json(['message' => 'Sharing not yet implemented']);
    }

    private function getAvailableMetrics(): array
    {
        return [
            ['key' => 'visitors', 'label' => 'Total Visitors', 'category' => 'website'],
            ['key' => 'pageviews', 'label' => 'Page Views', 'category' => 'website'],
            ['key' => 'sent', 'label' => 'Emails Sent', 'category' => 'email'],
            ['key' => 'open_rate', 'label' => 'Open Rate', 'category' => 'email'],
            ['key' => 'contacts', 'label' => 'Total Contacts', 'category' => 'crm'],
            ['key' => 'deals', 'label' => 'Total Deals', 'category' => 'crm'],
            ['key' => 'followers', 'label' => 'Followers', 'category' => 'social'],
            ['key' => 'revenue', 'label' => 'Total Revenue', 'category' => 'transactions'],
        ];
    }

    private function getAvailableDimensions(): array
    {
        return [
            ['key' => 'date', 'label' => 'Date'],
            ['key' => 'week', 'label' => 'Week'],
            ['key' => 'month', 'label' => 'Month'],
            ['key' => 'platform', 'label' => 'Platform'],
            ['key' => 'device', 'label' => 'Device'],
        ];
    }
}
