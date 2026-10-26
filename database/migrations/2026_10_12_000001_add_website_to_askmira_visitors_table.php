<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The website a visitor entered in the homepage Ask Mira "Analyse my website"
 * tab (their own site, also for a competitor comparison). Filled by
 * PublicChatController::recordVisitor().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('askmira_visitors', function (Blueprint $table) {
            $table->string('website', 2048)->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('askmira_visitors', function (Blueprint $table) {
            $table->dropColumn('website');
        });
    }
};
