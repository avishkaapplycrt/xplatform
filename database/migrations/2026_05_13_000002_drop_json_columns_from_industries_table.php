<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('industries', function (Blueprint $table) {
            if (Schema::hasColumn('industries', 'signals')) {
                $table->dropColumn('signals');
            }
            if (Schema::hasColumn('industries', 'predictions')) {
                $table->dropColumn('predictions');
            }
        });
    }

    public function down(): void
    {
        Schema::table('industries', function (Blueprint $table) {
            $table->json('signals')->nullable()->after('name');
            $table->json('predictions')->nullable()->after('signals');
        });
    }
};
