<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automated_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('customer_id'); // No FK - customers table may not exist
            $table->string('template');
            $table->timestamp('scheduled_at');
            $table->timestamp('sent_at')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->string('channel')->default('email'); // email, sms, in_app
            $table->string('status')->default('scheduled'); // scheduled, sent, failed, cancelled
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automated_checkins');
    }
};
