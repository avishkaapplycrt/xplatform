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
        Schema::create('website_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('connection_id')->index();
            $table->unsignedBigInteger('client_id')->index();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('event_type', 50)->index(); // page_view, click, scroll_depth, form_submit, etc.
            $table->json('data')->nullable();
            $table->string('page_url', 1000)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->integer('screen_width')->nullable();
            $table->integer('screen_height')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['client_id', 'event_type', 'created_at']);
            $table->index(['connection_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('website_events');
    }
};
