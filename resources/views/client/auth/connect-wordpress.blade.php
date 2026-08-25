@extends('layouts.client')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
        <div class="mb-6">
            <svg class="w-16 h-16 mx-auto text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
            </svg>
        </div>
        <h2 class="text-2xl font-bold mb-2">Connect Your WordPress Site</h2>
        <p class="text-gray-600 mb-6">Start collecting analytics data from your WordPress website.</p>
        
        <a href="{{ route('client.analytics.sites.create') }}" class="block w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 mb-4">
            Connect WordPress Site
        </a>
        
        <a href="{{ route('client.dashboard') }}" class="text-gray-500 hover:text-gray-700 text-sm">
            Skip for now →
        </a>
    </div>
</div>
@endsection