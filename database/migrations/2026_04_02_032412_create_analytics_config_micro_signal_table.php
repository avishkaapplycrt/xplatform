<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_config_micro_signal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analytics_config_id')->constrained('analytics_configs')->cascadeOnDelete();
            $table->foreignId('micro_signal_id')->constrained('micro_signals')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_config_micro_signal');
    }
};