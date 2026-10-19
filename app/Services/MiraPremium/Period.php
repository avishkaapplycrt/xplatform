<?php

namespace App\Services\MiraPremium;

use Illuminate\Support\Carbon;

/**
 * A comparison window for the overview panels: the current window ends now,
 * the previous one is the same length immediately before it
 * (config mira_premium.periods, e.g. 30d = last 30 days vs the 30 before).
 */
class Period
{
    private function __construct(
        public readonly string $key,
        public readonly int $days,
        public readonly Carbon $start,
        public readonly Carbon $end,
        public readonly Carbon $prevStart,
    ) {}

    public static function fromKey(?string $key): self
    {
        $periods = (array) config('mira_premium.periods');
        $key     = isset($periods[$key]) ? $key : (string) config('mira_premium.default_period', '30d');
        $days    = (int) $periods[$key];
        $end     = now();
        $start   = $end->copy()->subDays($days);

        return new self($key, $days, $start, $end, $start->copy()->subDays($days));
    }

    /** Change from previous to current; null when either side is unknown. */
    public static function delta(int|float|null $current, int|float|null $previous): int|float|null
    {
        if ($current === null || $previous === null) {
            return null;
        }

        return is_float($current) || is_float($previous) ? round($current - $previous, 2) : $current - $previous;
    }

    public function toArray(): array
    {
        return [
            'key'        => $this->key,
            'days'       => $this->days,
            'start'      => $this->start->toIso8601String(),
            'end'        => $this->end->toIso8601String(),
            'prev_start' => $this->prevStart->toIso8601String(),
            'label'      => $this->key === '12m' ? 'Last 12 months vs previous 12' : "Last {$this->days} days vs previous {$this->days}",
        ];
    }
}
