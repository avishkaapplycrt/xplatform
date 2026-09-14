<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per individual order/product/activity record for a customer.
 * header_id links each row back to its customer in
 * website_events_provider_header.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_events_provider_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('header_id')->constrained('website_events_provider_header')->onDelete('cascade');
            $table->string('order_id')->nullable();
            $table->dateTime('order_date')->nullable();
            $table->string('order_status')->nullable();
            $table->string('financial_status')->nullable();
            $table->decimal('order_total', 15, 2)->default(0);
            $table->string('currency')->nullable();
            $table->string('product_name')->nullable();
            $table->string('product_id')->nullable();
            $table->string('sku')->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('shipping', 15, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->string('fulfillment_status')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_country')->nullable();
            $table->string('product_category')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('order_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_events_provider_detail');
    }
};
