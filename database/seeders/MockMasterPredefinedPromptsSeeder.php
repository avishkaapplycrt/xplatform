<?php

namespace Database\Seeders;

use App\Models\AgentPredefinedPrompt;
use Illuminate\Database\Seeder;

/**
 * "Ask Mira" predefined prompts for the Mock Master Helper page
 * (resources/views/client/mock-master-helper.blade.php) — is_mock_master = 1.
 *
 * Every question here is answerable from real mm_* table data (see
 * App\Services\MockMaster\MockMasterDataService::chatSnapshot() and the
 * per-step builders it exposes) — nothing is invented or advisory-only.
 * The Marketing and Retention "A/B test" steps intentionally have no rows:
 * there is no A/B-testing engine for Mock Master data, so that tab stays an
 * honest empty state rather than a fabricated question.
 */
class MockMasterPredefinedPromptsSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // Marketing · Campaign — outreach copy for a real, named segment
            ['marketing', 'Campaign', 'mm-mk-camp-renewal-email', 'Write a renewal reminder email for students whose package expires this week', 1],
            ['marketing', 'Campaign', 'mm-mk-camp-whatsapp-trial', "Give me a WhatsApp message for trial students who haven't booked a mock test", 2],
            ['marketing', 'Campaign', 'mm-mk-camp-winback-quiet', 'Write a win-back message for students with no mock test activity in 14+ days', 3],
            ['marketing', 'Campaign', 'mm-mk-camp-highscorer-testimonial', 'Write a testimonial request for our high-scoring students', 4],

            // Marketing · Performance — headline KPIs
            ['marketing', 'Performance', 'mm-mk-perf-weekly-trends', "Summarize this week's mock test performance trends", 1],
            ['marketing', 'Performance', 'mm-mk-perf-renewal-rate', "What's our current package renewal rate?", 2],
            ['marketing', 'Performance', 'mm-mk-perf-avgscore-trend', 'How is the average overall score trending?', 3],
            ['marketing', 'Performance', 'mm-mk-perf-payment-health', 'What share of payments are failing right now?', 4],

            // Marketing · Audience — named/counted segments
            ['marketing', 'Audience', 'mm-mk-aud-highscorers', 'Who are our high-scoring students right now?', 1],
            ['marketing', 'Audience', 'mm-mk-aud-expiring-7d', 'Which students have a package expiring in the next 7 days?', 2],
            ['marketing', 'Audience', 'mm-mk-aud-renewal-watch-30d', "Who's on the renewal watchlist for the next 30 days?", 3],
            ['marketing', 'Audience', 'mm-mk-aud-new-14d', 'Which students joined in the last 14 days?', 4],

            // Marketing · Insights — computed diagnostics
            ['marketing', 'Insights', 'mm-mk-ins-expired-pct', 'What percentage of issued packages have already expired?', 1],
            ['marketing', 'Insights', 'mm-mk-ins-recent-volume', 'How many mock tests were taken in the last 7 days?', 2],
            ['marketing', 'Insights', 'mm-mk-ins-failed-payments', 'How many payments failed or went unpaid?', 3],
            ['marketing', 'Insights', 'mm-mk-ins-biggest-blocker', "What's the single biggest thing holding back renewals right now?", 4],

            // Sales · Today — who to act on today
            ['sales', 'Today', 'mm-sl-today-who-call', 'Which prospects should I call today?', 1],
            ['sales', 'Today', 'mm-sl-today-ready-upgrade', 'Which free-trial students are most ready to upgrade?', 2],
            ['sales', 'Today', 'mm-sl-today-active-no-package', "Who's actively testing but still hasn't bought a package?", 3],

            // Sales · Accounts — named student lookups ([name] collected via prompt)
            ['sales', 'Accounts', 'mm-sl-acct-lookup-status', "What's [name]'s current package and score?", 1],
            ['sales', 'Accounts', 'mm-sl-acct-lookup-lastactive', 'When did [name] last log in or take a test?', 2],
            ['sales', 'Accounts', 'mm-sl-acct-lookup-contact', "What's [name]'s email and phone number?", 3],

            // Sales · Scripts — creative copy
            ['sales', 'Scripts', 'mm-sl-scr-followup-email', 'Write a follow-up email for someone who took a free mock test', 1],
            ['sales', 'Scripts', 'mm-sl-scr-call-opener', 'Give me a 30-second call opener for a free-trial student', 2],
            ['sales', 'Scripts', 'mm-sl-scr-whatsapp-nudge', "Write a WhatsApp nudge for a student who hasn't booked a paid package yet", 3],

            // Sales · Objections — advisory playbook (mirrors the static objection cards on this step)
            ['sales', 'Objections', 'mm-sl-obj-price', "What's the best way to handle a price objection?", 1],
            ['sales', 'Objections', 'mm-sl-obj-free-first', 'How do I respond if they want to try more free tests first?', 2],
            ['sales', 'Objections', 'mm-sl-obj-examdate', "What if their exam date isn't confirmed yet?", 3],

            // Sales · Close & grow
            ['sales', 'Close & grow', 'mm-sl-close-trial-convert', 'Which trial students are most likely to convert this week?', 1],
            ['sales', 'Close & grow', 'mm-sl-close-most-tests-no-upgrade', 'Which free-trial students have taken the most tests without upgrading?', 2],
            ['sales', 'Close & grow', 'mm-sl-close-renewal-upsell', 'Which active students are close to renewal and worth an upsell call?', 3],

            // Retention · Save first — who to save this week
            ['retention', 'Save first', 'mm-ch-save-who-churn', 'Who is most likely to churn this week?', 1],
            ['retention', 'Save first', 'mm-ch-save-value-at-risk', 'How much renewal value is at risk in the next 7 days?', 2],
            ['retention', 'Save first', 'mm-ch-save-inactive-highrisk', "Which at-risk students haven't logged in recently?", 3],

            // Retention · Root cause — mirrors the real, computed root-cause breakdown
            ['retention', 'Root cause', 'mm-ch-root-top-driver', "What's the top reason students are at risk right now?", 1],
            ['retention', 'Root cause', 'mm-ch-root-payment-failed', 'How many students are at risk because of failed payments?', 2],
            ['retention', 'Root cause', 'mm-ch-root-inactivity-14d', 'How many students have gone quiet with no mock test activity in 14+ days?', 3],
            ['retention', 'Root cause', 'mm-ch-root-lowscore', 'How many students are losing confidence with low mock test scores?', 4],

            // Retention · Offers — mirrors the three static offer cards on this step
            ['retention', 'Offers', 'mm-ch-offer-best', 'What offer works best for high-value at-risk students?', 1],
            ['retention', 'Offers', 'mm-ch-offer-extra-test', 'Which students have gone quiet long enough to offer a free extra mock test?', 2],
            ['retention', 'Offers', 'mm-ch-offer-coaching-call', 'Which students need a free coaching call because their scores are dropping?', 3],

            // Retention · Watchlist — 8-30 day expiry window
            ['retention', 'Watchlist', 'mm-ch-watch-drifting', 'Who is drifting onto the watchlist?', 1],
            ['retention', 'Watchlist', 'mm-ch-watch-count', 'How many students are in the 30-day renewal watchlist?', 2],
            ['retention', 'Watchlist', 'mm-ch-watch-highvalue', 'Which watchlist students have the highest value at risk?', 3],

            // Retention · Renew & win back — paid plans due for renewal and lapsed plans still in use
            ['retention', 'Renew & win back', 'mm-ch-renew-due', 'Which paid plans expire in the next 30 days?', 1],
            ['retention', 'Renew & win back', 'mm-ch-renew-winback', 'Which students let their plan lapse but are still logging in?', 2],
            ['retention', 'Renew & win back', 'mm-ch-renew-value', 'How much revenue is up for renewal or win-back right now?', 3],

            // Retention · A/B test — intentionally no rows: no A/B-testing
            // engine exists for Mock Master data, so this stays an honest
            // empty state instead of a fabricated question.
        ];

        $rowsBySlug = collect($rows)->keyBy(fn ($row) => $row[0] . '|' . $row[2]);
        // Scoped to is_mock_master = true so this cleanup never touches the
        // Business Helpers prompt set (see AgentPredefinedPromptsSeeder).
        AgentPredefinedPrompt::whereIn('agent', ['sales', 'marketing', 'retention'])
            ->where('is_mock_master', true)
            ->get(['id', 'agent', 'slug'])
            ->each(function ($existing) use ($rowsBySlug) {
                if (!$rowsBySlug->has($existing->agent . '|' . $existing->slug)) {
                    $existing->delete();
                }
            });

        foreach ($rows as [$agent, $stepTitle, $slug, $label, $sortOrder]) {
            AgentPredefinedPrompt::updateOrCreate(
                ['agent' => $agent, 'slug' => $slug],
                [
                    'step_title' => $stepTitle,
                    'label' => $label,
                    'sort_order' => $sortOrder,
                    'is_active' => true,
                    'is_mock_master' => true,
                ]
            );
        }

        // Remove the earlier, unorganised draft rows (created before this
        // step-grouped set existed) that are no longer part of $rows above.
        AgentPredefinedPrompt::whereIn('slug', [
            'mm-mk-at-risk-renew', 'mm-mk-whatsapp-trial', 'mm-mk-weekly-perf',
            'mm-sl-who-call', 'mm-sl-followup-email', 'mm-sl-price-objection',
            'mm-ch-who-churn', 'mm-ch-save-email', 'mm-ch-best-offer',
        ])->where('is_mock_master', false)->delete();
    }
}
