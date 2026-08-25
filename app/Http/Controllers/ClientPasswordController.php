<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRules;

class ClientPasswordController extends Controller
{
    // Show "Forgot password" email form
    public function showForgotForm()
    {
        return view('client.auth.forgot-password');
    }

    // Send the reset link email
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email:rfc'],
        ]);

        $status = Password::broker('clients')->sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', 'A password reset link has been sent to your email address.');
        }

        // Don't reveal whether the email exists
        return back()->with('success', 'If that email address is registered, you will receive a reset link shortly.');
    }

    // Show the reset password form
    public function showResetForm(Request $request, string $token)
    {
        return view('client.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    // Process the password reset
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email:rfc'],
            'password' => [
                'required',
                'string',
                'confirmed',
                PasswordRules::min(8)->letters()->numbers(),
            ],
        ], [
            'password.confirmed' => 'Passwords do not match.',
        ]);

        $status = Password::broker('clients')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Client $client, string $password) {
                // Ensure new password is different from old
                if (Hash::check($password, $client->password)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'password' => 'Your new password must be different from your current password.',
                    ]);
                }

                $client->forceFill([
                    'password'       => $password, // cast: hashed
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($client));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('client.login')
                ->with('success', 'Your password has been reset. You can now log in.');
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
