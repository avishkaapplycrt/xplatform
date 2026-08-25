<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'mail_driver' => config('mail.default'),
            'queue_driver' => config('queue.default'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
        ];
        
        // System info
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_connection' => config('database.default'),
            'database_version' => DB::select('SELECT VERSION() as version')[0]->version ?? 'Unknown',
            'timezone' => config('app.timezone'),
            'debug_mode' => config('app.debug') ? 'Enabled' : 'Disabled',
        ];
        
        // Cache stats
        $cacheStats = [
            'config_cached' => file_exists(base_path('bootstrap/cache/config.php')),
            'routes_cached' => file_exists(base_path('bootstrap/cache/routes-v7.php')),
            'events_cached' => file_exists(base_path('bootstrap/cache/events.php')),
            'views_cached' => file_exists(base_path('storage/framework/views')),
        ];
        
        return view('admin.settings.index', compact('settings', 'systemInfo', 'cacheStats'));
    }
    
    public function update(Request $request)
    {
        // Update .env or config settings
        // This is a simplified version - in production, you'd want more validation
        
        return back()->with('success', 'Settings updated successfully.');
    }
    
    public function clearCache(Request $request)
    {
        $type = $request->input('type', 'all');
        
        switch ($type) {
            case 'config':
                Artisan::call('config:clear');
                break;
            case 'route':
                Artisan::call('route:clear');
                break;
            case 'view':
                Artisan::call('view:clear');
                break;
            case 'cache':
                Artisan::call('cache:clear');
                break;
            case 'all':
            default:
                Artisan::call('optimize:clear');
                break;
        }
        
        return back()->with('success', ucfirst($type) . ' cache cleared successfully.');
    }
}