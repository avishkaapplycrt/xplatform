<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('connection_id')->index();
            $table->string('provider', 50)->index();
            $table->string('external_id');
            $table->string('email')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('company')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->unique(['connection_id', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_contacts');
    }
};
