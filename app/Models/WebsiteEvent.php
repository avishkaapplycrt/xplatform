<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteEvent extends Model
{
    use HasFactory;

    protected $table = 'website_events';

    public $timestamps = false;

    protected $fillable = [
        'connection_id',
        'client_id',
        'tenant_id',
        'event_type',
        'data',
        'page_url',
        'user_agent',
        'ip_address',
        'screen_width',
        'screen_height',
        'created_at',
    ];

    protected $casts = [
        'data'       => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the connection that generated this event.
     */
    public function connection()
    {
        return $this->belongsTo(WebsiteConnection::class, 'connection_id');
    }
}
