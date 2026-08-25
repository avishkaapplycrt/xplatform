@extends('layouts.platform')

@section('title', 'Executive Dashboard')

@section('content')
@php
  $cn = auth('client')->user()?->company_name ?? 'Test Company';
  $av = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ',$cn),0,2))));
@endphp

<div class="flex flex-col h-full overflow-hidden bg-white">

  {{-- HEADER --}}
  <header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <div>
        <h1 class="text-[16px] font-semibold text-gray-900">Executive Dashboard</h1>
        <p class="text-[11px] text-gray-500 mt-0.5">Unified view of all your connected platforms</p>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <select class="form-input" style="width: 130px; cursor: pointer;" id="periodSelector" onchange="changePeriod(this.value)">
        <option value="7d">Last 7 Days</option>
        <option value="30d" selected>Last 30 Days</option>
        <option value="90d">Last 90 Days</option>
        <option value="1y">Last Year</option>
      </select>
      <a href="{{ route('client.reports.executive-dashboard.export', 'pdf') }}" class="btn-secondary flex items-center gap-2" style="text-decoration: none;">
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

  {{-- BODY --}}
  <div class="flex-1 overflow-y-auto px-5 py-5">

    {{-- Health Score + Growth Indicators --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
      {{-- Business Health --}}
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-[11px] text-gray-500 font-medium">Business Health</p>
            <h3 class="text-[20px] font-bold text-gray-900">{{ $businessHealthScore }}<span class="text-[13px] text-gray-400 font-normal">/100</span></h3>
          </div>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-1.5 mb-2">
          <div class="h-1.5 rounded-full transition-all duration-500" style="width: {{ $businessHealthScore }}%; background: {{ $businessHealthScore >= 70 ? '#10b981' : ($businessHealthScore >= 40 ? '#f59e0b' : '#ef4444') }}"></div>
        </div>
        <p class="text-[11px] text-gray-500">
          @if($businessHealthScore >= 70) <span class="text-green-600 font-medium">Good</span> — performing well
          @elseif($businessHealthScore >= 40) <span class="text-amber-600 font-medium">Fair</span> — room to improve
          @else <span class="text-red-600 font-medium">Needs Attention</span>
          @endif
        </p>
      </div>

      {{-- Growth Indicators --}}
      @foreach($growthIndicators as $key => $indicator)
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background: {{ $indicator['color'] === 'success' ? '#f0fdf4' : ($indicator['color'] === 'danger' ? '#fef2f2' : '#eff6ff') }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: {{ $indicator['color'] === 'success' ? '#16a34a' : ($indicator['color'] === 'danger' ? '#dc2626' : '#2563eb') }}">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
          </div>
          <div>
            <p class="text-[11px] text-gray-500 font-medium">{{ ucwords(str_replace('_', ' ', $key)) }}</p>
            <h3 class="text-[20px] font-bold text-gray-900">{{ $indicator['value'] }}</h3>
          </div>
        </div>
        <p class="text-[11px]" style="color: {{ $indicator['color'] === 'success' ? '#16a34a' : ($indicator['color'] === 'danger' ? '#dc2626' : '#2563eb') }}">
          <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M{{ $indicator['trend'] === 'up' ? '5 10l7-7m0 0l7 7m-7-7v18' : '19 14l-7 7m0 0l-7-7m7 7V3' }}"/>
          </svg>
          vs last period
        </p>
      </div>
      @endforeach
    </div>

    {{-- Platform Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-5">

      {{-- Website --}}
      <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
              <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
              </svg>
            </div>
            <h6 class="text-[13px] font-semibold text-gray-800">Website</h6>
          </div>
          <a href="{{ route('client.reports.website.overview') }}" class="text-[11px] text-blue-600 hover:text-blue-700 font-medium">Details →</a>
        </div>
        <div class="p-4">
          @if($metrics['website']['has_data'] ?? false)
          <div class="grid grid-cols-2 gap-3">
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-[10px] text-gray-500 mb-1">Visitors</p>
              <p class="text-[15px] font-bold text-gray-900">{{ number_format($metrics['website']['total_visitors'] ?? 0) }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-[10px] text-gray-500 mb-1">Pageviews</p>
              <p class="text-[15px] font-bold text-gray-900">{{ number_format($metrics['website']['total_pageviews'] ?? 0) }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-[10px] text-gray-500 mb-1">Bounce Rate</p>
              <p class="text-[15px] font-bold text-gray-900">{{ $metrics['website']['bounce_rate'] ?? 0 }}%</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-[10px] text-gray-500 mb-1">Avg Session</p>
              <p class="text-[15px] font-bold text-gray-900">{{ $metrics['website']['avg_session_duration'] ?? '0m' }}</p>
            </div>
          </div>
          @else
          <div class="text-center py-6">
            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
              <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
              </svg>
            </div>
            <p class="text-[12px] text-gray-500 mb-3">No website data connected</p>
            <a href="{{ route('client.website-connections') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-[12px] font-medium rounded-lg hover:bg-blue-700 transition" style="text-decoration: none;">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
              </svg>
              Connect Website
            </a>
          </div>
          @endif
        </div>
      </div>

      {{-- Email --}}
      <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center">
              <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
            </div>
            <h6 class="text-[13px] font-semibold text-gray-800">Email</h6>
          </div>
          <a href="{{ route('client.reports.email.overview') }}" class="text-[11px] text-green-600 hover:text-green-700 font-medium">Details →</a>
        </div>
        <div class="p-4">
          @if($metrics['email']['has_data'] ?? false)
          <div class="grid grid-cols-2 gap-3">
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-[10px] text-gray-500 mb-1">Sent</p>
              <p class="text-[15px] font-bold text-gray-900">{{ number_format($metrics['email']['total_sent'] ?? 0) }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-[10px] text-gray-500 mb-1">Open Rate</p>
              <p class="text-[15px] font-bold text-gray-900">{{ $metrics['email']['open_rate'] ?? 0 }}%</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-[10px] text-gray-500 mb-1">Click Rate</p>
              <p class="text-[15px] font-bold text-gray-900">{{ $metrics['email']['click_rate'] ?? 0 }}%</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-[10px] text-gray-500 mb-1">Bounce Rate</p>
              <p class="text-[15px] font-bold text-gray-900">{{ $metrics['email']['bounce_rate'] ?? 0 }}%</p>
            </div>
          </div>
          @else
          <div class="text-center py-6">
            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
              <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
            </div>
            <p class="text-[12px] text-gray-500 mb-3">No email data connected</p>
            <a href="{{ route('client.email-connections') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-[12px] font-medium rounded-lg hover:bg-green-700 transition" style="text-decoration: none;">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
              </svg>
              Connect Email
            </a>
          </div>
          @endif
        </div>
      </div>

      {{-- CRM --}}
      <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-cyan-50 flex items-center justify-center">
              <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
            </div>
            <h6 class="text-[13px] font-semibold text-gray-800">CRM</h6>
          </div>
          <a href="{{ route('client.reports.crm.overview') }}" class="text-[11px] text-cyan-600 hover:text-cyan-700 font-medium">Details →</a>
        </div>
        <div class="p-4">
          @if($metrics['crm']['has_data'] ?? false)
          <div class="grid grid-cols-2 gap-3">
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-[10px] text-gray-500 mb-1">Contacts</p>
              <p class="text-[15px] font-bold text-gray-900">{{ number_format($metrics['crm']['total_contacts'] ?? 0) }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-[10px] text-gray-500 mb-1">Deals</p>
              <p class="text-[15px] font-bold text-gray-900">{{ number_format($metrics['crm']['total_deals'] ?? 0) }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-[10px] text-gray-500 mb-1">Win Rate</p>
              <p class="text-[15px] font-bold text-gray-900">{{ $metrics['crm']['win_rate'] ?? 0 }}%</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-[10px] text-gray-500 mb-1">Pipeline</p>
              <p class="text-[15px] font-bold text-gray-900">${{ number_format($metrics['crm']['pipeline_value'] ?? 0) }}</p>
            </div>
          </div>
          @else
          <div class="text-center py-6">
            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
              <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
            </div>
            <p class="text-[12px] text-gray-500 mb-3">No CRM data connected</p>
            <a href="{{ route('client.crm-connections') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-cyan-600 text-white text-[12px] font-medium rounded-lg hover:bg-cyan-700 transition" style="text-decoration: none;">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
              </svg>
              Connect CRM
            </a>
          </div>
          @endif
        </div>
      </div>

      {{-- Social --}}
      <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
              <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
              </svg>
            </div>
            <h6 class="text-[13px] font-semibold text-gray-800">Social Media</h6>
          </div>
          <a href="{{ route('client.reports.social.overview') }}" class="text-[11px] text-amber-600 hover:text-amber-700 font-medium">Details →</a>
        </div>
        <div class="p-4">
          @if($metrics['social']['has_data'] ?? false)
          <div class="grid grid-cols-2 gap-3">
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-[10px] text-gray-500 mb-1">Followers</p>
              <p class="text-[15px] font-bold text-gray-900">{{ number_format($metrics['social']['total_followers'] ?? 0) }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-[10px] text-gray-500 mb-1">Engagement</p>
              <p class="text-[15px] font-bold text-gray-900">{{ $metrics['social']['engagement_rate'] ?? 0 }}%</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-[10px] text-gray-500 mb-1">Posts</p>
              <p class="text-[15px] font-bold text-gray-900">{{ number_format($metrics['social']['total_posts'] ?? 0) }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-[10px] text-gray-500 mb-1">Sentiment</p>
              <p class="text-[15px] font-bold text-gray-900">{{ $metrics['social']['sentiment_score'] ?? 0 }}/100</p>
            </div>
          </div>
          @else
          <div class="text-center py-6">
            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
              <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
              </svg>
            </div>
            <p class="text-[12px] text-gray-500 mb-3">No social data connected</p>
            <a href="{{ route('client.social-connections') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 text-white text-[12px] font-medium rounded-lg hover:bg-amber-600 transition" style="text-decoration: none;">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
              </svg>
              Connect Social
            </a>
          </div>
          @endif
        </div>
      </div>

      {{-- Chat & Support --}}
      <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center">
              <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
              </svg>
            </div>
            <h6 class="text-[13px] font-semibold text-gray-800">Chat & Support</h6>
          </div>
          <a href="{{ route('client.reports.chat-support.overview') }}" class="text-[11px] text-red-600 hover:text-red-700 font-medium">Details →</a>
        </div>
        <div class="p-4">
          @if($metrics['chat_support']['has_data'] ?? false)
          <div class="grid grid-cols-2 gap-3">
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-[10px] text-gray-500 mb-1">Conversations</p>
              <p class="text-[15px] font-bold text-gray-900">{{ number_format($metrics['chat_support']['total_conversations'] ?? 0) }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-[10px] text-gray-500 mb-1">Response</p>
              <p class="text-[15px] font-bold text-gray-900">{{ $metrics['chat_support']['avg_response_time'] ?? '0m' }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-[10px] text-gray-500 mb-1">CSAT</p>
              <p class="text-[15px] font-bold text-gray-900">{{ $metrics['chat_support']['csat_score'] ?? 0 }}/5</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-[10px] text-gray-500 mb-1">Open</p>
              <p class="text-[15px] font-bold text-gray-900">{{ number_format($metrics['chat_support']['open_tickets'] ?? 0) }}</p>
            </div>
          </div>
          @else
          <div class="text-center py-6">
            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
              <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
              </svg>
            </div>
            <p class="text-[12px] text-gray-500 mb-3">No chat data connected</p>
            <a href="{{ route('client.chat-support-connections') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-red-500 text-white text-[12px] font-medium rounded-lg hover:bg-red-600 transition" style="text-decoration: none;">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
              </svg>
              Connect Chat
            </a>
          </div>
          @endif
        </div>
      </div>

      {{-- Transactions --}}
      <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">
              <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
              </svg>
            </div>
            <h6 class="text-[13px] font-semibold text-gray-800">Transactions</h6>
          </div>
          <a href="{{ route('client.reports.transactions.overview') }}" class="text-[11px] text-gray-600 hover:text-gray-800 font-medium">Details →</a>
        </div>
        <div class="p-4">
          @if($metrics['transactions']['has_data'] ?? false)
          <div class="grid grid-cols-2 gap-3">
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-[10px] text-gray-500 mb-1">Revenue</p>
              <p class="text-[15px] font-bold text-gray-900">${{ number_format($metrics['transactions']['total_revenue'] ?? 0, 2) }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-[10px] text-gray-500 mb-1">Orders</p>
              <p class="text-[15px] font-bold text-gray-900">{{ number_format($metrics['transactions']['total_orders'] ?? 0) }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-[10px] text-gray-500 mb-1">AOV</p>
              <p class="text-[15px] font-bold text-gray-900">${{ number_format($metrics['transactions']['avg_order_value'] ?? 0, 2) }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-[10px] text-gray-500 mb-1">Refund</p>
              <p class="text-[15px] font-bold text-gray-900">{{ $metrics['transactions']['refund_rate'] ?? 0 }}%</p>
            </div>
          </div>
          @else
          <div class="text-center py-6">
            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
              <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
              </svg>
            </div>
            <p class="text-[12px] text-gray-500 mb-3">No transaction data connected</p>
            <a href="{{ route('client.payment-gateway-connections.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-700 text-white text-[12px] font-medium rounded-lg hover:bg-gray-800 transition" style="text-decoration: none;">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
              </svg>
              Connect Payments
            </a>
          </div>
          @endif
        </div>
      </div>
    </div>

    {{-- Recommendations --}}
    @if(count($recommendations) > 0)
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-5">
      <div class="px-4 py-3 border-b border-gray-100">
        <h6 class="text-[13px] font-semibold text-gray-800 flex items-center gap-2">
          <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
          </svg>
          Growth Recommendations
        </h6>
      </div>
      <div class="divide-y divide-gray-100">
        @foreach($recommendations as $rec)
        <div class="px-4 py-3 flex items-start gap-3">
          <span class="flex-shrink-0 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide
            @if($rec['priority'] === 'high') bg-red-100 text-red-700
            @elseif($rec['priority'] === 'medium') bg-amber-100 text-amber-700
            @else bg-blue-100 text-blue-700 @endif">
            {{ $rec['priority'] }}
          </span>
          <div class="flex-grow-1 min-w-0">
            <p class="text-[12px] font-semibold text-gray-800">{{ $rec['title'] }}</p>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ $rec['message'] }}</p>
            <a href="{{ $rec['action'] }}" class="text-[11px] text-blue-600 hover:text-blue-700 font-medium mt-1 inline-block" style="text-decoration: none;">{{ $rec['action_text'] }} →</a>
          </div>
        </div>
        @endforeach
      </div>
    </div>
    @endif

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
          <h6 class="text-[13px] font-semibold text-gray-800">Revenue Trend</h6>
        </div>
        <div class="p-4">
          <canvas id="revenueChart" height="260"></canvas>
        </div>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
          <h6 class="text-[13px] font-semibold text-gray-800">Channel Distribution</h6>
        </div>
        <div class="p-4">
          <canvas id="channelChart" height="260"></canvas>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function changePeriod(period) {
  window.location.href = '{{ route('client.reports.executive-dashboard') }}?period=' + period;
}

// Close dropdown on outside click
document.addEventListener('click', function(e) {
  var wrap = document.getElementById('l1AvatarWrap');
  var drop = document.getElementById('l1Dropdown');
  if (wrap && drop && !wrap.contains(e.target)) drop.style.display = 'none';
});

// Revenue Trend Chart
const revenueCtx = document.getElementById('revenueChart');
if (revenueCtx) {
  new Chart(revenueCtx, {
    type: 'line',
    data: {
      labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
      datasets: [{
        label: 'Revenue ($)',
        data: [12000, 15000, 13000, 18000],
        borderColor: '#4e73df',
        backgroundColor: 'rgba(78, 115, 223, 0.08)',
        tension: 0.4,
        fill: true,
        pointRadius: 4,
        pointBackgroundColor: '#4e73df',
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: '#f3f4f6' },
          ticks: {
            font: { size: 10 },
            callback: function(value) { return '$' + (value / 1000) + 'k'; }
          }
        },
        x: {
          grid: { display: false },
          ticks: { font: { size: 10 } }
        }
      }
    }
  });
}

// Channel Distribution Chart
const channelCtx = document.getElementById('channelChart');
if (channelCtx) {
  new Chart(channelCtx, {
    type: 'doughnut',
    data: {
      labels: ['Website', 'Email', 'Social', 'Direct'],
      datasets: [{
        data: [45, 25, 20, 10],
        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e'],
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: { usePointStyle: true, padding: 12, font: { size: 10 } }
        }
      },
      cutout: '70%'
    }
  });
}
</script>
@endsection
