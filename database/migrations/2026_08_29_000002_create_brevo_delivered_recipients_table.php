<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brevo_delivered_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('connection_id')->index();
            $table->string('campaign_id', 50);
            $table->string('email');
            $table->timestamps();

            $table->unique(['connection_id', 'campaign_id', 'email'], 'brevo_delivered_unique');
            $table->index(['connection_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brevo_delivered_recipients');
    }
};
