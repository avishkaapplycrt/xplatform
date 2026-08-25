<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Speaking Score Intervention',
                'subject' => 'Personal Speaking Coaching Session - Improve Your PTE Score',
                'body' => "Hi [[student_name]],\n\nWe've noticed your speaking score ([[speaking_score]]) has room for improvement. Our expert tutors are ready to help you boost it by 15+ points with personalized 1-on-1 coaching.\n\nWhat's included:\n• Personalized speaking assessment\n• Custom practice plan\n• Weekly mock tests\n• Progress tracking\n\nBook your session now: [[booking_link]]\n\nBest regards,\n[[company_name]] Team",
                'category' => 'intervention',
            ],
            [
                'name' => 'Win-Back - We Miss You',
                'subject' => 'We Miss You! Special 20% Off Your Next Course',
                'body' => "Hi [[student_name]],\n\nIt's been [[days_since_login]] days since we last saw you. We noticed you haven't logged in recently and wanted to check in.\n\nYour progress so far:\n• Courses completed: [[completed_courses]]\n• Best score: [[best_score]]\n\nCome back with 20% off any course! Use code: COMEBACK20\n\nYour learning journey is waiting.\n\n[[company_name]] Team",
                'category' => 'winback',
            ],
            [
                'name' => 'Course Upsell - Hot Buyer',
                'subject' => 'Unlock Your Full Potential - Advanced PTE Package',
                'body' => "Hi [[student_name]],\n\nGreat job on your recent progress! Your intent score ([[intent_score]]) shows you're ready for the next level.\n\nUpgrade to our Advanced Package:\n• 50+ full mock tests\n• AI-powered feedback\n• Priority tutor support\n• Guaranteed score improvement\n\nSpecial price: [[offer_price]] (Save [[discount_amount]])\n\nUpgrade now: [[upgrade_link]]\n\n[[company_name]] Team",
                'category' => 'upsell',
            ],
            [
                'name' => 'Welcome Onboarding',
                'subject' => 'Welcome to [[company_name]]! Your Learning Journey Starts Here',
                'body' => "Hi [[student_name]],\n\nWelcome to [[company_name]]! We're excited to help you achieve your PTE goals.\n\nGetting started:\n1. Take your diagnostic test\n2. Get your personalized study plan\n3. Start with recommended modules\n\nYour first mock test is FREE! Start here: [[test_link]]\n\nNeed help? Reply to this email or call us at [[support_phone]].\n\nCheers,\n[[company_name]] Team",
                'category' => 'onboarding',
            ],
            [
                'name' => 'Retention - Loyal Student Reward',
                'subject' => 'Thank You! Exclusive Loyalty Reward Inside',
                'body' => "Hi [[student_name]],\n\nThank you for being a loyal learner! Your loyalty score ([[loyalty_score]]) puts you in our top tier.\n\nAs a thank you, enjoy:\n• Free advanced module access\n• Exclusive webinar invites\n• Priority booking for new features\n• [[referral_bonus]] bonus for each friend you refer\n\nRefer friends: [[referral_link]]\n\nWe appreciate you!\n[[company_name]] Team",
                'category' => 'general',
            ],
            [
                'name' => 'Churn Risk - Personal Outreach',
                'subject' => 'Lets Get You Back on Track',
                'body' => "Hi [[student_name]],\n\nWe noticed you might be facing some challenges (churn risk: [[churn_score]]%). We're here to help!\n\nCommon issues we can solve:\n• Difficulty with specific modules → Custom tutoring\n• Time constraints → Flexible study plans\n• Low scores → Targeted practice sets\n\nLet's schedule a quick call: [[calendar_link]]\n\nOr reply to this email with your concerns.\n\nWe're here for you,\n[[company_name]] Team",
                'category' => 'intervention',
            ],
        ];

        $clientId = 1; // Default client, or get from context

        foreach ($templates as $template) {
            EmailTemplate::create(array_merge($template, ['client_id' => $clientId]));
        }
    }
}