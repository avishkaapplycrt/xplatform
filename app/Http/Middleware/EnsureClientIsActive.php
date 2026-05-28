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
            session()->flash('warning', 'Your account is pending approval. Some features are limited.');
            return $next($request);
        }

        if (!$client->is_active) {
            auth('client')->logout();
            return redirect()->route('client.login')
                ->with('error', 'Your subscription has expired. Please contact support to renew.');
        }

        app()->instance('current_client', $client);

        return $next($request);
    }
}