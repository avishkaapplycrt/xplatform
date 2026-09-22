<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\MockMaster\MmMarketingService;
use App\Services\MockMaster\MmRetentionService;
use App\Services\MockMaster\MmSalesService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * The MockMaster subsection of Business Helpers — same three-agent framing
 * (Marketing / Sales / Customer Retention) as the main Business Helpers
 * page, but every question here is answered from the real, imported
 * MockMaster PTE Portal tables (mm_studentuser, mm_payments, mm_purchases,
 * mm_packages, mm_feedbacks, mm_notifications, mm_coupon_usage) instead of
 * this client's own synced CRM/email data.
 */
class MockMasterController extends Controller
{
    public function index(Request $request)
    {
        $agent = $request->query('agent', 'marketing');
        if (!in_array($agent, ['marketing', 'sales', 'retention'], true)) {
            $agent = 'marketing';
        }

        return view('client.mockmaster', [
            'agent' => $agent,
            'marketingPrompts' => app(MmMarketingService::class)->quickPrompts(),
            'salesPrompts' => app(MmSalesService::class)->quickPrompts(),
            'retentionPrompts' => app(MmRetentionService::class)->quickPrompts(),
        ]);
    }

    public function prompt(Request $request): JsonResponse
    {
        $data = $request->validate([
            'agent' => 'required|in:marketing,sales,retention',
            'slug' => 'required|string',
        ]);

        return response()->json($this->service($data['agent'])->answer($data['slug']));
    }

    public function ask(Request $request): JsonResponse
    {
        $data = $request->validate([
            'agent' => 'required|in:marketing,sales,retention',
            'question' => 'nullable|string',
        ]);

        return response()->json($this->service($data['agent'])->ask((string) ($data['question'] ?? '')));
    }

    private function service(string $agent): MmMarketingService|MmSalesService|MmRetentionService
    {
        return match ($agent) {
            'sales' => app(MmSalesService::class),
            'retention' => app(MmRetentionService::class),
            default => app(MmMarketingService::class),
        };
    }
}
