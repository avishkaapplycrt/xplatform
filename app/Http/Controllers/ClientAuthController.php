<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Industry;
use App\Services\IndustryDefaults;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Monarobase\CountryList\CountryListFacade as Countries;

/**
 * Client registration is two steps:
 *
 *   1. Create account   — company details + credentials, account exists immediately
 *   2. Industry         — seeds layers, signals, predictions and actions, then dashboard
 *
 * Everything else that used to be part of registration is optional and lives
 * under /app/setup/* (see ClientSetupController), surfaced as a checklist on
 * the dashboard. Progress is held in the database, not the session, so a closed
 * tab costs nothing.
 */
class ClientAuthController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Login
    // ─────────────────────────────────────────────────────────────────────────

    public function showLogin()
    {
        if (Auth::guard('client')->check()) {
            return redirect()->route('client.dashboard');
        }
        return view('client.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'string', 'email:rfc'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('client')->attempt(
            $request->only('email', 'password'),
            $request->boolean('remember')
        )) {
            $client = Auth::guard('client')->user();

            if (in_array($client->status, ['suspended', 'cancelled'])) {
                Auth::guard('client')->logout();
                return back()
                    ->with('error', 'Your account is ' . $client->status . '. Please contact support.')
                    ->withInput();
            }

            $request->session()->regenerate();
            $client->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            // Back to the page that asked for a sign-in, otherwise the home page.
            return redirect()->intended(route('home'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Step 1 — Create account (company details + credentials)
    // ─────────────────────────────────────────────────────────────────────────

    public function showRegister()
    {
        if (Auth::guard('client')->check()) {
            return redirect()->route('client.dashboard');
        }

        $countries = Countries::getList('en');
        return view('client.auth.register', compact('countries'));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'size'         => ['required', 'string'],
            'country'      => ['required', 'string'],
            'email'        => ['required', 'email:rfc', 'max:255', 'unique:clients,email'],
            'password'     => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $client = Client::create([
            'company_name' => $validated['company_name'],
            'size'         => $validated['size'],
            'country'      => $validated['country'],
            'email'        => $validated['email'],
            'password'     => Hash::make($validated['password']),
            'status'       => 'pending',
        ]);

        Auth::guard('client')->login($client);
        $request->session()->regenerate();

        return redirect()->route('client.industry');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Step 2 — Industry (authenticated; seeds the rest of the configuration)
    // ─────────────────────────────────────────────────────────────────────────

    public function showIndustry()
    {
        $client = auth('client')->user();

        $industries = Industry::whereNull('client_id')
            ->with(['microSignals', 'predictionModels'])
            ->get();

        return view('client.auth.industry', [
            'industries' => $industries,
            'selectedId' => $client->industry_id,
        ]);
    }

    public function storeIndustry(Request $request, IndustryDefaults $defaults)
    {
        $request->validate([
            'industry_id' => ['required', 'exists:industries,id'],
        ]);

        $client   = auth('client')->user();
        $industry = Industry::with('predictionModels')->findOrFail($request->industry_id);

        $isFirstTime = !$client->industry_id;

        $client->update(['industry_id' => $industry->id]);
        $defaults->apply($client, $industry);

        // First time through sign-up: on to step 3, choosing a plan.
        if ($isFirstTime) {
            return redirect()->route('client.plan');
        }

        return redirect()->route('client.dashboard')->with(
            'success',
            $isFirstTime
                ? "You're all set. We've configured " . $industry->name . " defaults — review them any time below."
                : 'Industry updated to ' . $industry->name . '.'
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Dashboard setup checklist
    // ─────────────────────────────────────────────────────────────────────────

    // ─────────────────────────────────────────────────────────────────────────
    // Step 3 — Plan (authenticated)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * The plans offered at sign-up, as on the pricing page. Keys are
     * clients.plan values ("Growth" is stored as professional).
     */
    public const PLANS = [
        'starter' => [
            'name' => 'Starter', 'price' => '$499', 'per' => '/mo',
            'blurb' => 'For growing businesses ready to unify their data and get their first AI predictions.',
            'features' => ['Up to 25,000 customer profiles', 'Up to 5 data sources', 'Churn prediction & CLV scoring'],
        ],
        'professional' => [
            'name' => 'Growth', 'price' => '$1,499', 'per' => '/mo', 'popular' => true,
            'blurb' => 'For scaling businesses that need the full 8-layer engine with automated execution.',
            'features' => ['Up to 250,000 customer profiles', 'Up to 20 data sources', 'All 8 layers, incl. autonomous execution'],
        ],
        'enterprise' => [
            'name' => 'Enterprise', 'price' => 'Custom', 'per' => '',
            'blurb' => 'For large organisations with complex data environments and mission-critical SLAs.',
            'features' => ['Unlimited customer profiles', 'Unlimited data sources', 'Dedicated infrastructure & success manager'],
        ],
    ];

    /** Free-trial length when a plan is chosen at sign-up (as advertised: 30 days, no card). */
    private const TRIAL_DAYS = 30;

    public function showPlan()
    {
        $client = auth('client')->user();

        return view('client.auth.plan', [
            'plans'    => self::PLANS,
            'selected' => array_key_exists($client->plan, self::PLANS) ? $client->plan : 'professional',
        ]);
    }

    /**
     * Saves the chosen plan. Two buttons:
     *   trial    — starts the plan's free trial; the plan ends with the trial
     *              (subscription_ends_at) until billing is set up.
     *   continue — saves the plan only, for the payment gateway (added later)
     *              to bill. Until it records a payment by moving
     *              subscription_ends_at forward, the plan isn't active: a paid
     *              plan with no end date would otherwise count as active for good.
     */
    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'plan'   => ['required', 'in:' . implode(',', array_keys(self::PLANS))],
            'action' => ['nullable', 'in:trial,continue'],
        ]);

        $client = auth('client')->user();
        $name   = self::PLANS[$validated['plan']]['name'];

        if (($validated['action'] ?? 'trial') === 'continue') {
            $paidUntil = $client->subscription_ends_at && $client->subscription_ends_at->isFuture()
                ? $client->subscription_ends_at   // already paid up: keep it
                : now();                          // not paid yet: awaiting billing
            $client->forceFill(['plan' => $validated['plan'], 'subscription_ends_at' => $paidUntil])->save();

            return redirect()->route('home')->with(
                'success',
                "Thanks — you've chosen the {$name} plan. We'll be in touch to set up billing."
            );
        }

        $endsAt = $client->trial_ends_at && $client->trial_ends_at->isFuture()
            ? $client->trial_ends_at                       // switching plans keeps the running trial
            : now()->addDays(self::TRIAL_DAYS);

        $client->forceFill([
            'plan'                 => $validated['plan'],
            'trial_ends_at'        => $endsAt,
            'subscription_ends_at' => $client->subscription_ends_at && $client->subscription_ends_at->isAfter($endsAt)
                ? $client->subscription_ends_at
                : $endsAt,
        ])->save();

        // Sign-up is done: back to the home page with a success message.
        return redirect()->route('home')->with(
            'success',
            "You're all set — your free trial of {$name} runs until " . $endsAt->format('j M Y') . '.'
        );
    }

    public function dismissChecklist(Request $request)
    {
        auth('client')->user()->update(['onboarding_dismissed_at' => now()]);

        return back();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Logout
    // ─────────────────────────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        Auth::guard('client')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // The homepage's Log out button returns to the home page; elsewhere, to sign in.
        if ($request->input('redirect') === 'home') {
            return redirect()->route('home');
        }

        return redirect()->route('client.login');
    }
}
