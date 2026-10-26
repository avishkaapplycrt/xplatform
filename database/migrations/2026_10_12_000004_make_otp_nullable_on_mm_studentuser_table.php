<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * mm_studentuser is a read-only mirror of the live PTE Portal's
 * `studentuser` table, refreshed in full by MockMasterSyncService::sync().
 * On the live source `otp` is nullable (students who never requested an
 * OTP simply have no value), but this local mirror had it as NOT NULL with
 * no default, so any sync batch containing such a student failed the whole
 * sync with "Integrity constraint violation: 1048 Column 'otp' cannot be
 * null" — surfaced to the user as "Sync failed — the live database may be
 * unreachable."
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('mm_studentuser', 'otp')) {
            DB::statement('ALTER TABLE mm_studentuser MODIFY otp INT NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('mm_studentuser', 'otp')) {
            DB::statement("UPDATE mm_studentuser SET otp = 0 WHERE otp IS NULL");
            DB::statement('ALTER TABLE mm_studentuser MODIFY otp INT NOT NULL');
        }
    }
};
