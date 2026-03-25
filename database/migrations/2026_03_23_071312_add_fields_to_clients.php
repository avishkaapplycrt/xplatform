<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('company_size')->nullable()->after('company_name');
            $table->string('country')->nullable()->after('company_size');
            $table->string('industry')->nullable()->after('country');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['company_size', 'country', 'industry']);
        });
    }
};
