<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_health_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('health_score');
            $table->text('summary');
            $table->json('strengths');
            $table->json('weaknesses');
            $table->json('opportunities');
            $table->timestamp('generated_at');
            $table->timestamps();

            // One row per client — regenerating replaces it rather than piling up history.
            $table->unique('client_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_health_snapshots');
    }
};
