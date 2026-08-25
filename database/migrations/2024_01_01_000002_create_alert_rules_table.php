<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('alert_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('metric', [
                'churn_risk',
                'engagement_drop',
                'transaction_anomaly',
                'low_email_open_rate'
            ]);
            $table->decimal('threshold_value', 10, 2);
            $table->enum('comparison_operator', ['gt', 'lt', 'eq', 'gte', 'lte']);
            $table->json('notification_channels');
            $table->boolean('is_active')->default(true);
            $table->integer('escalation_minutes')->nullable();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('alert_rules');
    }
};