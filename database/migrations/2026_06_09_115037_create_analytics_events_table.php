<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('wordpress_sites')->onDelete('cascade');
            $table->string('event_type');
            $table->string('wp_entity_id')->nullable();
            $table->json('payload');
            $table->timestamp('wp_created_at')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamp('synced_at')->useCurrent();
            $table->timestamps();

            $table->index(['site_id', 'event_type', 'wp_created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};