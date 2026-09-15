<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteEventsProviderHeader extends Model
{
    protected $table = 'website_events_provider_header';

    protected $fillable = [
        'client_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'customer_status',
        'created_date',
        'last_order_date',
        'total_orders',
        'total_spent',
        'average_order_value',
        'days_since_last_order',
        'marketing_opt_in',
        'number_of_products_purchased',
        'default_country',
        'default_city',
        'tags',
        'customer_note',
        'x_platforms_risk_score',
        'x_platforms_risk_level',
    ];

    protected $casts = [
        'created_date'         => 'datetime',
        'last_order_date'      => 'datetime',
        'total_spent'          => 'decimal:2',
        'average_order_value'  => 'decimal:2',
        'marketing_opt_in'     => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function details()
    {
        return $this->hasMany(WebsiteEventsProviderDetail::class, 'header_id');
    }

    public static function providerOverviewStats(): array
    {
        $wepCustomerTotal   = self::count();
        $wepTotalSpent      = round((float) self::sum('total_spent'), 2);
        $wepAvgOrderValue   = round((float) self::avg('average_order_value'), 2);
        $wepOptInCount      = self::where('marketing_opt_in', true)->count();
        $wepOptInRate       = $wepCustomerTotal > 0
            ? round($wepOptInCount / $wepCustomerTotal * 100, 1)
            : 0;

        $wepRiskBreakdown = self::whereNotNull('x_platforms_risk_level')
            ->selectRaw('x_platforms_risk_level as level, COUNT(*) as total')
            ->groupBy('x_platforms_risk_level')
            ->orderByDesc('total')
            ->get()
            ->map(fn($r) => [
                'level' => ucfirst($r->level),
                'total' => (int) $r->total,
            ])->toArray();

        $wepOrderTotal   = WebsiteEventsProviderDetail::count();
        $wepOrderRevenue = round((float) WebsiteEventsProviderDetail::sum('order_total'), 2);

        $wepPaymentMethods = WebsiteEventsProviderDetail::whereNotNull('payment_method')
            ->selectRaw('payment_method, COUNT(*) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn($r) => [
                'method' => $r->payment_method,
                'total'  => (int) $r->total,
            ])->toArray();

        $wepFulfillmentBreakdown = WebsiteEventsProviderDetail::whereNotNull('fulfillment_status')
            ->selectRaw('fulfillment_status as status, COUNT(*) as total')
            ->groupBy('fulfillment_status')
            ->orderByDesc('total')
            ->get()
            ->map(fn($r) => [
                'status' => ucfirst($r->status),
                'total'  => (int) $r->total,
            ])->toArray();

        $wepOrderStatusBreakdown = WebsiteEventsProviderDetail::whereNotNull('order_status')
            ->selectRaw('order_status as status, COUNT(*) as total')
            ->groupBy('order_status')
            ->orderByDesc('total')
            ->get()
            ->map(fn($r) => [
                'status' => ucfirst($r->status),
                'total'  => (int) $r->total,
            ])->toArray();

        $wepFinancialStatusBreakdown = WebsiteEventsProviderDetail::whereNotNull('financial_status')
            ->selectRaw('financial_status as status, COUNT(*) as total')
            ->groupBy('financial_status')
            ->orderByDesc('total')
            ->get()
            ->map(fn($r) => [
                'status' => ucfirst($r->status),
                'total'  => (int) $r->total,
            ])->toArray();

        $wepCustomerStatusBreakdown = self::whereNotNull('customer_status')
            ->selectRaw('customer_status as status, COUNT(*) as total')
            ->groupBy('customer_status')
            ->orderByDesc('total')
            ->get()
            ->map(fn($r) => [
                'status' => strtoupper($r->status) === $r->status ? $r->status : ucfirst($r->status),
                'total'  => (int) $r->total,
            ])->toArray();

        $wepTopProducts = WebsiteEventsProviderDetail::whereNotNull('product_name')
            ->selectRaw('product_name, SUM(quantity) as units, SUM(order_total) as revenue')
            ->groupBy('product_name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get()
            ->map(fn($r) => [
                'product'  => $r->product_name,
                'units'    => (int) $r->units,
                'revenue'  => round((float) $r->revenue, 2),
            ])->toArray();

        $wepTopCountries = self::whereNotNull('default_country')
            ->selectRaw('default_country as country, COUNT(*) as total')
            ->groupBy('default_country')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn($r) => [
                'country' => $r->country,
                'total'   => (int) $r->total,
            ])->toArray();

        $wepAvgDaysSinceLastOrder = round((float) self::avg('days_since_last_order'), 1);
        $wepTotalDiscount = round((float) WebsiteEventsProviderDetail::sum('discount'), 2);
        $wepTotalTax      = round((float) WebsiteEventsProviderDetail::sum('tax'), 2);
        $wepTotalShipping = round((float) WebsiteEventsProviderDetail::sum('shipping'), 2);
        $wepCurrency      = WebsiteEventsProviderDetail::whereNotNull('currency')->value('currency') ?? 'USD';

        return compact(
            'wepCustomerTotal', 'wepTotalSpent', 'wepAvgOrderValue',
            'wepOptInCount', 'wepOptInRate', 'wepRiskBreakdown',
            'wepOrderTotal', 'wepOrderRevenue', 'wepPaymentMethods',
            'wepFulfillmentBreakdown', 'wepOrderStatusBreakdown',
            'wepFinancialStatusBreakdown', 'wepCustomerStatusBreakdown',
            'wepTopProducts', 'wepTopCountries', 'wepAvgDaysSinceLastOrder',
            'wepTotalDiscount', 'wepTotalTax', 'wepTotalShipping', 'wepCurrency'
        );
    }
}
