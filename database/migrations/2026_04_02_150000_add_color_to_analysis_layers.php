<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analysis_layers', function (Blueprint $table) {
            $table->string('color', 7)->default('#6b7280')->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('analysis_layers', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
