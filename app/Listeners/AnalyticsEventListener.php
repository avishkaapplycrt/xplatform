<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Logout;
use App\Http\Controllers\Api\WebhookController;

class AnalyticsEventListener
{
    private WebhookController $webhook;

    public function __construct()
    {
        $this->webhook = new WebhookController();
    }

    public function handleRegistered(Registered $event): void
    {
        $this->webhook->trackUserRegistration($event->user);
    }

    public function handleLogin(Login $event): void
    {
        $this->webhook->trackUserLogin($event->user);
    }

    public function handleLogout(Logout $event): void
    {
        $this->webhook->trackUserLogout($event->user);
    }
}