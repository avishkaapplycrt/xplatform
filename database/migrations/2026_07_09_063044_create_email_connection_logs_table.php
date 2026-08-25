<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_connection_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->index();
            $table->unsignedBigInteger('connection_id')->index();
            $table->string('event', 50)->index();
            $table->string('platform', 50)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['client_id', 'created_at']);
            $table->index(['connection_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_connection_logs');
    }
};
