<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analytics_configs', function (Blueprint $table) {
            $table->unsignedBigInteger('industry_id')->nullable()->after('client_id');
        });

        // Backfill: match the old string to the industries table
        DB::statement('
            UPDATE analytics_configs ac
            JOIN industries i ON i.name = ac.industry
            SET ac.industry_id = i.id
            WHERE ac.industry IS NOT NULL
        ');

        Schema::table('analytics_configs', function (Blueprint $table) {
            $table->foreign('industry_id')->references('id')->on('industries')->nullOnDelete();
            $table->dropColumn(['industry', 'data_source_id']);
        });
    }

    public function down(): void
    {
        Schema::table('analytics_configs', function (Blueprint $table) {
            $table->string('industry')->nullable()->after('config_name');
            $table->unsignedBigInteger('data_source_id')->nullable()->after('client_id');
        });

        DB::statement('
            UPDATE analytics_configs ac
            JOIN industries i ON i.id = ac.industry_id
            SET ac.industry = i.name
            WHERE ac.industry_id IS NOT NULL
        ');

        Schema::table('analytics_configs', function (Blueprint $table) {
            $table->dropForeign(['industry_id']);
            $table->dropColumn('industry_id');
        });
    }
};
