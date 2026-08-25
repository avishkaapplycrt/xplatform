<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('customer_id')->nullable(); // No FK - customers table may not exist
            $table->unsignedBigInteger('product_id')->nullable();  // No FK - products table just created
            $table->decimal('amount', 12, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->string('status')->default('completed'); // pending, completed, refunded, failed
            $table->string('payment_method')->nullable();
            $table->string('transaction_reference')->nullable()->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'created_at']);
            $table->index('customer_id');
            $table->index('product_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
