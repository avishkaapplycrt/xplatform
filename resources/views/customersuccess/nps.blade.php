@extends('layouts.platform')

@section('title', 'NPS Reports')

@section('content')
<div class="flex flex-col h-full overflow-hidden bg-gray-100">

  <header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
    <div>
      <h1 class="text-[16px] font-semibold text-gray-900">NPS Reports</h1>
      <p class="text-[11px] text-gray-500 mt-0.5">Net Promoter Score analytics & feedback</p>
    </div>
    <button class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white rounded-lg text-[11px] font-medium hover:bg-blue-700 transition" data-bs-toggle="modal" data-bs-target="#createSurveyModal">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      New Survey
    </button>
  </header>

  <div class="flex-1 overflow-y-auto px-5 py-5">

    @if(!empty($overallNps))
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
      <div class="bg-white border border-gray-200 rounded-xl p-6 text-center">
        <p class="text-[11px] text-gray-500 font-medium mb-2">Net Promoter Score</p>
        <h1 class="text-[48px] font-bold {{ $overallNps['score'] >= 50 ? 'text-green-600' : ($overallNps['score'] >= 0 ? 'text-amber-600' : 'text-red-600') }}">{{ $overallNps['score'] }}</h1>
        <div class="w-full bg-gray-100 rounded-full h-2 mt-3">
          <div class="h-2 rounded-full bg-green-500" style="width: {{ max(0, ($overallNps['score'] + 100) / 2) }}%"></div>
        </div>
        <p class="text-[10px] text-gray-400 mt-1">-100 to +100 scale</p>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-6">
        <div class="grid grid-cols-3 text-center divide-x divide-gray-100">
          <div class="p-3">
            <svg class="w-6 h-6 text-green-500 mx-auto mb-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <h3 class="text-[20px] font-bold text-green-600">{{ $overallNps['promoters'] }}</h3>
            <p class="text-[10px] text-gray-500">Promoters (9-10)</p>
          </div>
          <div class="p-3">
            <svg class="w-6 h-6 text-amber-500 mx-auto mb-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <h3 class="text-[20px] font-bold text-amber-600">{{ $overallNps['passives'] }}</h3>
            <p class="text-[10px] text-gray-500">Passives (7-8)</p>
          </div>
          <div class="p-3">
            <svg class="w-6 h-6 text-red-500 mx-auto mb-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <h3 class="text-[20px] font-bold text-red-600">{{ $overallNps['detractors'] }}</h3>
            <p class="text-[10px] text-gray-500">Detractors (0-6)</p>
          </div>
        </div>
        <div class="flex justify-between mt-4 pt-3 border-t border-gray-100 text-[11px] text-gray-500">
          <span>Total Responses: <strong class="text-gray-900">{{ $overallNps['total_responses'] }}</strong></span>
          <span>Response Rate: <strong class="text-gray-900">{{ $overallNps['response_rate'] }}%</strong></span>
        </div>
      </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <!-- Surveys List -->
      <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
          <h5 class="text-[13px] font-semibold text-gray-800">All Surveys</h5>
        </div>
        <div class="p-4">
          @if(!empty($surveys) && $surveys->count() > 0)
            <div class="overflow-x-auto">
              <table class="w-full text-[11px]">
                <thead>
                  <tr class="text-left text-gray-500 border-b border-gray-100">
                    <th class="pb-2 font-medium">Survey</th>
                    <th class="pb-2 font-medium">Responses</th>
                    <th class="pb-2 font-medium">Status</th>
                    <th class="pb-2 font-medium">Sent</th>
                    <th class="pb-2 font-medium">Action</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                  @foreach($surveys as $survey)
                  <tr>
                    <td class="py-2.5">
                      <p class="font-medium text-gray-900">{{ $survey->name }}</p>
                      <p class="text-[10px] text-gray-500 truncate max-w-[200px]">{{ Str::limit($survey->question, 50) }}</p>
                    </td>
                    <td class="py-2.5"><span class="px-2 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-700">{{ $survey->responses_count ?? 0 }}</span></td>
                    <td class="py-2.5">
                      @if($survey->is_active)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-700"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Active</span>
                      @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600">Inactive</span>
                      @endif
                    </td>
                    <td class="py-2.5 text-gray-500">{{ $survey->sent_at ? $survey->sent_at->format('M d, Y') : 'Not sent' }}</td>
                    <td class="py-2.5">
                      <a href="{{ route('client.success.nps.report', $survey->id) }}" class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-700 rounded text-[10px] font-medium hover:bg-blue-100 transition">Report →</a>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            {{ $surveys->links() }}
          @else
            <div class="text-center py-8">
              <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3"><svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
              <h5 class="text-[13px] font-medium text-gray-700">No surveys created yet.</h5>
              <p class="text-[11px] text-gray-500">Create your first NPS survey to start collecting feedback.</p>
            </div>
          @endif
        </div>
      </div>

      <!-- Recent Feedback -->
      <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
          <h5 class="text-[13px] font-semibold text-gray-800">Recent Feedback</h5>
        </div>
        <div class="p-4">
          @if(!empty($recentResponses) && count($recentResponses) > 0)
            @foreach($recentResponses as $response)
            <div class="mb-3 pb-3 border-b border-gray-50 last:border-0">
              <div class="flex items-start justify-between gap-2">
                <div class="flex items-center gap-2">
                  <div class="w-8 h-8 rounded-full {{ $response->category === 'promoter' ? 'bg-green-50' : ($response->category === 'passive' ? 'bg-amber-50' : 'bg-red-50') }} flex items-center justify-center flex-shrink-0">
                    <span class="text-[11px] font-bold {{ $response->category === 'promoter' ? 'text-green-600' : ($response->category === 'passive' ? 'text-amber-600' : 'text-red-600') }}">{{ $response->score }}</span>
                  </div>
                  <div>
                    <p class="text-[12px] font-medium text-gray-900">{{ $response->customer->name ?? 'Anonymous' }}</p>
                    <p class="text-[10px] text-gray-400">{{ $response->created_at->diffForHumans() }}</p>
                  </div>
                </div>
                <span class="flex-shrink-0 px-2 py-0.5 rounded text-[10px] font-medium {{ $response->category === 'promoter' ? 'bg-green-100 text-green-700' : ($response->category === 'passive' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">{{ ucfirst($response->category) }}</span>
              </div>
              @if($response->feedback)
              <p class="mt-2 text-[11px] text-gray-500 italic pl-10">"{{ $response->feedback }}"</p>
              @endif
            </div>
            @endforeach
          @else
            <div class="text-center py-6">
              <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3"><svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg></div>
              <p class="text-[12px] text-gray-500">No feedback received yet.</p>
            </div>
          @endif
        </div>
      </div>
    </div>

  </div>
</div>
@endsection
