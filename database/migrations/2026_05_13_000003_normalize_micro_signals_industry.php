<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('micro_signals', 'industry_id')) {
            Schema::table('micro_signals', function (Blueprint $table) {
                $table->unsignedBigInteger('industry_id')->nullable()->after('micro_signal_category_id');
            });
        }

        if (Schema::hasColumn('micro_signals', 'industry')) {
            DB::statement('
                UPDATE micro_signals ms
                JOIN industries i ON i.name = ms.industry
                SET ms.industry_id = i.id
                WHERE ms.industry IS NOT NULL
            ');

            Schema::table('micro_signals', function (Blueprint $table) {
                $table->foreign('industry_id')->references('id')->on('industries')->nullOnDelete();
                $table->dropColumn('industry');
            });
        }
    }

    public function down(): void
    {
        Schema::table('micro_signals', function (Blueprint $table) {
            $table->string('industry')->nullable()->after('micro_signal_category_id');
        });

        DB::statement('
            UPDATE micro_signals ms
            JOIN industries i ON i.id = ms.industry_id
            SET ms.industry = i.name
            WHERE ms.industry_id IS NOT NULL
        ');

        Schema::table('micro_signals', function (Blueprint $table) {
            $table->dropForeign(['industry_id']);
            $table->dropColumn('industry_id');
        });
    }
};
