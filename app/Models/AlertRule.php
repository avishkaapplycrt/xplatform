<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlertRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'name', 'description', 'metric', 'threshold_value',
        'comparison_operator', 'notification_channels', 'is_active',
        'escalation_minutes', 'assigned_team_member'
    ];

    protected $casts = [
        'notification_channels' => 'array',
        'is_active' => 'boolean',
        'escalation_minutes' => 'integer'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function assignedTeamMember()
    {
        return $this->belongsTo(User::class, 'assigned_team_member');
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class, 'rule_id');
    }

    public function getNotificationChannelsListAttribute()
    {
        return implode(', ', $this->notification_channels);
    }
}