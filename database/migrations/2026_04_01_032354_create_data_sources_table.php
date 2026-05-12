<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_layer_id')
                  ->constrained('analysis_layers')
                  ->onDelete('cascade');
            $table->string('name');
            $table->string('subtitle')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_sources');
    }
};