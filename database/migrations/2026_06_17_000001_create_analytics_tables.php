<?php
// database/migrations/2024_06_17_000001_create_analytics_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sites being tracked
        Schema::create('analytics_sites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id'); // Your existing client
            $table->string('domain')->index();
            $table->string('name');
            $table->string('tracking_id', 32)->unique(); // Public ID
            $table->string('api_key', 64)->unique(); // Secret key
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable(); // Track bots? IP anonymization?
            $table->timestamps();
            
            $table->index(['client_id', 'is_active']);
        });

        // Raw pageviews (high volume, partitioned/rotated)
        Schema::create('analytics_pageviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->index();
            $table->string('session_id', 64)->index(); // Visitor session
            $table->string('visitor_id', 64)->index(); // Daily visitor hash
            
            // Page data
            $table->string('url', 2048);
            $table->string('path', 1024)->index();
            $table->string('title', 500)->nullable();
            $table->string('referrer', 2048)->nullable();
            $table->string('referrer_domain', 255)->nullable()->index();
            
            // UTM tracking
            $table->string('utm_source', 255)->nullable()->index();
            $table->string('utm_medium', 255)->nullable();
            $table->string('utm_campaign', 255)->nullable();
            $table->string('utm_term', 255)->nullable();
            $table->string('utm_content', 255)->nullable();
            
            // Device/Geo (from IP/User-Agent)
            $table->string('country', 2)->nullable()->index();
            $table->string('country_name', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('region', 100)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            $table->string('device_type', 20)->nullable()->index(); // desktop, mobile, tablet
            $table->string('browser', 50)->nullable()->index();
            $table->string('browser_version', 30)->nullable();
            $table->string('os', 50)->nullable()->index();
            $table->string('os_version', 30)->nullable();
            
            // Screen resolution
            $table->smallInteger('screen_width')->nullable();
            $table->smallInteger('screen_height')->nullable();
            
            // Performance
            $table->integer('load_time_ms')->nullable();
            
            $table->timestamp('created_at')->index();
            
            // No updated_at for performance
        });

        // Sessions (aggregated per visit)
        Schema::create('analytics_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->index();
            $table->string('session_id', 64)->unique();
            $table->string('visitor_id', 64)->index();
            $table->string('first_page', 1024);
            $table->string('last_page', 1024)->nullable();
            $table->string('referrer', 2048)->nullable();
            $table->string('country', 2)->nullable();
            $table->string('device_type', 20)->nullable();
            $table->string('browser', 50)->nullable();
            $table->string('os', 50)->nullable();
            $table->smallInteger('pageviews')->default(0);
            $table->integer('duration_seconds')->default(0);
            $table->boolean('is_bounce')->default(true);
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            
            $table->index(['site_id', 'started_at']);
            $table->index(['site_id', 'country']);
        });

        // Hourly aggregated stats (for fast dashboard queries)
        Schema::create('analytics_hourly_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id');
            $table->date('date');
            $table->tinyInteger('hour');
            
            // Metrics
            $table->integer('pageviews')->default(0);
            $table->integer('unique_visitors')->default(0);
            $table->integer('sessions')->default(0);
            $table->integer('bounce_sessions')->default(0);
            
            // Dimensions (stored as JSON for flexibility)
            $table->json('countries')->nullable(); // {"US": 150, "IN": 80}
            $table->json('devices')->nullable(); // {"desktop": 200, "mobile": 100}
            $table->json('browsers')->nullable();
            $table->json('oses')->nullable();
            $table->json('pages')->nullable(); // Top pages
            $table->json('referrers')->nullable();
            $table->json('utm_sources')->nullable();
            
            $table->timestamps();
            
            $table->unique(['site_id', 'date', 'hour']);
            $table->index(['site_id', 'date']);
        });

        // Daily aggregated stats
        Schema::create('analytics_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id');
            $table->date('date');
            
            $table->integer('pageviews')->default(0);
            $table->integer('unique_visitors')->default(0);
            $table->integer('sessions')->default(0);
            $table->integer('bounce_sessions')->default(0);
            $table->decimal('avg_session_duration', 8, 2)->default(0);
            $table->decimal('bounce_rate', 5, 2)->default(0);
            
            $table->json('countries')->nullable();
            $table->json('devices')->nullable();
            $table->json('browsers')->nullable();
            $table->json('oses')->nullable();
            $table->json('pages')->nullable();
            $table->json('referrers')->nullable();
            $table->json('utm_sources')->nullable();
            
            $table->timestamps();
            
            $table->unique(['site_id', 'date']);
            $table->index(['site_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_daily_stats');
        Schema::dropIfExists('analytics_hourly_stats');
        Schema::dropIfExists('analytics_sessions');
        Schema::dropIfExists('analytics_pageviews');
        Schema::dropIfExists('analytics_sites');
    }
};