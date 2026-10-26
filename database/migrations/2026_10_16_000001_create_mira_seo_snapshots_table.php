<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stored provider responses for Mira Premium's Authority and Search panels
 * (App\Services\MiraPremium\SeoDataService). One row per project / kind /
 * fetch: reused for config mira_premium.dataforseo.cache_hours so a paid
 * provider is called at most once per window, and kept as history.
 *
 * kind: authority (backlinks summary + monthly history) | search (rank
 * overview, monthly history and top keywords).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mira_seo_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('kind', 20);
            $table->string('provider', 30)->default('dataforseo');
            $table->longText('payload');
            $table->timestamp('fetched_at');

            $table->index(['project_id', 'kind', 'fetched_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mira_seo_snapshots');
    }
};
