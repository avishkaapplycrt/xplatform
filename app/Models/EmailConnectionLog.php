<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailConnectionLog extends Model
{
    use HasFactory;

    protected $table = 'email_connection_logs';

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

    public function connection()
    {
        return $this->belongsTo(EmailConnection::class, 'connection_id');
    }
}
