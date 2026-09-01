<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_logs_brevo', function (Blueprint $table) {
            $table->dropColumn('connection_id');
        });
    }

    public function down(): void
    {
        Schema::table('email_logs_brevo', function (Blueprint $table) {
            $table->unsignedBigInteger('connection_id')->nullable()->after('id');
        });
    }
};
