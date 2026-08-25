<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CustomReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'description',
        'metrics',
        'dimensions',
        'filters',
        'date_range',
        'chart_type',
        'schedule',
        'schedule_config',
        'status',
        'share_token',
        'share_expires_at',
    ];

    protected $casts = [
        'metrics'         => 'array',
        'dimensions'      => 'array',
        'filters'         => 'array',
        'schedule_config' => 'array',
        'share_expires_at'=> 'datetime',
    ];

    /**
     * Boot method to auto-generate share token.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($report) {
            if (empty($report->share_token)) {
                $report->share_token = Str::random(32);
            }
        });
    }

    /**
     * Relationship: Custom report belongs to a client.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Generate a new share token with optional expiration.
     */
    public function generateShareToken(array $config = []): string
    {
        $this->update([
            'share_token'      => Str::random(32),
            'share_expires_at' => $config['expires'] ?? null,
        ]);

        return $this->share_token;
    }

    /**
     * Check if the share link is still valid.
     */
    public function isShareValid(): bool
    {
        if (empty($this->share_token)) {
            return false;
        }

        if ($this->share_expires_at && $this->share_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Scope: Active reports only.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Scheduled reports only.
     */
    public function scopeScheduled($query)
    {
        return $query->where('schedule', '!=', 'none');
    }
}
