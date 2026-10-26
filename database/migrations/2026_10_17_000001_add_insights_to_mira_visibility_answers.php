<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Brand-Radar-style detail on each AI visibility answer:
 *   sentiment       how the answer speaks about the brand: positive | neutral | negative (null = not named)
 *   brands_found    every brand / product the answer names, not just tracked ones ("most mentioned brands")
 *   cited_position  1-based position of the brand's own page among the answer's cited sources
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mira_visibility_answers', function (Blueprint $table) {
            $table->string('sentiment', 10)->nullable()->after('brand_cited');
            $table->json('brands_found')->nullable()->after('brands_mentioned');
            $table->unsignedTinyInteger('cited_position')->nullable()->after('cited_domains');
        });
    }

    public function down(): void
    {
        Schema::table('mira_visibility_answers', function (Blueprint $table) {
            $table->dropColumn(['sentiment', 'brands_found', 'cited_position']);
        });
    }
};
