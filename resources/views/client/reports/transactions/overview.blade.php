@extends('layouts.platform')

@section('title', 'Transaction Analytics - Overview')

@section('content')
@php
  $cn = auth('client')->user()?->company_name ?? 'Test Company';
  $av = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ',$cn),0,2))));
  $period = request('period', '30d');
@endphp

<div class="flex flex-col h-full overflow-hidden bg-white">

  <header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="{{ route('client.reports.executive-dashboard') }}"
         class="flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition"
         title="Back to Dashboard">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </a>
      <div>
        <h1 class="text-[16px] font-semibold text-gray-900">Transaction Analytics</h1>
        <p class="text-[11px] text-gray-500 mt-0.5">Track revenue, orders, and payment methods</p>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <select class="form-input" style="width: 130px; cursor: pointer;" onchange="window.location.href='{ request()->url() }?period='+this.value">
        <option value="7d" { $period == '7d' ? 'selected' : '' }>Last 7 Days</option>
        <option value="30d" { $period == '30d' ? 'selected' : '' }>Last 30 Days</option>
        <option value="90d" { $period == '90d' ? 'selected' : '' }>Last 90 Days</option>
        <option value="1y" { $period == '1y' ? 'selected' : '' }>Last Year</option>
      </select>
      <a href="{ request()->url() }/export/pdf" class="btn-secondary flex items-center gap-2" style="text-decoration: none;">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Export
      </a>
      <div style="position:relative" id="l1AvatarWrap">
        <button onclick="var d=document.getElementById('l1Dropdown');d.style.display=d.style.display==='block'?'none':'block'"
                style="width:32px;height:32px;border-radius:50%;background:#06b6d4;display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:700;border:none;cursor:pointer">
          {{ $av }}
        </button>
        <div id="l1Dropdown"
             style="display:none;position:absolute;right:0;top:40px;width:192px;background:#fff;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.12);border:1px solid #e5e7eb;padding:4px 0;z-index:999">
          <div style="padding:8px 16px;border-bottom:1px solid #f3f4f6">
            <p style="font-size:12px;font-weight:600;color:#111827;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $cn }}</p>
            <p style="font-size:10px;color:#9ca3af;margin:2px 0 0">Client Account</p>
          </div>
          <a href="{{ route('client.dashboard') }}"
             style="display:flex;align-items:center;gap:8px;padding:8px 16px;font-size:12px;color:#374151;text-decoration:none"
             onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
            <svg width="14" height="14" fill="none" stroke="#9ca3af" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Profile Settings
          </a>
          <hr style="margin:4px 0;border:none;border-top:1px solid #f3f4f6">
          <form method="POST" action="{{ route('client.logout') }}">
            @csrf
            <button type="submit"
                    style="width:100%;display:flex;align-items:center;gap:8px;padding:8px 16px;font-size:12px;color:#dc2626;background:none;border:none;cursor:pointer;text-align:left"
                    onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
              <svg width="14" height="14" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
              </svg>
              Log Out
            </button>
          </form>
        </div>
      </div>
    </div>
  </header>

  <div class="flex-1 overflow-y-auto px-5 py-5">

    <div class="flex items-center gap-1 mb-5 border-b border-gray-200 pb-0">
      <a href="{{ route('client.reports.transactions.overview') }}" class="px-4 py-2.5 text-[12px] font-medium rounded-t-lg transition text-blue-600 border-b-2 border-blue-600 bg-blue-50/50" style="text-decoration: none;">
        Overview
      </a>
      <a href="{{ route('client.reports.transactions.revenue') }}" class="px-4 py-2.5 text-[12px] font-medium rounded-t-lg transition text-gray-500 hover:text-gray-700 hover:bg-gray-50" style="text-decoration: none;">
        Revenue
      </a>
      <a href="{{ route('client.reports.transactions.sales-funnel') }}" class="px-4 py-2.5 text-[12px] font-medium rounded-t-lg transition text-gray-500 hover:text-gray-700 hover:bg-gray-50" style="text-decoration: none;">
        Sales Funnel
      </a>
      <a href="{{ route('client.reports.transactions.payment-methods') }}" class="px-4 py-2.5 text-[12px] font-medium rounded-t-lg transition text-gray-500 hover:text-gray-700 hover:bg-gray-50" style="text-decoration: none;">
        Payments
      </a>
      <a href="{{ route('client.reports.transactions.refunds') }}" class="px-4 py-2.5 text-[12px] font-medium rounded-t-lg transition text-gray-500 hover:text-gray-700 hover:bg-gray-50" style="text-decoration: none;">
        Refunds
      </a>
      <a href="{{ route('client.reports.transactions.customer-ltv') }}" class="px-4 py-2.5 text-[12px] font-medium rounded-t-lg transition text-gray-500 hover:text-gray-700 hover:bg-gray-50" style="text-decoration: none;">
        Customer LTV
      </a>
    </div>

    @if($data['has_data'] ?? false)
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-5">
      <div class="grid grid-cols-2 md:grid-cols-4">
        <div class="p-4 border-b md:border-b-0 md:border-r border-gray-100">
          <div class="w-8 h-8 rounded-lg flex items-center justify-center mb-3" style="background:#3b82f6;color:#fff;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
          </div>
          <p class="text-[11px] text-gray-500 font-semibold uppercase tracking-wide mb-1">Total Revenue</p>
          <div class="flex items-center gap-2">
            <p class="text-[20px] font-bold text-gray-900">${{ number_format($data['total_revenue'] ?? 0, 2) }}</p>
            <a href="{{ route('client.reports.transactions.revenue') }}" class="inline-flex items-center gap-1 text-[10px] font-bold rounded-full px-2.5 py-1" style="background:#eff6ff;color:#3b82f6;text-decoration:none;">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
              View
            </a>
          </div>
        </div>
        <div class="p-4 border-b md:border-b-0 md:border-r border-gray-100">
          <div class="w-8 h-8 rounded-lg flex items-center justify-center mb-3" style="background:#7c3aed;color:#fff;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m-9 3a1 1 0 102 0 1 1 0 00-2 0zm9 0a1 1 0 102 0 1 1 0 00-2 0z"/></svg>
          </div>
          <p class="text-[11px] text-gray-500 font-semibold uppercase tracking-wide mb-1">Total Orders</p>
          <div class="flex items-center gap-2">
            <p class="text-[20px] font-bold text-gray-900">{{ number_format($data['total_orders'] ?? 0) }}</p>
            <a href="{{ route('client.reports.transactions.sales-funnel') }}" class="inline-flex items-center gap-1 text-[10px] font-bold rounded-full px-2.5 py-1" style="background:#f5f3ff;color:#7c3aed;text-decoration:none;">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
              View
            </a>
          </div>
        </div>
        <div class="p-4 border-b md:border-b-0 md:border-r border-gray-100">
          <div class="w-8 h-8 rounded-lg flex items-center justify-center mb-3" style="background:#db2777;color:#fff;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.59 13.41L11 3.83A2 2 0 009.59 3.24L3 3v6.59a2 2 0 00.59 1.41l9.58 9.58a2 2 0 002.83 0l4.59-4.59a2 2 0 000-2.83z"/><circle cx="7.5" cy="7.5" r="1.5"/></svg>
          </div>
          <p class="text-[11px] text-gray-500 font-semibold uppercase tracking-wide mb-1">Avg Order Value</p>
          <div class="flex items-center gap-2">
            <p class="text-[20px] font-bold text-gray-900">${{ number_format($data['avg_order_value'] ?? 0, 2) }}</p>
            <a href="{{ route('client.reports.transactions.revenue') }}" class="inline-flex items-center gap-1 text-[10px] font-bold rounded-full px-2.5 py-1" style="background:#fdf2f8;color:#db2777;text-decoration:none;">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
              View
            </a>
          </div>
        </div>
        <div class="p-4">
          <div class="w-8 h-8 rounded-lg flex items-center justify-center mb-3" style="background:#ef4444;color:#fff;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          </div>
          <p class="text-[11px] text-gray-500 font-semibold uppercase tracking-wide mb-1">Refund Rate</p>
          <div class="flex items-center gap-2">
            <p class="text-[20px] font-bold text-gray-900">{{ $data['refund_rate'] ?? 0 }}%</p>
            <a href="{{ route('client.reports.transactions.refunds') }}" class="inline-flex items-center gap-1 text-[10px] font-bold rounded-full px-2.5 py-1" style="background:#fef2f2;color:#ef4444;text-decoration:none;">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
              View
            </a>
          </div>
        </div>
      </div>
    </div>
    @else
    <div class="bg-white border border-gray-200 rounded-xl p-8 text-center">
      <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
        </svg>
      </div>
      <h3 class="text-[14px] font-semibold text-gray-800 mb-1">No Payments data yet</h3>
      <p class="text-[12px] text-gray-500 mb-4">Connect your Payments account to start tracking analytics.</p>
      <a href="{{ route('client.payment-gateway-connections.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-600 text-white text-[12px] font-medium rounded-lg hover:bg-gray-700 transition" style="text-decoration: none;">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Connect Payments
      </a>
    </div>
    @endif

  </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('click', function(e) {
  var wrap = document.getElementById('l1AvatarWrap');
  var drop = document.getElementById('l1Dropdown');
  if (wrap && drop && !wrap.contains(e.target)) drop.style.display = 'none';
});
</script>
@endsection
