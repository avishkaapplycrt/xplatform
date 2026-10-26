<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('instagram_account_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_integration_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('followers_count')->default(0);
            $table->unsignedInteger('reach')->default(0);
            $table->unsignedInteger('profile_views')->default(0);
            $table->timestamps();
            $table->unique(['social_integration_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instagram_account_insights');
    }
};
