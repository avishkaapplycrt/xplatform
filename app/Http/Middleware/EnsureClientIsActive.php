<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $client = auth('client')->user();

        if (!$client) {
            return redirect()->route('client.login');
        }

        if ($client->status === 'suspended') {
            auth('client')->logout();
            return redirect()->route('client.login')
                ->with('error', 'Your account has been suspended. Please contact support.');
        }

        if ($client->status === 'pending') {
            return redirect()->route('client.dashboard')
                ->with('warning', 'Your account is pending approval. Some features are limited.');
        }

        if (!$client->is_active) {
            return redirect()->route('client.settings.billing')
                ->with('error', 'Your subscription has expired. Please renew to continue.');
        }

        app()->instance('current_client', $client);

        return $next($request);
    }
}