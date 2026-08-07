@extends('layouts.platform')

@section('title', 'Lead Scoring')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden">
    {{-- Header --}}
    <div class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between flex-shrink-0">
        <div>
            <h1 class="text-sm font-semibold text-gray-900">Leads</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">Manage and score your leads</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('client.leadscoring.dashboard') }}"
               class="px-3 py-1.5 text-[11px] font-medium text-gray-600 bg-gray-50 border border-gray-200 rounded-md hover:bg-gray-100 transition">
                Dashboard
            </a>
            <button onclick="document.getElementById('createLeadModal').classList.remove('hidden')"
                    class="px-3 py-1.5 text-[11px] font-medium text-white bg-purple-600 rounded-md hover:bg-purple-700 transition">
                + Add Lead
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white border-b border-gray-200 px-6 py-3 flex items-center gap-3 flex-shrink-0">
        <form method="GET" action="{{ route('client.leadscoring.index') }}" class="flex items-center gap-3 w-full">
            <div class="relative flex-1 max-w-xs">
                <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, company..."
                       class="w-full pl-8 pr-3 py-1.5 text-[11px] border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500 focus:border-purple-500">
            </div>

            <select name="status" onchange="this.form.submit()"
                    class="px-2.5 py-1.5 text-[11px] border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500 bg-white">
                <option value="">All Statuses</option>
                <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                <option value="contacted" {{ request('status') == 'contacted' ? 'selected' : '' }}>Contacted</option>
                <option value="qualified" {{ request('status') == 'qualified' ? 'selected' : '' }}>Qualified</option>
                <option value="converted" {{ request('status') == 'converted' ? 'selected' : '' }}>Converted</option>
                <option value="lost" {{ request('status') == 'lost' ? 'selected' : '' }}>Lost</option>
                <option value="nurturing" {{ request('status') == 'nurturing' ? 'selected' : '' }}>Nurturing</option>
            </select>

            <select name="qualification" onchange="this.form.submit()"
                    class="px-2.5 py-1.5 text-[11px] border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500 bg-white">
                <option value="">All Qualifications</option>
                <option value="hot" {{ request('qualification') == 'hot' ? 'selected' : '' }}>Hot</option>
                <option value="warm" {{ request('qualification') == 'warm' ? 'selected' : '' }}>Warm</option>
                <option value="cold" {{ request('qualification') == 'cold' ? 'selected' : '' }}>Cold</option>
                <option value="unscored" {{ request('qualification') == 'unscored' ? 'selected' : '' }}>Unscored</option>
            </select>

            <select name="source" onchange="this.form.submit()"
                    class="px-2.5 py-1.5 text-[11px] border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500 bg-white">
                <option value="">All Sources</option>
                @foreach($sources as $source)
                    <option value="{{ $source }}" {{ request('source') == $source ? 'selected' : '' }}>{{ $source }}</option>
                @endforeach
            </select>

            @if(request()->hasAny(['search','status','qualification','source']))
                <a href="{{ route('client.leadscoring.index') }}"
                   class="px-2.5 py-1.5 text-[11px] text-red-600 hover:text-red-700 font-medium">
                    Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="flex-1 overflow-y-auto p-6">
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <table class="w-full text-left text-[11px]">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-2.5 font-medium text-gray-600">Lead</th>
                        <th class="px-4 py-2.5 font-medium text-gray-600">Source</th>
                        <th class="px-4 py-2.5 font-medium text-gray-600">Status</th>
                        <th class="px-4 py-2.5 font-medium text-gray-600">Score</th>
                        <th class="px-4 py-2.5 font-medium text-gray-600">Qualification</th>
                        <th class="px-4 py-2.5 font-medium text-gray-600">Created</th>
                        <th class="px-4 py-2.5 font-medium text-gray-600 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($leads as $lead)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-[10px] font-bold">
                                        {{ strtoupper(substr($lead->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $lead->name }}</p>
                                        <p class="text-[10px] text-gray-400">{{ $lead->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                <span class="px-1.5 py-0.5 bg-gray-100 rounded text-[10px]">{{ $lead->source }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $statusColors = [
                                        'new' => 'bg-blue-50 text-blue-700',
                                        'contacted' => 'bg-amber-50 text-amber-700',
                                        'qualified' => 'bg-emerald-50 text-emerald-700',
                                        'converted' => 'bg-green-50 text-green-700',
                                        'lost' => 'bg-gray-100 text-gray-600',
                                        'nurturing' => 'bg-pink-50 text-pink-700',
                                    ];
                                @endphp
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium {{ $statusColors[$lead->status] ?? 'bg-gray-50 text-gray-600' }}">
                                    {{ ucfirst($lead->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($lead->latestScore)
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full {{ $lead->latestScore->total_score >= 75 ? 'bg-green-500' : ($lead->latestScore->total_score >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                                                 style="width: {{ min($lead->latestScore->total_score, 100) }}%"></div>
                                        </div>
                                        <span class="text-[10px] font-medium text-gray-700">{{ $lead->latestScore->total_score }}</span>
                                    </div>
                                @else
                                    <span class="text-[10px] text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $qualColors = [
                                        'hot' => 'bg-red-50 text-red-700 border border-red-100',
                                        'warm' => 'bg-amber-50 text-amber-700 border border-amber-100',
                                        'cold' => 'bg-sky-50 text-sky-700 border border-sky-100',
                                        'unscored' => 'bg-gray-50 text-gray-500 border border-gray-200',
                                    ];
                                @endphp
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium {{ $qualColors[$lead->qualification_status] ?? 'bg-gray-50 text-gray-600' }}">
                                    {{ ucfirst($lead->qualification_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-[10px]">
                                {{ $lead->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('client.leadscoring.show', $lead->id) }}"
                                   class="text-[11px] text-purple-600 hover:text-purple-700 font-medium">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-400 text-[11px]">
                                No leads found. <button onclick="document.getElementById('createLeadModal').classList.remove('hidden')" class="text-purple-600 hover:underline font-medium">Create one</button>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $leads->links() }}
        </div>
    </div>
</div>

{{-- Create Lead Modal --}}
<div id="createLeadModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Add New Lead</h3>
            <button onclick="document.getElementById('createLeadModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('client.leadscoring.store') }}" class="p-5 space-y-3">
            @csrf
            <div>
                <label class="block text-[10px] font-medium text-gray-600 uppercase mb-1">Name</label>
                <input type="text" name="name" required class="w-full px-3 py-2 text-xs border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-medium text-gray-600 uppercase mb-1">Email</label>
                    <input type="email" name="email" required class="w-full px-3 py-2 text-xs border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-[10px] font-medium text-gray-600 uppercase mb-1">Phone</label>
                    <input type="text" name="phone" class="w-full px-3 py-2 text-xs border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-medium text-gray-600 uppercase mb-1">Company</label>
                    <input type="text" name="company" class="w-full px-3 py-2 text-xs border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-[10px] font-medium text-gray-600 uppercase mb-1">Job Title</label>
                    <input type="text" name="job_title" class="w-full px-3 py-2 text-xs border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-medium text-gray-600 uppercase mb-1">Source</label>
                    <input type="text" name="source" required class="w-full px-3 py-2 text-xs border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-[10px] font-medium text-gray-600 uppercase mb-1">Source Detail</label>
                    <input type="text" name="source_detail" class="w-full px-3 py-2 text-xs border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500">
                </div>
            </div>
            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('createLeadModal').classList.add('hidden')"
                        class="px-4 py-2 text-[11px] text-gray-600 bg-gray-50 border border-gray-200 rounded-md hover:bg-gray-100">Cancel</button>
                <button type="submit" class="px-4 py-2 text-[11px] text-white bg-purple-600 rounded-md hover:bg-purple-700">Create Lead</button>
            </div>
        </form>
    </div>
</div>
@endsection