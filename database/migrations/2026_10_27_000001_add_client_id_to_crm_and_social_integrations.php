<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * CRM and social connections were shared by every client, so a new client
 * saw everyone's CRM contacts, deals and Instagram data (e.g. in Business
 * Helpers). Each connection now belongs to one client; contacts, deals and
 * Instagram rows belong to whoever owns their connection.
 *
 * Every connection made so far was made by client 1 (test@test.com), so the
 * existing rows are given to that client.
 */
return new class extends Migration
{
    private const TABLES = ['crm_integrations' => 'provider', 'social_integrations' => 'platform'];

    private const EXISTING_OWNER_EMAIL = 'test@test.com';

    public function up(): void
    {
        foreach (self::TABLES as $table => $typeColumn) {
            // social_integrations may already have client_id from
            // 2026_10_27_000001_add_client_id_to_social_integrations (run first on some databases).
            if (Schema::hasColumn($table, 'client_id')) {
                continue;
            }
            Schema::table($table, function (Blueprint $t) use ($typeColumn) {
                $t->foreignId('client_id')->nullable()->after('id')->constrained('clients')->cascadeOnDelete();
                $t->index(['client_id', $typeColumn]);
            });
        }

        $ownerId = DB::table('clients')->where('id', 1)->where('email', self::EXISTING_OWNER_EMAIL)->value('id');
        if ($ownerId !== null) {
            foreach (array_keys(self::TABLES) as $table) {
                DB::table($table)->whereNull('client_id')->update(['client_id' => $ownerId]);
            }
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table => $typeColumn) {
            Schema::table($table, function (Blueprint $t) use ($typeColumn) {
                $t->dropForeign(['client_id']);
                $t->dropIndex(['client_id', $typeColumn]);
                $t->dropColumn('client_id');
            });
        }
    }
};
