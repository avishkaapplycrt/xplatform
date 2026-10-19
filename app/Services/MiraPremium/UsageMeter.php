<?php

namespace App\Services\MiraPremium;

use App\Models\MiraPremium\MiraUsage;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;

/**
 * Metered usage (scans, chat messages, visibility runs) per subject and
 * calendar period. consume() is atomic: the increment only happens when it
 * stays within the limit, so two parallel requests can't both take the last
 * unit. Standing caps (projects, prompts, competitors) are counted from their
 * own tables by the callers, not here.
 */
class UsageMeter
{
    /** Metrics metered by period, in the order the page shows them. */
    public const METERED = ['scans', 'chat_messages', 'visibility_runs'];

    public function periodKey(string $period, ?Carbon $at = null): string
    {
        $at ??= now();

        return $period === 'day' ? $at->format('Y-m-d') : $at->format('Y-m');
    }

    public function used(string $subject, string $metric, string $period): int
    {
        return (int) MiraUsage::where(['subject' => $subject, 'metric' => $metric, 'period' => $this->periodKey($period)])->value('used');
    }

    /**
     * Takes $amount units or throws QuotaExceeded (NotEntitled when the tier
     * doesn't include the metric at all).
     */
    public function consume(Entitlement $who, string $metric, int $amount = 1): void
    {
        [$limit, $period] = $who->limit($metric);

        if ($limit <= 0) {
            throw new MiraApiException('NotEntitled', $this->label($metric) . ' are part of Mira Premium.', [
                'upgrade_url' => config('mira_premium.upgrade_url'),
            ]);
        }

        $key = ['subject' => $who->subject, 'metric' => $metric, 'period' => $this->periodKey($period ?? 'month')];

        try {
            MiraUsage::firstOrCreate($key, ['used' => 0]);
        } catch (QueryException) {
            // Created by a parallel request between the read and the insert — the row exists now.
        }

        $taken = MiraUsage::where($key)->where('used', '<=', $limit - $amount)->increment('used', $amount);

        if ($taken === 0) {
            throw new MiraApiException('QuotaExceeded', $this->quotaMessage($who, $metric, $limit, $period), [
                'metric'      => $metric,
                'limit'       => $limit,
                'resets_at'   => $this->resetsAt($period)->toIso8601String(),
                'upgrade_url' => config('mira_premium.upgrade_url'),
            ]);
        }
    }

    /** Gives units back, e.g. when a scan fails through no fault of the user. */
    public function refund(string $subject, string $metric, string $period, int $amount = 1): void
    {
        MiraUsage::where(['subject' => $subject, 'metric' => $metric, 'period' => $this->periodKey($period)])
            ->where('used', '>=', $amount)
            ->decrement('used', $amount);
    }

    /** @return list<array{metric: string, label: string, used: int, limit: int, period: ?string, resets_at: ?string}> */
    public function snapshot(Entitlement $who): array
    {
        return array_map(function ($metric) use ($who) {
            [$limit, $period] = $who->limit($metric);

            return [
                'metric'    => $metric,
                'label'     => $this->label($metric),
                'used'      => $limit > 0 ? $this->used($who->subject, $metric, $period ?? 'month') : 0,
                'limit'     => $limit,
                'period'    => $period,
                'resets_at' => $period ? $this->resetsAt($period)->toIso8601String() : null,
            ];
        }, self::METERED);
    }

    public function resetsAt(?string $period): Carbon
    {
        return $period === 'day' ? now()->addDay()->startOfDay() : now()->addMonthNoOverflow()->startOfMonth();
    }

    private function label(string $metric): string
    {
        return ['scans' => 'Site scans', 'chat_messages' => 'Ask Mira messages', 'visibility_runs' => 'AI visibility runs'][$metric]
            ?? ucfirst(str_replace('_', ' ', $metric));
    }

    private function quotaMessage(Entitlement $who, string $metric, int $limit, ?string $period): string
    {
        $per  = $period === 'day' ? 'today' : 'this month';
        $more = $who->isPremium() ? 'Upgrade your plan for more.' : 'Mira Premium includes far more.';

        return "You've used all {$limit} " . strtolower($this->label($metric)) . " {$per}. {$more}";
    }
}
