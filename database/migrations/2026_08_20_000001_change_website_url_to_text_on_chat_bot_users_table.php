<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_bot_users', function (Blueprint $table) {
            $table->text('website_url')->change();
        });
    }

    public function down(): void
    {
        Schema::table('chat_bot_users', function (Blueprint $table) {
            $table->string('website_url')->change();
        });
    }
};
