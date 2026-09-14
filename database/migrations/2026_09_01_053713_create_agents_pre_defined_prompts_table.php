<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agents_pre_defined_prompts', function (Blueprint $table) {
            $table->id();
            $table->string('agent');       // e.g. marketing, sales
            $table->string('step_key');    // e.g. audience, insights, campaign, ab_test, performance
            $table->string('slug');        // stable identifier used by the front-end, e.g. build_audiences
            $table->string('label');       // the prompt text shown on the button
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true); // 1 = shown in the main window, 0 = hidden
            $table->timestamps();

            $table->unique(['agent', 'step_key', 'slug'], 'app_agent_step_slug_unique');
            $table->index(['agent', 'step_key', 'is_active', 'sort_order'], 'app_agent_step_active_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agents_pre_defined_prompts');
    }
};
