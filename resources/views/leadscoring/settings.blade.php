@extends('layouts.platform')

@section('title', 'Lead Scoring Settings')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden">
    <div class="bg-white border-b border-gray-200 px-6 py-4 flex-shrink-0">
        <h1 class="text-sm font-semibold text-gray-900">Lead Scoring Settings</h1>
        <p class="text-[11px] text-gray-500 mt-0.5">Configure scoring thresholds and weights</p>
    </div>

    <div class="flex-1 overflow-y-auto p-6">
        <div class="max-w-2xl bg-white border border-gray-200 rounded-lg p-6">
            <form method="POST" action="#" class="space-y-5">
                @csrf

                <div>
                    <h3 class="text-xs font-semibold text-gray-900 mb-3">Qualification Thresholds</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-medium text-gray-600 uppercase mb-1">Hot Threshold</label>
                            <input type="number" name="hot_threshold" value="{{ $settings['hot_threshold'] }}" min="0" max="100"
                                   class="w-full px-3 py-2 text-xs border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-600 uppercase mb-1">Warm Threshold</label>
                            <input type="number" name="warm_threshold" value="{{ $settings['warm_threshold'] }}" min="0" max="100"
                                   class="w-full px-3 py-2 text-xs border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500">
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-xs font-semibold text-gray-900 mb-3">Score Weights (%)</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[10px] font-medium text-gray-600 uppercase mb-1">Behavior</label>
                            <input type="number" name="behavior_weight" value="{{ $settings['behavior_weight'] }}" min="0" max="100"
                                   class="w-full px-3 py-2 text-xs border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-600 uppercase mb-1">Demographic</label>
                            <input type="number" name="demographic_weight" value="{{ $settings['demographic_weight'] }}" min="0" max="100"
                                   class="w-full px-3 py-2 text-xs border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-600 uppercase mb-1">Engagement</label>
                            <input type="number" name="engagement_weight" value="{{ $settings['engagement_weight'] }}" min="0" max="100"
                                   class="w-full px-3 py-2 text-xs border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500">
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-xs font-semibold text-gray-900 mb-3">Auto-Routing</h3>
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="auto_route_hot" id="auto_route_hot" value="1" {{ $settings['auto_route_hot'] ? 'checked' : '' }}
                               class="w-3.5 h-3.5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        <label for="auto_route_hot" class="text-[11px] text-gray-700">Automatically route hot leads to sales team</label>
                    </div>
                    <div class="mt-3">
                        <label class="block text-[10px] font-medium text-gray-600 uppercase mb-1">Route Channel</label>
                        <select name="route_channel" class="w-full max-w-xs px-3 py-2 text-xs border border-gray-200 rounded-md bg-white focus:outline-none focus:ring-1 focus:ring-purple-500">
                            <option value="slack" {{ $settings['route_channel'] == 'slack' ? 'selected' : '' }}>Slack</option>
                            <option value="email" {{ $settings['route_channel'] == 'email' ? 'selected' : '' }}>Email</option>
                            <option value="crm" {{ $settings['route_channel'] == 'crm' ? 'selected' : '' }}>CRM</option>
                        </select>
                    </div>
                </div>

                <div class="pt-2 border-t border-gray-100 flex justify-end">
                    <button type="submit" class="px-4 py-2 text-[11px] font-medium text-white bg-purple-600 rounded-md hover:bg-purple-700 transition">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection