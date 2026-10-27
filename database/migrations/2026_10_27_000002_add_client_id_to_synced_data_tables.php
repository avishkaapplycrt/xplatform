<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Every synced-data table carries its owner directly, not only through the
 * connection it came from: crm_contacts / crm_deals (from crm_integrations),
 * instagram_media / instagram_account_insights (from social_integrations),
 * slack_channels (from chat_support_integrations) and slack_messages (from
 * slack_channels). Existing rows get the owner of their connection.
 *
 * Instagram posts and Slack channels also become unique per connection
 * instead of across the whole platform, like CRM contacts and deals already
 * are, so two clients can connect the same account without one sync failing
 * or taking over the other client's rows.
 */
return new class extends Migration
{
    /** table => [parent table, column pointing at the parent] */
    private const TABLES = [
        'crm_contacts'               => ['crm_integrations', 'connection_id'],
        'crm_deals'                  => ['crm_integrations', 'connection_id'],
        'instagram_media'            => ['social_integrations', 'social_integration_id'],
        'instagram_account_insights' => ['social_integrations', 'social_integration_id'],
        'slack_channels'             => ['chat_support_integrations', 'chat_support_integration_id'],
        'slack_messages'             => ['slack_channels', 'slack_channel_id'],   // after slack_channels is filled
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table => [$parent, $key]) {
            if (!Schema::hasTable($table)) continue;   // e.g. a test database with only some of these tables
            Schema::table($table, function (Blueprint $t) {
                $t->foreignId('client_id')->nullable()->after('id')->constrained('clients')->cascadeOnDelete();
            });

            DB::table($table)->whereNull('client_id')->update([
                'client_id' => DB::raw("(SELECT p.client_id FROM {$parent} p WHERE p.id = {$table}.{$key})"),
            ]);
        }

        if (Schema::hasTable('instagram_media')) {
            Schema::table('instagram_media', function (Blueprint $t) {
                $t->dropUnique(['media_id']);
                $t->unique(['social_integration_id', 'media_id']);
            });
        }
        if (Schema::hasTable('slack_channels')) {
            Schema::table('slack_channels', function (Blueprint $t) {
                $t->dropUnique(['channel_id']);
                $t->unique(['chat_support_integration_id', 'channel_id']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('slack_channels')) {
            Schema::table('slack_channels', function (Blueprint $t) {
                $t->dropUnique(['chat_support_integration_id', 'channel_id']);
                $t->unique('channel_id');
            });
        }
        if (Schema::hasTable('instagram_media')) {
            Schema::table('instagram_media', function (Blueprint $t) {
                $t->dropUnique(['social_integration_id', 'media_id']);
                $t->unique('media_id');
            });
        }

        foreach (array_reverse(array_keys(self::TABLES)) as $table) {
            if (!Schema::hasColumn($table, 'client_id')) continue;
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign(['client_id']);
                $t->dropColumn('client_id');
            });
        }
    }
};
