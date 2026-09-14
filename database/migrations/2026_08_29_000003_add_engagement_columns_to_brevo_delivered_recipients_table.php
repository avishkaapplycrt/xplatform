<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brevo_delivered_recipients', function (Blueprint $table) {
            $table->timestamp('delivered_at')->nullable()->after('email');
            $table->timestamp('opened_at')->nullable()->after('delivered_at');
            $table->boolean('clicked')->default(false)->after('opened_at');
            $table->timestamp('unsubscribed_at')->nullable()->after('clicked');
        });
    }

    public function down(): void
    {
        Schema::table('brevo_delivered_recipients', function (Blueprint $table) {
            $table->dropColumn(['delivered_at', 'opened_at', 'clicked', 'unsubscribed_at']);
        });
    }
};
