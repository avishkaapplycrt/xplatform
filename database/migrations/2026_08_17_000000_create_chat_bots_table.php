<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_bots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('clients')->cascadeOnDelete();
            $table->text('question');
            $table->text('answer');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_bots');
    }
};
