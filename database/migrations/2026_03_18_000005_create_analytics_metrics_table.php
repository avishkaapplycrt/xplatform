<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('data_source_id')->constrained('client_data_sources');
            $table->string('metric_type');
            $table->json('dimensions')->nullable();
            $table->decimal('value', 20, 4);
            $table->string('unit')->nullable();
            $table->timestamp('measured_at');
            $table->enum('granularity', ['realtime', '5min', 'hourly', 'daily', 'weekly'])->default('hourly');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'metric_type', 'measured_at']);
            $table->index(['data_source_id', 'granularity', 'measured_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_metrics');
    }
};