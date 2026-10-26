<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * askmira_visitors no longer records the visitor's country (it was looked up
 * from their IP address). down() restores the column and its index, empty.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('askmira_visitors', function (Blueprint $table) {
            $table->dropIndex('idx_visitor_country');
            $table->dropColumn('country');
        });
    }

    public function down(): void
    {
        Schema::table('askmira_visitors', function (Blueprint $table) {
            $table->string('country', 100)->nullable()->after('date');
            $table->index('country', 'idx_visitor_country');
        });
    }
};
