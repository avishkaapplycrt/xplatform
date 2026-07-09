<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateway_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('gateway_name'); // stripe, shopify, zapier, webhooks, paypal, woocommerce
            $table->string('display_name');
            $table->text('api_key')->nullable();
            $table->text('api_secret')->nullable();
            $table->text('webhook_secret')->nullable();
            $table->string('account_id')->nullable();
            $table->string('merchant_id')->nullable();
            $table->string('environment')->default('sandbox'); // sandbox, production
            $table->string('currency')->default('USD');
            $table->text('webhook_url')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_connected')->default(false);
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['client_id', 'gateway_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_connections');
    }
};
