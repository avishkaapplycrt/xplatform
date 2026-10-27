<?php

namespace App\Http\Controllers;

use App\Models\ClientSmtpSetting;
use App\Support\ClientMailSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;

/**
 * Email Settings → Sending Account: the client's own SMTP login, used for
 * every email they send from XPlatform (see App\Support\ClientMailSender).
 */
class ClientSmtpController extends Controller
{
    public function show()
    {
        $client = auth('client')->user();

        return view('client.email-sender', [
            'client'   => $client,
            'settings' => ClientSmtpSetting::where('client_id', $client->id)->first(),
        ]);
    }

    public function save(Request $request)
    {
        $client = auth('client')->user();
        $existing = ClientSmtpSetting::where('client_id', $client->id)->first();

        $data = $request->validate([
            'host'       => 'required|string|max:255',
            'port'       => 'required|integer|between:1,65535',
            'encryption' => 'required|in:tls,ssl,none',
            'username'   => 'required|string|max:255',
            'password'   => ($existing ? 'nullable' : 'required') . '|string|max:255',
            'from_name'  => 'nullable|string|max:255',
        ]);

        // Keep the saved password when the field is left blank on an update.
        $data['password'] = ($data['password'] ?? '') !== ''
            ? preg_replace('/\s+/', '', $data['password']) // Gmail shows App Passwords in groups of 4
            : $existing->password;

        // Check the login works before saving it, so a typo never replaces a working account.
        $candidate = new ClientSmtpSetting($data);
        $error = $this->checkLogin($candidate);
        if ($error) {
            return back()->withInput($request->except('password'))
                ->with('error', 'Could not sign in to ' . $data['host'] . ': ' . $error);
        }

        ClientSmtpSetting::updateOrCreate(
            ['client_id' => $client->id],
            $data + ['verified_at' => now()]
        );
        ClientMailSender::flush();

        return redirect()->route('client.email.sender')
            ->with('success', 'Sending account connected. Your emails now go out from ' . $client->email . '.');
    }

    public function test()
    {
        $client = auth('client')->user();
        if (!ClientMailSender::settings()) {
            return back()->with('error', 'Connect a sending account first.');
        }

        try {
            ClientMailSender::raw(
                "This is a test email from XPlatform.\n\nIf you can read this, emails you send from XPlatform go out from {$client->email}.",
                fn ($message) => $message->to($client->email)->subject('XPlatform test email')
            );
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'The test email could not be sent: ' . mb_substr($e->getMessage(), 0, 300));
        }

        return back()->with('success', 'Test email sent to ' . $client->email . '. Check that inbox.');
    }

    public function destroy()
    {
        ClientSmtpSetting::where('client_id', auth('client')->id())->delete();
        ClientMailSender::flush();

        return redirect()->route('client.email.sender')
            ->with('success', 'Sending account removed. Emails now go out from the shared XPlatform account.');
    }

    /** Opens an SMTP session and signs in. Returns null on success, or the reason it failed. */
    private function checkLogin(ClientSmtpSetting $settings): ?string
    {
        try {
            $transport = Mail::build($settings->mailerConfig())->getSymfonyTransport();
            if ($transport instanceof SmtpTransport) {
                $transport->start();
                $transport->stop();
            }
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, '535') || stripos($msg, 'Username and Password not accepted') !== false) {
                return 'the email or password was not accepted. For Gmail, use a 16-character App Password, not your normal password.';
            }
            return mb_substr($msg, 0, 300);
        }

        return null;
    }
}
