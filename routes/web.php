<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientAuthController;
use App\Http\Controllers\ClientDashboardController;
use App\Http\Controllers\ClientOverviewController;
use App\Http\Controllers\ClientPasswordController;
use App\Http\Controllers\DataSourceController;
use App\Http\Controllers\MasterAdminAuthController;
use App\Http\Controllers\MasterAdminDashboardController;
use App\Http\Controllers\ClientManagementController;
use App\Http\Controllers\AdminAnalyticsController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\DecisionCentreController;
use App\Models\EmailLog;
use App\Models\CallLog;
use App\Models\BehavioralProfile;

// ─── Public ───────────────────────────────────────────────────────────────────
Route::get('/', fn() => view('welcome'))->name('home');
Route::get('simulator', fn() => view('simulator'))->name('simulator');
Route::get('case-studies', fn() => view('case-studies'))->name('case-studies');
Route::get('pricing', fn() => view('pricing'))->name('pricing');
Route::get('book-demo', [App\Http\Controllers\BookDemoController::class, 'show'])->name('book-demo');
Route::post('book-demo', [App\Http\Controllers\BookDemoController::class, 'store'])->name('book-demo.store');
Route::get('about', fn() => view('about'))->name('about');
Route::get('industries', fn() => view('industries'))->name('industries');


// ─── Master Admin ─────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('guest:master_admin')->group(function () {
        Route::get('login',  [MasterAdminAuthController::class, 'showLogin'])->name('login');
        Route::post('login', [MasterAdminAuthController::class, 'login']);
    });

    Route::middleware('auth:master_admin')->group(function () {
        Route::get('dashboard', [MasterAdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('clients',              [ClientManagementController::class, 'index'])->name('clients.index');
        Route::get('clients/pending',      [ClientManagementController::class, 'pending'])->name('clients.pending');
        Route::get('clients/{client}',     [ClientManagementController::class, 'show'])->name('clients.show');
        Route::post('clients/{client}/approve',  [ClientManagementController::class, 'approve'])->name('clients.approve');
        Route::post('clients/{client}/suspend',  [ClientManagementController::class, 'suspend'])->name('clients.suspend');
        Route::post('clients/{client}/activate', [ClientManagementController::class, 'activate'])->name('clients.activate');
        Route::delete('clients/{client}',  [ClientManagementController::class, 'destroy'])->name('clients.destroy');

        Route::get('analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');

        Route::get('settings',  [AdminSettingsController::class, 'index'])->name('settings.index');
        Route::put('settings',  [AdminSettingsController::class, 'update'])->name('settings.update');

        Route::post('logout', [MasterAdminAuthController::class, 'logout'])->name('logout');
    });
});

// ─── Client Auth (login / password reset) ─────────────────────────────────────
Route::middleware('guest:client')->prefix('app')->name('client.')->group(function () {
    Route::get('login',  [ClientAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [ClientAuthController::class, 'login']);

    Route::get('forgot-password',        [ClientPasswordController::class, 'showForgotForm'])->name('password.request');
    Route::post('forgot-password',       [ClientPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('reset-password/{token}', [ClientPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password',        [ClientPasswordController::class, 'resetPassword'])->name('password.update');
});

// ─── Client Registration (Steps 1–7) ──────────────────────────────────────────
Route::prefix('app')->name('client.')->group(function () {
    Route::get('register',  [ClientAuthController::class, 'showRegister'])->name('register');
    Route::post('register', [ClientAuthController::class, 'register']);

    Route::get('industry',  [ClientAuthController::class, 'showIndustry'])->name('industry');
    Route::post('industry', [ClientAuthController::class, 'storeIndustry'])->name('industry.store');

    Route::get('register/analysis-layers',  [ClientAuthController::class, 'showAnalysisLayers'])->name('register.analysis.layers');
    Route::post('register/analysis-layers', [ClientAuthController::class, 'storeAnalysisLayers'])->name('register.analysis.layers.store');

    Route::get('register/connect-data-source',  [ClientAuthController::class, 'showConnectDataSource'])->name('register.connect.data.source');
    Route::post('register/connect-data-source', [ClientAuthController::class, 'storeConnectDataSource'])->name('register.connect.data.source.store');

    Route::get('register/micro-signals',  [ClientAuthController::class, 'showMicroSignals'])->name('register.micro.signals');
    Route::post('register/micro-signals', [ClientAuthController::class, 'storeMicroSignals'])->name('register.micro.signals.store');

    Route::get('register/select-predictions',  [ClientAuthController::class, 'showSelectPredictions'])->name('register.select.predictions');
    Route::post('register/select-predictions', [ClientAuthController::class, 'storeSelectPredictions'])->name('register.select.predictions.store');

    Route::get('register/configure-actions',  [ClientAuthController::class, 'showConfigureActions'])->name('register.configure.actions');
    Route::post('register/configure-actions', [ClientAuthController::class, 'storeConfigureActions'])->name('register.configure.actions.store');
});

// ─── Client Protected ─────────────────────────────────────────────────────────
Route::middleware(['auth:client', 'client.active'])->prefix('app')->name('client.')->group(function () {
    Route::get('/',          fn() => redirect()->route('client.dashboard'));
    Route::get('dashboard',  [ClientDashboardController::class, 'index'])->name('dashboard');
    Route::get('company/overview', [ClientOverviewController::class, 'show'])->name('company.overview');

    // Layer pages
    Route::get('layers/l1',      fn() => view('client.data-collection.mobile_events', array_merge(EmailLog::deliveryStats(), CallLog::callStats())))->name('layer.l1');
    Route::get('layers/l2',      [DecisionCentreController::class, 'l2'])->name('layer.l2');
    Route::get('layers/l3',      [DecisionCentreController::class, 'l3'])->name('layer.l3');
    Route::get('layers/l4',      [DecisionCentreController::class, 'index'])->name('layer.l4');
    Route::get('layers/l5',      [DecisionCentreController::class, 'l5'])->name('layer.l5');
    Route::get('layers/l6',      [DecisionCentreController::class, 'l6'])->name('layer.l6');
    Route::get('layers/l7',      [DecisionCentreController::class, 'l7'])->name('layer.l7');
    Route::get('layers/l8',      [DecisionCentreController::class, 'l8'])->name('layer.l8');
    Route::get('architecture',   fn() => view('client.architecture-overview'))->name('architecture');
    Route::get('data-collection',fn() => view('client.data-collection.mobile_events', array_merge(EmailLog::deliveryStats(), CallLog::callStats())))->name('data-collection');

    // Data sources
    Route::get('sources',                [DataSourceController::class, 'index'])->name('sources.index');
    Route::get('sources/create',         [DataSourceController::class, 'create'])->name('sources.create');
    Route::post('sources',               [DataSourceController::class, 'store'])->name('sources.store');
    Route::post('sources/{source}/test', [DataSourceController::class, 'test'])->name('sources.test');
    Route::delete('sources/{source}',    [DataSourceController::class, 'destroy'])->name('sources.destroy');

    Route::post('logout', [ClientAuthController::class, 'logout'])->name('logout');
});
