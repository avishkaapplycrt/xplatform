<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nps_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('survey_id'); // No FK constraint
            $table->unsignedBigInteger('customer_id'); // No FK - customers table may not exist
            $table->tinyInteger('score')->unsigned(); // 0-10
            $table->text('feedback')->nullable();
            $table->string('category'); // promoter, passive, detractor
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'survey_id']);
            $table->index(['customer_id', 'created_at']);
            $table->index('category');
            $table->index('score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nps_responses');
    }
};
