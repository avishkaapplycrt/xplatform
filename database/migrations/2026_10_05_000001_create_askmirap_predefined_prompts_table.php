<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Predefined questions for Ask Mira Premium (/mira-premium), replacing the
 * lists hardcoded in mira-premium.blade.php and PremiumMiraService.
 *
 * placement:
 *   sidebar    — the "Try asking" box, grouped by category (seo / aeo / geo).
 *   suggestion — clickable chips under Mira's first reply after a URL is
 *                loaded. `trigger` decides when each one shows:
 *                  missing_meta_description, no_question_headings,
 *                  no_answer_schema — only when the site lacks that;
 *                  NULL — always (general filler, shown after the specific ones).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('askmirap_predefined_prompts', function (Blueprint $table) {
            $table->id();
            $table->string('placement', 20);                 // sidebar | suggestion
            $table->string('category', 10)->nullable();      // seo | aeo | geo (sidebar only)
            $table->string('question', 255);
            $table->string('trigger', 50)->nullable();        // suggestion rule, NULL = always
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['placement', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('askmirap_predefined_prompts');
    }
};
