<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('lead_id');
            $table->decimal('behavior_score', 5, 2)->default(0);
            $table->decimal('demographic_score', 5, 2)->default(0);
            $table->decimal('engagement_score', 5, 2)->default(0);
            $table->decimal('total_score', 5, 2)->default(0);
            $table->string('qualification_status')->default('unscored');
            $table->decimal('conversion_probability', 5, 2)->default(0);
            $table->json('factors')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'lead_id']);
            $table->index(['client_id', 'total_score']);
            $table->index(['lead_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_scores');
    }
};
