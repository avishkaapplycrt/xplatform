<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('behavioral_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->enum('segment', ['champion', 'loyal', 'at_risk', 'dormant', 'new']);
            $table->unsignedTinyInteger('intent_score');
            $table->unsignedTinyInteger('engagement_score');
            $table->unsignedTinyInteger('churn_score');
            $table->unsignedTinyInteger('loyalty_score');
            $table->unsignedTinyInteger('trust_score');
            $table->unsignedTinyInteger('frustration_score');
            $table->unsignedTinyInteger('buying_readiness');
            $table->unsignedTinyInteger('dropoff_risk');
            $table->unsignedTinyInteger('reactivation_potential');
            $table->unsignedTinyInteger('overall_score');
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('behavioral_profiles');
    }
};
