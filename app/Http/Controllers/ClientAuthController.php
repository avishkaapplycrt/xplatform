<?php

// Author: Arvind Poudel
// Description: Handles client-side authentication - login, register, and logout

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Monarobase\CountryList\CountryListFacade as Countries;


class ClientAuthController extends Controller
{
    // Show login page - redirect to dashboard if already logged in
    public function showLogin()
    {
        if (Auth::guard('client')->check()) {
            return redirect()->route('client.dashboard');
        }
        return view('client.auth.login');
    }

    // Handle login form submission
    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'string', 'email:rfc'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('client')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {

            $client = Auth::guard('client')->user();

            // Block suspended or cancelled accounts before allowing session
            if (in_array($client->status, ['suspended', 'cancelled'])) {
                Auth::guard('client')->logout();
                return back()
                    ->with('error', 'Your account is ' . $client->status . '. Please contact support.')
                    ->withInput();
            }

            $request->session()->regenerate();

            // Track last login time and IP
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

    // Show registration page - redirect to dashboard if already logged in
    public function showRegister()
{
    if (Auth::guard('client')->check()) {
        return redirect()->route('client.dashboard');
    }

    $countries = Countries::getList('en');

    return view('client.auth.register', compact('countries'));
}


    // Handle registration form submission
    public function register(Request $request)
    {
        $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'email:rfc', 'max:255', 'unique:clients,email'],
            'password'     => [
                'required',
                'confirmed',
                Rules\Password::defaults()
                    ->min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'phone'        => ['nullable', 'string', 'max:20'],
        ]);

        // Create client with pending status - admin must approve before access
        Client::create([
            'company_name'  => $request->company_name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'phone'         => $request->phone,
            'status'        => 'pending',
            'trial_ends_at' => now()->addDays(14),
        ]);

        // Do not auto-login - account requires admin approval first
        return redirect()->route('client.login')
            ->with('success', 'Registration successful! Your account is pending admin approval.');
    }

    // Handle logout and clear session
    public function logout(Request $request)
    {
        Auth::guard('client')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('client.login');
    }
}
