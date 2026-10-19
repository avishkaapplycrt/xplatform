<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mira Premium (/mira-premium) — the subscription workspace.
 *
 *   mira_projects            a subscriber's tracked website: brand terms and competitors
 *   mira_scans               every site audit / competitor comparison (the two-phase
 *                            protocol's stored result), with scores for history and deltas
 *   mira_prompts             a project's AI visibility prompt set
 *   mira_visibility_runs     one pass of the prompt set through the AI engine, aggregated
 *   mira_visibility_answers  each prompt's answer in a run, parsed for mentions and citations
 *   mira_messages            Ask Mira chat history, per project
 *   mira_usage               metered usage per subject (client or guest) / metric / period
 *   mira_events              first-party product analytics (cookieless)
 *
 * Settings and limits: config/mira_premium.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mira_projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('name', 120);
            $table->string('url', 2048);
            $table->string('domain', 255);
            $table->json('brand_terms')->nullable();   // ["Acme", "Acme Labs"] — how the brand is named in answers
            $table->json('competitors')->nullable();   // [{"name": "Rival", "domain": "rival.com"}]
            $table->timestamp('last_scanned_at')->nullable();
            $table->timestamps();

            $table->unique(['client_id', 'domain']);
        });

        Schema::create('mira_scans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('subject', 80);                  // usage subject: client:<id> or guest:<hash>
            $table->string('url', 2048);
            $table->string('competitor_url', 2048)->nullable();
            $table->string('kind', 12);                      // site | comparison
            $table->string('mode', 12);                      // preview | premium
            $table->string('status', 12)->default('queued'); // queued | running | done | failed
            $table->string('error_code', 40)->nullable();
            $table->string('error_message', 500)->nullable();
            $table->unsignedTinyInteger('overall')->nullable();
            $table->string('grade', 2)->nullable();
            $table->unsignedTinyInteger('competitor_overall')->nullable();
            $table->json('stage_scores')->nullable();        // {"crawl": 82, ...}
            $table->json('issue_counts')->nullable();        // {"critical": 2, "important": 4, "minor": 7, "passing": 30}
            $table->longText('report')->nullable();          // JSON: buildReport() or compare() result
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'created_at']);
            // Not on url: a 2048-char column is over MySQL's 3072-byte key limit. The
            // reuse lookup narrows by subject + recent created_at, then matches url.
            $table->index(['subject', 'created_at']);
        });

        Schema::create('mira_prompts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('prompt', 300);
            $table->string('topic', 40)->nullable();
            $table->string('source', 12)->default('user');  // seed | user
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['project_id', 'is_active']);
        });

        Schema::create('mira_visibility_runs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('project_id');
            $table->string('status', 12)->default('queued'); // queued | running | done | failed
            $table->string('model', 80)->nullable();
            $table->unsignedSmallInteger('prompts_count')->default(0);
            $table->unsignedSmallInteger('answered_count')->default(0);
            $table->unsignedSmallInteger('mentions')->default(0);    // answers naming the brand
            $table->unsignedSmallInteger('citations')->default(0);   // answers linking the brand's domain
            $table->decimal('share_of_voice', 5, 2)->nullable();     // brand % of all tracked-brand mentions
            $table->json('summary')->nullable();                     // aggregates: per brand, domains, topics
            $table->string('error_message', 500)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'created_at']);
        });

        Schema::create('mira_visibility_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('run_id');
            $table->unsignedBigInteger('prompt_id')->nullable();
            $table->string('prompt', 300);
            $table->string('topic', 40)->nullable();
            $table->longText('answer')->nullable();
            $table->boolean('brand_mentioned')->default(false);
            $table->boolean('brand_cited')->default(false);
            $table->unsignedTinyInteger('brand_position')->nullable(); // 1 = first tracked brand named
            $table->json('brands_mentioned')->nullable();              // ["Acme", "Rival"] in order
            $table->json('cited_urls')->nullable();
            $table->json('cited_domains')->nullable();
            $table->string('error', 300)->nullable();
            $table->timestamps();

            $table->index('run_id');
        });

        Schema::create('mira_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('role', 10);   // user | assistant
            $table->text('content');
            $table->timestamps();

            $table->index(['client_id', 'project_id', 'id']);
        });

        Schema::create('mira_usage', function (Blueprint $table) {
            $table->id();
            $table->string('subject', 80);
            $table->string('metric', 30);
            $table->string('period', 10);  // 2026-10 or 2026-10-15
            $table->unsignedInteger('used')->default(0);
            $table->timestamps();

            $table->unique(['subject', 'metric', 'period']);
        });

        Schema::create('mira_events', function (Blueprint $table) {
            $table->id();
            $table->string('name', 40);
            $table->string('subject', 80)->nullable();
            $table->string('tier', 20)->nullable();
            $table->json('props')->nullable();
            $table->string('url', 2048)->nullable();
            $table->string('referrer', 2048)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['name', 'created_at']);
        });
    }

    public function down(): void
    {
        foreach (['mira_events', 'mira_usage', 'mira_messages', 'mira_visibility_answers',
                  'mira_visibility_runs', 'mira_prompts', 'mira_scans', 'mira_projects'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
