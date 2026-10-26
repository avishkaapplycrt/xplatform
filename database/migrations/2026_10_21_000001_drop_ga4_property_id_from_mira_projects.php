<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Removes mira_projects.ga4_property_id, from an earlier service-account GA4
 * connection that was replaced by "Connect Google" (mira_projects.ga4_property
 * and mira_google_connections). Safe where the column never existed.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('mira_projects', 'ga4_property_id')) {
            Schema::table('mira_projects', function (Blueprint $table) {
                $table->dropColumn('ga4_property_id');
            });
        }
    }

    public function down(): void
    {
        // Nothing to restore: the column is no longer used.
    }
};
