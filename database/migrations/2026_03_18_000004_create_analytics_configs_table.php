<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->unsignedBigInteger('data_source_id')->nullable(); // ← removed foreign key constraint
            $table->string('config_name')->nullable();
            $table->string('industry')->nullable();
            $table->string('status')->default('draft');
            $table->json('widget_layout')->nullable();
            $table->json('kpis')->nullable();
            $table->json('alert_rules')->nullable();
            $table->boolean('realtime_mode')->default(false);
            $table->integer('refresh_interval')->default(30);
            $table->boolean('ai_insights_enabled')->default(true);
            $table->json('insight_preferences')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_configs');
    }
};