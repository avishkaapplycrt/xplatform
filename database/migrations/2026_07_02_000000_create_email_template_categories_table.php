<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_template_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('name', 50);
            $table->string('slug', 50);
            $table->string('color', 7)->default('#374151');
            $table->string('bg_color', 7)->default('#f3f4f6');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            $table->unique(['client_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_template_categories');
    }
};