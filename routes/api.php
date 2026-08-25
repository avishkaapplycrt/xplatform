<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AnalyticsCollectController;
use App\Http\Controllers\Api\AnalyticsDashboardController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// ==========================================
// AUTHENTICATION ROUTES (Public)
// ==========================================
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// ==========================================
// PUBLIC TRACKING ENDPOINTS (No Auth Required)
// ==========================================
// CORS middleware applied explicitly for cross-origin tracking
Route::post('/collect', [AnalyticsCollectController::class, 'collect'])
    ->middleware([\App\Http\Middleware\AnalyticsCors::class]);
Route::options('/collect', [AnalyticsCollectController::class, 'options'])
    ->middleware([\App\Http\Middleware\AnalyticsCors::class]);

// ==========================================
// EXISTING ANALYTICS API (Your Current Routes)
// ==========================================
Route::prefix('analytics')->group(function () {
    Route::get('/ping', [AnalyticsController::class, 'ping']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/users', [AnalyticsController::class, 'users']);
        Route::get('/page-views', [AnalyticsController::class, 'pageViews']);
        Route::get('/orders', [AnalyticsController::class, 'orders']);
        Route::get('/events', [AnalyticsController::class, 'events']);
        Route::get('/stats', [AnalyticsController::class, 'stats']);
    });
});

// ==========================================
// NEW: WEBSITE ANALYTICS DASHBOARD API
// ==========================================
// Protected routes for viewing tracked website data
Route::middleware(['auth:sanctum'])->prefix('analytics/sites')->group(function () {
    
    // Site Management
    Route::get('/', [AnalyticsDashboardController::class, 'listSites']);
    Route::post('/', [AnalyticsDashboardController::class, 'createSite']);
    Route::get('/{siteId}', [AnalyticsDashboardController::class, 'showSite']);
    Route::put('/{siteId}', [AnalyticsDashboardController::class, 'updateSite']);
    Route::delete('/{siteId}', [AnalyticsDashboardController::class, 'deleteSite']);
    Route::post('/{siteId}/regenerate-key', [AnalyticsDashboardController::class, 'regenerateApiKey']);
    
    // Dashboard Overview
    Route::get('/{siteId}/overview', [AnalyticsDashboardController::class, 'overview']);
    Route::get('/{siteId}/realtime', [AnalyticsDashboardController::class, 'realtime']);
    
    // Time Series Data (for charts)
    Route::get('/{siteId}/timeseries', [AnalyticsDashboardController::class, 'timeseries']);
    
    // Breakdown Reports
    Route::get('/{siteId}/countries', [AnalyticsDashboardController::class, 'countries']);
    Route::get('/{siteId}/pages', [AnalyticsDashboardController::class, 'pages']);
    Route::get('/{siteId}/devices', [AnalyticsDashboardController::class, 'devices']);
    Route::get('/{siteId}/referrers', [AnalyticsDashboardController::class, 'referrers']);
    Route::get('/{siteId}/campaigns', [AnalyticsDashboardController::class, 'campaigns']);
    
    // Session Details
    Route::get('/{siteId}/sessions', [AnalyticsDashboardController::class, 'sessions']);
    Route::get('/{siteId}/sessions/{sessionId}', [AnalyticsDashboardController::class, 'sessionDetail']);
    
    // Export Data
    Route::get('/{siteId}/export', [AnalyticsDashboardController::class, 'export']);
});