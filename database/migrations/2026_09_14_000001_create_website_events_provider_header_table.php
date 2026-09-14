<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per customer — the main customer information behind the customer
 * list and X Platforms Risk Radar.
 *
 * x_platforms_risk_score and x_platforms_risk_level are calculated by X
 * Platforms itself, not sourced from Shopify.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_events_provider_header', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable()->index();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('customer_status')->nullable();
            $table->dateTime('created_date')->nullable();
            $table->dateTime('last_order_date')->nullable();
            $table->unsignedInteger('total_orders')->default(0);
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->decimal('average_order_value', 15, 2)->default(0);
            $table->unsignedInteger('days_since_last_order')->default(0);
            $table->boolean('marketing_opt_in')->default(false);
            $table->unsignedInteger('number_of_products_purchased')->default(0);
            $table->string('default_country')->nullable();
            $table->string('default_city')->nullable();
            $table->text('tags')->nullable();
            $table->text('customer_note')->nullable();
            $table->unsignedInteger('x_platforms_risk_score')->nullable();
            $table->string('x_platforms_risk_level')->nullable();
            $table->timestamps();

            $table->index('email');
            $table->index('last_order_date');
            $table->index('x_platforms_risk_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_events_provider_header');
    }
};
