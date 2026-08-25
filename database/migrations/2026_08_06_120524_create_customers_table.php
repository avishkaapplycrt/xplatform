<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('lifetime_value', 12, 2)->default(0);
            $table->unsignedBigInteger('onboarding_workflow_id')->nullable();
            $table->string('onboarding_status')->nullable(); // in_progress, completed, not_started
            $table->timestamp('onboarding_started_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->integer('support_tickets_count')->default(0);
            $table->integer('resolved_tickets_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'is_active']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
