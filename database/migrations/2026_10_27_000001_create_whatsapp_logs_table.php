<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per WhatsApp send attempt (single composer and "Send to all"),
 * the WhatsApp counterpart of email_logs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('phone', 32);
            $table->string('recipient_name')->nullable();
            $table->text('body');
            $table->enum('type', ['single', 'bulk'])->default('single');
            $table->unsignedInteger('bulk_count')->default(1);
            $table->enum('status', ['sent', 'failed'])->default('sent');
            $table->string('wa_message_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->index(['client_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_logs');
    }
};
