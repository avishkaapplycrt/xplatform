<?php

namespace App\Providers;

use App\Models\UserEvent;
use App\Observers\UserEventObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        UserEvent::observe(UserEventObserver::class);
    }
}
