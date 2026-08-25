<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('email_campaigns');

        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('email_address');
            $table->timestamp('sent_at');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->enum('device_type', ['mobile', 'desktop', 'tablet', 'other'])->nullable();
            $table->unsignedInteger('time_to_open_minutes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
