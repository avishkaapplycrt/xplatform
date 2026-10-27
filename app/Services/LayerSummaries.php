<?php

namespace App\Services;

use App\Models\BehavioralProfile;
use App\Models\ChatSupportIntegration;
use App\Models\Client;
use App\Models\CrmContact;
use App\Models\CrmIntegration;
use App\Models\EmailLog;
use App\Models\InstagramMedia;
use App\Models\SocialIntegration;
use App\Models\UserEvent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One line of real figures per platform layer (L1–L8) for the Architecture
 * Overview, from the client's own data. Each layer also gets 0–6 "dots":
 * how many of its figures have data so far. A new client sees zeros and a
 * pointer to connect data, never sample numbers.
 */
class LayerSummaries
{
    public function __construct(private DataCollectionStats $stats) {}

    /** @return array<string, array{summary: string, dots: int}> keyed by layer code */
    public function forClient(Client $client): array
    {
        $counts = $this->stats->counts($client);
        $id = $client->id;

        $profiles = BehavioralProfile::where('client_id', $id);
        $profileCount = (clone $profiles)->count();
        $events = UserEvent::where('client_id', $id)->count();
        $avgScore = $profileCount ? (int) round((clone $profiles)->avg('overall_score')) : null;
        $churnRisk = (clone $profiles)->where('churn_score', '>=', 65)->count();
        $readyToBuy = (clone $profiles)->where('buying_readiness', '>=', 80)->count();
        $leadsScored = $this->countFor('lead_scores', $id);

        $identity = $this->identity($client);
        $records = $counts['emails'] + $counts['crm'] + $counts['transactions'] + $events
            + InstagramMedia::forClient($client)->count() + $this->slackMessages($id)
            + $this->countFor('transactions_customers', $id);
        $lastSync = $this->lastSync($client);

        $emailsSent = $counts['emails'];
        $templates = $this->countFor('email_templates', $id);
        $layersOn = $client->analysisLayers()->count();
        $projects = $this->countFor('mira_projects', $id);
        $optOuts = EmailLog::forClient($client)->whereNotNull('unsubscribed_at')->count();
        $audit = $this->countFor('audit_logs', $id);

        $n = fn ($v) => number_format($v);
        $pl = fn ($v, string $word) => number_format($v) . ' ' . \Illuminate\Support\Str::plural($word, $v);

        return [
            'L1' => $this->line([
                [$counts['sources'], $counts['sources'] . ' of 5 sources connected'],
                [$counts['emails'], $pl($counts['emails'], 'email')],
                [$counts['crm'], $pl($counts['crm'], 'CRM record')],
                [$counts['transactions'], $pl($counts['transactions'], 'payment')],
            ], 'No sources connected yet — connect one in Data Collection'),
            'L2' => $this->line([
                [$identity['people'], $pl($identity['people'], 'person') . ' identified by email'],
                [$identity['matched'], $n($identity['matched']) . ' matched across 2+ sources'],
            ]),
            'L3' => $this->line([
                [$records, $pl($records, 'record') . ' stored'],
                [$lastSync ? 1 : 0, $lastSync ? 'last sync ' . $lastSync->diffForHumans() : 'not synced yet'],
            ]),
            'L4' => $this->line([
                [$profileCount, $pl($profileCount, 'behaviour profile')],
                [$events, $pl($events, 'tracked event')],
                [$avgScore, 'avg score ' . ($avgScore ?? '—')],
            ]),
            'L5' => $this->line([
                [$churnRisk, $n($churnRisk) . ' at churn risk'],
                [$readyToBuy, $n($readyToBuy) . ' ready to buy'],
                [$leadsScored, $pl($leadsScored, 'lead') . ' scored'],
            ]),
            'L6' => $this->line([
                [$emailsSent, $pl($emailsSent, 'email') . ' sent'],
                [$templates, $pl($templates, 'email template')],
            ]),
            'L7' => $this->line([
                [$layersOn, $layersOn . ' of 8 layers enabled'],
                [$projects, $pl($projects, 'Mira Premium project')],
            ]),
            'L8' => $this->line([
                [$optOuts, $pl($optOuts, 'email opt-out') . ' recorded'],
                [$audit, $pl($audit, 'audit log entry')],
            ]),
        ];
    }

    /**
     * @param array<int, array{0: int|null, 1: string}> $parts [value, text]
     */
    private function line(array $parts, string $whenEmpty = 'No data yet — fills in once your data is connected'): array
    {
        $withData = collect($parts)->filter(fn ($p) => (int) $p[0] > 0)->count();

        return [
            'summary' => $withData === 0 ? $whenEmpty : collect($parts)->pluck(1)->implode('  •  '),
            'dots'    => (int) round($withData / count($parts) * 6),
        ];
    }

    /** People known by email across CRM contacts, email logs, payment customers and behaviour profiles. */
    private function identity(Client $client): array
    {
        $norm = fn ($emails) => collect($emails)->filter()->map(fn ($e) => strtolower(trim($e)))->unique();
        $sources = [
            $norm(CrmContact::forClient($client)->pluck('email')),
            $norm(EmailLog::forClient($client)->pluck('email_address')),
            $norm(DB::table('transactions_customers')->where('client_id', $client->id)->pluck('email')),
            $norm(BehavioralProfile::where('client_id', $client->id)->pluck('email')),
        ];
        $seen = collect($sources)->flatten()->countBy();

        return ['people' => $seen->count(), 'matched' => $seen->filter(fn ($n) => $n > 1)->count()];
    }

    private function lastSync(Client $client): ?Carbon
    {
        $times = collect([
            CrmIntegration::forClient($client)->max('last_sync_at'),
            SocialIntegration::forClient($client)->max('last_sync_at'),
            ChatSupportIntegration::where('client_id', $client->id)->max('last_sync_at'),
            Schema::hasColumn('email_connections', 'last_sync_at')
                ? DB::table('email_connections')->where('client_id', $client->id)->max('last_sync_at') : null,
            DB::table('transactions')->where('client_id', $client->id)->max('updated_at'),
        ])->filter();

        return $times->isEmpty() ? null : Carbon::parse($times->max());
    }

    private function slackMessages(int $clientId): int
    {
        return DB::table('slack_messages')
            ->join('slack_channels', 'slack_channels.id', '=', 'slack_messages.slack_channel_id')
            ->join('chat_support_integrations', 'chat_support_integrations.id', '=', 'slack_channels.chat_support_integration_id')
            ->where('chat_support_integrations.client_id', $clientId)
            ->count();
    }

    private function countFor(string $table, int $clientId): int
    {
        return Schema::hasTable($table) ? DB::table($table)->where('client_id', $clientId)->count() : 0;
    }
}
