<?php

namespace Database\Seeders;

use App\Models\Industry;
use App\Models\MicroSignal;
use App\Models\MicroSignalCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MicroSignalSeeder extends Seeder
{
    private array $industryCache = [];

    public function run(): void
    {
        $engagement  = $this->category('engagement-signals',  'ENGAGEMENT SIGNALS',  1);
        $conversion  = $this->category('conversion-signals',  'CONVERSION SIGNALS',  2);
        $interaction = $this->category('interaction-signals', 'INTERACTION SIGNALS', 3);
        $learning    = $this->category('learning-signals',    'LEARNING SIGNALS',    4);
        $commerce    = $this->category('commerce-signals',    'COMMERCE SIGNALS',    5);

        // Global signals (available to all industries)
        $this->insertSignals($engagement->id,  ['Page visits', 'Session duration', 'Feature usage', 'Scroll depth'],           null);
        $this->insertSignals($conversion->id,  ['Purchases', 'Form submissions', 'Sign-ups', 'Plan upgrades'],                  null);
        $this->insertSignals($interaction->id, ['Email opens', 'Chat messages', 'Support tickets', 'Call bookings'],            null);

        // EdTech only
        $this->insertSignals($learning->id, ['Lesson completion', 'Practice tests', 'Study consistency', 'Mentor sessions'], 'EdTech');

        // E-commerce + Retail
        $commerceSignals = ['Cart additions', 'Wishlist saves', 'Coupon usage', 'Review reads'];
        $this->insertSignals($commerce->id, $commerceSignals, 'E-commerce');
        $this->insertSignals($commerce->id, $commerceSignals, 'Retail');

        // SaaS
        $this->insertSignals($engagement->id, ['Feature usage', 'Session duration', 'Page visits'], 'SaaS');

        // Recruitment
        $this->insertSignals($engagement->id, ['Profile views', 'Application rate', 'Response time'], 'Recruitment');

        // Finance
        $this->insertSignals($engagement->id, ['Transaction volume', 'Login frequency', 'Feature usage'], 'Finance');

        // Healthcare
        $this->insertSignals($engagement->id, ['Appointment frequency', 'Portal logins', 'Survey responses'], 'Healthcare');

        // Travel
        $this->insertSignals($engagement->id, ['Search frequency', 'Booking attempts', 'Wishlist additions'], 'Travel');

        // Gaming
        $this->insertSignals($engagement->id, ['Session length', 'In-app purchases', 'Daily logins'], 'Gaming');

        // Media
        $this->insertSignals($engagement->id, ['Watch time', 'Content shares', 'Return visits'], 'Media');

        // Insurance
        $this->insertSignals($engagement->id, ['Claim frequency', 'Policy renewals', 'Support contacts'], 'Insurance');

        // Real Estate
        $this->insertSignals($engagement->id, ['Property views', 'Enquiry rate', 'Return visits'], 'Real Estate');

        // Marketing / AdTech
        $this->insertSignals($engagement->id, ['Click-through rate', 'Impression volume', 'Engagement time'], 'Marketing / AdTech');

        // Government
        $this->insertSignals($engagement->id, ['Service requests', 'Portal logins', 'Complaint submissions'], 'Government');

        // Manufacturing
        $this->insertSignals($engagement->id, ['Production rate', 'Downtime events', 'Quality checks'], 'Manufacturing');
    }

    private function category(string $slug, string $name, int $order): MicroSignalCategory
    {
        return MicroSignalCategory::updateOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'sort_order' => $order]
        );
    }

    private function insertSignals(int $categoryId, array $signals, ?string $industryName): void
    {
        $industryId = null;
        if ($industryName !== null) {
            if (!isset($this->industryCache[$industryName])) {
                $this->industryCache[$industryName] = Industry::where('name', $industryName)->value('id');
            }
            $industryId = $this->industryCache[$industryName];
        }

        foreach ($signals as $index => $name) {
            $slug = Str::slug($name . '-' . ($industryName ?? 'global'));

            MicroSignal::updateOrCreate(
                ['slug' => $slug],
                [
                    'micro_signal_category_id' => $categoryId,
                    'industry_id'              => $industryId,
                    'name'                     => $name,
                    'is_active'                => true,
                    'sort_order'               => $index + 1,
                ]
            );
        }
    }
}
