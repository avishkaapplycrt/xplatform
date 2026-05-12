<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            // Basic Info
            $table->string('name');
            $table->string('company_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Registration Steps
            $table->unsignedBigInteger('industry_id')->nullable();
            $table->string('size')->nullable();
            $table->string('website')->nullable();
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->text('address')->nullable();

            // Account
            $table->enum('status', ['pending', 'active', 'suspended', 'cancelled'])->default('pending');
            $table->string('timezone')->default('UTC');
            $table->string('currency', 3)->default('USD');
            $table->json('settings')->nullable();
            $table->string('avatar')->nullable();

            // Plan & Billing
            $table->enum('plan', ['free', 'starter', 'professional', 'enterprise'])->default('free');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            $table->json('limits')->nullable();

            // Security
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->rememberToken();
            $table->timestamps();

            // Indexes
            $table->index(['status', 'plan']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};