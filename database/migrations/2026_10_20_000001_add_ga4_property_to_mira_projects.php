<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Google Analytics 4 property a Mira Premium project reads its Traffic
 * analytics from (numeric property ID, e.g. 412345678).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mira_projects', function (Blueprint $table) {
            $table->string('ga4_property_id', 20)->nullable()->after('competitors');
        });
    }

    public function down(): void
    {
        Schema::table('mira_projects', function (Blueprint $table) {
            $table->dropColumn('ga4_property_id');
        });
    }
};
