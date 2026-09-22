<?php

namespace App\Services;

use App\Models\PaymentGatewayConnection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

/**
 * Pulls real customer and charge data from a client's connected Stripe
 * account into the local `transactions_customers` and `transactions` tables
 * that TransactionAnalyticsController reads from.
 *
 * `transactions_customers` is a dedicated table for payment-gateway
 * customers — deliberately separate from the general `customers` table,
 * which is shared by unrelated features (Customer Success, onboarding,
 * health scores) and was never meant to hold Stripe-specific data.
 *
 * There was previously no sync mechanism at all for Stripe — connecting a
 * gateway only stored API credentials in payment_gateway_connections;
 * nothing ever pulled data in. This is a polling sync, triggered manually
 * via the "Sync Now" button on the connection page, chosen over a webhook
 * so it works immediately on localhost without needing the Stripe CLI or a
 * public tunnel.
 *
 * Customers are synced first so each Charge can be linked to the local
 * transactions_customers.id it actually belongs to (transactions.customer_id),
 * rather than left null.
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

        try {
            // stripe_customer_id => local transactions_customers.id, so
            // charges below can link to the right row instead of leaving
            // customer_id null.
            $customerIdMap = $this->syncCustomers($stripe, $connection->client_id);
            $synced = $this->syncCharges($stripe, $connection->client_id, $customerIdMap);

            foreach (array_unique(array_filter($customerIdMap)) as $localId) {
                $this->recalculateCustomerTotals($localId, $connection->client_id);
            }
        } catch (ApiErrorException $e) {
            return ['success' => false, 'message' => 'Stripe API error: ' . $e->getMessage(), 'synced' => 0];
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

    /**
     * @return array<string,int> stripe_customer_id => local transactions_customers.id
     */
    private function syncCustomers(StripeClient $stripe, int $clientId): array
    {
        $map = [];
        $startingAfter = null;

        do {
            $params = ['limit' => 100];
            if ($startingAfter) {
                $params['starting_after'] = $startingAfter;
            }

            $customers = $stripe->customers->all($params);

            foreach ($customers->data as $customer) {
                $map[$customer->id] = $this->upsertCustomer($customer, $clientId);
            }

            $startingAfter = count($customers->data) ? end($customers->data)->id : null;
        } while ($customers->has_more);

        return $map;
    }

    private function upsertCustomer(\Stripe\Customer $customer, int $clientId): int
    {
        $existingId = DB::table('transactions_customers')
            ->where('client_id', $clientId)
            ->where('gateway_customer_id', $customer->id)
            ->value('id');

        $attributes = [
            'client_id' => $clientId,
            'gateway_customer_id' => $customer->id,
            'gateway' => 'stripe',
            'name' => $customer->name ?: ($customer->email ?: $customer->id),
            'email' => $customer->email,
            'phone' => $customer->phone,
            'updated_at' => now(),
        ];

        if ($existingId) {
            DB::table('transactions_customers')->where('id', $existingId)->update($attributes);
            return $existingId;
        }

        $attributes['lifetime_value'] = 0;
        $attributes['orders_count'] = 0;
        $attributes['gateway_created_at'] = date('Y-m-d H:i:s', $customer->created);
        $attributes['created_at'] = now();

        return DB::table('transactions_customers')->insertGetId($attributes);
    }

    /**
     * @param array<string,int> $customerIdMap stripe_customer_id => local transactions_customers.id
     */
    private function syncCharges(StripeClient $stripe, int $clientId, array $customerIdMap): int
    {
        $synced = 0;
        $startingAfter = null;

        do {
            $params = ['limit' => 100];
            if ($startingAfter) {
                $params['starting_after'] = $startingAfter;
            }

            $charges = $stripe->charges->all($params);

            foreach ($charges->data as $charge) {
                $localCustomerId = $charge->customer ? ($customerIdMap[$charge->customer] ?? null) : null;
                $this->upsertCharge($charge, $clientId, $localCustomerId);
                $synced++;
            }

            $startingAfter = count($charges->data) ? end($charges->data)->id : null;
        } while ($charges->has_more);

        return $synced;
    }

    private function upsertCharge(\Stripe\Charge $charge, int $clientId, ?int $localCustomerId): void
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
                'customer_id' => $localCustomerId,
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

    private function recalculateCustomerTotals(int $customerId, int $clientId): void
    {
        $completed = DB::table('transactions')
            ->where('client_id', $clientId)
            ->where('customer_id', $customerId)
            ->where('status', 'completed');

        DB::table('transactions_customers')->where('id', $customerId)->update([
            'lifetime_value' => (clone $completed)->sum('amount'),
            'orders_count' => (clone $completed)->count(),
        ]);
    }
}
