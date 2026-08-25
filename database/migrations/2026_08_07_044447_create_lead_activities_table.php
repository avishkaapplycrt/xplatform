<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('lead_id');
            $table->string('type'); // page_view, form_fill, email_open, email_click, demo_request, sales_routed, etc.
            $table->json('data')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'lead_id']);
            $table->index(['lead_id', 'type']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_activities');
    }
};
