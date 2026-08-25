<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Set when the client dismisses the "Finish setup" checklist on the
            // dashboard. Null means the checklist is still shown.
            $table->timestamp('onboarding_dismissed_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('onboarding_dismissed_at');
        });
    }
};
