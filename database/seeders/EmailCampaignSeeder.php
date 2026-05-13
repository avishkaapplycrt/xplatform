<?php

namespace Database\Seeders;

use App\Models\EmailCampaign;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class EmailCampaignSeeder extends Seeder
{
    public function run(): void
    {
        EmailCampaign::truncate();

        $names = [
            'Welcome Series',       'Black Friday Blast',    'Product Launch – Pro',
            'Newsletter Jan',       'Newsletter Feb',        'Newsletter Mar',
            'Newsletter Apr',       'Re-engagement Wave 1',  'Re-engagement Wave 2',
            'Holiday Special',      'Cart Abandonment #1',   'Cart Abandonment #2',
            'Win-Back Q1',          'Win-Back Q2',           'Flash Sale 24h',
            'Loyalty Reward',       'Referral Invite',       'Upsell – Premium',
            'Upsell – Add-ons',     'Post-Purchase Thanks',  'Survey Invite',
            'Webinar Invite',       'Webinar Reminder',      'Product Update v2',
            'Product Update v3',    'Beta Invite',           'Early Access',
            'Spring Promo',         'Summer Sale',           'End of Year Recap',
            'Milestone – 1 Year',   'Milestone – 100 Orders','Anniversary Offer',
            'VIP Exclusive',        'Back-in-Stock Alert',   'Price Drop Alert',
            'Abandoned Browse',     'Onboarding Step 2',     'Onboarding Step 3',
            'Onboarding Step 4',    'Churn Prevention A',    'Churn Prevention B',
            'Reorder Reminder',     'Review Request',        'Feedback Loop',
        ];

        $campaigns = [];

        foreach ($names as $i => $name) {
            $sent      = rand(900, 3500);
            $delivered = (int) round($sent * (rand(880, 980) / 1000));
            $open      = (int) round($delivered * (rand(180, 340) / 1000));
            $clicked   = (int) round($open * (rand(150, 300) / 1000));
            $converted = (int) round($clicked * (rand(100, 230) / 1000));

            // device split of opens (must sum to open)
            $mobileShare  = rand(400, 540);
            $desktopShare = rand(330, 450);
            $tabletShare  = rand(60, 120);
            $otherShare   = 1000 - $mobileShare - $desktopShare - $tabletShare;
            $otherShare   = max(10, $otherShare);
            $total        = $mobileShare + $desktopShare + $tabletShare + $otherShare;

            $mobileOpens  = (int) round($open * $mobileShare  / $total);
            $desktopOpens = (int) round($open * $desktopShare / $total);
            $tabletOpens  = (int) round($open * $tabletShare  / $total);
            $otherOpens   = $open - $mobileOpens - $desktopOpens - $tabletOpens;

            // time-to-open split (must sum to open)
            $lt1h   = (int) round($open * rand(260, 340) / 1000);
            $t1to4h = (int) round($open * rand(300, 380) / 1000);
            $t4to24 = (int) round($open * rand(200, 280) / 1000);
            $gt24h  = $open - $lt1h - $t1to4h - $t4to24;
            $gt24h  = max(0, $gt24h);

            $campaigns[] = [
                'campaign_name'   => $name,
                'sent_count'      => $sent,
                'delivered_count' => $delivered,
                'open_count'      => $open,
                'clicked_count'   => $clicked,
                'converted_count' => $converted,
                'mobile_opens'    => $mobileOpens,
                'desktop_opens'   => $desktopOpens,
                'tablet_opens'    => $tabletOpens,
                'other_opens'     => max(0, $otherOpens),
                'time_lt1h'       => $lt1h,
                'time_1to4h'      => $t1to4h,
                'time_4to24h'     => $t4to24,
                'time_gt24h'      => $gt24h,
                'sent_date'       => Carbon::now()->subDays(rand(1, 180))->toDateString(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }

        EmailCampaign::insert($campaigns);
    }
}
