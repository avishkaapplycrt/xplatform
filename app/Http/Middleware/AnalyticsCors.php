<?php
// app/Http/Middleware/AnalyticsCors.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AnalyticsCors
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Allow tracking requests from any origin
        if ($request->is('api/collect') || $request->is('api/collect/*')) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'POST, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With');
            $response->headers->set('Access-Control-Max-Age', '86400');
        }
        
        return $response;
    }
}