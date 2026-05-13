<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('call_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('call_type', ['inbound', 'outbound']);
            $table->enum('status', ['answered', 'missed', 'abandoned']);
            $table->unsignedSmallInteger('duration_seconds')->nullable();
            $table->unsignedSmallInteger('wait_time_seconds')->default(0);
            $table->string('agent_name')->nullable();
            $table->enum('department', ['sales', 'support', 'billing', 'general']);
            $table->decimal('cost_usd', 8, 4)->default(0);
            $table->timestamp('called_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_logs');
    }
};
