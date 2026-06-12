<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiToken;

class AnalyticsApiToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken() ?? $request->header('X-Analytics-Token');
        
        if (!$token) {
            return response()->json(['error' => 'Token required'], 401);
        }

        $apiToken = ApiToken::where('token', hash('sha256', $token))->first();
        
        if (!$apiToken) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        $apiToken->update(['last_used_at' => now()]);
        
        return $next($request);
    }
}