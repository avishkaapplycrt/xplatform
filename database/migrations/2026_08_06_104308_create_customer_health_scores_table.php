<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_health_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('customer_id'); // No FK - customers table may not exist
            $table->decimal('score', 5, 2)->default(50); // 0-100
            $table->decimal('engagement_score', 5, 2)->default(50);
            $table->decimal('transaction_score', 5, 2)->default(50);
            $table->decimal('support_score', 5, 2)->default(50);
            $table->decimal('nps_score', 5, 2)->default(50);
            $table->string('status')->default('healthy'); // healthy, at_risk, critical
            $table->json('recommendations')->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'customer_id']);
            $table->index(['client_id', 'status']);
            $table->index('score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_health_scores');
    }
};
