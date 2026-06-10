<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_connections', function (Blueprint $table) {
            $table->string('instance_url')->nullable()->after('hub_id');
        });
    }

    public function down(): void
    {
        Schema::table('crm_connections', function (Blueprint $table) {
            $table->dropColumn('instance_url');
        });
    }
};
