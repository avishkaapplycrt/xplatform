<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientAuthController;
use App\Http\Controllers\ClientDashboardController;
use App\Http\Controllers\DataSourceController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\BusinessInsightsController;
use App\Http\Controllers\ClientSettingsController;
use App\Http\Controllers\MasterAdminAuthController;
use App\Http\Controllers\MasterAdminDashboardController;
use App\Http\Controllers\ClientManagementController;
use App\Http\Controllers\AdminAnalyticsController;
use App\Http\Controllers\AdminSettingsController;

// Public
Route::get('/', function () {
    return view('welcome');
})->name('home');

// ==========================================
// MASTER ADMIN ROUTES (NO DOMAIN CONSTRAINT)
// ==========================================
Route::prefix('admin')->name('admin.')->group(function () {
    
    Route::middleware('guest:master_admin')->group(function () {
        Route::get('login', [MasterAdminAuthController::class, 'showLogin'])->name('login');
        Route::post('login', [MasterAdminAuthController::class, 'login']);
    });
    
    Route::middleware('auth:master_admin')->group(function () {
        Route::get('dashboard', [MasterAdminDashboardController::class, 'index'])->name('dashboard');
        
        // Clients
        Route::get('clients', [ClientManagementController::class, 'index'])->name('clients.index');
        Route::get('clients/pending', [ClientManagementController::class, 'pending'])->name('clients.pending');
        Route::get('clients/{client}', [ClientManagementController::class, 'show'])->name('clients.show');
        Route::post('clients/{client}/approve', [ClientManagementController::class, 'approve'])->name('clients.approve');
        Route::post('clients/{client}/suspend', [ClientManagementController::class, 'suspend'])->name('clients.suspend');
        Route::post('clients/{client}/activate', [ClientManagementController::class, 'activate'])->name('clients.activate');
        Route::delete('clients/{client}', [ClientManagementController::class, 'destroy'])->name('clients.destroy');
        
        // Analytics Overview
        Route::get('analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');
        
        // Settings
        Route::get('settings', [AdminSettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [AdminSettingsController::class, 'update'])->name('settings.update');
        
        Route::post('logout', [MasterAdminAuthController::class, 'logout'])->name('logout');
    });
});

// ==========================================
// CLIENT ROUTES
// ==========================================
Route::middleware('guest:client')->prefix('app')->group(function () {
    Route::get('login', [ClientAuthController::class, 'showLogin'])->name('client.login');
    Route::post('login', [ClientAuthController::class, 'login']);
    Route::get('register', [ClientAuthController::class, 'showRegister'])->name('client.register');
    Route::post('register', [ClientAuthController::class, 'register']);
});

Route::middleware(['auth:client', 'client.active'])->prefix('app')->name('client.')->group(function () {
    Route::get('dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
    Route::get('/', function () { return redirect()->route('client.dashboard'); });
    
    // Data Sources
    Route::get('sources', [DataSourceController::class, 'index'])->name('sources.index');
    Route::get('sources/create', [DataSourceController::class, 'create'])->name('sources.create');
    Route::post('sources', [DataSourceController::class, 'store'])->name('sources.store');
    Route::post('sources/{source}/test', [DataSourceController::class, 'test'])->name('sources.test');
    Route::delete('sources/{source}', [DataSourceController::class, 'destroy'])->name('sources.destroy');
    
    // Analytics
    Route::get('analytics/metrics', [AnalyticsController::class, 'getMetrics'])->name('analytics.metrics');
    Route::get('analytics/insights', [BusinessInsightsController::class, 'index'])->name('analytics.insights');
    
    // Settings
    Route::get('settings/profile', [ClientSettingsController::class, 'profile'])->name('settings.profile');
    Route::get('settings/billing', [ClientSettingsController::class, 'billing'])->name('settings.billing');
    
    Route::post('logout', [ClientAuthController::class, 'logout'])->name('logout');
});