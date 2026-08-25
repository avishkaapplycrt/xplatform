<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConnectionLog extends Model
{
    use HasFactory;

    protected $table = 'connection_logs';

    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'connection_id',
        'event',
        'platform',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the connection this log belongs to.
     */
    public function connection()
    {
        return $this->belongsTo(WebsiteConnection::class, 'connection_id');
    }
}
