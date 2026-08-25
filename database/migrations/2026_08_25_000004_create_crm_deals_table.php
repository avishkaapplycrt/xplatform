<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_deals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('connection_id')->index();
            $table->string('provider', 50)->index();
            $table->string('external_id');
            $table->string('name')->nullable();
            $table->decimal('value', 14, 2)->default(0);
            $table->string('stage')->nullable();
            $table->string('status', 20)->default('open');
            $table->timestamp('close_date')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->unique(['connection_id', 'external_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_deals');
    }
};
