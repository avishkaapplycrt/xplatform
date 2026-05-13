<?php

namespace App\Console\Commands;

use App\Models\BehavioralProfile;
use App\Models\UserEvent;
use App\Services\ScoreCalculator;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ComputeBehavioralScores extends Command
{
    protected $signature   = 'scores:compute';
    protected $description = 'Compute behavioural scores from user_events and update behavioral_profiles';

    public function handle(): int
    {
        $profiles = BehavioralProfile::all();

        if ($profiles->isEmpty()) {
            $this->warn('No profiles found. Run the seeder first.');
            return self::FAILURE;
        }

        $this->info("Computing scores for {$profiles->count()} profiles...");

        $bar = $this->output->createProgressBar($profiles->count());
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%%  %message%');
        $bar->start();

        $updated = 0;

        foreach ($profiles as $profile) {
            $bar->setMessage($profile->name);

            // Load all events for this user in one query
            $events = UserEvent::where('email', $profile->email)->get();

            // Find when they last logged in
            $lastLoginAt = $events
                ->where('event_type', 'login')
                ->sortByDesc('created_at')
                ->first()
                ?->created_at;

            $calc   = new ScoreCalculator($events, $lastLoginAt);
            $scores = $calc->compute();

            $profile->update(array_merge($scores, [
                'overall_score' => $calc->overallScore($scores),
            ]));

            $bar->advance();
            $updated++;
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. {$updated} profiles updated from " . UserEvent::count() . " events.");

        // Print a summary table
        $this->newLine();
        $this->table(
            ['Name', 'Segment', 'Intent', 'Engage', 'Churn', 'Loyal', 'Trust', 'Frust', 'Buying', 'Dropoff', 'React', 'Overall'],
            BehavioralProfile::orderByDesc('overall_score')->get()->map(fn($p) => [
                $p->name, $p->segmentLabel(),
                $p->intent_score, $p->engagement_score, $p->churn_score,
                $p->loyalty_score, $p->trust_score, $p->frustration_score,
                $p->buying_readiness, $p->dropoff_risk, $p->reactivation_potential,
                $p->overall_score,
            ])
        );

        return self::SUCCESS;
    }
}
