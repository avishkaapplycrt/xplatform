<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laravel_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('laravel_sites')->onDelete('cascade');
            $table->string('event_type'); // page_view, user_login, user_register, order, etc.
            $table->string('entity_id')->nullable();
            $table->json('payload');
            $table->timestamp('event_created_at')->nullable();
            $table->timestamp('synced_at')->useCurrent();
            $table->timestamps();

            $table->index(['site_id', 'event_type', 'event_created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laravel_events');
    }
};