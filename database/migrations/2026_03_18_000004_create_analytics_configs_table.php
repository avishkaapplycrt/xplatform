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
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('data_source_id')->nullable()->constrained('client_data_sources')->nullOnDelete();
            $table->json('widget_layout')->nullable();
            $table->json('kpis')->nullable();
            $table->json('alert_rules')->nullable();
            $table->enum('realtime_mode', ['websocket', 'polling', 'static'])->default('websocket');
            $table->integer('refresh_interval')->default(30);
            $table->boolean('ai_insights_enabled')->default(true);
            $table->json('insight_preferences')->nullable();
            $table->timestamps();

            $table->unique(['client_id', 'data_source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_configs');
    }
};