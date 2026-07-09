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
        Schema::create('website_connections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->index();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('platform', 50)->index(); // wordpress, wix, shopify, webflow, squarespace
            $table->string('site_url', 500);
            $table->string('site_name', 255)->nullable();
            $table->string('tracking_code', 100)->unique();
            $table->text('api_key')->nullable();
            $table->json('settings')->nullable();
            $table->string('status', 20)->default('active')->index(); // active, pause, error
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'platform']);
            $table->index(['client_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('website_connections');
    }
};
