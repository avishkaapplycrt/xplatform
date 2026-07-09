<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_integrations', function (Blueprint $table) {
            $table->id();
            $table->string('platform')->index(); // facebook, instagram, tiktok, youtube, linkedin
            $table->string('connection_name');
            $table->string('status')->default('disconnected'); // connected, disconnected, error, pending
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->string('page_id')->nullable(); // Facebook/Instagram page ID
            $table->string('account_id')->nullable(); // TikTok/YouTube account ID
            $table->string('channel_id')->nullable(); // YouTube channel ID
            $table->string('profile_url')->nullable();
            $table->string('username')->nullable();
            $table->string('profile_image')->nullable();
            $table->json('settings')->nullable(); // platform-specific settings
            $table->json('sync_config')->nullable(); // sync frequency, content types, metrics
            $table->json('metrics')->nullable(); // follower count, engagement rate, etc.
            $table->timestamp('last_sync_at')->nullable();
            $table->integer('sync_count')->default(0);
            $table->text('last_error')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_integrations');
    }
};