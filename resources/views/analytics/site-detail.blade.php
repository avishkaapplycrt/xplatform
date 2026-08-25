<?php
// resources/views/analytics/site-detail.blade.php

?>
@extends('layouts.client')

@section('title', 'Tracking Code')
@section('header', $site->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Tracking Code Card -->
    <div class="bg-white rounded-xl border overflow-hidden">
        <div class="px-6 py-4 border-b bg-gradient-to-r from-emerald-50 to-blue-50">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-code text-emerald-600"></i>
                Tracking Code
            </h3>
            <p class="text-sm text-gray-500">Copy and paste this code into the &lt;head&gt; section of your website</p>
        </div>
        
        <div class="p-6">
            <div class="relative">
                <pre class="bg-slate-900 text-emerald-400 p-6 rounded-lg overflow-x-auto text-sm leading-relaxed" id="trackingCode">{{ $trackingScript }}</pre>
                <button onclick="copyCode()" 
                        class="absolute top-4 right-4 bg-white/10 hover:bg-white/20 text-white px-3 py-2 rounded-lg text-sm transition flex items-center gap-2">
                    <i class="fas fa-copy"></i>
                    <span id="copyText">Copy</span>
                </button>
            </div>
            
            <div class="mt-4 flex items-center gap-2 text-sm text-gray-600">
                <i class="fas fa-info-circle text-blue-500"></i>
                <span>This code should be placed before the closing &lt;/head&gt; tag on every page you want to track</span>
            </div>
        </div>
    </div>

    <!-- Site Info -->
    <div class="bg-white rounded-xl border p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Website Information</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500">Tracking ID</p>
                <p class="font-mono text-sm bg-gray-100 px-3 py-2 rounded mt-1">{{ $site->tracking_id }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">API Key</p>
                <p class="font-mono text-sm bg-gray-100 px-3 py-2 rounded mt-1">{{ $site->api_key }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Domain</p>
                <p class="text-sm font-medium mt-1">{{ $site->domain }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Created</p>
                <p class="text-sm font-medium mt-1">{{ $site->created_at->format('M d, Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="flex gap-4">
        <a href="{{ route('client.analytics.site.data', $site->id) }}" 
           class="flex-1 bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-xl text-center transition">
            <i class="fas fa-chart-bar text-2xl mb-2"></i>
            <p class="font-medium">View Analytics</p>
        </a>
        <a href="{{ route('client.analytics.dashboard') }}" 
           class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 p-4 rounded-xl text-center transition">
            <i class="fas fa-arrow-left text-2xl mb-2"></i>
            <p class="font-medium">Back to Dashboard</p>
        </a>
    </div>
</div>

@section('scripts')
<script>
function copyCode() {
    const code = document.getElementById('trackingCode').textContent;
    navigator.clipboard.writeText(code).then(() => {
        const btn = document.getElementById('copyText');
        btn.textContent = 'Copied!';
        setTimeout(() => btn.textContent = 'Copy', 2000);
    });
}
</script>
@endsection
@endsection