<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['ping', 'publicStats']);
    }

    /**
     * Ping endpoint for connectivity test
     */
    public function ping(Request $request)
    {
        return response()->json([
            'success' => true,
            'app_name' => config('app.name'),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
        ]);
    }

    /**
     * Get users data
     */
    public function users(Request $request)
    {
        $since = $request->input('since', now()->subDays(30)->toDateTimeString());
        
        $users = \App\Models\User::where('created_at', '>=', $since)
            ->select('id', 'name', 'email', 'created_at', 'updated_at')
            ->get();

        return response()->json([
            'total' => $users->count(),
            'users' => $users,
        ]);
    }

    /**
     * Get page views / visits (if you have tracking table)
     */
    public function pageViews(Request $request)
    {
        $since = $request->input('since', now()->subDays(30)->toDateTimeString());
        
        // If you have a page_views table or use Laravel Analytics package
        $views = DB::table('page_views')
            ->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->get();

        return response()->json([
            'views' => $views,
            'total' => $views->sum('count'),
        ]);
    }

    /**
     * Get orders / transactions (if e-commerce)
     */
    public function orders(Request $request)
    {
        $since = $request->input('since', now()->subDays(30)->toDateTimeString());
        
        $orders = DB::table('orders')
            ->where('created_at', '>=', $since)
            ->select('id', 'user_id', 'total', 'status', 'created_at')
            ->get();

        $revenue = $orders->where('status', 'completed')->sum('total');

        return response()->json([
            'orders' => $orders,
            'total_orders' => $orders->count(),
            'total_revenue' => $revenue,
        ]);
    }

    /**
     * Get custom events
     */
    public function events(Request $request)
    {
        $since = $request->input('since', now()->subDays(30)->toDateTimeString());
        $eventType = $request->input('type');
        
        $query = DB::table('custom_events')
            ->where('created_at', '>=', $since);
            
        if ($eventType) {
            $query->where('event_type', $eventType);
        }

        $events = $query->orderBy('created_at', 'desc')
            ->limit(1000)
            ->get();

        return response()->json([
            'events' => $events,
            'total' => $events->count(),
        ]);
    }

    /**
     * Get all stats in one call
     */
    public function stats(Request $request)
    {
        $since = $request->input('since', now()->subDays(30)->toDateTimeString());

        return response()->json([
            'users' => [
                'total' => \App\Models\User::count(),
                'new' => \App\Models\User::where('created_at', '>=', $since)->count(),
            ],
            'page_views' => DB::table('page_views')->where('created_at', '>=', $since)->count(),
            'orders' => [
                'total' => DB::table('orders')->where('created_at', '>=', $since)->count(),
                'revenue' => DB::table('orders')->where('created_at', '>=', $since)->where('status', 'completed')->sum('total'),
            ],
            'app' => [
                'name' => config('app.name'),
                'url' => config('app.url'),
            ],
        ]);
    }
}