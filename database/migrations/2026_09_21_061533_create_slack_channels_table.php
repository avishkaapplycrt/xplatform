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
        Schema::create('slack_channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_support_integration_id')->constrained()->cascadeOnDelete();
            $table->string('channel_id')->unique(); // Slack's own channel id, e.g. C0123ABC
            $table->string('name')->nullable();
            $table->boolean('is_private')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->unsignedInteger('member_count')->default(0);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slack_channels');
    }
};
