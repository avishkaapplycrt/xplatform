<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_layer_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('analysis_layer_id')->constrained('analysis_layers')->cascadeOnDelete();
            // Flexible bullet-point stats per layer, e.g. ["12 sources","100+ micro-signals","4.2M events/day"]
            $table->json('stats')->nullable();
            // 0–100 drives the health-dot fill in the UI
            $table->unsignedTinyInteger('health_score')->default(0);
            $table->enum('status', ['active', 'warning', 'inactive'])->default('inactive');
            $table->date('recorded_date');
            $table->timestamps();

            $table->unique(['client_id', 'analysis_layer_id', 'recorded_date'], 'cls_client_layer_date_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_layer_stats');
    }
};
