<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agents_pre_defined_prompts', function (Blueprint $table) {
            $table->string('step_title')->nullable()->after('step_key');
            $table->string('step_caption')->nullable()->after('step_title');
        });
    }

    public function down(): void
    {
        Schema::table('agents_pre_defined_prompts', function (Blueprint $table) {
            $table->dropColumn(['step_title', 'step_caption']);
        });
    }
};
