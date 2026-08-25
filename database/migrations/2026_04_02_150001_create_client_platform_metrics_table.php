<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_platform_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->unsignedBigInteger('events_per_day')->nullable();
            $table->decimal('events_wow_pct', 5, 2)->nullable()->comment('Week-over-week % change, can be negative');
            $table->unsignedBigInteger('profiles_resolved')->nullable();
            $table->decimal('match_rate_pct', 5, 2)->nullable()->comment('Profile match rate percentage');
            $table->date('recorded_date');
            $table->timestamps();

            $table->unique(['client_id', 'recorded_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_platform_metrics');
    }
};
