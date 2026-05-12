<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_selected_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')
                  ->constrained('clients')
                  ->onDelete('cascade');
            $table->foreignId('data_source_id')
                  ->constrained('data_sources')
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_selected_sources');
    }
};