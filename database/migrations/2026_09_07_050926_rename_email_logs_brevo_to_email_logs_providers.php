<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * email_logs_brevo started out Brevo-only, but the table now stores delivery
 * data for whichever provider a client has connected — so it's renamed to
 * email_logs_providers and gains a provider_name column that says which one.
 * provider_name is backfilled from email_connections.platform for the given
 * client_id (the client's most recently connected provider), since the rows
 * synced so far never recorded which provider produced them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('email_logs_brevo', 'email_logs_providers');

        Schema::table('email_logs_providers', function (Blueprint $table) {
            $table->string('provider_name', 50)->nullable()->after('client_id');
        });

        DB::statement('
            UPDATE email_logs_providers elp
            JOIN (
                SELECT ec1.client_id, ec1.platform
                FROM email_connections ec1
                LEFT JOIN email_connections ec2
                  ON ec2.client_id = ec1.client_id
                 AND (ec2.connected_at > ec1.connected_at
                      OR (ec2.connected_at = ec1.connected_at AND ec2.id > ec1.id))
                WHERE ec2.id IS NULL
            ) latest ON latest.client_id = elp.client_id
            SET elp.provider_name = latest.platform
            WHERE elp.provider_name IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('email_logs_providers', function (Blueprint $table) {
            $table->dropColumn('provider_name');
        });

        Schema::rename('email_logs_providers', 'email_logs_brevo');
    }
};
