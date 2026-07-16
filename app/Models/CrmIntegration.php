<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmIntegration extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'crm_integrations';

    protected $fillable = [
        'provider',
        'connection_name',
        'status',
        'api_key',
        'api_secret',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'instance_url',
        'portal_id',
        'organization_id',
        'settings',
        'sync_config',
        'last_sync_at',
        'sync_count',
        'last_error',
        'created_by',
    ];

    protected $casts = [
        'settings'     => 'array',
        'sync_config'  => 'array',
        'token_expires_at' => 'datetime',
        'last_sync_at' => 'datetime',
    ];

    protected $hidden = [
        'api_key',
        'api_secret',
        'access_token',
        'refresh_token',
    ];

    // Provider metadata
    public static function providers(): array
    {
        return [
            'salesforce' => [
                'name'        => 'Salesforce',
                'icon'        => 'salesforce',
                'color'       => '#00A1E0',
                'description' => 'Connect your Salesforce CRM to sync leads, contacts, opportunities and custom objects.',
                'features'    => ['Leads Sync', 'Contact Sync', 'Opportunity Tracking', 'Custom Objects', 'Campaign Sync'],
                'auth_type'   => 'oauth2',
                'docs_url'    => 'https://developer.salesforce.com/docs',
            ],
            'hubspot' => [
                'name'        => 'HubSpot',
                'icon'        => 'hubspot',
                'color'       => '#FF7A59',
                'description' => 'Sync contacts, companies, deals and marketing activities with HubSpot.',
                'features'    => ['Contact Sync', 'Company Sync', 'Deal Pipeline', 'Marketing Events', 'Ticket Sync'],
                'auth_type'   => 'api_key',
                'docs_url'    => 'https://developers.hubspot.com/docs/api/overview',
            ],
            'zoho' => [
                'name'        => 'Zoho CRM',
                'icon'        => 'zoho',
                'color'       => '#E42527',
                'description' => 'Integrate Zoho CRM to sync leads, contacts, accounts and sales data.',
                'features'    => ['Lead Sync', 'Contact Sync', 'Account Sync', 'Sales Pipeline', 'Activity Sync'],
                'auth_type'   => 'oauth2',
                'docs_url'    => 'https://www.zoho.com/crm/developer/docs/api/',
            ],
            'pipedrive' => [
                'name'        => 'Pipedrive',
                'icon'        => 'pipedrive',
                'color'       => '#00B2FF',
                'description' => 'Connect Pipedrive to sync deals, contacts, activities and pipeline data.',
                'features'    => ['Deal Sync', 'Contact Sync', 'Activity Sync', 'Pipeline Sync', 'Revenue Forecast'],
                'auth_type'   => 'api_key',
                'docs_url'    => 'https://developers.pipedrive.com/',
            ],
            'monday' => [
                'name'        => 'Monday.com',
                'icon'        => 'monday',
                'color'       => '#FF3D57',
                'description' => 'Sync boards, items, contacts and project data from Monday.com.',
                'features'    => ['Board Sync', 'Item Sync', 'Contact Sync', 'Project Tracking', 'Time Tracking'],
                'auth_type'   => 'oauth2',  // <-- Changed from 'api_key' to 'oauth2'
                'docs_url'    => 'https://developer.monday.com/',
            ],
        ];
    }

    public function getProviderMetaAttribute(): ?array
    {
        return self::providers()[$this->provider] ?? null;
    }

    public function getIsConnectedAttribute(): bool
    {
        return $this->status === 'connected';
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->isPast();
    }

    public function scopeConnected($query)
    {
        return $query->where('status', 'connected');
    }

    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'connected')->whereNull('deleted_at');
    }
}