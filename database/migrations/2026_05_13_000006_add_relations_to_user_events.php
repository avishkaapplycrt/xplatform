<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_events', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable()->after('id');
            $table->unsignedBigInteger('behavioral_profile_id')->nullable()->after('client_id');

            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
            $table->foreign('behavioral_profile_id')->references('id')->on('behavioral_profiles')->nullOnDelete();

            $table->index('client_id');
            $table->index('behavioral_profile_id');
        });
    }

    public function down(): void
    {
        Schema::table('user_events', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['behavioral_profile_id']);
            $table->dropIndex(['client_id']);
            $table->dropIndex(['behavioral_profile_id']);
            $table->dropColumn(['client_id', 'behavioral_profile_id']);
        });
    }
};
