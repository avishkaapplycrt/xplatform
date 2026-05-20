<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_demos', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('company_name');
            $table->string('job_title');
            $table->string('company_size')->nullable();
            $table->string('industry')->nullable();
            $table->string('monthly_active_customers')->nullable();
            $table->string('monthly_revenue')->nullable();
            $table->string('primary_challenge')->nullable();
            $table->string('data_sources')->nullable();
            $table->text('demo_notes')->nullable();
            $table->date('demo_date')->nullable();
            $table->string('demo_time')->nullable();
            $table->string('timezone')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_demos');
    }
};
