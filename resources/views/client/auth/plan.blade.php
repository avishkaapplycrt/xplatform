<x-guest-layout title="Choose Your Plan"
                :backUrl="route('client.industry')"
                :previousUrl="route('client.industry')"
                :currentStep="3"
                :totalSteps="3">

{{-- Step 3 of sign-up: pick a plan (ClientAuthController::PLANS). Picking one
     starts its free trial — no card needed. --}}
<style>
  .plan-list{display:grid;gap:10px;margin-bottom:16px}
  .plan-card{position:relative;display:flex;gap:14px;align-items:flex-start;border:1.5px solid #e5e7eb;border-radius:14px;padding:14px 16px;cursor:pointer;transition:border-color .15s,background .15s,box-shadow .15s}
  .plan-card:hover{border-color:#93c5fd}
  .plan-card:has(input:checked){border-color:#3b82f6;background:#eff6ff;box-shadow:0 0 0 3px rgba(59,130,246,.12)}
  .plan-card input{margin-top:4px;accent-color:#2563eb;flex:none}
  .plan-body{flex:1;min-width:0}
  .plan-top{display:flex;align-items:baseline;justify-content:space-between;gap:10px;flex-wrap:wrap}
  .plan-name{font-weight:700;color:#111827;font-size:15px}
  .plan-price{font-weight:700;color:#111827;font-size:17px;white-space:nowrap}
  .plan-price small{font-weight:500;color:#6b7280;font-size:12px}
  .plan-blurb{font-size:12.5px;color:#6b7280;margin-top:2px;line-height:1.45}
  .plan-feats{margin:8px 0 0;padding:0;list-style:none;display:grid;gap:3px}
  .plan-feats li{font-size:12.5px;color:#374151;display:flex;gap:6px;align-items:flex-start}
  .plan-feats li::before{content:"\2713";color:#16a34a;font-weight:700}
  .plan-badge{position:absolute;top:-9px;right:14px;background:#2563eb;color:#fff;font-size:10.5px;font-weight:600;letter-spacing:.03em;padding:2px 9px;border-radius:999px}
  .plan-note{font-size:12.5px;color:#6b7280;text-align:center;margin:0 0 16px}
</style>

<div class="p-8">

    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Choose your plan</h2>
        <p class="text-sm text-gray-400 mt-1">Start with a free 30-day trial. No credit card needed.</p>
    </div>

    <form method="POST" action="{{ route('client.plan.store') }}">
        @csrf

        <div class="plan-list">
            @foreach($plans as $key => $plan)
                <label class="plan-card">
                    @if(!empty($plan['popular']))<span class="plan-badge">Most popular</span>@endif
                    <input type="radio" name="plan" value="{{ $key }}" {{ $selected === $key ? 'checked' : '' }} required>
                    <span class="plan-body">
                        <span class="plan-top">
                            <span class="plan-name">{{ $plan['name'] }}</span>
                            <span class="plan-price">{{ $plan['price'] }}<small>{{ $plan['per'] }}</small></span>
                        </span>
                        <span class="plan-blurb" style="display:block">{{ $plan['blurb'] }}</span>
                        <ul class="plan-feats">
                            @foreach($plan['features'] as $feature)
                                <li>{{ $feature }}</li>
                            @endforeach
                        </ul>
                    </span>
                </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('plan')" class="mb-3" />

        <p class="plan-note">
            Compare everything on the <a href="{{ route('pricing') }}" target="_blank" rel="noopener" class="text-blue-500 hover:text-blue-700">pricing page</a>.
        </p>

        {{-- Two ways in: try the plan free for 30 days, or go ahead with it now
             (saved for billing; the label follows the selected plan). --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <button type="submit" name="action" value="trial"
                class="w-full bg-white hover:bg-gray-50 text-blue-600 font-medium py-2.5 rounded-lg transition"
                style="border:1.5px solid #3b82f6">
                Start free trial
            </button>
            <button type="submit" name="action" value="continue" id="planContinue"
                class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2.5 rounded-lg transition">
                Continue with <span id="planContinueName">{{ $plans[$selected]['name'] }}</span>
            </button>
        </div>
        <p class="plan-note" style="margin:10px 0 0">30-day free trial, no card needed — or continue with the plan and we'll set up billing.</p>
    </form>

</div>

<script>
    // "Continue with <plan>" follows the selected plan.
    document.querySelectorAll('input[name="plan"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            var names = @json(array_map(fn ($p) => $p['name'], $plans));
            document.getElementById('planContinueName').textContent = names[radio.value] || '';
        });
    });
</script>

</x-guest-layout>
