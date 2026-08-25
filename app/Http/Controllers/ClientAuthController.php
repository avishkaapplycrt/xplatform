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

            return redirect()->intended(route('client.dashboard'));
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

        return redirect()->route('client.login');
    }
}
