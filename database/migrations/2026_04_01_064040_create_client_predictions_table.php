<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('client_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('industry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prediction_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('client_predictions');
    }
};