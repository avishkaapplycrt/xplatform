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
            ['marketing', 'Audience', 'build_audiences', 'Build my audiences', 1],
            ['marketing', 'Audience', 'suppression_list', 'Suppression list', 2],
            ['marketing', 'Audience', 'mqls_to_sales', 'Which MQLs go to Sales?', 3],
            ['marketing', 'Audience', 'winback_list', 'Show me the win-back list', 4],
            ['marketing', 'Audience', 'freq_capped_sales_cycle', 'Frequency-capped / in sales cycle', 5],

            // Marketing · Insights
            ['marketing', 'Insights', 'segment_rule_mql_sales', 'Segment rule for MQL → Sales', 1],
            ['marketing', 'Insights', 'top_signals_mql_sales', 'Top signals in MQL → Sales', 2],
            ['marketing', 'Insights', 'levers_mql_sales', 'Levers for MQL → Sales', 3],
            ['marketing', 'Insights', 'changed_last_week', 'What changed since last week?', 4],
            ['marketing', 'Insights', 'why_in_segment', 'Why is [name] in this segment?', 5],

            // Marketing · Campaign
            ['marketing', 'Campaign', 'email_sequence_mql_sales', 'Email sequence for MQL → Sales', 1],
            ['marketing', 'Campaign', 'whatsapp_version', 'WhatsApp version', 2],
            ['marketing', 'Campaign', 'sms_version', 'SMS version', 3],
            ['marketing', 'Campaign', 'linkedin_dm_post', 'LinkedIn DM + post', 4],
            ['marketing', 'Campaign', 'social_ad_version', 'Social / ad version', 5],
            ['marketing', 'Campaign', 'proof_or_offer_led', 'Proof-led or offer-led?', 6],
            ['marketing', 'Campaign', 'discount_allowed', 'Discount allowed?', 7],
            ['marketing', 'Campaign', 'brand_voice_rewrite', 'Brand-voice rewrite', 8],

            // Marketing · A/B test
            ['marketing', 'A/B test', 'test_ideas_mql_sales', 'Test ideas for MQL → Sales', 1],
            ['marketing', 'A/B test', 'sample_size', 'Sample size', 2],
            ['marketing', 'A/B test', 'best_send_window', 'Best send window', 3],
            ['marketing', 'A/B test', 'holdout_check', 'Holdout check', 4],

            // Marketing · Performance
            ['marketing', 'Performance', 'forecast_roi_by_audience', 'Forecast ROI by audience', 1],
            ['marketing', 'Performance', 'lift_vs_holdout_mql_sales', 'Lift vs holdout for MQL → Sales', 2],
            ['marketing', 'Performance', 'new_mqls_this_week', 'New MQLs this week?', 3],
            ['marketing', 'Performance', 'where_should_budget_go', 'Where should budget go?', 4],
            ['marketing', 'Performance', 'push_mqls_to_sales', 'Push MQLs to Sales', 5],
        ];

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
