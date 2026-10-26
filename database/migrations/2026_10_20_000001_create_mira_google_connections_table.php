<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Google (Search Console + Analytics 4) connection for Mira Premium:
 *   mira_google_connections  one per client: the Google account that granted read-only
 *                            access, its OAuth tokens (encrypted at rest) and status
 *                            (active | revoked — Google refused the refresh token)
 *   mira_projects            gsc_site_url (Search Console property, e.g. "sc-domain:example.com")
 *                            and ga4_property ("properties/123456") chosen per project
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mira_google_connections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->unique();
            $table->string('google_sub', 64)->nullable();
            $table->string('email')->nullable();
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('scopes', 1000)->nullable();
            $table->string('status', 10)->default('active');
            $table->timestamp('connected_at')->nullable();
            $table->timestamps();
        });

        Schema::table('mira_projects', function (Blueprint $table) {
            $table->string('gsc_site_url')->nullable()->after('exclude_terms');
            $table->string('ga4_property', 40)->nullable()->after('gsc_site_url');
        });
    }

    public function down(): void
    {
        Schema::table('mira_projects', fn (Blueprint $t) => $t->dropColumn(['gsc_site_url', 'ga4_property']));
        Schema::dropIfExists('mira_google_connections');
    }
};
