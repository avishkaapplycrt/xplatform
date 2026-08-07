@extends('layouts.platform')

@section('title', 'Lead: ' . $lead->name)

@section('content')
<div class="flex-1 flex flex-col overflow-hidden">
    {{-- Header --}}
    <div class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between flex-shrink-0">
        <div class="flex items-center gap-3">
            <a href="{{ route('client.leadscoring.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-sm font-semibold text-gray-900">{{ $lead->name }}</h1>
                <p class="text-[11px] text-gray-500">{{ $lead->email }} @if($lead->company) · {{ $lead->company }} @endif</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('client.leadscoring.rescore', $lead->id) }}" class="inline" onsubmit="event.preventDefault(); fetch(this.action,{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}}).then(r=>r.json()).then(d=>{alert('Re-scored! New score: '+d.score);location.reload()})">
                @csrf
                <button type="submit" class="px-3 py-1.5 text-[11px] font-medium text-purple-600 bg-purple-50 border border-purple-200 rounded-md hover:bg-purple-100 transition">
                    Re-score
                </button>
            </form>
            <form method="POST" action="{{ route('client.leadscoring.route-to-sales', $lead->id) }}" class="inline" onsubmit="event.preventDefault(); if(!confirm('Route this lead to sales?')) return; fetch(this.action,{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}}).then(r=>r.json()).then(d=>alert(d.message))">
                @csrf
                <button type="submit" class="px-3 py-1.5 text-[11px] font-medium text-white bg-purple-600 rounded-md hover:bg-purple-700 transition">
                    Route to Sales
                </button>
            </form>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto p-6">
        <div class="grid grid-cols-3 gap-6">
            {{-- Lead Info --}}
            <div class="col-span-1 bg-white border border-gray-200 rounded-lg p-4">
                <h3 class="text-xs font-semibold text-gray-900 mb-3">Lead Information</h3>
                <div class="space-y-2.5 text-[11px]">
                    <div class="flex justify-between"><span class="text-gray-500">Status</span> <span class="font-medium text-gray-900 capitalize">{{ $lead->status }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Qualification</span> <span class="font-medium capitalize {{ $lead->qualification_status == 'hot' ? 'text-red-600' : ($lead->qualification_status == 'warm' ? 'text-amber-600' : 'text-gray-900') }}">{{ $lead->qualification_status }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Source</span> <span class="font-medium text-gray-900">{{ $lead->source }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Phone</span> <span class="font-medium text-gray-900">{{ $lead->phone ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Job Title</span> <span class="font-medium text-gray-900">{{ $lead->job_title ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Created</span> <span class="font-medium text-gray-900">{{ $lead->created_at->format('M d, Y H:i') }}</span></div>
                </div>

                <hr class="my-4 border-gray-100">

                <h3 class="text-xs font-semibold text-gray-900 mb-2">Update Status</h3>
                <form method="POST" action="{{ route('client.leadscoring.update-status', $lead->id) }}" class="flex gap-2" onsubmit="event.preventDefault(); const fd=new FormData(this); fetch(this.action,{method:'POST',body:fd,headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}}).then(r=>r.json()).then(d=>location.reload())">
                    @csrf
                    <select name="status" class="flex-1 px-2.5 py-1.5 text-[11px] border border-gray-200 rounded-md bg-white">
                        <option value="new" {{ $lead->status == 'new' ? 'selected' : '' }}>New</option>
                        <option value="contacted" {{ $lead->status == 'contacted' ? 'selected' : '' }}>Contacted</option>
                        <option value="qualified" {{ $lead->status == 'qualified' ? 'selected' : '' }}>Qualified</option>
                        <option value="converted" {{ $lead->status == 'converted' ? 'selected' : '' }}>Converted</option>
                        <option value="lost" {{ $lead->status == 'lost' ? 'selected' : '' }}>Lost</option>
                        <option value="nurturing" {{ $lead->status == 'nurturing' ? 'selected' : '' }}>Nurturing</option>
                    </select>
                    <button type="submit" class="px-3 py-1.5 text-[11px] text-white bg-gray-800 rounded-md hover:bg-gray-900">Update</button>
                </form>
            </div>

            {{-- Score & History --}}
            <div class="col-span-2 space-y-6">
                {{-- Current Score --}}
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h3 class="text-xs font-semibold text-gray-900 mb-3">Current Score</h3>
                    @if($lead->latestScore)
                        <div class="flex items-center gap-6">
                            <div class="text-center">
                                <div class="text-3xl font-bold {{ $lead->latestScore->total_score >= 75 ? 'text-green-600' : ($lead->latestScore->total_score >= 50 ? 'text-amber-600' : 'text-red-600') }}">
                                    {{ $lead->latestScore->total_score }}
                                </div>
                                <div class="text-[10px] text-gray-500 uppercase mt-1">Total Score</div>
                            </div>
                            <div class="flex-1 space-y-2">
                                @php
                                    $breakdown = [
                                        'Behavior' => $lead->latestScore->behavior_score ?? 0,
                                        'Demographic' => $lead->latestScore->demographic_score ?? 0,
                                        'Engagement' => $lead->latestScore->engagement_score ?? 0,
                                    ];
                                @endphp
                                @foreach($breakdown as $label => $score)
                                    <div>
                                        <div class="flex justify-between text-[10px] mb-0.5">
                                            <span class="text-gray-600">{{ $label }}</span>
                                            <span class="font-medium text-gray-900">{{ $score }}</span>
                                        </div>
                                        <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-purple-500 rounded-full" style="width: {{ min($score, 100) }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="text-center border-l border-gray-100 pl-6">
                                <div class="text-lg font-bold text-purple-600">{{ $conversionProbability ?? 0 }}%</div>
                                <div class="text-[10px] text-gray-500 uppercase mt-1">Conversion Probability</div>
                            </div>
                        </div>
                    @else
                        <p class="text-[11px] text-gray-400">No score available yet.</p>
                    @endif
                </div>

                {{-- Score History --}}
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h3 class="text-xs font-semibold text-gray-900 mb-3">Score History</h3>
                    @if($scoreHistory->count())
                        <div class="h-40">
                            <canvas id="scoreHistoryChart"></canvas>
                        </div>
                        <div class="mt-3 divide-y divide-gray-50">
                            @foreach($scoreHistory->sortByDesc('created_at') as $score)
                                <div class="py-2 flex items-center justify-between text-[11px]">
                                    <span class="text-gray-500">{{ $score->created_at->format('M d, Y H:i') }}</span>
                                    <span class="font-medium text-gray-900">{{ $score->total_score }} points</span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded {{ $score->qualification_status == 'hot' ? 'bg-red-50 text-red-600' : ($score->qualification_status == 'warm' ? 'bg-amber-50 text-amber-600' : 'bg-sky-50 text-sky-600') }}">
                                        {{ ucfirst($score->qualification_status) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-[11px] text-gray-400">No score history available.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    @if(isset($scoreHistory) && $scoreHistory->count() > 0)
    const hCtx = document.getElementById('scoreHistoryChart').getContext('2d');
    new Chart(hCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($scoreHistory->pluck('created_at')->map(fn($d) => $d->format('M d'))) !!},
            datasets: [{
                label: 'Score',
                data: {!! json_encode($scoreHistory->pluck('total_score')) !!},
                borderColor: 'rgba(147, 51, 234, 1)',
                backgroundColor: 'rgba(147, 51, 234, 0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, max: 100, ticks: { font: { size: 10 } }, grid: { color: '#f3f4f6' } },
                x: { ticks: { font: { size: 10 } }, grid: { display: false } }
            }
        }
    });
    @endif
</script>
@endpush
@endsection