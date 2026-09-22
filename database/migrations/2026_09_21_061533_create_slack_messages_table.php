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
        Schema::create('slack_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slack_channel_id')->constrained('slack_channels')->cascadeOnDelete();
            $table->string('ts'); // Slack's message timestamp id, unique within a channel
            $table->string('user_id')->nullable(); // Slack user id, e.g. U0123ABC
            $table->text('text')->nullable();
            $table->unsignedInteger('reply_count')->default(0);
            $table->unsignedInteger('reaction_count')->default(0);
            $table->timestamp('posted_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['slack_channel_id', 'ts']);
            $table->index('posted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slack_messages');
    }
};
