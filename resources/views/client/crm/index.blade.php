@extends('layouts.client')

@section('title', 'CRM Connections')

@section('content')
<div class="px-4 sm:px-0 max-w-4xl">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">CRM Connections</h2>
            <p class="text-sm text-gray-500 mt-0.5">Connect your CRM to sync contacts and power behavioral scoring.</p>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="mb-5 flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-lg px-4 py-3">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="mb-5 flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    @php
        $hubspot     = $connections->firstWhere('provider', 'hubspot');
        $salesforce  = $connections->firstWhere('provider', 'salesforce');
    @endphp

    {{-- ── HubSpot card ──────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-4">

        <div class="flex items-center gap-4 px-6 py-5 border-b border-gray-100">
            <div class="w-11 h-11 rounded-lg flex items-center justify-center flex-shrink-0"
                 style="background:#fff3ec; border:1px solid #fddccc;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
                    <path d="M15.54 8.27V5.74a1.66 1.66 0 0 0 .96-1.5V4.2a1.66 1.66 0 0 0-3.32 0v.04a1.66 1.66 0 0 0 .96 1.5v2.53a4.72 4.72 0 0 0-2.24 1.04L5.4 5.07a1.84 1.84 0 1 0-.88 1.56l6.37 4.11a4.74 4.74 0 0 0-.62 2.34 4.72 4.72 0 0 0 .9 2.78l-1.93 1.93a1.57 1.57 0 1 0 1.1 1.07l2.07-2.07a4.72 4.72 0 1 0 3.13-8.52Zm0 6.92a2.37 2.37 0 1 1 0-4.74 2.37 2.37 0 0 1 0 4.74Z" fill="#FF7A59"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-base font-semibold text-gray-900">HubSpot CRM</p>
                <p class="text-xs text-gray-400">Sync contacts, deals &amp; engagement signals</p>
            </div>

            @if($hubspot)
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                    @if($hubspot->status === 'connected') bg-emerald-50 text-emerald-700 border border-emerald-200
                    @elseif($hubspot->status === 'syncing') bg-blue-50 text-blue-700 border border-blue-200
                    @else bg-red-50 text-red-600 border border-red-200 @endif">
                    {{ ucfirst($hubspot->status) }}
                </span>
            @else
                <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-gray-100 text-gray-500 border border-gray-200">
                    Not connected
                </span>
            @endif
        </div>

        @if($hubspot)
        <div class="px-6 py-5">
            <div class="grid grid-cols-3 gap-4 mb-5">
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <p class="text-xl font-bold text-gray-900">{{ number_format($hubspot->contacts_synced) }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Contacts synced</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $hubspot->hub_domain ?? '—' }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Portal domain</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <p class="text-sm font-semibold text-gray-900">
                        {{ $hubspot->last_sync_at ? $hubspot->last_sync_at->diffForHumans() : 'Never' }}
                    </p>
                    <p class="text-xs text-gray-500 mt-0.5">Last synced</p>
                </div>
            </div>

            @if($hubspot->status === 'error' && $hubspot->last_error)
            <div class="mb-4 flex items-start gap-2 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ $hubspot->last_error }}</span>
            </div>
            @endif

            <div class="mb-5">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">What gets imported</p>
                <div class="flex flex-wrap gap-2">
                    @foreach(['Email opens → engagement score', 'Email clicks → trust score', 'Conversions → loyalty score', 'Support contacts → frustration score', 'Sequence enrolments → intent score'] as $signal)
                    <span class="text-xs bg-indigo-50 text-indigo-700 border border-indigo-100 px-2.5 py-1 rounded-full">{{ $signal }}</span>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                <form method="POST" action="{{ route('client.crm.sync', $hubspot) }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition"
                            @if($hubspot->status === 'syncing') disabled @endif>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        {{ $hubspot->status === 'syncing' ? 'Syncing…' : 'Sync Now' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('client.crm.destroy', $hubspot) }}"
                      onsubmit="return confirm('Disconnect HubSpot? Synced contacts and scores will remain.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-medium transition">Disconnect</button>
                </form>
            </div>
        </div>

        @else
        <div class="px-6 py-8 text-center">
            <p class="text-sm text-gray-500 mb-1">Import your HubSpot contacts to start behavioral scoring.</p>
            <p class="text-xs text-gray-400 mb-6">Email opens, clicks, conversions, and deal activity are mapped to your intelligence pipeline automatically.</p>

            <a href="{{ route('client.crm.hubspot.connect') }}"
               class="inline-flex items-center gap-2 font-semibold text-sm px-5 py-2.5 rounded-lg text-white transition"
               style="background:#FF7A59;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <path d="M15.54 8.27V5.74a1.66 1.66 0 0 0 .96-1.5V4.2a1.66 1.66 0 0 0-3.32 0v.04a1.66 1.66 0 0 0 .96 1.5v2.53a4.72 4.72 0 0 0-2.24 1.04L5.4 5.07a1.84 1.84 0 1 0-.88 1.56l6.37 4.11a4.74 4.74 0 0 0-.62 2.34 4.72 4.72 0 0 0 .9 2.78l-1.93 1.93a1.57 1.57 0 1 0 1.1 1.07l2.07-2.07a4.72 4.72 0 1 0 3.13-8.52Zm0 6.92a2.37 2.37 0 1 1 0-4.74 2.37 2.37 0 0 1 0 4.74Z" fill="white"/>
                </svg>
                Connect HubSpot
            </a>

            <div class="mt-8 text-left">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3 text-center">How HubSpot data maps to your pipeline</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach([
                        ['HubSpot email open',       'email_open event',        'Engagement + Trust scores'],
                        ['HubSpot email click',      'email_click event',       'Trust + Buying Readiness'],
                        ['Conversion event',         'checkout_complete event', 'Loyalty + Overall score'],
                        ['Support contact note',     'support_ticket event',    'Frustration + Churn scores'],
                        ['Sequence enrolment',       'pricing_view event',      'Intent score'],
                        ['Last sales activity date', 'last_active_at',          'Reactivation Potential'],
                    ] as [$from, $event, $score])
                    <div class="flex items-start gap-3 bg-gray-50 rounded-lg px-3 py-2.5">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-gray-700 truncate">{{ $from }}</p>
                            <p class="text-[11px] text-indigo-600 truncate">→ {{ $event }}</p>
                        </div>
                        <span class="text-[10px] text-gray-400 flex-shrink-0 mt-0.5">{{ $score }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- ── Salesforce card ────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-4">

        <div class="flex items-center gap-4 px-6 py-5 border-b border-gray-100">
            <div class="w-11 h-11 rounded-lg flex items-center justify-center flex-shrink-0"
                 style="background:#e8f4fb; border:1px solid #b3d9ef;">
                {{-- Salesforce cloud icon --}}
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10.07 5.5a3.93 3.93 0 0 1 3.07 1.48 4.91 4.91 0 0 1 2.36-.6 4.94 4.94 0 0 1 4.94 4.94 4.94 4.94 0 0 1-.28 1.65A3.7 3.7 0 0 1 21 16.3a3.7 3.7 0 0 1-3.7 3.7H7.1A4.1 4.1 0 0 1 3 15.9a4.1 4.1 0 0 1 2.6-3.82A4.93 4.93 0 0 1 5.5 10.5a4.57 4.57 0 0 1 4.57-5Z" fill="#00A1E0"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-base font-semibold text-gray-900">Salesforce</p>
                <p class="text-xs text-gray-400">Sync opportunities &amp; cases via SOQL</p>
            </div>

            @if($salesforce)
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                    @if($salesforce->status === 'connected') bg-emerald-50 text-emerald-700 border border-emerald-200
                    @elseif($salesforce->status === 'syncing') bg-blue-50 text-blue-700 border border-blue-200
                    @else bg-red-50 text-red-600 border border-red-200 @endif">
                    {{ ucfirst($salesforce->status) }}
                </span>
            @else
                <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-gray-100 text-gray-500 border border-gray-200">
                    Not connected
                </span>
            @endif
        </div>

        @if($salesforce)
        <div class="px-6 py-5">
            <div class="grid grid-cols-3 gap-4 mb-5">
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <p class="text-xl font-bold text-gray-900">{{ number_format($salesforce->contacts_synced) }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Records synced</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $salesforce->account_name ?? '—' }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Organisation</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <p class="text-sm font-semibold text-gray-900">
                        {{ $salesforce->last_sync_at ? $salesforce->last_sync_at->diffForHumans() : 'Never' }}
                    </p>
                    <p class="text-xs text-gray-500 mt-0.5">Last synced</p>
                </div>
            </div>

            @if($salesforce->status === 'error' && $salesforce->last_error)
            <div class="mb-4 flex items-start gap-2 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ $salesforce->last_error }}</span>
            </div>
            @endif

            <div class="mb-5">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">What gets imported</p>
                <div class="flex flex-wrap gap-2">
                    @foreach(['Closed Won → loyalty score', 'Closed Lost → dropoff risk', 'Open opportunities → buying readiness', 'Cases → frustration score', 'All opportunities → intent score'] as $signal)
                    <span class="text-xs bg-indigo-50 text-indigo-700 border border-indigo-100 px-2.5 py-1 rounded-full">{{ $signal }}</span>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                <form method="POST" action="{{ route('client.crm.sync', $salesforce) }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition"
                            @if($salesforce->status === 'syncing') disabled @endif>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        {{ $salesforce->status === 'syncing' ? 'Syncing…' : 'Sync Now' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('client.crm.destroy', $salesforce) }}"
                      onsubmit="return confirm('Disconnect Salesforce? Synced records and scores will remain.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-medium transition">Disconnect</button>
                </form>
            </div>
        </div>

        @else
        <div class="px-6 py-8 text-center">
            <p class="text-sm text-gray-500 mb-1">Import Salesforce opportunities and cases to power behavioral scoring.</p>
            <p class="text-xs text-gray-400 mb-6">Deal stages, case priority, and contact activity are mapped to your intelligence pipeline via SOQL.</p>

            <a href="{{ route('client.crm.salesforce.connect') }}"
               class="inline-flex items-center gap-2 font-semibold text-sm px-5 py-2.5 rounded-lg text-white transition"
               style="background:#00A1E0;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <path d="M10.07 5.5a3.93 3.93 0 0 1 3.07 1.48 4.91 4.91 0 0 1 2.36-.6 4.94 4.94 0 0 1 4.94 4.94 4.94 4.94 0 0 1-.28 1.65A3.7 3.7 0 0 1 21 16.3a3.7 3.7 0 0 1-3.7 3.7H7.1A4.1 4.1 0 0 1 3 15.9a4.1 4.1 0 0 1 2.6-3.82A4.93 4.93 0 0 1 5.5 10.5a4.57 4.57 0 0 1 4.57-5Z" fill="white"/>
                </svg>
                Connect Salesforce
            </a>

            <div class="mt-8 text-left">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3 text-center">How Salesforce data maps to your pipeline</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach([
                        ['Opportunity — Closed Won',   'checkout_complete event', 'Loyalty + Trust scores'],
                        ['Opportunity — Closed Lost',  'form_abandon event',      'Dropoff Risk score'],
                        ['Opportunity — Open/Active',  'checkout_start event',    'Buying Readiness score'],
                        ['Any opportunity',            'pricing_view event',      'Intent score'],
                        ['Case (any)',                 'support_ticket event',    'Frustration + Churn scores'],
                        ['Case — High/Critical',       'support_ticket ×2',       'Amplified Frustration score'],
                    ] as [$from, $event, $score])
                    <div class="flex items-start gap-3 bg-gray-50 rounded-lg px-3 py-2.5">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-gray-700 truncate">{{ $from }}</p>
                            <p class="text-[11px] text-indigo-600 truncate">→ {{ $event }}</p>
                        </div>
                        <span class="text-[10px] text-gray-400 flex-shrink-0 mt-0.5">{{ $score }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- ── Coming soon: Zoho & Pipedrive ─────────────────────────────────── --}}
    <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
        @foreach([
            ['Zoho CRM',   '#e31e24'],
            ['Pipedrive',  '#1a1a1a'],
        ] as [$name, $color])
        <div class="bg-white rounded-xl border border-dashed border-gray-200 p-4 flex items-center gap-3 opacity-60">
            <div class="w-8 h-8 rounded-md flex-shrink-0" style="background:{{ $color }}20; border:1px solid {{ $color }}40;"></div>
            <div>
                <p class="text-sm font-medium text-gray-600">{{ $name }}</p>
                <p class="text-xs text-gray-400">Coming soon</p>
            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection
