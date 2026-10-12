<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * askmira_visitors.device now records the visitor's operating system
 * (Android / iOS / Windows / Mac / Linux / Other / Unknown) instead of the
 * device type (Desktop / Mobile / Tablet / Unknown). Existing values don't
 * say which system it was, so they become 'Unknown'. Detected from the user
 * agent by PublicChatController::deviceFrom().
 */
return new class extends Migration
{
    private const NEW_VALUES = "'Android','iOS','Windows','Mac','Linux','Other','Unknown'";
    private const OLD_VALUES = "'Desktop','Mobile','Tablet','Unknown'";

    public function up(): void
    {
        // Widen to text, convert the old values, then narrow to the new set.
        DB::statement("ALTER TABLE askmira_visitors MODIFY device VARCHAR(20) NOT NULL DEFAULT 'Unknown'");
        DB::table('askmira_visitors')->whereIn('device', ['Desktop', 'Mobile', 'Tablet'])->update(['device' => 'Unknown']);
        DB::statement('ALTER TABLE askmira_visitors MODIFY device ENUM(' . self::NEW_VALUES . ") NOT NULL DEFAULT 'Unknown'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE askmira_visitors MODIFY device VARCHAR(20) NOT NULL DEFAULT 'Unknown'");
        DB::table('askmira_visitors')->whereIn('device', ['Windows', 'Mac', 'Linux'])->update(['device' => 'Desktop']);
        DB::table('askmira_visitors')->whereIn('device', ['Android', 'iOS'])->update(['device' => 'Mobile']);
        DB::table('askmira_visitors')->where('device', 'Other')->update(['device' => 'Unknown']);
        DB::statement('ALTER TABLE askmira_visitors MODIFY device ENUM(' . self::OLD_VALUES . ") NOT NULL DEFAULT 'Unknown'");
    }
};
