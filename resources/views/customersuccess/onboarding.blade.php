@extends('layouts.platform')

@section('title', 'Customer Onboarding')

@section('content')
<div class="flex flex-col h-full overflow-hidden bg-gray-100">

  <header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
    <div>
      <h1 class="text-[16px] font-semibold text-gray-900">Customer Onboarding</h1>
      <p class="text-[11px] text-gray-500 mt-0.5">Manage onboarding workflows & track progress</p>
    </div>
    <button class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white rounded-lg text-[11px] font-medium hover:bg-blue-700 transition" data-bs-toggle="modal" data-bs-target="#createWorkflowModal">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      New Workflow
    </button>
  </header>

  <div class="flex-1 overflow-y-auto px-5 py-5">

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
      <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center mx-auto mb-2"><svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
        <h3 class="text-[20px] font-bold text-gray-900">{{ $workflows->count() ?? 0 }}</h3>
        <p class="text-[11px] text-gray-500">Active Workflows</p>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
        <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center mx-auto mb-2"><svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <h3 class="text-[20px] font-bold text-gray-900">{{ $activeOnboardings->total() ?? 0 }}</h3>
        <p class="text-[11px] text-gray-500">In Progress</p>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
        <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center mx-auto mb-2"><svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <h3 class="text-[20px] font-bold text-gray-900">{{ $completionRate ?? 0 }}%</h3>
        <p class="text-[11px] text-gray-500">Completion Rate</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
      <!-- Workflows -->
      <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
          <h5 class="text-[13px] font-semibold text-gray-800">Onboarding Workflows</h5>
        </div>
        <div class="p-4">
          @if(!empty($workflows) && $workflows->count() > 0)
            @foreach($workflows as $workflow)
            <div class="mb-3 p-3 bg-gray-50 rounded-lg">
              <div class="flex items-start justify-between mb-2">
                <div>
                  <h6 class="text-[12px] font-semibold text-gray-900">{{ $workflow->name }}</h6>
                  <p class="text-[10px] text-gray-500">{{ $workflow->description }}</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" class="sr-only peer" {{ $workflow->is_active ? 'checked' : '' }}>
                  <div class="w-8 h-4 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>
              <div class="flex gap-2 mb-2">
                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-700">{{ count($workflow->steps ?? []) }} steps</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600">{{ $workflow->customers_count ?? 0 }} customers</span>
              </div>
              <div class="space-y-1">
                @foreach($workflow->steps ?? [] as $index => $step)
                <div class="flex items-center gap-2 text-[10px] text-gray-500">
                  <span class="w-4 h-4 rounded bg-gray-200 flex items-center justify-center text-[9px] font-bold text-gray-600">{{ $index + 1 }}</span>
                  <span class="truncate">{{ $step['title'] ?? 'Step' }} ({{ $step['delay_days'] ?? 0 }} days)</span>
                  <span class="ml-auto px-1.5 py-0.5 rounded bg-white text-gray-400 text-[9px]">{{ $step['action_type'] ?? 'email' }}</span>
                </div>
                @endforeach
              </div>
            </div>
            @endforeach
          @else
            <div class="text-center py-6">
              <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3"><svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div>
              <p class="text-[12px] text-gray-500">No workflows created yet.</p>
            </div>
          @endif
        </div>
      </div>

      <!-- Active Onboardings -->
      <div class="lg:col-span-3 bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
          <h5 class="text-[13px] font-semibold text-gray-800">Active Onboardings</h5>
        </div>
        <div class="p-4">
          @if(!empty($activeOnboardings) && $activeOnboardings->count() > 0)
            <div class="overflow-x-auto">
              <table class="w-full text-[11px]">
                <thead>
                  <tr class="text-left text-gray-500 border-b border-gray-100">
                    <th class="pb-2 font-medium">Customer</th>
                    <th class="pb-2 font-medium">Workflow</th>
                    <th class="pb-2 font-medium">Progress</th>
                    <th class="pb-2 font-medium">Started</th>
                    <th class="pb-2 font-medium">Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                  @foreach($activeOnboardings as $customer)
                  <tr>
                    <td class="py-2.5">
                      <p class="font-medium text-gray-900">{{ $customer->name ?? 'Unknown' }}</p>
                      <p class="text-[10px] text-gray-400">{{ $customer->email ?? '' }}</p>
                    </td>
                    <td class="py-2.5 text-gray-600">{{ $customer->onboardingWorkflow->name ?? 'N/A' }}</td>
                    <td class="py-2.5">
                      <div class="flex items-center gap-2">
                        <div class="w-16 bg-gray-100 rounded-full h-1.5">
                          <div class="h-1.5 rounded-full bg-blue-500" style="width: 45%"></div>
                        </div>
                        <span class="text-gray-500">45%</span>
                      </div>
                    </td>
                    <td class="py-2.5 text-gray-500">{{ $customer->onboarding_started_at ? $customer->onboarding_started_at->diffForHumans() : 'N/A' }}</td>
                    <td class="py-2.5"><span class="px-2 py-0.5 rounded text-[10px] font-medium bg-amber-100 text-amber-700">In Progress</span></td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            {{ $activeOnboardings->links() }}
          @else
            <div class="text-center py-8">
              <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3"><svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
              <p class="text-[12px] text-gray-500">No active onboardings.</p>
            </div>
          @endif
        </div>
      </div>
    </div>

  </div>
</div>
@endsection
