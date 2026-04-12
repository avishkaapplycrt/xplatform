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
use App\Http\Controllers\PredictionSelectionController;
use App\Http\Controllers\ClientOverviewController;
use App\Http\Controllers\ClientPasswordController;

// Public
Route::get('/', function () {
    return view('welcome');
})->name('home');

// ==========================================
// MASTER ADMIN ROUTES
// ==========================================
Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('guest:master_admin')->group(function () {
        Route::get('login', [MasterAdminAuthController::class, 'showLogin'])->name('login');
        Route::post('login', [MasterAdminAuthController::class, 'login']);
    });

    Route::middleware('auth:master_admin')->group(function () {
        Route::get('dashboard', [MasterAdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('clients', [ClientManagementController::class, 'index'])->name('clients.index');
        Route::get('clients/pending', [ClientManagementController::class, 'pending'])->name('clients.pending');
        Route::get('clients/{client}', [ClientManagementController::class, 'show'])->name('clients.show');
        Route::post('clients/{client}/approve', [ClientManagementController::class, 'approve'])->name('clients.approve');
        Route::post('clients/{client}/suspend', [ClientManagementController::class, 'suspend'])->name('clients.suspend');
        Route::post('clients/{client}/activate', [ClientManagementController::class, 'activate'])->name('clients.activate');
        Route::delete('clients/{client}', [ClientManagementController::class, 'destroy'])->name('clients.destroy');

        Route::get('analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');

        Route::get('settings', [AdminSettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [AdminSettingsController::class, 'update'])->name('settings.update');

        Route::post('logout', [MasterAdminAuthController::class, 'logout'])->name('logout');
    });
});

// ==========================================
// CLIENT AUTH (login / forgot password only)
// ==========================================
Route::middleware('guest:client')->prefix('app')->name('client.')->group(function () {
    Route::get('login', [ClientAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [ClientAuthController::class, 'login']);

    Route::get('forgot-password', [ClientPasswordController::class, 'showForgotForm'])->name('password.request');
    Route::post('forgot-password', [ClientPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('reset-password/{token}', [ClientPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [ClientPasswordController::class, 'resetPassword'])->name('password.update');
});

// ==========================================
// CLIENT REGISTRATION FLOW (Steps 1–7)
// No middleware — all guards handled in controller
// ==========================================
Route::prefix('app')->name('client.')->group(function () {

    // Step 1 — Company Info
    Route::get('register', [ClientAuthController::class, 'showRegister'])->name('register');
    Route::post('register', [ClientAuthController::class, 'register']);

    // Step 2 — Industry
    Route::get('industry', [ClientAuthController::class, 'showIndustry'])->name('industry');
    Route::post('industry', [ClientAuthController::class, 'storeIndustry'])->name('industry.store');

    // Step 3 — Analysis Layers
    Route::get('register/analysis-layers', [ClientAuthController::class, 'showAnalysisLayers'])->name('register.analysis.layers');
    Route::post('register/analysis-layers', [ClientAuthController::class, 'storeAnalysisLayers'])->name('register.analysis.layers.store');

    // Step 4 — Connect Data Source
    Route::get('register/connect-data-source', [ClientAuthController::class, 'showConnectDataSource'])->name('register.connect.data.source');
    Route::post('register/connect-data-source', [ClientAuthController::class, 'storeConnectDataSource'])->name('register.connect.data.source.store');

    // Step 5 — Micro Signals
    Route::get('register/micro-signals', [ClientAuthController::class, 'showMicroSignals'])->name('register.micro.signals');
    Route::post('register/micro-signals', [ClientAuthController::class, 'storeMicroSignals'])->name('register.micro.signals.store');

    // Step 6 — Select Predictions
    Route::get('register/select-predictions', [ClientAuthController::class, 'showSelectPredictions'])->name('register.select.predictions');
    Route::post('register/select-predictions', [ClientAuthController::class, 'storeSelectPredictions'])->name('register.select.predictions.store');

    // Step 7 — Configure Actions
    Route::get('register/configure-actions', [ClientAuthController::class, 'showConfigureActions'])->name('register.configure.actions');
    Route::post('register/configure-actions', [ClientAuthController::class, 'storeConfigureActions'])->name('register.configure.actions.store');
});

// ==========================================
// CLIENT PROTECTED ROUTES
// ==========================================
Route::middleware(['auth:client', 'client.active'])->prefix('app')->name('client.')->group(function () {
    Route::get('dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
    Route::get('company/overview', [ClientOverviewController::class, 'show'])->name('company.overview');
    Route::get('/', function () { return redirect()->route('client.dashboard'); });

    Route::get('sources', [DataSourceController::class, 'index'])->name('sources.index');
    Route::get('sources/create', [DataSourceController::class, 'create'])->name('sources.create');
    Route::post('sources', [DataSourceController::class, 'store'])->name('sources.store');
    Route::post('sources/{source}/test', [DataSourceController::class, 'test'])->name('sources.test');
    Route::delete('sources/{source}', [DataSourceController::class, 'destroy'])->name('sources.destroy');

    Route::get('analytics/metrics', [AnalyticsController::class, 'getMetrics'])->name('analytics.metrics');
    Route::get('analytics/insights', [BusinessInsightsController::class, 'index'])->name('analytics.insights');

    Route::get('settings/profile', [ClientSettingsController::class, 'profile'])->name('settings.profile');
    Route::get('settings/billing', [ClientSettingsController::class, 'billing'])->name('settings.billing');

    Route::post('logout', [ClientAuthController::class, 'logout'])->name('logout');
});
