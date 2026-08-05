<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Registration creates the account (step 1) and then asks for an industry
 * (step 2). A client who abandons between the two is fully logged in but has
 * no industry, so send them back to finish step 2 before anything else.
 *
 * Applied to the protected client routes only — the industry routes themselves
 * sit outside this middleware so there is no redirect loop.
 */
class EnsureClientOnboarded
{
    public function handle(Request $request, Closure $next): Response
    {
        $client = auth('client')->user();

        if ($client && !$client->industry_id) {
            return redirect()->route('client.industry');
        }

        return $next($request);
    }
}
