<?php
// resources/views/analytics/create-site.blade.php

?>
@extends('layouts.client')

@section('title', 'Add Website')
@section('header', 'Add New Website')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl border p-8">
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Website Details</h3>
            <p class="text-sm text-gray-500">Enter your website information to generate tracking code</p>
        </div>

        <form action="{{ route('client.analytics.site.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Website Name</label>
                <input type="text" name="name" value="{{ old('name') }}" 
                       class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                       placeholder="My Awesome Website" required>
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Website URL</label>
                <input type="url" name="domain" value="{{ old('domain') }}" 
                       class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                       placeholder="https://example.com" required>
                @error('domain')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">Enter the full URL including https://</p>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button type="submit" 
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-medium transition">
                    <i class="fas fa-plus mr-2"></i>Add Website
                </button>
                <a href="{{ route('client.analytics.dashboard') }}" 
                   class="text-gray-600 hover:text-gray-800 px-4 py-3 rounded-lg hover:bg-gray-100 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection