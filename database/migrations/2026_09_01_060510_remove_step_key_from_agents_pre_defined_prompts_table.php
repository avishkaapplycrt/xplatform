<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('agents_pre_defined_prompts', 'step_key')) {
            Schema::table('agents_pre_defined_prompts', function (Blueprint $table) {
                $table->dropColumn('step_key');
            });
        }

        Schema::table('agents_pre_defined_prompts', function (Blueprint $table) {
            $table->string('step_title')->nullable(false)->change();
        });

        Schema::table('agents_pre_defined_prompts', function (Blueprint $table) {
            $table->unique(['agent', 'step_title', 'slug'], 'app_agent_step_slug_unique');
            $table->index(['agent', 'step_title', 'is_active', 'sort_order'], 'app_agent_step_active_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::table('agents_pre_defined_prompts', function (Blueprint $table) {
            $table->dropUnique('app_agent_step_slug_unique');
            $table->dropIndex('app_agent_step_active_sort_idx');
        });

        Schema::table('agents_pre_defined_prompts', function (Blueprint $table) {
            $table->string('step_key')->after('agent')->default('');
            $table->string('step_title')->nullable()->change();
        });

        Schema::table('agents_pre_defined_prompts', function (Blueprint $table) {
            $table->unique(['agent', 'step_key', 'slug'], 'app_agent_step_slug_unique_old');
            $table->index(['agent', 'step_key', 'is_active', 'sort_order'], 'app_agent_step_active_sort_idx_old');
        });
    }
};
