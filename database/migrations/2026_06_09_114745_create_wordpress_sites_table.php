<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wordpress_sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('site_name');
            $table->string('site_url');
            $table->string('site_id')->unique();
            $table->string('api_type')->default('rest_poll');
            $table->string('auth_type')->default('application_password');
            $table->text('auth_credentials')->nullable();
            $table->json('connection_config')->nullable();
            $table->string('sync_frequency')->default('6hours');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wordpress_sites');
    }
};