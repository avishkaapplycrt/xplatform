<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmailLogsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Sample email addresses
        $emails = [
            'john.doe@example.com', 'jane.smith@example.com', 'mike.johnson@example.com',
            'sarah.williams@example.com', 'david.brown@example.com', 'emma.davis@example.com',
            'chris.miller@example.com', 'olivia.wilson@example.com', 'james.moore@example.com',
            'sophia.taylor@example.com', 'daniel.anderson@example.com', 'ava.thomas@example.com',
            'matthew.jackson@example.com', 'mia.white@example.com', 'andrew.harris@example.com',
            'charlotte.martin@example.com', 'joseph.thompson@example.com', 'amelia.garcia@example.com',
            'ryan.martinez@example.com', 'harper.robinson@example.com', 'brandon.clark@example.com',
            'evelyn.rodriguez@example.com', 'tyler.lewis@example.com', 'abigail.lee@example.com',
            'austin.walker@example.com', 'elizabeth.hall@example.com', 'dylan.allen@example.com',
            'sofia.young@example.com', 'ethan.hernandez@example.com', 'victoria.king@example.com',
            'nathan.wright@example.com', 'madison.lopez@example.com', 'alexander.hill@example.com',
            'chloe.scott@example.com', 'kevin.green@example.com', 'grace.adams@example.com',
            'jacob.baker@example.com', 'lily.gonzalez@example.com', 'zachary.nelson@example.com',
            'hannah.carter@example.com', 'benjamin.mitchell@example.com', 'zoe.perez@example.com',
            'samuel.roberts@example.com', 'nora.turner@example.com', 'jonathan.phillips@example.com',
            'layla.campbell@example.com', 'carter.parker@example.com', 'aubrey.evans@example.com',
            'luke.edwards@example.com', 'ellie.collins@example.com', 'gabriel.stewart@example.com',
        ];

        // Sample subjects
        $subjects = [
            'Welcome to MockMaster Academy - Start Your PTE Journey',
            'Your Speaking Score Needs Attention - Personal Coaching Available',
            'Limited Time: 20% Off PTE Full Mock Test Package',
            'Your Weekly Progress Report - Week 3',
            'Dont Miss Out: Free Speaking Assessment This Week',
            'Congratulations! You have Completed 50% of Your Course',
            'Reminder: Your Mock Test Expires in 3 Days',
            'New Feature: AI-Powered Speaking Feedback',
            'Your PTE Score Prediction - March 2026',
            'Exclusive: Early Access to New Writing Templates',
            'Flash Sale: 48 Hours Only - 30% Off All Plans',
            'Your Study Streak is at Risk - Keep It Going!',
            'Personalized Study Plan Ready for You',
            'Referral Bonus: Earn $20 for Every Friend',
            'Your Payment Receipt - Invoice #MM-2026-001',
            'Account Security Alert - New Login Detected',
            'Summer Intensive PTE Prep - Registration Open',
            'Your Feedback Matters - Quick Survey Inside',
            'Course Update: New Listening Practice Added',
            'Last Chance: Upgrade to Premium at 40% Off',
        ];

        // Sample email bodies
        $bodies = [
            'Hi [[student_name]], Welcome to MockMaster Academy! We are excited to help you achieve your PTE goals. Your speaking score of [[speaking_score]] shows great potential. Lets get started with your personalized study plan.',
            'Dear [[student_name]], We noticed your speaking score is below 30. Our expert tutors are ready to help with 1-on-1 coaching sessions. Book your session now and improve by an average of 15 points!',
            'Hello [[student_name]], Flash sale alert! Get 20% off our Full Mock Test package. Use code FLASH20 at checkout. Valid for 48 hours only. Your current overall score: [[overall_score]].',
            'Hi [[student_name]], Here is your weekly progress report. You have completed [[completed_courses]] courses this week. Keep up the great work! Your engagement score is [[engagement_score]].',
            'Hey [[student_name]], Dont miss our free speaking assessment this week. Limited slots available. Your last login was [[days_since_login]] days ago. Come back and practice!',
            'Congratulations [[student_name]]! You have reached the 50% milestone in your PTE preparation course. Your loyalty score is [[loyalty_score]]. Keep pushing towards your goal!',
            'Hi [[student_name]], Just a friendly reminder that your mock test access expires in 3 days. Complete it now to get your detailed score breakdown. Your intent score: [[intent_score]].',
            'Hello [[student_name]], We have launched AI-powered speaking feedback! Get instant, detailed analysis of your pronunciation and fluency. Try it now in your dashboard.',
            'Hi [[student_name]], Based on your recent performance, your predicted PTE score is looking strong. Your churn risk is [[churn_score]]%. Stay consistent with your practice!',
            'Dear [[student_name]], Get early access to our new Writing Templates. These are designed to boost your writing score by 10+ points. Available exclusively for premium members.',
        ];

        // Device types
        $devices = ['mobile', 'desktop', 'tablet', 'other'];
        $deviceWeights = [45, 40, 12, 3]; // Mobile most common

        $data = [];
        $id = 1;

        // Generate 200 email log entries
        for ($i = 0; $i < 200; $i++) {
            $email = $emails[array_rand($emails)];
            $subject = $subjects[array_rand($subjects)];
            $body = $bodies[array_rand($bodies)];

            // Random date within last 60 days, weighted towards recent
            $daysAgo = rand(0, 60);
            if (rand(1, 100) <= 60) {
                $daysAgo = rand(0, 14); // 60% of emails in last 2 weeks
            }
            $baseDate = $now->copy()->subDays($daysAgo);

            // Random hour (more emails during business hours)
            $hour = rand(1, 100) <= 70 ? rand(9, 18) : rand(0, 23);
            $minute = rand(0, 59);
            $sentAt = $baseDate->copy()->setTime($hour, $minute);

            // Determine status flow (sent -> delivered -> opened -> clicked -> converted)
            $deliveredAt = null;
            $openedAt = null;
            $clickedAt = null;
            $convertedAt = null;
            $unsubscribedAt = null;
            $deviceType = null;
            $timeToOpen = null;

            // 95% delivered
            if (rand(1, 100) <= 95) {
                $deliveredAt = $sentAt->copy()->addMinutes(rand(1, 30));

                // 65% opened of delivered
                if (rand(1, 100) <= 65) {
                    $timeToOpen = rand(5, 2880); // 5 min to 48 hours
                    $openedAt = $deliveredAt->copy()->addMinutes($timeToOpen);
                    $deviceType = $this->weightedRandom($devices, $deviceWeights);

                    // 35% clicked of opened
                    if (rand(1, 100) <= 35) {
                        $clickedAt = $openedAt->copy()->addMinutes(rand(1, 60));

                        // 15% converted of clicked
                        if (rand(1, 100) <= 15) {
                            $convertedAt = $clickedAt->copy()->addHours(rand(1, 72));
                        }
                    }

                    // 2% unsubscribed
                    if (rand(1, 100) <= 2) {
                        $unsubscribedAt = $openedAt->copy()->addDays(rand(1, 7));
                    }
                }
            }

            $data[] = [
                'id' => $id++,
                'email_address' => $email,
                'sent_at' => $sentAt,
                'delivered_at' => $deliveredAt,
                'opened_at' => $openedAt,
                'clicked_at' => $clickedAt,
                'converted_at' => $convertedAt,
                'unsubscribed_at' => $unsubscribedAt,
                'device_type' => $deviceType,
                'time_to_open_minutes' => $timeToOpen,
                'created_at' => $sentAt,
                'updated_at' => $now,
            ];
        }

        // Insert in chunks
        foreach (array_chunk($data, 50) as $chunk) {
            DB::table('email_logs')->insert($chunk);
        }

        $this->command->info('Inserted ' . count($data) . ' email log records.');
    }

    private function weightedRandom(array $items, array $weights): string
    {
        $total = array_sum($weights);
        $rand = rand(1, $total);
        $current = 0;

        foreach ($items as $index => $item) {
            $current += $weights[$index];
            if ($rand <= $current) {
                return $item;
            }
        }

        return $items[0];
    }
}
