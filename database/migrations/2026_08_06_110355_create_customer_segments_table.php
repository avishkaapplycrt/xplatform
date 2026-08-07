<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('rules'); // Array of filter rules
            $table->integer('customer_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['client_id', 'is_active']);
        });

        // Pivot table: customer_segment (many-to-many)
        Schema::create('customer_segment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->foreignId('customer_segment_id')->constrained('customer_segments')->onDelete('cascade');
            $table->timestamp('assigned_at')->useCurrent();

            $table->unique(['customer_id', 'customer_segment_id']);
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_segment');
        Schema::dropIfExists('customer_segments');
    }
};
