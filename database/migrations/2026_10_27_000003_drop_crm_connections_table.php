<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * crm_connections belonged to an earlier CRM integration that was replaced by
 * crm_integrations (see CrmConnectionController). Nothing writes to it and it
 * is empty, so it goes, along with its model, controller, jobs and services.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('crm_connections');
    }

    public function down(): void
    {
        Schema::create('crm_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('hubspot');
            $table->string('account_name')->nullable();
            $table->string('hub_domain')->nullable();
            $table->string('hub_id')->nullable();
            $table->string('instance_url')->nullable();
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->json('scopes')->nullable();
            $table->string('status')->default('connected');
            $table->text('last_error')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->integer('contacts_synced')->default(0);
            $table->timestamps();
        });
    }
};
