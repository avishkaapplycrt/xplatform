<?php

namespace Database\Seeders;

use App\Models\Industry;
use Illuminate\Database\Seeder;

class IndustrySeeder extends Seeder
{
    public function run(): void
    {
        $industries = [
            ['name' => 'EdTech',             'signals' => ['Lesson completion', 'Practice tests', 'Study consistency'],       'predictions' => ['Exam success prediction', 'Dropout risk', 'Learning path optimization']],
            ['name' => 'E-commerce',         'signals' => ['Cart additions', 'Page visits', 'Session duration'],              'predictions' => ['Purchase intent', 'Churn probability', 'Customer lifetime value']],
            ['name' => 'SaaS',               'signals' => ['Page visits', 'Session duration', 'Feature usage'],               'predictions' => ['Churn probability', 'Trial conversion', 'Feature adoption score']],
            ['name' => 'Recruitment',        'signals' => ['Profile views', 'Application rate', 'Response time'],             'predictions' => ['Hiring probability', 'Candidate fit score', 'Time-to-hire prediction']],
            ['name' => 'Finance',            'signals' => ['Transaction volume', 'Login frequency', 'Feature usage'],         'predictions' => ['Fraud detection', 'Credit default risk', 'Churn probability']],
            ['name' => 'Healthcare',         'signals' => ['Appointment frequency', 'Portal logins', 'Survey responses'],     'predictions' => ['Readmission risk', 'No-show probability', 'Treatment adherence']],
            ['name' => 'Travel',             'signals' => ['Search frequency', 'Booking attempts', 'Wishlist additions'],     'predictions' => ['Booking probability', 'Cancellation risk', 'Loyalty prediction']],
            ['name' => 'Gaming',             'signals' => ['Session length', 'In-app purchases', 'Daily logins'],             'predictions' => ['Player churn risk', 'Monetization propensity', 'Matchmaking score']],
            ['name' => 'Media',              'signals' => ['Watch time', 'Content shares', 'Return visits'],                  'predictions' => ['Subscriber churn', 'Content affinity', 'Ad revenue prediction']],
            ['name' => 'Retail',             'signals' => ['Store visits', 'Basket size', 'Promotion clicks'],                'predictions' => ['Purchase probability', 'Basket size prediction', 'Promotion response']],
            ['name' => 'Insurance',          'signals' => ['Claim frequency', 'Policy renewals', 'Support contacts'],        'predictions' => ['Claims fraud score', 'Lapse probability', 'Risk score']],
            ['name' => 'Real Estate',        'signals' => ['Property views', 'Enquiry rate', 'Return visits'],                'predictions' => ['Buyer qualification', 'Time-to-sell', 'Property valuation']],
            ['name' => 'Marketing / AdTech', 'signals' => ['Click-through rate', 'Impression volume', 'Engagement time'],    'predictions' => ['Campaign ROI', 'Audience lookalike', 'Creative fatigue']],
            ['name' => 'Government',         'signals' => ['Service requests', 'Portal logins', 'Complaint submissions'],    'predictions' => ['Service demand forecast', 'Fraud detection', 'Resource allocation']],
            ['name' => 'Manufacturing',      'signals' => ['Production rate', 'Downtime events', 'Quality checks'],          'predictions' => ['Demand forecast', 'Supplier reliability', 'Defect prediction']],
        ];

        foreach ($industries as $industry) {
            Industry::create($industry);
        }
    }
}
