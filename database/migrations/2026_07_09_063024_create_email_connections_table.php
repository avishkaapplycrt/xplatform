<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_connections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->index();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('platform', 50)->index(); // mailchimp, brevo, constantcontact, mailerlite, moosend
            $table->text('api_key');
            $table->string('account_name', 255)->nullable();
            $table->json('settings')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'platform']);
            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_connections');
    }
};
