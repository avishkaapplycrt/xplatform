<?php
// resources/views/analytics/dashboard.blade.php

?>
@extends('layouts.client')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Website Analytics</h1>
        <p class="text-gray-600">Track visitors and analyze traffic for your websites</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 uppercase">Total Websites</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $sites->count() }}</p>
                </div>
                <i class="fas fa-globe text-blue-500 text-2xl"></i>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 uppercase">Active Tracking</p>
                    <p class="text-3xl font-bold text-green-600">{{ $sites->where('is_active', true)->count() }}</p>
                </div>
                <i class="fas fa-signal text-green-500 text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Websites Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
            <h3 class="font-semibold text-gray-800">Your Websites</h3>
            <a href="{{ route('client.analytics.site.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                <i class="fas fa-plus mr-2"></i>Add Website
            </a>
        </div>

        @if($sites->isEmpty())
            <div class="p-12 text-center">
                <i class="fas fa-globe text-gray-300 text-5xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-800 mb-2">No websites yet</h3>
                <p class="text-gray-500 mb-6">Add your first website to start tracking visitors</p>
                <a href="{{ route('client.analytics.site.create') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg">
                    Add Your First Website
                </a>
            </div>
        @else
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Website</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tracking ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($sites as $site)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $site->name }}</div>
                            <div class="text-sm text-gray-500">{{ $site->domain }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <code class="bg-gray-100 px-2 py-1 rounded text-sm">{{ $site->tracking_id }}</code>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs {{ $site->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $site->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('client.analytics.site.data', $site->id) }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm">
                                <i class="fas fa-chart-bar"></i> Analytics
                            </a>
                            <a href="{{ route('client.analytics.site.detail', $site->id) }}" 
                               class="text-gray-600 hover:text-gray-800 text-sm">
                                <i class="fas fa-code"></i> Code
                            </a>
                            <form action="{{ route('client.analytics.site.delete', $site->id) }}" method="POST" class="inline" onsubmit="return confirm('Remove this website?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection