<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AI visibility tracking controls:
 *   mira_projects         visibility_schedule (off | daily | weekly | monthly) + next run time;
 *                         match_mode (word | case_sensitive) and exclude_terms — how a brand
 *                         mention is recognised (so "Apple" the fruit isn't counted)
 *   mira_prompts          keyword + search_volume behind each prompt (Estimated impressions),
 *                         and tags for reporting by topic, funnel stage or campaign
 *   mira_visibility_runs  trigger: manual | schedule
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mira_projects', function (Blueprint $table) {
            $table->string('visibility_schedule', 10)->default('off')->after('competitors');
            $table->timestamp('next_visibility_run_at')->nullable()->after('visibility_schedule');
            $table->string('match_mode', 16)->default('word')->after('next_visibility_run_at');
            $table->json('exclude_terms')->nullable()->after('match_mode');
        });

        Schema::table('mira_prompts', function (Blueprint $table) {
            $table->string('keyword', 80)->nullable()->after('topic');
            $table->unsignedInteger('search_volume')->nullable()->after('keyword');
            $table->timestamp('volume_checked_at')->nullable()->after('search_volume');
            $table->json('tags')->nullable()->after('volume_checked_at');
        });

        Schema::table('mira_visibility_runs', function (Blueprint $table) {
            $table->string('trigger', 10)->default('manual')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('mira_projects', fn (Blueprint $t) => $t->dropColumn(['visibility_schedule', 'next_visibility_run_at', 'match_mode', 'exclude_terms']));
        Schema::table('mira_prompts', fn (Blueprint $t) => $t->dropColumn(['keyword', 'search_volume', 'volume_checked_at', 'tags']));
        Schema::table('mira_visibility_runs', fn (Blueprint $t) => $t->dropColumn('trigger'));
    }
};
