<?php

namespace App\Jobs;

use App\Models\BehavioralProfile;
use App\Models\UserEvent;
use App\Services\ScoreCalculator;
use App\Services\SegmentClassifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Recomputes all 9 behavioural scores + segment for one user from their raw
 * UserEvent log.  Dispatched by UserEventObserver on every new event and by
 * the RefreshBehavioralScores command for full batch refreshes.
 *
 * Works with any queue driver (sync, database, Redis).
 */
class RefreshUserScore implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public readonly string $email) {}

    public function handle(SegmentClassifier $classifier): void
    {
        $events = UserEvent::where('email', $this->email)
            ->orderBy('created_at')
            ->get();

        if ($events->isEmpty()) {
            return;
        }

        // Derive last login from the most recent login event in the event log
        $lastLoginEvent = $events
            ->where('event_type', 'login')
            ->sortByDesc('created_at')
            ->first();

        $profile   = BehavioralProfile::firstWhere('email', $this->email);
        $lastLogin = $lastLoginEvent?->created_at ?? $profile?->last_active_at;

        // Run the scoring model
        $calculator = new ScoreCalculator($events, $lastLogin);
        $scores     = $calculator->compute();
        $overall    = $calculator->overallScore($scores);
        $segment    = $classifier->classify($scores, $lastLogin);

        $update = array_merge($scores, [
            'overall_score'  => $overall,
            'segment'        => $segment,
            'last_active_at' => $lastLogin,
        ]);

        if ($profile) {
            $profile->update($update);
        } else {
            // New user — create a minimal profile (name populated later via registration)
            BehavioralProfile::create(array_merge($update, [
                'email' => $this->email,
                'name'  => explode('@', $this->email)[0],
            ]));
        }
    }
}
