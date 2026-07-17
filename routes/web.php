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
use App\Http\Controllers\AdminDemoRequestController;
use App\Http\Controllers\AnalyticsDashboardViewController;
use App\Models\EmailLog;
use App\Models\CallLog;
use App\Models\BehavioralProfile;
use App\Http\Controllers\Analytics\LaravelSiteManagementController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\WebsiteConnectionController;
use App\Http\Controllers\EmailConnectionController;
use App\Http\Controllers\CrmConnectionController;
use App\Http\Controllers\SocialConnectionController;
use App\Http\Controllers\ChatSupportConnectionController;
use App\Http\Controllers\PaymentGatewayConnectionController;

// ─── Public ───────────────────────────────────────────────────────────────────
Route::get('/', fn() => view('welcome'))->name('home');
Route::get('simulator', fn() => view('simulator'))->name('simulator');
Route::get('case-studies', fn() => view('case-studies'))->name('case-studies');
Route::get('pricing', fn() => view('pricing'))->name('pricing');
Route::get('book-demo', [App\Http\Controllers\BookDemoController::class, 'show'])->name('book-demo');
Route::post('book-demo', [App\Http\Controllers\BookDemoController::class, 'store'])->name('book-demo.store');
Route::get('book-demo/thank-you', [App\Http\Controllers\BookDemoController::class, 'thankYou'])->name('book-demo.thank-you');
Route::get('about', fn() => view('about'))->name('about');
Route::get('industries', fn() => view('industries'))->name('industries');
Route::get('banking',   fn() => view('banking'))->name('banking');
Route::get('privacy',   fn() => view('privacy'))->name('privacy');
Route::get('terms',     fn() => view('terms'))->name('terms');
Route::get('security',  fn() => view('security'))->name('security');
Route::get('careers',   fn() => view('careers'))->name('careers');
Route::get('contact',        fn() => view('contact'))->name('contact');
Route::get('blog',           fn() => view('blog'))->name('blog');
Route::get('platform/architecture', fn() => view('platform-architecture'))->name('platform.architecture');

Route::get('/test-mail-config', function () {
    return response()->json([
        'mailer' => config('mail.default'),
        'host' => config('mail.mailers.smtp.host'),
        'port' => config('mail.mailers.smtp.port'),
        'username' => config('mail.mailers.smtp.username'),
        'password_set' => !empty(config('mail.mailers.smtp.password')),
        'encryption' => config('mail.mailers.smtp.encryption'),
        'from_address' => config('mail.from.address'),
        'admin_email' => config('mail.admin_email'),
    ]);
});

// Public API route for tracking script (no auth required - called from client websites)
Route::get('/api/tracking/script', [WebsiteConnectionController::class, 'getTrackingScript'])
    ->name('api.tracking.script');

// Event collection endpoint (called from tracking script)
Route::post('/api/events/collect', function (Illuminate\Http\Request $request) {
    // Validate tracking code
    $request->validate([
        'tracking_code' => 'required|string|size:32',
        'event_type'    => 'required|string|max:50',
        'data'          => 'nullable|array',
        'url'           => 'nullable|url',
        'timestamp'     => 'nullable|date',
    ]);

    // Find the connection
    $connection = App\Models\WebsiteConnection::where('tracking_code', $request->tracking_code)
        ->where('status', 'active')
        ->first();

    if (!$connection) {
        return response()->json(['success' => false, 'message' => 'Invalid tracking code'], 404);
    }

    // Store the event (you may want to use a queue for high volume)
    App\Models\WebsiteEvent::create([
        'connection_id' => $connection->id,
        'client_id'     => $connection->client_id,
        'tenant_id'     => $connection->tenant_id,
        'event_type'    => $request->event_type,
        'data'          => $request->data,
        'page_url'      => $request->url,
        'user_agent'    => $request->user_agent,
        'ip_address'    => $request->ip(),
        'screen_width'  => $request->input('data.screen.width'),
        'screen_height' => $request->input('data.screen.height'),
        'created_at'    => $request->timestamp ?? now(),
    ]);

    // Update last sync
    $connection->update(['last_sync_at' => now()]);

    return response()->json(['success' => true]);
})->name('api.events.collect');


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

        Route::get('demo-requests',                   [AdminDemoRequestController::class, 'index'])->name('demo-requests.index');
        Route::post('demo-requests/{demo}/approve',   [AdminDemoRequestController::class, 'approve'])->name('demo-requests.approve');
        Route::delete('demo-requests/{demo}',         [AdminDemoRequestController::class, 'destroy'])->name('demo-requests.destroy');

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

    Route::get('register/credentials',  [ClientAuthController::class, 'showCredentials'])->name('register.credentials');
    Route::post('register/credentials', [ClientAuthController::class, 'storeCredentials'])->name('credentials.store');
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
    Route::get('layers/rl',      fn() => view('client.layers.rl'))->name('layer.rl');
    Route::get('architecture',   fn() => view('client.architecture-overview'))->name('architecture');
    Route::get('data-collection',fn() => view('client.data-collection.mobile_events', array_merge(EmailLog::deliveryStats(), CallLog::callStats())))->name('data-collection');
    
    Route::get('behavioral-analytics', [DecisionCentreController::class, 'analytics'])->name('analytics');
    Route::get('aiml-dashboard', [DecisionCentreController::class, 'aimlDashboard'])->name('aiml.dashboard');
    Route::post('/email/send', [DecisionCentreController::class, 'sendEmail'])->name('email.send');
    Route::post('/email/send-bulk', [DecisionCentreController::class, 'sendBulkEmail'])->name('email.send.bulk');
    
    Route::get('email-templates', [DecisionCentreController::class, 'emailTemplates'])->name('email.templates');
    Route::post('email-templates', [DecisionCentreController::class, 'storeTemplate'])->name('email.templates.store');
    Route::put('email-templates/{template}', [DecisionCentreController::class, 'updateTemplate'])->name('email.templates.update');
    Route::delete('email-templates/{template}', [DecisionCentreController::class, 'deleteTemplate'])->name('email.templates.destroy');

    // Email Template Categories
    Route::get('email-template-categories', [DecisionCentreController::class, 'emailTemplateCategories'])->name('email.template.categories');
    Route::post('email-template-categories', [DecisionCentreController::class, 'storeCategory'])->name('email.template.categories.store');
    Route::put('email-template-categories/{category}', [DecisionCentreController::class, 'updateCategory'])->name('email.template.categories.update');
    Route::delete('email-template-categories/{category}', [DecisionCentreController::class, 'deleteCategory'])->name('email.template.categories.destroy');

    // Email Logs
    Route::get('/email-logs', [DecisionCentreController::class, 'emailLogs'])->name('email.logs');
    Route::get('/email-logs/{log}/detail', [DecisionCentreController::class, 'emailLogDetail'])->name('email.logs.detail');
    Route::post('/email-logs/{log}/retry', [DecisionCentreController::class, 'retryEmail'])->name('email.logs.retry');
    Route::delete('/email-logs/{log}', [DecisionCentreController::class, 'deleteEmailLog'])->name('email.logs.delete');

    // Tracking pixel (outside auth group - no middleware)
    Route::get('/track/email/{token}', [DecisionCentreController::class, 'trackOpen'])->name('email.track.open');

    // Database data sources
    Route::get('sources',                [DataSourceController::class, 'index'])->name('sources.index');
    Route::get('sources/create',         [DataSourceController::class, 'create'])->name('sources.create');
    Route::post('sources',               [DataSourceController::class, 'store'])->name('sources.store');
    Route::post('sources/{source}/test', [DataSourceController::class, 'test'])->name('sources.test');
    Route::delete('sources/{source}',    [DataSourceController::class, 'destroy'])->name('sources.destroy');

    Route::post('logout', [ClientAuthController::class, 'logout'])->name('logout');

    Route::prefix('laravel')->name('laravel.')->group(function () {
        Route::get('sites', [LaravelSiteManagementController::class, 'index'])->name('sites.index');
        Route::get('sites/create', [LaravelSiteManagementController::class, 'create'])->name('sites.create');
        Route::post('sites', [LaravelSiteManagementController::class, 'store'])->name('sites.store');
        Route::get('sites/{site}', [LaravelSiteManagementController::class, 'show'])->name('sites.show');
        Route::post('sites/test', [LaravelSiteManagementController::class, 'testConnection'])->name('sites.test');
        Route::delete('sites/{site}', [LaravelSiteManagementController::class, 'destroy'])->name('sites.destroy');
    });

    // Analytics Dashboard Routes
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [AnalyticsDashboardViewController::class, 'index'])->name('dashboard');
        Route::get('/sites/create', [AnalyticsDashboardViewController::class, 'createSiteForm'])->name('site.create');
        Route::post('/sites', [AnalyticsDashboardViewController::class, 'storeSite'])->name('site.store');
        Route::get('/sites/{siteId}', [AnalyticsDashboardViewController::class, 'showSite'])->name('site.detail');
        Route::get('/sites/{siteId}/data', [AnalyticsDashboardViewController::class, 'analyticsData'])->name('site.data');
        Route::delete('/sites/{siteId}', [AnalyticsDashboardViewController::class, 'deleteSite'])->name('site.delete');
    });

    /* ═══════════════════════════════════════════════════════════
       WEBSITE CONNECTION ROUTES
       ═══════════════════════════════════════════════════════════ */

    // Main connections page
    Route::get('/website-connections', [WebsiteConnectionController::class, 'index'])
        ->name('website-connections');

    // Platform-specific connection pages
    Route::get('/website-connections/wordpress', [WebsiteConnectionController::class, 'wordpress'])
        ->name('website-connections.wordpress');
    Route::get('/website-connections/wix', [WebsiteConnectionController::class, 'wix'])
        ->name('website-connections.wix');
    Route::get('/website-connections/shopify', [WebsiteConnectionController::class, 'shopify'])
        ->name('website-connections.shopify');
    Route::get('/website-connections/webflow', [WebsiteConnectionController::class, 'webflow'])
        ->name('website-connections.webflow');
    Route::get('/website-connections/squarespace', [WebsiteConnectionController::class, 'squarespace'])
        ->name('website-connections.squarespace');

    // CRUD operations
    Route::post('/website-connections', [WebsiteConnectionController::class, 'store'])
        ->name('website-connections.store');
    Route::get('/website-connections/{id}', [WebsiteConnectionController::class, 'show'])
        ->name('website-connections.show');
    Route::put('/website-connections/{id}', [WebsiteConnectionController::class, 'update'])
        ->name('website-connections.update');
    Route::delete('/website-connections/{id}', [WebsiteConnectionController::class, 'destroy'])
        ->name('website-connections.destroy');
    Route::get('/website-connections/{id}/verify', [WebsiteConnectionController::class, 'verify'])
        ->name('website-connections.verify');

    // Analytics for a specific connection
    Route::get('/website-connections/{id}/analytics', [WebsiteConnectionController::class, 'analytics'])
        ->name('website-connections.analytics');

    /* ═══════════════════════════════════════════════════════════
       EMAIL ENGAGEMENT CONNECTION ROUTES
       ═══════════════════════════════════════════════════════════ */

    // Main email connections page
    Route::get('/email-connections', [EmailConnectionController::class, 'index'])
        ->name('email-connections');

    // Provider-specific connection pages
    Route::get('/email-connections/mailchimp', [EmailConnectionController::class, 'mailchimp'])
        ->name('email-connections.mailchimp');
    Route::get('/email-connections/mailchimp/oauth', [EmailConnectionController::class, 'redirectToMailChimp'])
    ->name('email-connections.mailchimp.oauth');
    Route::get('/email-connections/mailchimp/callback', [EmailConnectionController::class, 'handleMailChimpCallback'])
        ->name('email-connections.mailchimp.callback');
    Route::get('/email-connections/brevo', [EmailConnectionController::class, 'brevo'])
        ->name('email-connections.brevo');
    Route::get('/email-connections/constantcontact', [EmailConnectionController::class, 'constantcontact'])
        ->name('email-connections.constantcontact');
    Route::get('/email-connections/mailerlite', [EmailConnectionController::class, 'mailerlite'])
        ->name('email-connections.mailerlite');
    Route::get('/email-connections/moosend', [EmailConnectionController::class, 'moosend'])
        ->name('email-connections.moosend');

    // CRUD operations
    Route::post('/email-connections', [EmailConnectionController::class, 'store'])
        ->name('email-connections.store');
    Route::delete('/email-connections/{id}', [EmailConnectionController::class, 'destroy'])
        ->name('email-connections.destroy');
    Route::get('/email-connections/{id}/verify', [EmailConnectionController::class, 'verify'])
        ->name('email-connections.verify');
    Route::post('/email-connections/{id}/sync', [EmailConnectionController::class, 'sync'])
        ->name('email-connections.sync');

    /* ═══════════════════════════════════════════════════════════
       CRM CONNECTION ROUTES (CrmConnectionController)
       ═══════════════════════════════════════════════════════════ */

    // Main connections page
    Route::get('/crm-connections', [CrmConnectionController::class, 'index'])
        ->name('crm-connections');

    // Zoho OAuth Routes (FIXED PATHS - must be before wildcard routes)
    Route::get('/crm-connections/zoho/oauth', [CrmConnectionController::class, 'redirectToZoho'])
        ->name('crm.zoho.oauth');
    Route::get('/crm-connections/zoho/callback', [CrmConnectionController::class, 'handleZohoCallback'])
        ->name('crm.zoho.callback');

    // Wildcard routes with {provider} parameter
    Route::get('/crm-connections/{provider}/connect', [CrmConnectionController::class, 'create'])
        ->name('crm.connect');
    Route::post('/crm-connections/{provider}', [CrmConnectionController::class, 'store'])
        ->name('crm.store');
    Route::post('/crm-connections/{provider}/test', [CrmConnectionController::class, 'test'])
        ->name('crm.test');
    Route::post('/crm-connections/{provider}/sync', [CrmConnectionController::class, 'sync'])
        ->name('crm.sync');
    Route::post('/crm-connections/{provider}/disconnect', [CrmConnectionController::class, 'disconnect'])
        ->name('crm.disconnect');
    Route::delete('/crm-connections/{provider}', [CrmConnectionController::class, 'destroy'])
        ->name('crm.destroy');
    Route::get('/crm-connections/status', [CrmConnectionController::class, 'status'])
        ->name('crm.status');

    // Monday.com OAuth Routes (FIXED PATHS - must be before wildcard routes)
    Route::get('/crm-connections/monday/oauth', [CrmConnectionController::class, 'redirectToMonday'])
    ->name('crm.monday.oauth');
    Route::get('/crm-connections/monday/callback', [CrmConnectionController::class, 'handleMondayCallback'])
    ->name('crm.monday.callback');

    // ============================================
    // SOCIAL MEDIA CONNECTIONS ROUTES
    // ============================================
    Route::prefix('social')->group(function () {
        // Listing page - shows all social platforms
        Route::get('/', [SocialConnectionController::class, 'index'])
            ->name('social-connections');

        // ── Facebook OAuth (fixed paths — must be BEFORE {platform} wildcards)
        Route::get('/facebook/oauth', [SocialConnectionController::class, 'redirectToFacebook'])
            ->name('social.facebook.oauth');
        Route::get('/facebook/callback', [SocialConnectionController::class, 'handleFacebookCallback'])
            ->name('social.facebook.callback');

        Route::get('/instagram/oauth', [SocialConnectionController::class, 'redirectToInstagram'])
            ->name('social.instagram.oauth');
        Route::get('/instagram/callback', [SocialConnectionController::class, 'handleInstagramCallback'])
            ->name('social.instagram.callback');

        // ── LinkedIn OAuth
        Route::get('/linkedin/oauth', [SocialConnectionController::class, 'redirectToLinkedin'])
            ->name('social.linkedin.oauth');
        Route::get('/linkedin/callback', [SocialConnectionController::class, 'handleLinkedinCallback'])
            ->name('social.linkedin.callback');

        // Connect form for specific platform
        Route::get('/{platform}/connect', [SocialConnectionController::class, 'create'])
            ->name('social.connect');

        // Store / update connection
        Route::post('/{platform}', [SocialConnectionController::class, 'store'])
            ->name('social.store');

        // Test connection
        Route::post('/{platform}/test', [SocialConnectionController::class, 'test'])
            ->name('social.test');

        // Sync data from social platform
        Route::post('/{platform}/sync', [SocialConnectionController::class, 'sync'])
            ->name('social.sync');

        // Disconnect (soft - keeps config, removes tokens)
        Route::post('/{platform}/disconnect', [SocialConnectionController::class, 'disconnect'])
            ->name('social.disconnect');

        // Delete connection permanently
        Route::delete('/{platform}', [SocialConnectionController::class, 'destroy'])
            ->name('social.destroy');

        // Get status of all connections (JSON)
        Route::get('/status', [SocialConnectionController::class, 'status'])
            ->name('social.status');
    });

    // ============================================
    // CHAT & SUPPORT CONNECTIONS ROUTES
    // ============================================
    Route::prefix('chat-support')->group(function () {
        // Listing page - shows all Chat & Support providers
        Route::get('/', [ChatSupportConnectionController::class, 'index'])
            ->name('chat-support-connections');

        // Connect form for specific provider
        Route::get('/{provider}/connect', [ChatSupportConnectionController::class, 'create'])
            ->name('chat-support.connect');

        // Store / update connection
        Route::post('/{provider}', [ChatSupportConnectionController::class, 'store'])
            ->name('chat-support.store');

        // Test connection
        Route::post('/{provider}/test', [ChatSupportConnectionController::class, 'test'])
            ->name('chat-support.test');

        // Sync data from provider
        Route::post('/{provider}/sync', [ChatSupportConnectionController::class, 'sync'])
            ->name('chat-support.sync');

        // Disconnect (soft - keeps config, removes tokens)
        Route::post('/{provider}/disconnect', [ChatSupportConnectionController::class, 'disconnect'])
            ->name('chat-support.disconnect');

        // Delete connection permanently
        Route::delete('/{provider}', [ChatSupportConnectionController::class, 'destroy'])
            ->name('chat-support.destroy');

        // Get status of all connections (JSON)
        Route::get('/status', [ChatSupportConnectionController::class, 'status'])
            ->name('chat-support.status');
    });

    // Payment Gateway Connections Routes
    Route::get('/payment-gateway-connections', [PaymentGatewayConnectionController::class, 'index'])
        ->name('payment-gateway-connections.index');

    Route::get('/payment-gateway-connections/{gateway}', [PaymentGatewayConnectionController::class, 'show'])
        ->name('payment-gateway-connections.show');

    Route::post('/payment-gateway-connections/{gateway}/connect', [PaymentGatewayConnectionController::class, 'connect'])
        ->name('payment-gateway-connections.connect');

    Route::patch('/payment-gateway-connections/{gateway}/disconnect', [PaymentGatewayConnectionController::class, 'disconnect'])
        ->name('payment-gateway-connections.disconnect');

    Route::patch('/payment-gateway-connections/{gateway}/toggle-active', [PaymentGatewayConnectionController::class, 'toggleActive'])
        ->name('payment-gateway-connections.toggle-active');

    Route::post('/payment-gateway-connections/{gateway}/test', [PaymentGatewayConnectionController::class, 'testConnection'])
        ->name('payment-gateway-connections.test');

    Route::delete('/payment-gateway-connections/{gateway}', [PaymentGatewayConnectionController::class, 'destroy'])
        ->name('payment-gateway-connections.destroy');
});

Route::middleware('analytics.token')->group(function () {
    Route::get('/users', [AnalyticsController::class, 'users']);
    Route::get('/page-views', [AnalyticsController::class, 'pageViews']);
    Route::get('/orders', [AnalyticsController::class, 'orders']);
    Route::get('/events', [AnalyticsController::class, 'events']);
    Route::get('/stats', [AnalyticsController::class, 'stats']);
});

Route::middleware(['auth'])->get('/admin/analytics-tokens', function () {
    $tokens = \App\Models\ApiToken::latest()->get();
    return view('admin.tokens', compact('tokens'));
})->name('admin.tokens');

Route::get('/debug-env-file', fn() => [
    'loaded_file' => app()->environmentFile(),
    'fb_app_id'   => config('services.facebook.client_id'),
]);