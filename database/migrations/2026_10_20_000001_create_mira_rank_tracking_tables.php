<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mira Premium rank tracking: the keywords a project follows, and one Google
 * position check per keyword per day (with the competitors' positions on the
 * same results page, for share of voice).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mira_tracked_keywords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('mira_projects')->cascadeOnDelete();
            $table->string('keyword', 200);
            $table->unsignedInteger('volume')->nullable();          // monthly searches
            $table->unsignedTinyInteger('difficulty')->nullable();  // keyword difficulty 0–100
            $table->string('intent', 20)->nullable();               // informational / commercial / transactional / navigational
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
            $table->unique(['project_id', 'keyword']);
        });

        Schema::create('mira_rank_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tracked_keyword_id')->constrained('mira_tracked_keywords')->cascadeOnDelete();
            $table->date('checked_on');
            $table->unsignedSmallInteger('position')->nullable();   // null = not in the top 100
            $table->string('url', 2048)->nullable();                // the page that ranks
            $table->json('competitors')->nullable();                // {domain: position|null}
            $table->json('serp_features')->nullable();              // result types on the page
            $table->timestamps();
            $table->unique(['tracked_keyword_id', 'checked_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mira_rank_checks');
        Schema::dropIfExists('mira_tracked_keywords');
    }
};
