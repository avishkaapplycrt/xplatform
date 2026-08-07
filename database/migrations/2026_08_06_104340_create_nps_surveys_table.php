<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nps_surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('question')->default('How likely are you to recommend us to a friend or colleague?');
            $table->string('send_to')->default('all'); // all, segment, specific
            $table->unsignedBigInteger('segment_id')->nullable(); // No FK - segments table may not exist
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->integer('sent_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['client_id', 'is_active']);
            $table->index('segment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nps_surveys');
    }
};
