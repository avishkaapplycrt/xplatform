<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('custom_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('metrics');           // Selected metrics
            $table->json('dimensions')->nullable(); // Group by dimensions
            $table->json('filters')->nullable();  // Applied filters
            $table->string('date_range')->default('30d'); // 7d, 30d, 90d, 1y, custom
            $table->string('chart_type')->default('table'); // table, line, bar, pie, doughnut, area
            $table->string('schedule')->default('none'); // none, daily, weekly, monthly
            $table->json('schedule_config')->nullable(); // Recipients, time, etc.
            $table->string('status')->default('active'); // active, paused, archived
            $table->string('share_token', 32)->nullable()->unique();
            $table->timestamp('share_expires_at')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index('share_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_reports');
    }
};
