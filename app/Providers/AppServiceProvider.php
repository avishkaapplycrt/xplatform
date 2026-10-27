<?php

namespace App\Providers;

use App\Models\UserEvent;
use App\Observers\UserEventObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        UserEvent::observe(UserEventObserver::class);

        // ngrok (and any real reverse proxy) terminates HTTPS itself and
        // forwards plain HTTP to this app, so Laravel sees every request as
        // insecure and generates http:// asset/URL links even though the
        // browser is on https:// — causing mixed-content blocking. Trust the
        // proxy's X-Forwarded-Proto header instead of the raw connection.
        if (request()->header('X-Forwarded-Proto') === 'https') {
            URL::forceScheme('https');
        }
    }
}
