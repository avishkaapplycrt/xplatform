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
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Token name/description (e.g., "Analytics Platform", "Mobile App")
            $table->string('token', 64)->unique(); // Hashed token stored in DB
            $table->json('abilities')->nullable(); // Permissions array: ['users:read', 'orders:read', 'stats:read']
            $table->timestamp('last_used_at')->nullable(); // Track when token was last used
            $table->timestamp('expires_at')->nullable(); // Optional token expiration
            $table->timestamps(); // created_at, updated_at

            // Index for faster lookups
            $table->index('token');
            $table->index('last_used_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_tokens');
    }
};