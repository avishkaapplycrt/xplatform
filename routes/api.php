<?php

use App\Http\Controllers\Api\AnalyticsController;
use Illuminate\Support\Facades\Route;

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