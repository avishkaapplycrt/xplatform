<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_provider_audiences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('connection_id')->index();
            $table->unsignedBigInteger('client_id')->index();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('platform', 50)->index();
            $table->string('external_id');
            $table->string('name')->nullable();
            $table->unsignedInteger('member_count')->default(0);
            $table->decimal('open_rate', 5, 2)->default(0);
            $table->decimal('click_rate', 5, 2)->default(0);
            $table->timestamps();

            $table->unique(['connection_id', 'external_id']);
            $table->index(['client_id', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_provider_audiences');
    }
};
