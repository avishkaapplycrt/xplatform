<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Social Media connections become per customer (client), like chat_support_integrations:
 * each XPlatform client connects their own Instagram / Facebook / TikTok / YouTube /
 * LinkedIn account instead of sharing one global connection per platform.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('social_integrations', function (Blueprint $table) {
            if (!Schema::hasColumn('social_integrations', 'client_id')) {
                $table->unsignedBigInteger('client_id')->nullable()->after('id')->index();
            }
        });

        // Existing connections were made before connections had an owner. Give them to
        // whoever created them, else to the first client, so they stay visible.
        $firstClient = DB::table('clients')->min('id');
        DB::table('social_integrations')->whereNull('client_id')->whereNotNull('created_by')->update(['client_id' => DB::raw('created_by')]);
        if ($firstClient) {
            DB::table('social_integrations')->whereNull('client_id')->update(['client_id' => $firstClient]);
        }

        Schema::table('social_integrations', function (Blueprint $table) {
            $table->index(['client_id', 'platform'], 'social_integrations_client_platform_index');
        });
    }

    public function down(): void
    {
        Schema::table('social_integrations', function (Blueprint $table) {
            $table->dropIndex('social_integrations_client_platform_index');
            $table->dropColumn('client_id');
        });
    }
};
