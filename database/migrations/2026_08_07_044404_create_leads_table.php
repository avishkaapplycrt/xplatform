<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('job_title')->nullable();
            $table->string('source');
            $table->string('source_detail')->nullable();
            $table->string('status')->default('new'); // new, contacted, qualified, converted, lost, nurturing
            $table->string('qualification_status')->default('unscored'); // hot, warm, cold, unqualified, unscored
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->timestamp('last_scored_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'qualification_status']);
            $table->index(['client_id', 'status']);
            $table->index(['client_id', 'source']);
            $table->index('email');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
