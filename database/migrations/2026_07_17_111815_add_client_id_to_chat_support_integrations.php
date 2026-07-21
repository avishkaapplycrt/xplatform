<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_support_integrations', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_support_integrations', 'client_id')) {
                $table->unsignedBigInteger('client_id')->nullable()->after('id')->index();
            }
        });

        // Drop the old global unique on `provider` (one connection per platform)
        // — it must become one connection per platform PER CLIENT
        $indexName = 'chat_support_integrations_provider_unique';
        $exists = collect(\DB::select("SHOW INDEX FROM chat_support_integrations WHERE Key_name = ?", [$indexName]))->isNotEmpty();

        if ($exists) {
            Schema::table('chat_support_integrations', function (Blueprint $table) {
                $table->dropUnique(['provider']);
            });
        }

        Schema::table('chat_support_integrations', function (Blueprint $table) {
            $table->unique(['client_id', 'provider'], 'chat_support_client_provider_unique');
        });
    }

    public function down(): void
    {
        Schema::table('chat_support_integrations', function (Blueprint $table) {
            $table->dropUnique('chat_support_client_provider_unique');
            $table->dropColumn('client_id');
        });
    }
};