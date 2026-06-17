<?php
// database/migrations/2026_06_17_113400_add_last_activity_to_analytics_sessions.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analytics_sessions', function (Blueprint $table) {
            $table->timestamp('last_activity')->nullable()->after('ended_at');
        });
    }

    public function down(): void
    {
        Schema::table('analytics_sessions', function (Blueprint $table) {
            $table->dropColumn('last_activity');
        });
    }
};