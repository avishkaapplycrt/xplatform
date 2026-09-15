<?php

namespace App\Services;

use App\Models\PaymentGatewayConnection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

/**
 * Pulls real charge data from a client's connected Stripe account into the
 * local `transactions` table that TransactionAnalyticsController reads from.
 *
 * There was previously no sync mechanism at all for Stripe — connecting a
 * gateway only stored API credentials in payment_gateway_connections; nothing
 * ever pulled transaction data in (no webhook route was registered despite
 * one being advertised in the UI, and "Test Connection" was hardcoded to
 * always report success without calling Stripe). This is a polling sync,
 * triggered manually via the "Sync Now" button on the connection page,
 * chosen over a webhook so it works immediately on localhost without needing
 * the Stripe CLI or a public tunnel.
 *
 * Charges are used (not PaymentIntents) since a Charge's `refunded`/`status`
 * fields map directly onto transactions.status without a second API call.
 */
class StripeSyncService
{
    /**
     * @return array{success: bool, message: string, synced: int}
     */
    public function sync(PaymentGatewayConnection $connection): array
    {
        if ($connection->gateway_name !== 'stripe') {
            return ['success' => false, 'message' => 'Not a Stripe connection.', 'synced' => 0];
        }

        if (empty($connection->api_secret)) {
            return ['success' => false, 'message' => 'No Stripe secret key configured.', 'synced' => 0];
        }

        try {
            $stripe = new StripeClient(Crypt::decryptString($connection->api_secret));
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Could not decrypt the stored Stripe secret key.', 'synced' => 0];
        }

        $synced = 0;
        $startingAfter = null;

        try {
            do {
                $params = ['limit' => 100];
                if ($startingAfter) {
                    $params['starting_after'] = $startingAfter;
                }

                $charges = $stripe->charges->all($params);

                foreach ($charges->data as $charge) {
                    $this->upsertCharge($charge, $connection->client_id);
                    $synced++;
                }

                $startingAfter = count($charges->data) ? end($charges->data)->id : null;
            } while ($charges->has_more);
        } catch (ApiErrorException $e) {
            return ['success' => false, 'message' => 'Stripe API error: ' . $e->getMessage(), 'synced' => $synced];
        }

        $connection->update(['last_synced_at' => now()]);

        return [
            'success' => true,
            'message' => $synced > 0
                ? "Synced {$synced} transaction(s) from Stripe."
                : 'Connected to Stripe, but no charges were found on this account yet.',
            'synced' => $synced,
        ];
    }

    private function upsertCharge(\Stripe\Charge $charge, int $clientId): void
    {
        $status = match (true) {
            $charge->refunded => 'refunded',
            $charge->status === 'succeeded' => 'completed',
            $charge->status === 'failed' => 'failed',
            default => 'pending',
        };

        DB::table('transactions')->updateOrInsert(
            ['transaction_reference' => $charge->id],
            [
                'client_id' => $clientId,
                'amount' => $charge->amount / 100,
                'quantity' => 1,
                'status' => $status,
                'payment_method' => $charge->payment_method_details?->type,
                'metadata' => json_encode([
                    'currency' => strtoupper($charge->currency),
                    'customer_email' => $charge->billing_details?->email ?? $charge->receipt_email,
                    'description' => $charge->description,
                    'stripe_customer_id' => $charge->customer,
                    'card_brand' => $charge->payment_method_details?->card?->brand,
                ]),
                'created_at' => date('Y-m-d H:i:s', $charge->created),
                'updated_at' => now(),
            ]
        );
    }
}
