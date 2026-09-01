<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * connection_id is not stable — disconnecting and reconnecting the same
 * Brevo account creates a brand-new email_connections row, which orphaned
 * every previously-synced run/recipient in production the first time a
 * client reconnected mid-sync. client_id is the same across reconnects, so
 * it becomes the primary key these tables are queried by going forward.
 * connection_id is kept only as a reference for which connection produced
 * the row.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brevo_delivered_recipients', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable()->after('connection_id');
        });

        // Backfill from the connection-event log, which still records which
        // client_id each now-possibly-deleted connection_id belonged to.
        DB::statement('
            UPDATE brevo_delivered_recipients d
            JOIN (SELECT DISTINCT connection_id, client_id FROM email_connection_logs) ecl
              ON ecl.connection_id = d.connection_id
            SET d.client_id = ecl.client_id
            WHERE d.client_id IS NULL
        ');

        Schema::table('brevo_delivered_recipients', function (Blueprint $table) {
            $table->dropUnique('brevo_delivered_unique');
            $table->dropIndex(['connection_id', 'email']);
        });

        Schema::table('brevo_delivered_recipients', function (Blueprint $table) {
            $table->unique(['client_id', 'campaign_id', 'email'], 'brevo_delivered_client_unique');
            $table->index(['client_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::table('brevo_delivered_recipients', function (Blueprint $table) {
            $table->dropUnique('brevo_delivered_client_unique');
            $table->dropIndex(['client_id', 'email']);
            $table->unique(['connection_id', 'campaign_id', 'email'], 'brevo_delivered_unique');
            $table->index(['connection_id', 'email']);
            $table->dropColumn('client_id');
        });
    }
};
