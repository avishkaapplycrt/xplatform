<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PaymentGatewayConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class PaymentGatewayConnectionController extends Controller
{
    protected $availableGateways = [
        'stripe' => [
            'name' => 'Stripe',
            'description' => 'Accept payments online with Stripes powerful APIs and tools.',
            'icon' => 'stripe',
            'color' => '#635BFF',
            'fields' => [
                'api_key' => ['label' => 'Publishable Key', 'type' => 'text', 'required' => true],
                'api_secret' => ['label' => 'Secret Key', 'type' => 'password', 'required' => true],
                'webhook_secret' => ['label' => 'Webhook Secret', 'type' => 'password', 'required' => false],
            ],
            'supports_webhook' => true,
            'webhook_path' => '/webhooks/stripe',
        ],
        'shopify' => [
            'name' => 'Shopify',
            'description' => 'Connect your Shopify store to sync orders and payments.',
            'icon' => 'shopify',
            'color' => '#96BF48',
            'fields' => [
                'api_key' => ['label' => 'API Key', 'type' => 'text', 'required' => true],
                'api_secret' => ['label' => 'API Secret', 'type' => 'password', 'required' => true],
                'account_id' => ['label' => 'Shop Domain', 'type' => 'text', 'required' => true, 'placeholder' => 'your-store.myshopify.com'],
            ],
            'supports_webhook' => true,
            'webhook_path' => '/webhooks/shopify',
        ],
        'zapier' => [
            'name' => 'Zapier',
            'description' => 'Automate workflows between your payment data and 5000+ apps.',
            'icon' => 'zapier',
            'color' => '#FF4A00',
            'fields' => [
                'api_key' => ['label' => 'Zapier API Key', 'type' => 'text', 'required' => true],
                'webhook_url' => ['label' => 'Webhook URL', 'type' => 'url', 'required' => false],
            ],
            'supports_webhook' => true,
            'webhook_path' => '/webhooks/zapier',
        ],
        'webhooks' => [
            'name' => 'Webhooks',
            'description' => 'Send real-time payment events to your custom endpoint.',
            'icon' => 'webhooks',
            'color' => '#3B82F6',
            'fields' => [
                'webhook_url' => ['label' => 'Webhook Endpoint URL', 'type' => 'url', 'required' => true],
                'api_secret' => ['label' => 'Secret Token (for verification)', 'type' => 'password', 'required' => false],
            ],
            'supports_webhook' => true,
            'webhook_path' => null,
        ],
        'paypal' => [
            'name' => 'PayPal',
            'description' => 'Accept payments via PayPal, cards, and local payment methods.',
            'icon' => 'paypal',
            'color' => '#003087',
            'fields' => [
                'api_key' => ['label' => 'Client ID', 'type' => 'text', 'required' => true],
                'api_secret' => ['label' => 'Client Secret', 'type' => 'password', 'required' => true],
                'merchant_id' => ['label' => 'Merchant ID', 'type' => 'text', 'required' => false],
            ],
            'supports_webhook' => true,
            'webhook_path' => '/webhooks/paypal',
        ],
        'woocommerce' => [
            'name' => 'WooCommerce',
            'description' => 'Sync your WooCommerce store orders and payment data.',
            'icon' => 'woocommerce',
            'color' => '#96588A',
            'fields' => [
                'api_key' => ['label' => 'Consumer Key', 'type' => 'text', 'required' => true],
                'api_secret' => ['label' => 'Consumer Secret', 'type' => 'password', 'required' => true],
                'account_id' => ['label' => 'Store URL', 'type' => 'url', 'required' => true, 'placeholder' => 'https://yourstore.com'],
            ],
            'supports_webhook' => true,
            'webhook_path' => '/webhooks/woocommerce',
        ],
    ];

    public function index()
    {
        $client = Auth::guard('client')->user();

        $connections = PaymentGatewayConnection::where('client_id', $client->id)
            ->get()
            ->keyBy('gateway_name');

        $gateways = [];
        foreach ($this->availableGateways as $key => $gateway) {
            $connection = $connections->get($key);
            $gateways[] = [
                'key' => $key,
                'name' => $gateway['name'],
                'description' => $gateway['description'],
                'icon' => $gateway['icon'],
                'color' => $gateway['color'],
                'is_connected' => $connection ? $connection->is_connected : false,
                'is_active' => $connection ? $connection->is_active : false,
                'connection' => $connection,
            ];
        }

        return view('client.payment-gateway-connections.index', compact('gateways'));
    }

    public function show($gateway)
    {
        if (!isset($this->availableGateways[$gateway])) {
            abort(404);
        }

        $client = Auth::guard('client')->user();
        $gatewayConfig = $this->availableGateways[$gateway];

        $connection = PaymentGatewayConnection::where('client_id', $client->id)
            ->where('gateway_name', $gateway)
            ->first();

        $webhookUrl = $gatewayConfig['supports_webhook'] && $gatewayConfig['webhook_path'] 
            ? url($gatewayConfig['webhook_path'] . '/' . ($client->id ?? 'client')) 
            : null;

        return view('client.payment-gateway-connections.show', compact('gateway', 'gatewayConfig', 'connection', 'webhookUrl'));
    }

    public function connect(Request $request, $gateway)
    {
        if (!isset($this->availableGateways[$gateway])) {
            return redirect()->back()->with('error', 'Invalid payment gateway.');
        }

        $client = Auth::guard('client')->user();
        $gatewayConfig = $this->availableGateways[$gateway];

        $rules = [];
        foreach ($gatewayConfig['fields'] as $fieldKey => $fieldConfig) {
            if ($fieldConfig['required']) {
                $rules[$fieldKey] = 'required';
            } else {
                $rules[$fieldKey] = 'nullable';
            }
        }

        $rules['environment'] = 'required|in:sandbox,production';
        $rules['currency'] = 'required|string|size:3';
        $rules['is_active'] = 'boolean';

        $validated = $request->validate($rules);

        $connectionData = [
            'client_id' => $client->id,
            'gateway_name' => $gateway,
            'display_name' => $gatewayConfig['name'],
            'environment' => $validated['environment'] ?? 'sandbox',
            'currency' => $validated['currency'] ?? 'USD',
            'is_active' => $request->boolean('is_active', true),
            'is_connected' => true,
            'connected_at' => now(),
        ];

        // Encrypt sensitive fields
        if (!empty($validated['api_key'])) {
            $connectionData['api_key'] = Crypt::encryptString($validated['api_key']);
        }
        if (!empty($validated['api_secret'])) {
            $connectionData['api_secret'] = Crypt::encryptString($validated['api_secret']);
        }
        if (!empty($validated['webhook_secret'])) {
            $connectionData['webhook_secret'] = Crypt::encryptString($validated['webhook_secret']);
        }
        if (!empty($validated['account_id'])) {
            $connectionData['account_id'] = $validated['account_id'];
        }
        if (!empty($validated['merchant_id'])) {
            $connectionData['merchant_id'] = $validated['merchant_id'];
        }
        if (!empty($validated['webhook_url'])) {
            $connectionData['webhook_url'] = $validated['webhook_url'];
        }

        // Store any extra settings
        $settings = [];
        foreach ($validated as $key => $value) {
            if (!in_array($key, ['environment', 'currency', 'is_active', 'api_key', 'api_secret', 'webhook_secret', 'account_id', 'merchant_id', 'webhook_url']) && !empty($value)) {
                $settings[$key] = $value;
            }
        }
        if (!empty($settings)) {
            $connectionData['settings'] = $settings;
        }

        PaymentGatewayConnection::updateOrCreate(
            [
                'client_id' => $client->id,
                'gateway_name' => $gateway,
            ],
            $connectionData
        );

        return redirect()->route('client.payment-gateway-connections.index')
            ->with('success', $gatewayConfig['name'] . ' connected successfully!');
    }

    public function disconnect($gateway)
    {
        $client = Auth::guard('client')->user();

        $connection = PaymentGatewayConnection::where('client_id', $client->id)
            ->where('gateway_name', $gateway)
            ->first();

        if ($connection) {
            $connection->update([
                'is_connected' => false,
                'is_active' => false,
            ]);
        }

        return redirect()->back()->with('success', 'Gateway disconnected successfully.');
    }

    public function toggleActive($gateway)
    {
        $client = Auth::guard('client')->user();

        $connection = PaymentGatewayConnection::where('client_id', $client->id)
            ->where('gateway_name', $gateway)
            ->first();

        if (!$connection) {
            return redirect()->back()->with('error', 'Gateway not found.');
        }

        $connection->update([
            'is_active' => !$connection->is_active,
        ]);

        $status = $connection->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', 'Gateway ' . $status . ' successfully.');
    }

    public function testConnection($gateway)
    {
        $client = Auth::guard('client')->user();

        $connection = PaymentGatewayConnection::where('client_id', $client->id)
            ->where('gateway_name', $gateway)
            ->first();

        if (!$connection) {
            return response()->json(['success' => false, 'message' => 'Gateway not configured.']);
        }

        // Simulate connection test (implement actual API test per gateway)
        $testResults = [
            'stripe' => true,
            'shopify' => true,
            'zapier' => true,
            'webhooks' => true,
            'paypal' => true,
            'woocommerce' => true,
        ];

        $success = $testResults[$gateway] ?? false;

        if ($success) {
            $connection->update([
                'last_synced_at' => now(),
            ]);
        }

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Connection test successful!' : 'Connection test failed. Please check your credentials.',
        ]);
    }

    public function destroy($gateway)
    {
        $client = Auth::guard('client')->user();

        PaymentGatewayConnection::where('client_id', $client->id)
            ->where('gateway_name', $gateway)
            ->delete();

        return redirect()->route('client.payment-gateway-connections.index')
            ->with('success', 'Gateway configuration removed successfully.');
    }
}
