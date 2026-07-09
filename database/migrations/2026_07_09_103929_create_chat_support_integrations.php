<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_support_integrations', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->index(); // whatsapp, slack, twilio, zendesk, tawk, intercom, livechat
            $table->string('connection_name');
            $table->string('status')->default('disconnected'); // connected, disconnected, error, pending
            $table->text('api_key')->nullable();
            $table->text('api_secret')->nullable();
            $table->text('access_token')->nullable();
            $table->text('auth_token')->nullable(); // Twilio-specific
            $table->string('account_sid')->nullable(); // Twilio-specific
            $table->string('phone_number')->nullable(); // WhatsApp/Twilio-specific
            $table->string('webhook_url')->nullable();
            $table->string('workspace_id')->nullable(); // Slack-specific
            $table->string('channel_id')->nullable(); // Slack/Tawk-specific
            $table->string('subdomain')->nullable(); // Zendesk-specific
            $table->string('app_id')->nullable(); // Intercom/Tawk-specific
            $table->string('license_id')->nullable(); // LiveChat/Tawk-specific
            $table->json('settings')->nullable(); // provider-specific settings
            $table->json('sync_config')->nullable(); // sync frequency, message types, auto-reply
            $table->json('metrics')->nullable(); // messages today, avg response time, satisfaction
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
        Schema::dropIfExists('chat_support_integrations');
    }
};