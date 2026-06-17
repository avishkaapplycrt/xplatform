<?php
// app/Http/Middleware/AnalyticsCors.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AnalyticsCors
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('OPTIONS')) {
            return response()->noContent()
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With')
                ->header('Access-Control-Max-Age', '86400');
        }
        
        $response = $next($request);
        
        // Always add CORS headers for collect endpoint
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'POST, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With');
        $response->headers->set('Access-Control-Max-Age', '86400');
        
        return $response;
    }
}