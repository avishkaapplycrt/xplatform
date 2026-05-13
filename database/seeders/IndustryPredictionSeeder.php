<?php

namespace Database\Seeders;

use App\Models\Industry;
use App\Models\Prediction;
use Illuminate\Database\Seeder;

class IndustryPredictionSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'EdTech' => [
                'signals'     => ['Lesson completion', 'Practice tests', 'Study consistency'],
                'predictions' => ['Exam success prediction', 'Dropout risk', 'Learning path optimization', 'Mentor match score'],
            ],
            'E-commerce' => [
                'signals'     => ['Cart additions', 'Page visits', 'Session duration'],
                'predictions' => ['Purchase intent', 'Churn probability', 'Customer lifetime value', 'Upsell probability'],
            ],
            'SaaS' => [
                'signals'     => ['Page visits', 'Session duration', 'Feature usage'],
                'predictions' => ['Churn probability', 'Trial conversion', 'Feature adoption score', 'Upsell propensity'],
            ],
            'Recruitment' => [
                'signals'     => ['Profile views', 'Application rate', 'Response time'],
                'predictions' => ['Hiring probability', 'Candidate fit score', 'Time-to-hire prediction', 'Offer acceptance'],
            ],
            'Finance' => [
                'signals'     => ['Transaction volume', 'Login frequency', 'Feature usage'],
                'predictions' => ['Fraud detection', 'Credit default risk', 'Churn probability', 'Transaction anomaly'],
            ],
            'Healthcare' => [
                'signals'     => ['Appointment frequency', 'Portal logins', 'Survey responses'],
                'predictions' => ['Readmission risk', 'No-show probability', 'Treatment adherence', 'Patient churn'],
            ],
            'Travel' => [
                'signals'     => ['Search frequency', 'Booking attempts', 'Wishlist additions'],
                'predictions' => ['Booking probability', 'Cancellation risk', 'Loyalty prediction', 'Price sensitivity'],
            ],
            'Gaming' => [
                'signals'     => ['Session length', 'In-app purchases', 'Daily logins'],
                'predictions' => ['Player churn risk', 'Monetization propensity', 'Matchmaking score', 'Toxicity risk'],
            ],
            'Media' => [
                'signals'     => ['Watch time', 'Content shares', 'Return visits'],
                'predictions' => ['Subscriber churn', 'Content affinity', 'Ad revenue prediction', 'Binge probability'],
            ],
            'Retail' => [
                'signals'     => ['Store visits', 'Basket size', 'Promotion clicks'],
                'predictions' => ['Purchase probability', 'Basket size prediction', 'Promotion response', 'Foot traffic'],
            ],
            'Insurance' => [
                'signals'     => ['Claim frequency', 'Policy renewals', 'Support contacts'],
                'predictions' => ['Claims fraud score', 'Lapse probability', 'Risk score', 'Cross-sell propensity'],
            ],
            'Real Estate' => [
                'signals'     => ['Property views', 'Enquiry rate', 'Return visits'],
                'predictions' => ['Buyer qualification', 'Time-to-sell', 'Property valuation', 'Lead conversion'],
            ],
            'Marketing / AdTech' => [
                'signals'     => ['Click-through rate', 'Impression volume', 'Engagement time'],
                'predictions' => ['Campaign ROI', 'Audience lookalike', 'Creative fatigue', 'Bid optimization'],
            ],
            'Government' => [
                'signals'     => ['Service requests', 'Portal logins', 'Complaint submissions'],
                'predictions' => ['Service demand forecast', 'Fraud detection', 'Resource allocation', 'Citizen satisfaction'],
            ],
            'Manufacturing' => [
                'signals'     => ['Production rate', 'Downtime events', 'Quality checks'],
                'predictions' => ['Demand forecast', 'Supplier reliability', 'Defect prediction', 'Price optimization'],
            ],
        ];

        foreach ($data as $industryName => $industryData) {
            $industry = Industry::updateOrCreate(
                ['name' => $industryName],
                ['is_custom' => false, 'client_id' => null]
            );

            foreach ($industryData['predictions'] as $predictionName) {
                $prediction = Prediction::updateOrCreate(['name' => $predictionName]);
                $industry->predictionModels()->syncWithoutDetaching([$prediction->id]);
            }
        }

        $this->command->info('✅ Industries and predictions seeded successfully!');
    }
}