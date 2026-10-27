<?php

namespace App\Support;

use App\Models\ClientSmtpSetting;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

/**
 * Sends client-initiated emails (composer, "Send to all", retries) from the
 * logged-in client's own address — clients.email — through that client's
 * own SMTP login (client_smtp_settings), so the From address is really theirs.
 *
 * Without a saved SMTP login it falls back to the shared mailer (.env MAIL_*);
 * the From/Reply-To are still set to the client, but providers such as Gmail
 * rewrite the From to the shared account.
 */
class ClientMailSender
{
    private static array $mailers = [];
    private static array $settings = [];

    /** Mail::raw() through the client's own mailer, with From/Reply-To set to the client. */
    public static function raw(string $body, callable $callback): void
    {
        self::mailer()->raw($body, function (Message $message) use ($callback) {
            $callback(self::apply($message));
        });
    }

    public static function apply(Message $message): Message
    {
        $client = Auth::guard('client')->user();
        $email = $client?->email;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $message;
        }

        $name = self::settings()?->from_name ?: ($client->company_name ?: null);

        return $message->from($email, $name)->replyTo($email, $name);
    }

    /** The client's own SMTP mailer, or the default mailer when none is saved. */
    public static function mailer(): Mailer
    {
        $settings = self::settings();
        if (!$settings) {
            return Mail::mailer();
        }

        return self::$mailers[$settings->client_id] ??= Mail::build($settings->mailerConfig());
    }

    public static function settings(): ?ClientSmtpSetting
    {
        $id = Auth::guard('client')->id();
        if (!$id) {
            return null;
        }

        // Looked up once per request — "Send to all" calls this for every recipient.
        if (!array_key_exists($id, self::$settings)) {
            self::$settings[$id] = ClientSmtpSetting::where('client_id', $id)->first();
        }

        return self::$settings[$id];
    }

    /** Forget the cached settings/mailer after the client saves or removes them. */
    public static function flush(): void
    {
        self::$settings = [];
        self::$mailers = [];
    }
}
