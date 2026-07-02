<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_logs', function (Blueprint $table) {
            // Only add if not exists — use checks if your DB supports it
            if (!Schema::hasColumn('email_logs', 'client_id')) {
                $table->foreignId('client_id')->after('id')->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('email_logs', 'email_template_id')) {
                $table->foreignId('email_template_id')->nullable()->after('client_id')->constrained('email_templates')->onDelete('set null');
            }
            if (!Schema::hasColumn('email_logs', 'recipient_name')) {
                $table->string('recipient_name')->nullable()->after('email_address');
            }
            if (!Schema::hasColumn('email_logs', 'subject')) {
                $table->string('subject')->nullable()->after('recipient_name');
            }
            if (!Schema::hasColumn('email_logs', 'body')) {
                $table->longText('body')->nullable()->after('subject');
            }
            if (!Schema::hasColumn('email_logs', 'type')) {
                $table->enum('type', ['single', 'bulk'])->default('single')->after('body');
            }
            if (!Schema::hasColumn('email_logs', 'bulk_count')) {
                $table->integer('bulk_count')->default(1)->after('type');
            }
            if (!Schema::hasColumn('email_logs', 'status')) {
                $table->enum('status', ['sent', 'failed', 'queued', 'bounced'])->default('sent')->after('bulk_count');
            }
            if (!Schema::hasColumn('email_logs', 'error_message')) {
                $table->text('error_message')->nullable()->after('status');
            }
            if (!Schema::hasColumn('email_logs', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('unsubscribed_at');
            }
            if (!Schema::hasColumn('email_logs', 'user_agent')) {
                $table->string('user_agent')->nullable()->after('ip_address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('email_logs', function (Blueprint $table) {
            $columns = ['client_id', 'email_template_id', 'recipient_name', 'subject', 'body', 
                       'type', 'bulk_count', 'status', 'error_message', 'ip_address', 'user_agent'];
            $table->dropColumn($columns);
        });
    }
};