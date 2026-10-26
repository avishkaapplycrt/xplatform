<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Reverts a manual schema edit made directly against this table (outside of
 * any migration) that dropped its `id` primary key, made `client_id` alone
 * the primary key, and repointed `client_id`'s foreign key at
 * `crm_contacts.id` instead of `clients.id`.
 *
 * That change broke the table's actual purpose — tracking every Stripe
 * customer belonging to a client (many rows per client) — down to at most
 * one row per `client_id` value, and made `client_id` mean "a crm_contacts
 * row id" instead of "a clients row id", so every service/query in this app
 * that filters this table by the logged-in client's real account id
 * (Auth::guard('client')->id()) was coincidentally matching on numeric
 * overlap with an unrelated crm_contacts id, not real ownership. It also
 * broke StripeSyncService::upsertCustomer(), which selects/updates by `id`
 * (SQLSTATE[42S22]: Unknown column 'id').
 *
 * The 10 rows present under the broken schema can't be trusted to represent
 * real per-client Stripe customers (most of their client_id values don't
 * even match a real clients.id), so this clears them — a fresh "Sync Now"
 * from the client's Payment Gateway Connections page rebuilds them
 * correctly under the restored schema.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions_customers', function (Blueprint $table) {
            $table->dropForeign('transactions_customers_client_id_foreign');
        });

        Schema::table('transactions_customers', function (Blueprint $table) {
            $table->dropUnique('transactions_customers_client_id_gateway_customer_id_unique');
        });

        Schema::table('transactions_customers', function (Blueprint $table) {
            $table->dropPrimary();
        });

        DB::table('transactions_customers')->truncate();

        Schema::table('transactions_customers', function (Blueprint $table) {
            $table->id()->first();
        });

        Schema::table('transactions_customers', function (Blueprint $table) {
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->unique(['client_id', 'gateway_customer_id']);
        });
    }

    public function down(): void
    {
        Schema::table('transactions_customers', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropUnique('transactions_customers_client_id_gateway_customer_id_unique');
            $table->dropColumn('id');
        });

        DB::table('transactions_customers')->truncate();

        Schema::table('transactions_customers', function (Blueprint $table) {
            $table->primary('client_id');
            $table->foreign('client_id')->references('id')->on('crm_contacts')->onDelete('cascade');
            $table->unique(['client_id', 'gateway_customer_id']);
        });
    }
};
