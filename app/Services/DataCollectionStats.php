<?php

namespace App\Services;

use App\Models\ChatSupportIntegration;
use App\Models\Client;
use App\Models\CrmContact;
use App\Models\CrmDeal;
use App\Models\SlackChannel;
use App\Models\SlackMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Real numbers for the Data Collection (Layer 1) CRM, Chat & Support and
 * Transactions tabs, for one client. Plain arithmetic over what has been
 * synced: crm_contacts / crm_deals, slack_channels / slack_messages and
 * transactions / transactions_customers. Nothing is estimated.
 *
 * Each panel is an array the view's stats-panel partial draws:
 *   title, note, color, stats [[label, value]], bars [title, rows [[label, valueText, pct]]],
 *   tables [[title, cols, rows, empty]], or 'empty' => message when there is no data yet.
 */
class DataCollectionStats
{
    /** Headline counts for the top cards on Data Collection and Architecture Overview. */
    public function counts(Client $client): array
    {
        $payments = DB::table('transactions')->where('client_id', $client->id);

        return [
            'emails'       => \App\Models\EmailLog::forClient($client)->count(),
            'crm'          => CrmContact::forClient($client)->count() + CrmDeal::forClient($client)->count(),
            'transactions' => (clone $payments)->count(),
            'sources'      => collect([
                \App\Models\EmailConnection::where('client_id', $client->id)->exists(),
                \App\Models\CrmIntegration::forClient($client)->where('status', 'connected')->exists(),
                \App\Models\SocialIntegration::forClient($client)->where('status', 'connected')->exists(),
                ChatSupportIntegration::where('client_id', $client->id)->where('status', 'connected')->exists(),
                (clone $payments)->exists(),
            ])->filter()->count(),
        ];
    }

    public function crm(Client $client): array
    {
        $deals = CrmDeal::forClient($client)->get();
        $contacts = CrmContact::forClient($client)->get();

        return $this->connectState([
            'stage-transitions' => $this->pipelineByStage($deals),
            'field-completion'  => $this->fieldCompletion($contacts, $deals),
        ], \App\Models\CrmIntegration::forClient($client)->where('status', 'connected')->exists(),
            'your CRM', 'client.crm-connections', 'Connect CRM');
    }

    public function chat(Client $client): array
    {
        $integrationIds = ChatSupportIntegration::where('client_id', $client->id)->pluck('id');
        $channels = SlackChannel::whereIn('chat_support_integration_id', $integrationIds)->get()->keyBy('id');
        $messages = SlackMessage::whereIn('slack_channel_id', $channels->keys())->orderByDesc('posted_at')->get();

        return $this->connectState([
            'conversation-events' => $this->conversationEvents($channels, $messages),
            'resolution-metrics'  => $this->replies($channels, $messages),
        ], ChatSupportIntegration::where('client_id', $client->id)->where('status', 'connected')->exists(),
            'Slack', 'client.chat-support-connections', 'Connect Slack');
    }

    public function transactions(Client $client): array
    {
        $customers = DB::table('transactions_customers')->where('client_id', $client->id)->get();
        $customersById = $customers->keyBy('id');
        $txns = DB::table('transactions')->where('client_id', $client->id)->orderByDesc('created_at')->get()
            ->map(function ($t) use ($customersById) {
                $t->meta = json_decode($t->metadata ?? '', true) ?: [];
                $t->created = $t->created_at ? Carbon::parse($t->created_at) : null;
                // Who paid: the payment's email, else the synced customer it belongs to.
                $customer = $customersById->get($t->customer_id);
                $t->who = ($t->meta['customer_email'] ?? null) ?: ($customer->email ?? null) ?: ($customer->name ?? null) ?: '—';

                return $t;
            });
        $currency = $txns->pluck('meta.currency')->filter()->first() ?? 'USD';

        return $this->connectState([
            'cart-analytics'  => $this->orders($txns, $currency),
            'payment-methods' => $this->paymentMethods($txns, $currency),
            'failure-codes'   => $this->paymentStatus($txns, $currency),
            'refunds'         => $this->refunds($txns, $currency),
            'repeat-purchase' => $this->repeatPurchase($customers, $currency),
        ], $txns->isNotEmpty() || (\Illuminate\Support\Facades\Schema::hasTable('payment_gateway_connections')
            && DB::table('payment_gateway_connections')->where('client_id', $client->id)->where('is_connected', true)->exists()),
            'your payment provider', 'client.payment-gateway-connections.index', 'Connect payments');
    }

    /**
     * Empty panels say what to do next: connect the service when it isn't
     * connected, otherwise (connected, nothing synced yet) keep the panel's
     * own sync hint. Both get a button to the service's connection page.
     */
    private function connectState(array $panels, bool $connected, string $service, string $route, string $label): array
    {
        foreach ($panels as $tab => $panel) {
            if (!isset($panel['empty'])) continue;
            $panels[$tab] = $connected
                ? $panel + ['tag' => 'No data yet', 'action' => [route($route), 'Open connection']]
                : array_merge($panel, ['empty' => "Connect {$service} to view this data.", 'tag' => 'Not connected', 'action' => [route($route), $label]]);
        }

        return $panels;
    }

    /* ── CRM ─────────────────────────────────────────────────────────────── */

    private function pipelineByStage(Collection $deals): array
    {
        $panel = ['title' => 'CRM – Pipeline by Stage', 'color' => '#10b981',
            'note' => 'Where each synced deal sits right now. Your CRM sync brings in the current stage only, not the history of stage changes.'];
        if ($deals->isEmpty()) {
            return $panel + ['empty' => 'No deals synced yet. Connect your CRM and run a sync to see your pipeline here.'];
        }

        $open = $deals->where('status', 'open');
        $won = $deals->where('status', 'won');
        $lost = $deals->where('status', 'lost');
        $closed = $won->count() + $lost->count();

        $rows = $deals->groupBy(fn ($d) => ($d->stage ?: '(no stage)') . '|' . ($d->status ?: 'open'))
            ->map(function ($group, $key) {
                [$stage, $status] = explode('|', $key, 2);
                $ages = $group->map(fn ($d) => $this->dealCreatedAt($d)?->diffInDays(now()))->filter(fn ($v) => $v !== null);

                return ['stage' => $this->stageLabel($stage), 'status' => ucfirst($status), 'deals' => $group->count(),
                    'value' => (float) $group->sum('value'), 'age' => $ages->isEmpty() ? null : (int) round($ages->avg())];
            })
            ->sortByDesc('value')->values()
            ->map(fn ($r) => [$r['stage'], $r['status'], number_format($r['deals']), $this->money($r['value']),
                $r['age'] === null ? '—' : $r['age'] . ' days'])
            ->all();

        return $panel + [
            'stats' => [
                ['Open deals', number_format($open->count())],
                ['Open pipeline', $this->money($open->sum('value'))],
                ['Won', $this->money($won->sum('value')) . ' (' . $won->count() . ')'],
                ['Win rate', $closed ? round($won->count() / $closed * 100) . '%' : '—'],
            ],
            'tables' => [[
                'title' => 'Deals by stage',
                'cols'  => ['Stage', 'Status', 'Deals', 'Value', 'Avg age'],
                'rows'  => $rows,
            ]],
        ];
    }

    private function fieldCompletion(Collection $contacts, Collection $deals): array
    {
        $panel = ['title' => 'CRM – Field Completion', 'color' => '#10b981',
            'note' => 'How many of your synced records have each field filled in. Gaps here limit what the other layers can work out.'];
        if ($contacts->isEmpty() && $deals->isEmpty()) {
            return $panel + ['empty' => 'No CRM records synced yet. Connect your CRM and run a sync to see field completion here.'];
        }

        $filled = fn (Collection $rows, callable $has) => $rows->isEmpty() ? 0 : (int) round($rows->filter($has)->count() / $rows->count() * 100);
        $present = fn ($v) => trim((string) $v) !== '';

        $contactFields = [
            'Email'         => fn ($c) => $present($c->email),
            'First name'    => fn ($c) => $present($c->first_name),
            'Last name'     => fn ($c) => $present($c->last_name),
            'Company'       => fn ($c) => $present($c->company),
            'Last activity' => fn ($c) => $c->last_activity_at !== null,
        ];
        $dealFields = [
            'Deal value' => fn ($d) => (float) $d->value > 0,
            'Stage'      => fn ($d) => $present($d->stage),
            'Close date' => fn ($d) => $d->close_date !== null,
        ];

        $contactRows = collect($contactFields)->map(fn ($has, $label) => [$label, $filled($contacts, $has)]);
        $dealRows = collect($dealFields)->map(fn ($has, $label) => [$label, $filled($deals, $has)]);
        $complete = $contacts->filter(fn ($c) => collect($contactFields)->every(fn ($has) => $has($c)))->count();

        $bar = fn ($rows) => $rows->map(fn ($r) => [$r[0], $r[1] . '%', $r[1]])->values()->all();

        return $panel + [
            'stats' => [
                ['Contacts', number_format($contacts->count())],
                ['Fully complete', $contacts->isEmpty() ? '—' : round($complete / $contacts->count() * 100) . '%'],
                ['Missing email', number_format($contacts->reject(fn ($c) => $present($c->email))->count())],
                ['Deals', number_format($deals->count())],
            ],
            'bars' => array_values(array_filter([
                $contacts->isNotEmpty() ? ['title' => 'Contacts — fields filled in', 'rows' => $bar($contactRows)] : null,
                $deals->isNotEmpty() ? ['title' => 'Deals — fields filled in', 'rows' => $bar($dealRows)] : null,
            ])),
        ];
    }

    private function dealCreatedAt(CrmDeal $deal): ?Carbon
    {
        $raw = is_array($deal->raw_data) ? $deal->raw_data : (json_decode((string) $deal->raw_data, true) ?: []);
        $created = $raw['createdAt'] ?? $raw['Created_Time'] ?? $deal->created_at;

        try {
            return $created ? Carbon::parse($created) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /** HubSpot stage ids (e.g. "qualifiedtobuy") read better with spaces; other CRMs already send labels. */
    private function stageLabel(string $stage): string
    {
        $hubspot = [
            'appointmentscheduled' => 'Appointment scheduled', 'qualifiedtobuy' => 'Qualified to buy',
            'presentationscheduled' => 'Presentation scheduled', 'decisionmakerboughtin' => 'Decision maker bought-in',
            'contractsent' => 'Contract sent', 'closedwon' => 'Closed won', 'closedlost' => 'Closed lost',
        ];

        return $hubspot[$stage] ?? $stage;
    }

    /* ── Chat & Support (Slack) ──────────────────────────────────────────── */

    private function conversationEvents(Collection $channels, Collection $messages): array
    {
        $panel = ['title' => 'Chat & Support – Conversation Events', 'color' => '#f59e0b',
            'note' => 'Messages synced from the Slack channels your Slack bot has joined.'];
        if ($messages->isEmpty()) {
            return $panel + ['empty' => $channels->isEmpty()
                ? 'No Slack workspace synced yet. Connect Slack under Chat & Support and run a sync.'
                : 'No messages synced yet. Invite the Slack bot to your channels and run a sync.'];
        }

        $byChannel = $messages->groupBy('slack_channel_id')->map->count()->sortDesc();
        $max = max(1, $byChannel->max());
        $byDay = $messages->filter(fn ($m) => $m->posted_at && $m->posted_at->gte(now()->subDays(14)))
            ->groupBy(fn ($m) => $m->posted_at->format('Y-m-d'))->map->count()->sortKeysDesc();

        return $panel + [
            'stats' => [
                ['Messages', number_format($messages->count())],
                ['Channels', number_format($channels->count())],
                ['People posting', number_format($messages->pluck('user_id')->filter()->unique()->count())],
                ['Last 7 days', number_format($messages->filter(fn ($m) => $m->posted_at && $m->posted_at->gte(now()->subDays(7)))->count())],
            ],
            'bars' => [[
                'title' => 'Messages by channel',
                'rows'  => $byChannel->map(fn ($n, $id) => ['#' . ($channels[$id]->name ?? 'channel'), number_format($n), (int) round($n / $max * 100)])->values()->all(),
            ]],
            'tables' => [[
                'title' => 'Messages per day (last 14 days)',
                'cols'  => ['Day', 'Messages'],
                'rows'  => $byDay->map(fn ($n, $day) => [Carbon::parse($day)->format('D j M'), number_format($n)])->values()->all(),
                'empty' => 'No messages in the last 14 days.',
            ]],
        ];
    }

    private function replies(Collection $channels, Collection $messages): array
    {
        $panel = ['title' => 'Chat & Support – Replies & Reactions', 'color' => '#f59e0b',
            'note' => 'Slack doesn\'t mark conversations as resolved, so this shows how often messages get a reply or a reaction.'];
        if ($messages->isEmpty()) {
            return $panel + ['empty' => 'No messages synced yet. Invite the Slack bot to your channels and run a sync.'];
        }

        $answered = $messages->filter(fn ($m) => $m->reply_count > 0 || $m->reaction_count > 0)->count();
        $top = $messages->filter(fn ($m) => $m->reply_count > 0 || $m->reaction_count > 0)
            ->sortByDesc(fn ($m) => $m->reply_count * 10 + $m->reaction_count)->take(5);

        return $panel + [
            'stats' => [
                ['Threads with replies', number_format($messages->where('reply_count', '>', 0)->count())],
                ['Replies', number_format($messages->sum('reply_count'))],
                ['Reactions', number_format($messages->sum('reaction_count'))],
                ['Got a response', round($answered / $messages->count() * 100) . '%'],
            ],
            'tables' => [[
                'title' => 'Messages with the most responses',
                'cols'  => ['Message', 'Channel', 'Replies', 'Reactions'],
                'rows'  => $top->map(fn ($m) => [
                    \Illuminate\Support\Str::limit(trim((string) $m->text) ?: '(no text)', 70),
                    '#' . ($channels[$m->slack_channel_id]->name ?? 'channel'),
                    number_format($m->reply_count), number_format($m->reaction_count),
                ])->values()->all(),
                'empty' => 'No message has had a reply or a reaction yet.',
            ]],
        ];
    }

    /* ── Transactions (Stripe) ───────────────────────────────────────────── */

    private function noPayments(array $panel): array
    {
        return $panel + ['empty' => 'No payments synced yet. Connect Stripe under Transactions and run a sync.'];
    }

    private function orders(Collection $txns, string $currency): array
    {
        $panel = ['title' => 'Transactions – Order Analytics', 'color' => '#14b8a6',
            'note' => 'Completed payments synced from your payment provider. Cart activity (items added or abandoned) isn\'t tracked by the provider.'];
        if ($txns->isEmpty()) return $this->noPayments($panel);

        $done = $txns->where('status', 'completed');
        $byMonth = $done->filter(fn ($t) => $t->created)->groupBy(fn ($t) => $t->created->format('Y-m'))
            ->map(fn ($g) => (float) $g->sum('amount'))->sortKeysDesc()->take(6);
        $max = max(1, $byMonth->max() ?? 1);

        return $panel + [
            'stats' => [
                ['Revenue', $this->money($done->sum('amount'), $currency)],
                ['Orders', number_format($done->count())],
                ['Avg order value', $done->isEmpty() ? '—' : $this->money($done->avg('amount'), $currency)],
                ['Items sold', number_format($done->sum('quantity'))],
            ],
            'bars' => $byMonth->isEmpty() ? [] : [[
                'title' => 'Revenue by month',
                'rows'  => $byMonth->map(fn ($v, $m) => [Carbon::parse($m . '-01')->format('M Y'), $this->money($v, $currency), (int) round($v / $max * 100)])->values()->all(),
            ]],
            'tables' => [[
                'title' => 'Latest payments',
                'cols'  => ['Date', 'Customer', 'Amount', 'Status'],
                'rows'  => $txns->take(8)->map(fn ($t) => [$t->created?->format('j M Y') ?? '—', $t->who,
                    $this->money($t->amount, $currency), ucfirst($t->status)])->values()->all(),
            ]],
        ];
    }

    private function paymentMethods(Collection $txns, string $currency): array
    {
        $panel = ['title' => 'Transactions – Payment Methods', 'color' => '#14b8a6',
            'note' => 'How customers paid, from the payment details your provider sends.'];
        if ($txns->isEmpty()) return $this->noPayments($panel);

        $label = fn ($t) => trim(ucfirst((string) ($t->payment_method ?: 'Unknown')) . (!empty($t->meta['card_brand']) ? ' · ' . ucfirst($t->meta['card_brand']) : ''));
        $groups = $txns->groupBy($label)->map(fn ($g) => ['count' => $g->count(), 'amount' => (float) $g->where('status', 'completed')->sum('amount'),
            'ok' => $g->where('status', 'completed')->count()])->sortByDesc('count');
        $total = $txns->count();

        return $panel + [
            'stats' => [
                ['Payments', number_format($total)],
                ['Methods used', number_format($groups->count())],
                ['Most used', $groups->keys()->first()],
                ['Success rate', round($txns->where('status', 'completed')->count() / $total * 100) . '%'],
            ],
            'bars' => [[
                'title' => 'Share of payments',
                'rows'  => $groups->map(fn ($g, $name) => [$name, round($g['count'] / $total * 100) . '%', (int) round($g['count'] / $total * 100)])->values()->all(),
            ]],
            'tables' => [[
                'title' => 'By method',
                'cols'  => ['Method', 'Payments', 'Successful', 'Revenue'],
                'rows'  => $groups->map(fn ($g, $name) => [$name, number_format($g['count']), number_format($g['ok']), $this->money($g['amount'], $currency)])->values()->all(),
            ]],
        ];
    }

    private function paymentStatus(Collection $txns, string $currency): array
    {
        $panel = ['title' => 'Transactions – Payment Status', 'color' => '#14b8a6',
            'note' => 'Successful, failed and pending payments. The provider\'s decline reason isn\'t synced yet, so failures are counted rather than broken down by code.'];
        if ($txns->isEmpty()) return $this->noPayments($panel);

        $count = fn ($s) => $txns->where('status', $s)->count();
        $failed = $txns->where('status', 'failed');

        return $panel + [
            'stats' => [
                ['Successful', number_format($count('completed'))],
                ['Failed', number_format($count('failed'))],
                ['Pending', number_format($count('pending'))],
                ['Failure rate', round($count('failed') / $txns->count() * 100) . '%'],
            ],
            'tables' => [[
                'title' => 'Latest failed payments',
                'cols'  => ['Date', 'Customer', 'Amount'],
                'rows'  => $failed->take(8)->map(fn ($t) => [$t->created?->format('j M Y') ?? '—', $t->who, $this->money($t->amount, $currency)])->values()->all(),
                'empty' => 'No failed payments.',
            ]],
        ];
    }

    private function refunds(Collection $txns, string $currency): array
    {
        $panel = ['title' => 'Transactions – Refunds', 'color' => '#14b8a6',
            'note' => 'Payments your provider reports as refunded.'];
        if ($txns->isEmpty()) return $this->noPayments($panel);

        $refunded = $txns->where('status', 'refunded');
        $paid = $txns->whereIn('status', ['completed', 'refunded']);

        return $panel + [
            'stats' => [
                ['Refunds', number_format($refunded->count())],
                ['Refunded amount', $this->money($refunded->sum('amount'), $currency)],
                ['Refund rate', $paid->isEmpty() ? '—' : round($refunded->count() / $paid->count() * 100) . '%'],
                ['Kept revenue', $this->money($txns->where('status', 'completed')->sum('amount'), $currency)],
            ],
            'tables' => [[
                'title' => 'Latest refunds',
                'cols'  => ['Date', 'Customer', 'Amount'],
                'rows'  => $refunded->take(8)->map(fn ($t) => [$t->created?->format('j M Y') ?? '—', $t->who, $this->money($t->amount, $currency)])->values()->all(),
                'empty' => 'No refunds.',
            ]],
        ];
    }

    private function repeatPurchase(Collection $customers, string $currency): array
    {
        $panel = ['title' => 'Transactions – Repeat Purchase', 'color' => '#14b8a6',
            'note' => 'Customers synced from your payment provider and how many have paid more than once.'];
        if ($customers->isEmpty()) {
            return $panel + ['empty' => 'No customers synced yet. Connect Stripe under Transactions and run a sync.'];
        }

        $buyers = $customers->where('orders_count', '>', 0);
        $repeat = $customers->where('orders_count', '>', 1);

        return $panel + [
            'stats' => [
                ['Customers', number_format($customers->count())],
                ['Have paid', number_format($buyers->count())],
                ['Paid more than once', number_format($repeat->count())],
                ['Repeat rate', $buyers->isEmpty() ? '—' : round($repeat->count() / $buyers->count() * 100) . '%'],
            ],
            'tables' => [[
                'title' => 'Top customers by lifetime value',
                'cols'  => ['Customer', 'Orders', 'Lifetime value'],
                'rows'  => $buyers->sortByDesc('lifetime_value')->take(8)->map(fn ($c) => [
                    $c->name ?: ($c->email ?: 'Customer'), number_format($c->orders_count), $this->money($c->lifetime_value, $currency),
                ])->values()->all(),
                'empty' => 'No customer has paid yet.',
            ]],
        ];
    }

    private function money($amount, string $currency = 'USD'): string
    {
        $symbols = ['USD' => '$', 'AUD' => 'A$', 'NZD' => 'NZ$', 'CAD' => 'C$', 'GBP' => '£', 'EUR' => '€', 'LKR' => 'Rs '];
        $amount = (float) $amount;
        $text = abs($amount) >= 1000 ? number_format($amount) : number_format($amount, 2);

        return ($symbols[strtoupper($currency)] ?? strtoupper($currency) . ' ') . $text;
    }
}
