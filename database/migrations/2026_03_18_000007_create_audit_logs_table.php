<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable();
            $table->morphs('auditable');
            $table->string('action');
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->json('context')->nullable();
            
            // FIX: Use timestamp() instead of just created_at
            $table->timestamp('created_at')->nullable();
            // OR if you want both created and updated:
            // $table->timestamps();

            $table->index(['client_id', 'action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};