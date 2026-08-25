<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('steps'); // Array of step objects
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['client_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_workflows');
    }
};
