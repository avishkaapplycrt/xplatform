<?php

namespace App\Jobs;

use App\Exceptions\BrevoRateLimitedException;
use App\Models\BrevoDeliveredRecipient;
use App\Models\EmailConnection;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Exports one Brevo campaign's full recipient list ("all") via Brevo's async
 * export API, keeps only the rows with a Delivered_Date, and stores those
 * emails locally so the "Delivered — Email IDs" view can read instantly
 * instead of re-hitting Brevo (and waiting on its export job) on every click.
 *
 * Brevo has no "delivered" export type — only all/clickers/openers/
 * nonClickers/nonOpeners/hardBounces/softBounces — but the "all" export's
 * CSV includes a Delivered_Date column per recipient, which is what this
 * job actually filters on.
 */
class ExportBrevoCampaignDeliveries implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;
    public int $timeout = 180;

    private const MAX_RATE_LIMIT_RETRIES = 6;

    public function __construct(
        public readonly int $clientId,
        public readonly string $campaignId,
        public readonly int $retryCount = 0,
    ) {}

    public function handle(): void
    {
        // Resolved fresh on every attempt rather than passed in at dispatch
        // time: a batch can take hours to finish across several rate-limit
        // windows, and the client may disconnect/reconnect Brevo in the
        // meantime, which replaces the connection row entirely.
        $connection = EmailConnection::where('client_id', $this->clientId)
            ->where('platform', 'brevo')
            ->where('status', 'active')
            ->first();

        if (!$connection) {
            Log::warning("Brevo export for campaign {$this->campaignId}: no active connection for client {$this->clientId}, skipping.");
            $this->finishTask();
            return;
        }

        try {
            $this->exportAndStore($connection);
        } catch (BrevoRateLimitedException $e) {
            if ($this->retryCount < self::MAX_RATE_LIMIT_RETRIES) {
                // Brevo's export endpoint has its own (much stricter) quota than
                // the rest of the API — wait for the window it told us about and
                // retry, rather than burning through the whole batch's budget on
                // instant 429s. This campaign's task is NOT counted as done yet.
                self::dispatch($this->clientId, $this->campaignId, $this->retryCount + 1)
                    ->delay(now()->addSeconds($e->retryAfterSeconds));
                return;
            }
            Log::warning("Brevo export rate-limit retry cap reached for campaign {$this->campaignId}; giving up.");
        } catch (\Throwable $e) {
            Log::warning("Brevo delivered-recipients export failed for campaign {$this->campaignId}: " . $e->getMessage());
        }

        $this->finishTask();
    }

    /**
     * Progress lives in the cache (per client_id), not a DB table — this job
     * is the only writer of "done", so a plain atomic increment is enough to
     * avoid needing row-level locking.
     */
    private function finishTask(): void
    {
        $done  = Cache::increment("brevo_sync_done_{$this->clientId}");
        $total = (int) Cache::get("brevo_sync_total_{$this->clientId}", 0);

        if ($total > 0 && $done >= $total) {
            Cache::forever("brevo_sync_status_{$this->clientId}", 'completed');
        }
    }

    private function rateLimitRetryAfter(Response $response): int
    {
        $reset = $response->header('x-sib-ratelimit-reset');
        return $reset !== null && $reset !== '' ? max(30, (int) $reset) : 300;
    }

    private function exportAndStore(EmailConnection $connection): void
    {
        $apiKey = decrypt($connection->api_key);

        $start = Http::withHeaders(['api-key' => $apiKey, 'Content-Type' => 'application/json'])
            ->timeout(20)
            ->post("https://api.brevo.com/v3/emailCampaigns/{$this->campaignId}/exportRecipients", [
                'recipientsType' => 'all',
            ]);

        if ($start->status() === 429) {
            throw new BrevoRateLimitedException($this->rateLimitRetryAfter($start));
        }

        if (!$start->successful()) {
            throw new \RuntimeException('Export request failed: ' . $start->body());
        }

        $processId = $start->json('processId');
        if (!$processId) {
            throw new \RuntimeException('Brevo did not return a processId.');
        }

        $exportUrl = null;

        for ($attempt = 0; $attempt < 20; $attempt++) {
            sleep(5);

            $poll = Http::withHeaders(['api-key' => $apiKey])
                ->timeout(15)
                ->get("https://api.brevo.com/v3/processes/{$processId}");

            if ($poll->status() === 429) {
                throw new BrevoRateLimitedException($this->rateLimitRetryAfter($poll));
            }

            if (!$poll->successful()) {
                continue;
            }

            $data = $poll->json();

            if (!empty($data['export_url'])) {
                $exportUrl = $data['export_url'];
                break;
            }

            $status = strtolower((string) ($data['status'] ?? ''));
            if (str_contains($status, 'fail') || str_contains($status, 'error')) {
                throw new \RuntimeException("Brevo export process failed for campaign {$this->campaignId} (status: {$status}).");
            }
        }

        if (!$exportUrl) {
            throw new \RuntimeException("Timed out waiting for Brevo export of campaign {$this->campaignId}.");
        }

        $csv = Http::withHeaders(['api-key' => $apiKey])->timeout(30)->get($exportUrl);

        if ($csv->status() === 429) {
            throw new BrevoRateLimitedException($this->rateLimitRetryAfter($csv));
        }

        if (!$csv->successful()) {
            throw new \RuntimeException("Could not download export CSV for campaign {$this->campaignId}.");
        }

        $this->storeDeliveredFromCsv($csv->body());
    }

    private function storeDeliveredFromCsv(string $csv): void
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($csv));
        if (empty($lines)) {
            return;
        }

        $header = array_map(fn ($h) => strtolower(trim($h)), str_getcsv(array_shift($lines), ';'));
        $find = fn (string $name) => array_search($name, $header);

        $emailCol     = $find('email_id');
        $deliveredCol = $find('delivered_date');
        $openedCol    = $find('open_date');
        $clickedCol   = $find('clicked_links_count');
        $unsubCol     = $find('unsubscribe_date');

        if ($emailCol === false || $deliveredCol === false) {
            throw new \RuntimeException("Unexpected export CSV format for campaign {$this->campaignId}.");
        }

        // Returns a DB-ready datetime string (or null) — upsert() bypasses Eloquent
        // casts, so dates must already be plain strings, not Carbon instances.
        $parseDate = function (string $value) {
            $value = trim($value);
            if ($value === '') {
                return null;
            }
            try {
                return \Carbon\Carbon::createFromFormat('d-m-Y H:i:s', $value)->toDateTimeString();
            } catch (\Throwable) {
                return null;
            }
        };

        $rows = [];
        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            $cols      = str_getcsv($line, ';');
            $email     = strtolower(trim($cols[$emailCol] ?? ''));
            $delivered = trim($cols[$deliveredCol] ?? '');

            if (!$email || !str_contains($email, '@') || $delivered === '') {
                continue;
            }

            $rows[$email] = [
                'client_id'       => $this->clientId,
                'campaign_id'     => $this->campaignId,
                'email'           => $email,
                'delivered_at'    => $parseDate($delivered),
                'opened_at'       => $openedCol !== false ? $parseDate($cols[$openedCol] ?? '') : null,
                'clicked'         => $clickedCol !== false && (float) ($cols[$clickedCol] ?? 0) > 0,
                'unsubscribed_at' => $unsubCol !== false ? $parseDate($cols[$unsubCol] ?? '') : null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }

        foreach (array_chunk(array_values($rows), 500) as $chunk) {
            BrevoDeliveredRecipient::upsert(
                $chunk,
                ['client_id', 'campaign_id', 'email'],
                ['delivered_at', 'opened_at', 'clicked', 'unsubscribed_at', 'updated_at']
            );
        }
    }
}
