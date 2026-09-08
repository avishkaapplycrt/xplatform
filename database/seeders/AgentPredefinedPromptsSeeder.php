<?php

namespace Database\Seeders;

use App\Models\AgentPredefinedPrompt;
use Illuminate\Database\Seeder;

class AgentPredefinedPromptsSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // Sales · Prioritise
            ['sales', 'prioritise', 'contact_today', 'Who should I contact today?', 1],
            ['sales', 'prioritise', 'top5_hour', 'Give me my top 5 for this hour', 2],
            ['sales', 'prioritise', 'buying_window', "Who's in the buying window right now?", 3],
            ['sales', 'prioritise', 'not_call', 'Who should I NOT call this week, and why?', 4],
            ['sales', 'prioritise', 'changed_yesterday', 'What changed since yesterday?', 5],

            // Sales · Understand
            ['sales', 'understand', 'why_ranked', 'Why is [name] ranked here?', 1],
            ['sales', 'understand', 'been_doing', 'What has [name] been doing?', 2],
            ['sales', 'understand', 'holding_back', "What's holding [name] back?", 3],
            ['sales', 'understand', 'ready_or_researching', 'Is [name] ready or just researching?', 4],
            ['sales', 'understand', 'cares_about', 'What does [name] care about most?', 5],

            // Sales · Craft (Pitch)
            ['sales', 'craft', 'script_for', 'Script for [name]', 1],
            ['sales', 'craft', 'opener_30s', '30-second opener for [name]', 2],
            ['sales', 'craft', 'whatsapp_version', 'WhatsApp / DM version', 3],
            ['sales', 'craft', 'email_version', 'Email version', 4],
            ['sales', 'craft', 'proof_to_show', 'What proof should I show [name]?', 5],
            ['sales', 'craft', 'shorter_less_salesy', 'Make it shorter / less salesy', 6],

            // Sales · Handle (Overcome)
            ['sales', 'handle', 'too_expensive', 'Too expensive', 1],
            ['sales', 'handle', 'not_right_now', 'Not right now', 2],
            ['sales', 'handle', 'use_competitor', 'We use a competitor', 3],
            ['sales', 'handle', 'send_info', 'Send me some info', 4],
            ['sales', 'handle', 'no_budget', 'No budget', 5],
            ['sales', 'handle', 'need_boss', 'Need my boss', 6],
            ['sales', 'handle', 'something_else', 'They said something else…', 7],

            // Sales · Launch (Close & grow)
            ['sales', 'launch', 'how_close', 'How do I close [name]?', 1],
            ['sales', 'launch', 'smallest_ask', 'Smallest ask I can make to [name]?', 2],
            ['sales', 'launch', 'ready_upgrade', "Who's ready for an upgrade?", 3],
            ['sales', 'launch', 'offer_discount', 'Should I offer a discount?', 4],
            ['sales', 'launch', 'weighted_pipeline', "What's my weighted pipeline?", 5],
            ['sales', 'launch', 'at_risk_no_touch', "What's at risk that I shouldn't touch?", 6],

            // Marketing · Audience
            ['marketing', 'Audience', 'winback_sequence_this_week', 'Who should get the win-back sequence this week?', 1],
            ['marketing', 'Audience', 'new_not_reached_first_value', "Who is new and still hasn't reached first value?", 2],
            ['marketing', 'Audience', 'ready_for_upsell', 'Which customers are ready for an upsell offer?', 3],
            ['marketing', 'Audience', 'who_would_refer_us', 'Who would refer us if I asked?', 4],
            ['marketing', 'Audience', 'exclude_from_every_send', 'Who must be excluded from every send, and why?', 5],
            ['marketing', 'Audience', 'in_live_sales_cycle', 'Who is in a live sales cycle — leave them alone?', 6],

            // Marketing · Insights
            ['marketing', 'Insights', 'top_shared_signal_mql_sales', 'What is the top shared signal in MQL → Sales right now?', 1],
            ['marketing', 'Insights', 'proof_or_offer_audience', 'Is MQL → Sales a proof audience or an offer audience?', 2],
            ['marketing', 'Insights', 'one_lever_mql_sales', 'What is the one lever that moves MQL → Sales?', 3],
            ['marketing', 'Insights', 'why_name_here_not_sales', 'Why is [name] here and not with Sales?', 4],
            ['marketing', 'Insights', 'rule_put_people_mql_sales', 'What rule put people into MQL → Sales?', 5],
            ['marketing', 'Insights', 'changed_last_7_days', 'What changed in the last 7 days?', 6],

            // Marketing · Campaign
            ['marketing', 'Campaign', 'email_sequence_mql_sales', 'Write the 3-touch email sequence for MQL → Sales', 1],
            ['marketing', 'Campaign', 'whatsapp_oneliner_mql_sales', 'Give me the WhatsApp one-liner for MQL → Sales', 2],
            ['marketing', 'Campaign', 'sms_optout_mql_sales', 'SMS version with opt-out for MQL → Sales', 3],
            ['marketing', 'Campaign', 'linkedin_dm_post_mql_sales', 'LinkedIn DM and post for MQL → Sales', 4],
            ['marketing', 'Campaign', 'ad_social_copy_mql_sales', 'Ad / social copy for MQL → Sales', 5],
            ['marketing', 'Campaign', 'discount_or_proof_mql_sales', 'Can MQL → Sales get a discount, or proof only?', 6],
            ['marketing', 'Campaign', 'rewrite_touch1_brand_voice', 'Rewrite touch 1 in our brand voice', 7],

            // Marketing · A/B test
            ['marketing', 'A/B test', 'subject_line_test_mql_sales', 'Which subject-line test is worth running on MQL → Sales?', 1],
            ['marketing', 'A/B test', 'proof_vs_offer_test_mql_sales', 'Should I test proof vs offer on MQL → Sales?', 2],
            ['marketing', 'A/B test', 'sample_size_per_arm_mql_sales', 'How many per arm do I need for MQL → Sales?', 3],
            ['marketing', 'A/B test', 'when_receive_touch1_mql_sales', 'When should MQL → Sales receive touch 1?', 4],
            ['marketing', 'A/B test', 'holdout_15_enough_mql_sales', 'Is my 15% holdout enough for MQL → Sales?', 5],
            ['marketing', 'A/B test', 'all_test_ideas_mql_sales', 'All test ideas for MQL → Sales', 6],

            // Marketing · Performance
            ['marketing', 'Performance', 'lift_vs_holdout_mql_sales', 'What lift did MQL → Sales get vs its holdout?', 1],
            ['marketing', 'Performance', 'audience_worth_next_dollar', 'Which audience is worth the next dollar?', 2],
            ['marketing', 'Performance', 'expected_return_send_everything_week', 'Expected return if I send everything this week?', 3],
            ['marketing', 'Performance', 'audience_worst_unsub_rate', 'Which audience has the worst unsubscribe rate?', 4],
            ['marketing', 'Performance', 'who_became_mql_since_last_send', 'Who became an MQL since the last send?', 5],
            ['marketing', 'Performance', 'push_week_mqls_to_sales', "Push this week's MQLs to Sales", 6],

            // Retention · Risk radar
            ['retention', 'Risk radar', 'who_save_first_week', 'Who do I save first this week?', 1],
            ['retention', 'Risk radar', 'renewals_in_danger', 'Which renewals are in danger soon?', 2],
            ['retention', 'Risk radar', 'payment_failures_today', 'Any payment failures to fix today?', 3],
            ['retention', 'Risk radar', 'drifting_watchlist', 'Who is drifting onto the watchlist?', 4],
            ['retention', 'Risk radar', 'value_at_risk', 'How much value is at risk right now?', 5],

            // Retention · Root cause
            ['retention', 'Root cause', 'why_leaving', 'Why is [Name] leaving?', 1],
            ['retention', 'Root cause', 'price_or_product', 'Is [Name] having a price problem or a product problem?', 2],
            ['retention', 'Root cause', 'top_churn_driver', 'What is the top churn driver across the book?', 3],
            ['retention', 'Root cause', 'lost_champion', 'Who lost their champion / main contact?', 4],
            ['retention', 'Root cause', 'changed_recently', 'What changed for [name] recently?', 5],

            // Retention · Save play
            ['retention', 'Save play', 'save_plan_call', 'Save plan for [name]', 1],
            ['retention', 'Save play', 'save_email', 'Save email for [name]', 2],
            ['retention', 'Save play', 'whatsapp_checkin', 'WhatsApp check-in for [name]', 3],
            ['retention', 'Save play', 'first_48_hours', 'What are the first 48 hours for [name]?', 4],
            ['retention', 'Save play', 'should_escalate', 'Should this go to an executive sponsor?', 5],
            ['retention', 'Save play', 'exec_brief', 'Exec brief for [name]', 6],

            // Retention · Offers
            ['retention', 'Offers', 'can_discount', 'Can I discount [name]?', 1],
            ['retention', 'Offers', 'what_allowed_offer', 'What am I allowed to offer [name]?', 2],
            ['retention', 'Offers', 'concession_worth_it', 'Is a concession worth it for [name]?', 3],
            ['retention', 'Offers', 'downgrade_to_save', 'Downgrade-to-save option for [name]', 4],
            ['retention', 'Offers', 'give_get_ask', 'What give-get should I ask for?', 5],

            // Retention · Recover & grow
            ['retention', 'Recover & grow', 'save_rate_revenue', 'What is our save rate and revenue saved?', 1],
            ['retention', 'Recover & grow', 'lost_to_what', 'What are we actually losing to?', 2],
            ['retention', 'Recover & grow', 'stabilising_hands_off', 'Who is stabilising — hands off?', 3],
            ['retention', 'Recover & grow', 'stabilisation_plan_for', 'Stabilisation plan for [name]', 4],
            ['retention', 'Recover & grow', 'ready_hand_back_sales', 'Who is ready to hand back to Sales?', 5],
            ['retention', 'Recover & grow', 'send_growth_ready', 'Send growth-ready accounts to Sales', 6],
        ];

        $rowsBySlug = collect($rows)->keyBy(fn ($row) => $row[0] . '|' . $row[2]);
        AgentPredefinedPrompt::whereIn('agent', ['marketing', 'retention'])
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
                ]
            );
        }
    }
}
