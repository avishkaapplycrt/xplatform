<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upsell_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('customer_id')->nullable(); // No FK - customers table may not exist
            $table->unsignedBigInteger('product_id')->nullable();  // No FK constraint
            $table->unsignedBigInteger('original_product_id')->nullable(); // No FK constraint
            $table->string('strategy')->default('complementary'); // complementary, upgrade, accessory, bundle
            $table->integer('confidence_score')->default(50); // 1-100
            $table->decimal('expected_revenue', 12, 2)->default(0);
            $table->text('message')->nullable();
            $table->string('status')->default('pending'); // pending, sent, executed, dismissed
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index('customer_id');
            $table->index('product_id');
            $table->index('strategy');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upsell_recommendations');
    }
};
