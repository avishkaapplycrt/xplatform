<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_data_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('connection_name');
            $table->enum('db_type', ['mysql', 'postgresql', 'mongodb', 'sqlserver', 'snowflake']);
            $table->text('host');
            $table->text('port');
            $table->text('database_name');
            $table->text('username');
            $table->text('password');
            $table->text('ssl_ca')->nullable();
            $table->json('connection_options')->nullable();
            $table->enum('status', ['pending', 'connected', 'failed', 'disconnected'])->default('pending');
            $table->text('last_error')->nullable();
            $table->timestamp('last_tested_at')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->json('schema_mapping')->nullable();
            $table->json('custom_queries')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index('connected_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_data_sources');
    }
};