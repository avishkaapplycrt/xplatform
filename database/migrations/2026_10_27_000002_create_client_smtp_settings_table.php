<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Each client's own outgoing mail (SMTP) login, so the emails they send from
 * XPlatform go out from their own address (clients.email). The password is
 * stored encrypted (see App\Models\ClientSmtpSetting).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_smtp_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('host');
            $table->unsignedSmallInteger('port')->default(587);
            $table->enum('encryption', ['tls', 'ssl', 'none'])->default('tls');
            $table->string('username');
            $table->text('password');
            $table->string('from_name')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_smtp_settings');
    }
};
