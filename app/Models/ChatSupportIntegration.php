<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatSupportIntegration extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'chat_support_integrations';

    protected $fillable = [
        'client_id',
        'provider',
        'connection_name',
        'status',
        'api_key',
        'api_secret',
        'access_token',
        'auth_token',
        'account_sid',
        'phone_number',
        'webhook_url',
        'workspace_id',
        'workspace_name',
        'channel_id',
        'subdomain',
        'app_id',
        'license_id',
        'settings',
        'sync_config',
        'metrics',
        'last_sync_at',
        'sync_count',
        'last_error',
        'created_by',
    ];

    protected $casts = [
        'settings'     => 'array',
        'sync_config'  => 'array',
        'metrics'      => 'array',
        'last_sync_at' => 'datetime',
    ];

    protected $hidden = [
        'api_key',
        'api_secret',
        'access_token',
        'auth_token',
    ];

    // Provider metadata
    public static function providers(): array
    {
        return [
            'whatsapp' => [
                'name'        => 'WhatsApp Business',
                'icon'        => 'whatsapp',
                'color'       => '#25D366',
                'bg_color'    => '#E8F5E9',
                'description' => 'Connect WhatsApp Business API to sync messages, conversations, and customer interactions.',
                'features'    => ['Message Sync', 'Conversation History', 'Media Sharing', 'Auto Replies', 'Broadcast Messages', 'Template Messages'],
                'auth_type'   => 'api_key',
                'docs_url'    => 'https://developers.facebook.com/docs/whatsapp',
                'fields'      => ['phone_number', 'api_key', 'webhook_url'],
                'scopes'      => [],
            ],
            'slack' => [
                'name'        => 'Slack',
                'icon'        => 'slack',
                'color'       => '#4A154B',
                'bg_color'    => '#F3E5F5',
                'description' => 'Connect your Slack workspace to sync channels, messages, and team communications.',
                'features'    => ['Channel Sync', 'Message History', 'Thread Tracking', 'File Sharing', 'Bot Integration', 'Slash Commands'],
                'auth_type'   => 'oauth2',
                'docs_url'    => 'https://api.slack.com/',
                'fields'      => ['workspace_id', 'channel_id', 'access_token'],
                'scopes'      => ['channels:read', 'channels:history', 'chat:write', 'users:read', 'files:read', 'groups:read', 'im:read', 'mpim:read'],
            ],
            'twilio' => [
                'name'        => 'Twilio',
                'icon'        => 'twilio',
                'color'       => '#F22F46',
                'bg_color'    => '#FFEBEE',
                'description' => 'Connect Twilio for SMS, voice calls, and programmable messaging integration.',
                'features'    => ['SMS Sync', 'Voice Calls', 'WhatsApp via Twilio', 'Programmable Chat', 'Call Analytics', 'IVR Integration'],
                'auth_type'   => 'api_key',
                'docs_url'    => 'https://www.twilio.com/docs',
                'fields'      => ['account_sid', 'auth_token', 'phone_number'],
                'scopes'      => [],
            ],
            'zendesk' => [
                'name'        => 'Zendesk',
                'icon'        => 'zendesk',
                'color'       => '#03363D',
                'bg_color'    => '#E0F2F1',
                'description' => 'Connect Zendesk to sync tickets, conversations, and customer support data.',
                'features'    => ['Ticket Sync', 'Chat History', 'Agent Activity', 'CSAT Scores', 'Knowledge Base', 'Macros'],
                'auth_type'   => 'oauth2',
                'docs_url'    => 'https://developer.zendesk.com/',
                'fields'      => ['subdomain', 'api_key', 'access_token'],
                'scopes'      => ['read', 'write', 'tickets', 'users'],
            ],
            'tawk' => [
                'name'        => 'Tawk.to',
                'icon'        => 'tawk',
                'color'       => '#03A84E',
                'bg_color'    => '#E8F5E9',
                'description' => 'Connect Tawk.to live chat to sync conversations, visitor data, and agent performance.',
                'features'    => ['Chat History', 'Visitor Tracking', 'Agent Reports', 'Offline Messages', 'Triggers', 'Canned Responses'],
                'auth_type'   => 'api_key',
                'docs_url'    => 'https://developer.tawk.to/',
                'fields'      => ['app_id', 'api_key', 'channel_id'],
                'scopes'      => [],
            ],
            'intercom' => [
                'name'        => 'Intercom',
                'icon'        => 'intercom',
                'color'       => '#1F8DED',
                'bg_color'    => '#E3F2FD',
                'description' => 'Connect Intercom to sync conversations, user data, and support metrics.',
                'features'    => ['Conversation Sync', 'User Profiles', 'Article Sync', 'Team Inbox', 'Outbound Messages', 'Product Tours'],
                'auth_type'   => 'oauth2',
                'docs_url'    => 'https://developers.intercom.com/',
                'fields'      => ['app_id', 'access_token', 'webhook_url'],
                'scopes'      => ['read_conversations', 'read_users', 'read_contacts', 'read_admins', 'write_conversations'],
            ],
            'livechat' => [
                'name'        => 'LiveChat',
                'icon'        => 'livechat',
                'color'       => '#FF6B6B',
                'bg_color'    => '#FFEBEE',
                'description' => 'Connect LiveChat to sync chats, agent performance, and customer satisfaction data.',
                'features'    => ['Chat Transcripts', 'Agent Ratings', 'Queue Reports', 'Greetings', 'Canned Responses', 'Post-Chat Surveys'],
                'auth_type'   => 'api_key',
                'docs_url'    => 'https://developers.livechat.com/',
                'fields'      => ['license_id', 'api_key', 'webhook_url'],
                'scopes'      => [],
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

    // Format message count
    public function getFormattedMessagesAttribute(): string
    {
        $messages = $this->metrics['messages_today'] ?? 0;
        if ($messages >= 1000) {
            return number_format($messages / 1000, 1) . 'K';
        }
        return number_format($messages);
    }

    // Format response time
    public function getAvgResponseTimeAttribute(): string
    {
        $seconds = $this->metrics['avg_response_time'] ?? 0;
        if ($seconds < 60) {
            return $seconds . 's';
        }
        return floor($seconds / 60) . 'm ' . ($seconds % 60) . 's';
    }

    public function client()
    {
        return $this->belongsTo(\App\Models\Client::class);
    }
}
