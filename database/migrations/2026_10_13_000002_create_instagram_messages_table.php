<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('instagram_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_integration_id')->constrained()->cascadeOnDelete();
            $table->string('message_id')->unique(); // Instagram's own message id
            $table->string('conversation_id')->index();
            $table->string('from_id')->nullable();
            $table->string('from_username')->nullable();
            $table->string('to_id')->nullable();
            $table->string('to_username')->nullable();
            $table->boolean('is_from_business')->default(false); // sent by the connected account
            $table->text('message')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instagram_messages');
    }
};
