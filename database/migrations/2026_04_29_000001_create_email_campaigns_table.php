<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('campaign_name');
            $table->unsignedInteger('sent_count');
            $table->unsignedInteger('delivered_count');
            $table->unsignedInteger('open_count');
            $table->unsignedInteger('clicked_count');
            $table->unsignedInteger('converted_count');
            $table->unsignedInteger('mobile_opens');
            $table->unsignedInteger('desktop_opens');
            $table->unsignedInteger('tablet_opens');
            $table->unsignedInteger('other_opens');
            $table->unsignedInteger('time_lt1h');
            $table->unsignedInteger('time_1to4h');
            $table->unsignedInteger('time_4to24h');
            $table->unsignedInteger('time_gt24h');
            $table->date('sent_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_campaigns');
    }
};
