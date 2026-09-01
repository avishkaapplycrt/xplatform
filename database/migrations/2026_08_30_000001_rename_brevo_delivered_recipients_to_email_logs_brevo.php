<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('brevo_delivered_recipients', 'email_logs_brevo');
    }

    public function down(): void
    {
        Schema::rename('email_logs_brevo', 'brevo_delivered_recipients');
    }
};
