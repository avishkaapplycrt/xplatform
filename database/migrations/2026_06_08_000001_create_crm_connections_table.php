<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('hubspot');
            $table->string('account_name')->nullable();
            $table->string('hub_domain')->nullable();
            $table->string('hub_id')->nullable();
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

    public function down(): void
    {
        Schema::dropIfExists('crm_connections');
    }
};
