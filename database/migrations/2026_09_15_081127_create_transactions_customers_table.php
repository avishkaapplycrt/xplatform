<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A dedicated customer table for the Transaction Analytics feature —
 * separate from the general `customers` table, which is shared by
 * unrelated features (Customer Success, onboarding, health scores). Synced
 * straight from Stripe's Customer objects by StripeSyncService, one row per
 * real Stripe customer per client.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('gateway_customer_id'); // e.g. Stripe's cus_xxx
            $table->string('gateway')->default('stripe');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->decimal('lifetime_value', 12, 2)->default(0);
            $table->unsignedInteger('orders_count')->default(0);
            $table->timestamp('gateway_created_at')->nullable();
            $table->timestamps();

            $table->unique(['client_id', 'gateway_customer_id']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions_customers');
    }
};
