<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revenue_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->date('forecast_date');
            $table->decimal('expected_revenue', 12, 2)->default(0);
            $table->integer('confidence_level')->default(50); // 1-100
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['client_id', 'forecast_date']);
            $table->index(['client_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revenue_forecasts');
    }
};
