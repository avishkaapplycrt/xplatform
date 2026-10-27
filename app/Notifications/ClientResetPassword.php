<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;

/**
 * The "Reset your password" email for clients. Laravel's own one links to a
 * route named password.reset, which doesn't exist here — the client reset
 * page is client.password.reset (/app/reset-password/{token}).
 */
class ClientResetPassword extends ResetPassword
{
    protected function resetUrl($notifiable)
    {
        return route('client.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }
}
