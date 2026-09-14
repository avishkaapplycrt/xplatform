<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Sample customers for the Customer Retention agent — enough of a spread
 * across the retention lifecycle that every step has something real to show:
 *
 *   - Lost            → deals marked closed-lost           (Root cause, "what are we losing to")
 *   - Silent after win → won deal, no activity 70-130 days (Risk radar, Save play, A/B test)
 *   - Deal stalling    → open deal past / near its close   (Risk radar, A/B test)
 *   - Stabilising      → won deal, active again recently   (Recover & grow — "who's stabilising")
 *   - Growth-ready     → won deal + a live expansion deal  (Recover & grow — "hand back to Sales")
 *
 * Rows are matched to deals by the "deal name starts with company name"
 * heuristic the retention services use, so nothing here needs a real FK.
 *
 * Idempotent: keyed on (connection_id, external_id) with a SMPL- prefix, so
 * re-running updates in place and everything is removable with
 *   DB::table('crm_deals')->where('external_id','like','SMPL-%')->delete();
 *   DB::table('crm_contacts')->where('external_id','like','SMPL-%')->delete();
 */
class RetentionSampleDataSeeder extends Seeder
{
    private const CONNECTION_ID = 4;
    private const PROVIDER = 'hubspot';

    public function run(): void
    {
        $now = now();

        // [company, first, last, lastActivityDaysAgo, deals[]]
        // deal: [nameSuffix, value, stage, status, closeDaysFromNow, lastModifiedDaysAgo]
        $accounts = [
            // ── Lost ────────────────────────────────────────────────────────
            ['Redgum Timber Co',      'Marcus',  'Reilly',     35,  [['', 28000, 'closedlost', 'lost', -20, 25]]],
            ['Halcyon Wellness',      'Priya',   'Anand',      50,  [['', 9000,  'closedlost', 'lost', -45, 48]]],
            ['Kestrel Freight',       'Dominic', 'Vance',      18,  [['', 41000, 'closedlost', 'lost', -12, 14]]],
            ['Moss & Fern Interiors', 'Elena',   'Sokolova',   80,  [['', 7500,  'closedlost', 'lost', -60, 62]]],
            ['Verdant Energy',        'Callum',  'Whitfield',  40,  [['', 52000, 'closedlost', 'lost', -30, 33]]],
            ['Tidewater Marine',      'Grace',   'Okafor',     33,  [['', 11000, 'closedlost', 'lost', -25, 27]]],

            // ── Silent after the win ────────────────────────────────────────
            ['Copperline Foods',   'Theo',    'Barnett',   95,  [['', 15000, 'closedwon', 'won', -200, 90]]],
            ['Larkspur Media',     'Nadia',   'Rahman',    110, [['', 33000, 'closedwon', 'won', -160, 105]]],
            ['Ironbark Construction', 'Wade', 'Ferreira',  78,  [['', 47000, 'closedwon', 'won', -240, 74]]],
            ['Petrichor Skincare', 'Yasmin',  'Delacroix', 130, [['', 12000, 'closedwon', 'won', -140, 120]]],
            ['Fathom Analytics Co','Rory',    'Kavanagh',  85,  [['', 26000, 'closedwon', 'won', -180, 80]]],

            // ── Deal stalling in the pipeline ───────────────────────────────
            ['Wildwood Brewing',        'Isla',   'MacLeoD',   22, [['', 19000, 'qualifiedtobuy',      'open', -8,  47]]],
            ['Slate & Pine Architects', 'Hugo',   'Lindqvist', 30, [['', 24000, 'appointmentscheduled','open', 10,  40]]],
            ['Umbra Security',          'Farah',  'Nasser',    19, [['', 38000, 'qualifiedtobuy',      'open', -15, 55]]],
            ['Marlow Logistics',        'Devin',  'Cho',       26, [['', 16000, 'appointmentscheduled','open', 14,  38]]],

            // ── Stabilising — recovered, hands off ──────────────────────────
            ['Sundial Coffee Roasters', 'Bea',    'Ashworth',  5,  [['', 21000, 'closedwon', 'won', -120, 7]]],
            ['Greenhaven Gardens',      'Oscar',  'Pettigrew', 8,  [['', 14000, 'closedwon', 'won', -100, 9]]],
            ['Aster Financial',         'Lena',   'Hoffmann',  4,  [['', 44000, 'closedwon', 'won', -135, 6]]],
            ['Cove Digital',            'Sam',    'Whitlock',  11, [['', 18000, 'closedwon', 'won', -95,  12]]],

            // ── Growth-ready — won + a live expansion deal ──────────────────
            ['Northwind Publishing',  'Aria',   'Ford',      3, [
                ['', 30000, 'closedwon', 'won', -210, 4],
                [' - Expansion', 22000, 'decisionmakerboughtin', 'open', 25, 3],
            ]],
            ['Bright Harbor Hotels',  'Julian', 'Meade',     6, [
                ['', 55000, 'closedwon', 'won', -180, 6],
                [' - Upsell', 30000, 'presentationscheduled', 'open', 18, 5],
            ]],
            ['Quill & Co Stationery', 'Mira',   'Solberg',   2, [
                ['', 12000, 'closedwon', 'won', -150, 3],
                [' - Expansion', 8000, 'qualifiedtobuy', 'open', 40, 2],
            ]],
            ['Terra Firma Surveying', 'Cyrus',  'Ellington', 7, [
                ['', 38000, 'closedwon', 'won', -220, 7],
                [' - Renewal + Expansion', 45000, 'decisionmakerboughtin', 'open', 30, 4],
            ]],
            ['Lumina Labs',           'Priyanka','Iyer',     5, [
                ['', 27000, 'closedwon', 'won', -170, 5],
                [' - Expansion', 19000, 'presentationscheduled', 'open', 22, 3],
            ]],
        ];

        $contactRows = [];
        $dealRows = [];
        $i = 0;

        foreach ($accounts as [$company, $first, $last, $activityDaysAgo, $deals]) {
            $i++;
            $slug = strtolower(str_replace([' ', '&', '.', ',', '+'], ['-', 'and', '', '', 'plus'], $company));
            $email = $slug . '@example.com';
            $contactExtId = sprintf('SMPL-C-%02d', $i);
            $lastActivity = $now->copy()->subDays($activityDaysAgo);

            $contactRows[] = [
                'connection_id'    => self::CONNECTION_ID,
                'provider'         => self::PROVIDER,
                'external_id'      => $contactExtId,
                'email'            => $email,
                'first_name'       => $first,
                'last_name'        => $last,
                'company'          => $company,
                'last_activity_at' => $lastActivity,
                'raw_data'         => json_encode([
                    'id' => $contactExtId,
                    'properties' => [
                        'company' => $company,
                        'email' => $email,
                        'firstname' => $first,
                        'lastname' => $last,
                        'lastmodifieddate' => $lastActivity->toIso8601String(),
                    ],
                    'sample' => true,
                ]),
                'created_at'       => $now,
                'updated_at'       => $now,
            ];

            $d = 0;
            foreach ($deals as [$suffix, $value, $stage, $status, $closeDaysFromNow, $lastModDaysAgo]) {
                $d++;
                $dealExtId = sprintf('SMPL-D-%02d-%d', $i, $d);
                $closeDate = $now->copy()->addDays($closeDaysFromNow);
                $lastMod = $now->copy()->subDays($lastModDaysAgo);

                $dealRows[] = [
                    'connection_id' => self::CONNECTION_ID,
                    'provider'      => self::PROVIDER,
                    'external_id'   => $dealExtId,
                    'name'          => $company . $suffix,
                    'value'         => $value,
                    'stage'         => $stage,
                    'status'        => $status,
                    'close_date'    => $closeDate,
                    'raw_data'      => json_encode([
                        'id' => $dealExtId,
                        'properties' => [
                            'amount' => (string) $value,
                            'dealname' => $company . $suffix,
                            'dealstage' => $stage,
                            'closedate' => $closeDate->toIso8601String(),
                            'hs_is_closed_won' => $status === 'won' ? 'true' : 'false',
                            'hs_is_closed_lost' => $status === 'lost' ? 'true' : 'false',
                            'hs_lastmodifieddate' => $lastMod->toIso8601String(),
                        ],
                        'sample' => true,
                    ]),
                    'created_at'    => $now,
                    'updated_at'    => $lastMod,
                ];
            }
        }

        DB::table('crm_contacts')->upsert($contactRows, ['connection_id', 'external_id']);
        DB::table('crm_deals')->upsert($dealRows, ['connection_id', 'external_id']);

        $this->command?->info(
            'Retention sample data: ' . count($contactRows) . ' contacts, ' . count($dealRows) . ' deals '
            . '(external_id prefix SMPL-).'
        );
    }
}
