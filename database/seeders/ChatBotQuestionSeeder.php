<?php

namespace Database\Seeders;

use App\Models\ChatBotQuestion;
use App\Models\Industry;
use Illuminate\Database\Seeder;

/**
 * Seeds chat_bot_questions from the curated "Ask Mira" question set
 * (sourced from the Mira playbook), mapped onto the real industries
 * table by name. Industries with no matching playbook section
 * (Recruitment, Marketing / AdTech, Government) are left unseeded.
 */
class ChatBotQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $byIndustryName = [
            'SaaS' => [
                'Why are my SaaS customers churning in month 3?',
                'What is a good churn rate for SaaS?',
                'What signals predict SaaS churn earliest?',
                'How do I improve SaaS onboarding to reduce churn?',
                'What should I do when a SaaS customer asks to cancel?',
                'What is Net Revenue Retention NRR for SaaS?',
                'How do I identify SaaS upsell opportunities?',
                'How do I reduce SaaS churn for enterprise customers?',
                'What is the SaaS month 6 churn cliff?',
                'What is a SaaS free trial conversion rate?',
                'How do I calculate CLV for SaaS?',
                'What is the SaaS Rule of 40?',
                'What is the SaaS magic number?',
                'How do I handle SaaS enterprise renewals?',
                'What is SaaS expansion revenue?',
                'What causes SaaS involuntary churn?',
                'What is Gross Revenue Retention for SaaS?',
                'How do I reduce SaaS SMB churn?',
                'What is a SaaS health score?',
                'What is the SaaS post-onboarding cliff?',
                'How do I create a SaaS customer success programme?',
                'What is SaaS product-led growth PLG?',
                'What is the Net Promoter Score for SaaS?',
                'What is a SaaS QBR?',
                'What drives SaaS annual plan adoption?',
                'How do I track SaaS product usage?',
                'What is the SaaS time to value?',
                'How do I segment SaaS customers for retention?',
                'What is a SaaS CSM ratio?',
                'What is user onboarding vs account onboarding in SaaS?',
                'How do I calculate SaaS churn by cohort?',
                'What is the SaaS activation rate?',
                'How do I handle a SaaS churn spike?',
                'What is SaaS gross churn?',
                'How do I improve SaaS customer communication?',
                'What is churn in a SaaS business?',
                'How do I reduce SaaS churn through product improvement?',
                'What is a SaaS expansion playbook?',
                'How do I measure SaaS onboarding success?',
                'What is the difference between SaaS and traditional software churn?',
                'How do I run a SaaS churn analysis?',
                'What is SaaS time to churn?',
                'What is SaaS logo retention?',
                'How do I build a SaaS churn model?',
                'What is the best SaaS retention metric?',
                'How do I recover a churned SaaS customer?',
                'How do I track customer satisfaction in SaaS?',
                'What is SaaS churn by industry vertical?',
                'How do I reduce SaaS churn through community?',
                'What is SaaS customer ROI?',
                'What is SaaS voluntary vs involuntary churn?',
                'How do I build a SaaS retention team?',
                'What is a SaaS renewal rate?',
                'What is a sticky SaaS product?',
                'How do I improve SaaS NPS?',
                'What is SaaS customer onboarding best practice?',
                'What data predicts SaaS churn best?',
                'How do I create a SaaS exit survey?',
                'What is annual contract value ACV in SaaS?',
                'How do I prioritise SaaS feature development to reduce churn?',
                'What is SaaS customer concentration risk?',
                'How does customer success differ in B2B vs B2C SaaS?',
                'What is a SaaS revenue model?',
            ],
            'E-commerce' => [
                'Why do customers stop buying from my online store?',
                'What is a good repeat purchase rate?',
                'How do I predict which e-commerce customers will churn?',
                'How do I increase CLV for e-commerce customers?',
                'How do I recover abandoned cart customers?',
                'What is RFM segmentation for e-commerce?',
                'How do I win back lapsed e-commerce customers?',
                'How do I reduce e-commerce returns?',
                'What is the best e-commerce loyalty programme?',
                'How do I reduce e-commerce acquisition cost?',
                'What is the post-purchase experience in e-commerce?',
                'What is e-commerce email marketing?',
                'How do I calculate e-commerce customer lifetime value?',
                'What is the e-commerce customer acquisition funnel?',
                'What is e-commerce subscription churn?',
                'How do I track e-commerce customer behaviour?',
                'What is average order value AOV and how do I increase it?',
                'What is the e-commerce NPS benchmark?',
                'What is e-commerce customer segmentation?',
                'How do I handle e-commerce negative reviews?',
                'What is the e-commerce returns rate benchmark?',
            ],
            'Finance' => [
                'Why do banking customers switch to another bank?',
                'What signals predict banking customer churn?',
                'How do I retain high-value banking customers?',
                'How do I cross-sell banking products?',
                'What is customer lifetime value in banking?',
                'How do I handle banking customers in financial difficulty?',
                'What is the best way to onboard new banking customers?',
                'How do I improve the digital banking experience to reduce churn?',
                'What is banking NPS and how do I improve it?',
                'How do I detect banking customer fraud risk alongside churn risk?',
                'How do I segment banking customers?',
                'What is open banking and how does it affect churn?',
                'What is banking churn at account opening?',
                'How does life event marketing work in banking?',
                'What is a banking product penetration rate?',
                'How do I retain banking customers at the end of a fixed mortgage?',
                'What is banking relationship banking vs transaction banking?',
            ],
            'Healthcare' => [
                'Why do patients stop coming back?',
                'What signals predict patient churn?',
                'How do I reduce patient no-shows?',
                'How do I improve patient engagement and retention?',
                'What is patient lifetime value?',
                'How do I improve patient satisfaction?',
            ],
            'EdTech' => [
                'Why do students stop using my EdTech platform?',
                'What signals predict EdTech student churn?',
                'How do I improve course completion rates?',
                'How do I intervene with at-risk EdTech students?',
                'What is CLV for EdTech companies?',
            ],
            'Retail' => [
                'How do I predict which retail customers are about to stop shopping?',
                'How do I retain loyal retail customers?',
                'How do I increase CLV in retail?',
            ],
            'Insurance' => [
                'Why do insurance customers cancel their policies?',
                'What signals predict insurance customer churn?',
                'How do I retain insurance customers at renewal?',
                'How do I improve the claims experience to reduce churn?',
            ],
            'Media' => [
                'Why do streaming subscribers cancel?',
                'What signals predict streaming subscriber churn?',
                'How do I improve subscriber activation in the first 30 days?',
            ],
            'Travel' => [
                'How do I get travel customers to book with me again?',
                'What signals predict travel customer churn?',
                'How do I increase CLV for travel customers?',
            ],
            'Real Estate' => [
                'How do I keep real estate clients coming back?',
                'What signals show a real estate client is ready to buy or sell?',
                'How do I increase CLV in real estate?',
            ],
            'Manufacturing' => [
                'How do I predict which manufacturing clients will not renew?',
                'How do I retain B2B manufacturing clients long-term?',
                'How do I handle price competition in B2B manufacturing?',
            ],
            'Gaming' => [
                'What are the key gaming retention metrics?',
                'What signals predict gaming player churn?',
                'What is player lifetime value in gaming?',
                'How do I improve Day 1 gaming retention?',
                'How do I retain gaming whales?',
                'What is the mobile gaming churn curve?',
                'How do push notifications affect gaming retention?',
                'What is a gaming season pass and how does it improve retention?',
                'How do social features affect gaming retention?',
                'How do game updates and new content affect retention?',
            ],
        ];

        // General / cross-industry questions — stored with a null industry_id
        // and shown for "All industries" (the default dropdown option).
        $general = [
            'What is customer churn?',
            'What is a good churn rate?',
            'What is customer lifetime value CLV?',
            'What signals predict churn earliest?',
            'How do I reduce churn?',
            'What is a good CLV to CAC ratio?',
            'What is customer segmentation?',
            'How does the RL engine work?',
            'What is Net Promoter Score NPS?',
            'How do I calculate MRR?',
        ];

        ChatBotQuestion::query()->delete();

        $industries = Industry::pluck('id', 'name');

        foreach ($byIndustryName as $name => $questions) {
            $industryId = $industries[$name] ?? null;
            if (!$industryId) {
                continue; // no matching row in the industries table
            }
            foreach ($questions as $question) {
                ChatBotQuestion::create([
                    'industry_id' => $industryId,
                    'question'    => $question,
                ]);
            }
        }

        foreach ($general as $question) {
            ChatBotQuestion::create([
                'industry_id' => null,
                'question'    => $question,
            ]);
        }
    }
}
