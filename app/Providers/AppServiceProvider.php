<?php

namespace App\Providers;

use App\Models\UserEvent;
use App\Observers\UserEventObserver;
use App\Services\HubSpotService;
use App\Services\SalesforceService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(HubSpotService::class, fn() => new HubSpotService(
            clientId:     config('crm.hubspot.client_id'),
            clientSecret: config('crm.hubspot.client_secret'),
            redirectUri:  config('crm.hubspot.redirect_uri'),
        ));

        $this->app->singleton(SalesforceService::class, fn() => new SalesforceService(
            clientId:     config('crm.salesforce.client_id', ''),
            clientSecret: config('crm.salesforce.client_secret', ''),
            redirectUri:  config('crm.salesforce.redirect_uri', ''),
        ));
    }

    public function boot(): void
    {
        UserEvent::observe(UserEventObserver::class);
    }
}
