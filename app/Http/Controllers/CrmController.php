<?php

namespace App\Http\Controllers;

use App\Jobs\SyncHubSpotContacts;
use App\Jobs\SyncSalesforceRecords;
use App\Models\CrmConnection;
use App\Services\HubSpotService;
use App\Services\SalesforceService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CrmController extends Controller
{
    public function __construct(
        private readonly HubSpotService $hubspot,
        private readonly SalesforceService $salesforce,
    ) {}

    // ── Pages ─────────────────────────────────────────────────────────────────

    public function index()
    {
        $connections = auth('client')->user()
            ->crmConnections()
            ->latest()
            ->get();

        return view('client.crm.index', compact('connections'));
    }

    // ── HubSpot OAuth ─────────────────────────────────────────────────────────

    public function redirectToHubSpot()
    {
        $state = Str::random(40);
        session(['hubspot_oauth_state' => $state]);

        return redirect($this->hubspot->getAuthorizationUrl($state));
    }

    public function handleHubSpotCallback(Request $request)
    {
        if ($request->state !== session('hubspot_oauth_state')) {
            return redirect()->route('client.crm.index')
                ->with('error', 'Invalid OAuth state. Please try again.');
        }

        if ($request->has('error')) {
            return redirect()->route('client.crm.index')
                ->with('error', 'HubSpot authorisation was denied: ' . $request->get('error_description', $request->error));
        }

        try {
            $tokens      = $this->hubspot->exchangeCode($request->code);
            $accountInfo = $this->hubspot->getAccountInfo($tokens['access_token']);
            $client      = auth('client')->user();

            $client->crmConnections()->where('provider', 'hubspot')->delete();

            $connection = $client->crmConnections()->create([
                'provider'         => 'hubspot',
                'account_name'     => $accountInfo['hub_domain'] ?? 'HubSpot Account',
                'hub_domain'       => $accountInfo['hub_domain'] ?? null,
                'hub_id'           => $accountInfo['hub_id']     ?? null,
                'access_token'     => $tokens['access_token'],
                'refresh_token'    => $tokens['refresh_token']   ?? null,
                'token_expires_at' => now()->addSeconds($tokens['expires_in'] ?? 1800),
                'scopes'           => explode(' ', $tokens['scope'] ?? ''),
                'status'           => 'connected',
            ]);

            SyncHubSpotContacts::dispatch($connection->id);

            return redirect()->route('client.crm.index')
                ->with('success', 'HubSpot connected! Contact sync has started in the background.');

        } catch (\Throwable $e) {
            return redirect()->route('client.crm.index')
                ->with('error', 'Failed to connect HubSpot: ' . $e->getMessage());
        }
    }

    // ── Salesforce OAuth ──────────────────────────────────────────────────────

    public function redirectToSalesforce()
    {
        $state = Str::random(40);
        session(['salesforce_oauth_state' => $state]);

        return redirect($this->salesforce->getAuthorizationUrl($state));
    }

    public function handleSalesforceCallback(Request $request)
    {
        if ($request->state !== session('salesforce_oauth_state')) {
            return redirect()->route('client.crm.index')
                ->with('error', 'Invalid OAuth state. Please try again.');
        }

        if ($request->has('error')) {
            return redirect()->route('client.crm.index')
                ->with('error', 'Salesforce authorisation was denied: ' . $request->get('error_description', $request->error));
        }

        try {
            $tokens      = $this->salesforce->exchangeCode($request->code);
            $instanceUrl = $tokens['instance_url'];
            $orgName     = $this->salesforce->getOrganizationName($tokens['access_token'], $instanceUrl);
            $client      = auth('client')->user();

            $client->crmConnections()->where('provider', 'salesforce')->delete();

            $connection = $client->crmConnections()->create([
                'provider'         => 'salesforce',
                'account_name'     => $orgName,
                'instance_url'     => $instanceUrl,
                'hub_domain'       => parse_url($instanceUrl, PHP_URL_HOST),
                'access_token'     => $tokens['access_token'],
                'refresh_token'    => $tokens['refresh_token'] ?? null,
                'token_expires_at' => now()->addSeconds(7200),
                'scopes'           => ['api', 'refresh_token', 'offline_access'],
                'status'           => 'connected',
            ]);

            SyncSalesforceRecords::dispatch($connection->id);

            return redirect()->route('client.crm.index')
                ->with('success', 'Salesforce connected! Record sync has started in the background.');

        } catch (\Throwable $e) {
            return redirect()->route('client.crm.index')
                ->with('error', 'Failed to connect Salesforce: ' . $e->getMessage());
        }
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    public function sync(CrmConnection $connection)
    {
        abort_if($connection->client_id !== auth('client')->id(), 403);

        match ($connection->provider) {
            'hubspot'    => SyncHubSpotContacts::dispatch($connection->id),
            'salesforce' => SyncSalesforceRecords::dispatch($connection->id),
            default      => abort(422, 'Unsupported provider'),
        };

        return back()->with('success', 'Sync started — records will refresh in the background.');
    }

    public function destroy(CrmConnection $connection)
    {
        abort_if($connection->client_id !== auth('client')->id(), 403);

        $label = $connection->providerLabel();
        $connection->delete();

        return back()->with('success', "{$label} disconnected.");
    }
}
