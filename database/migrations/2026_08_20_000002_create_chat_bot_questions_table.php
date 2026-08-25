<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_bot_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('industry_id')->nullable()->constrained('industries')->nullOnDelete();
            $table->text('question');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_bot_questions');
    }
};
