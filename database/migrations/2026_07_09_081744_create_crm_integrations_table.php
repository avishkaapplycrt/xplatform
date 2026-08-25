<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_integrations', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->index(); // salesforce, hubspot, zoho, pipedrive, monday
            $table->string('connection_name');
            $table->string('status')->default('disconnected'); // connected, disconnected, error
            $table->text('api_key')->nullable();
            $table->text('api_secret')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->string('instance_url')->nullable(); // Salesforce-specific
            $table->string('portal_id')->nullable(); // HubSpot-specific
            $table->string('organization_id')->nullable(); // Monday.com-specific
            $table->json('settings')->nullable(); // provider-specific settings
            $table->json('sync_config')->nullable(); // fields to sync, sync frequency
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
        Schema::dropIfExists('crm_integrations');
    }
};