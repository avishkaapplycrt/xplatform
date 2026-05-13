<?php

namespace App\Observers;

use App\Jobs\RefreshUserScore;
use App\Models\UserEvent;

/**
 * Automatically triggers a score refresh whenever a new behavioural event is
 * recorded.  The job is dispatched with a 5-second delay so rapid bursts of
 * events (e.g. a page-load triggering several events at once) are batched into
 * a single recalculation rather than running one per event.
 */
class UserEventObserver
{
    public function created(UserEvent $event): void
    {
        RefreshUserScore::dispatch($event->email)
            ->delay(now()->addSeconds(5));
    }
}
