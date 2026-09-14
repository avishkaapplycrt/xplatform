<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmContact extends Model
{
    protected $fillable = [
        'connection_id',
        'provider',
        'external_id',
        'email',
        'first_name',
        'last_name',
        'company',
        'last_activity_at',
        'raw_data',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'raw_data'          => 'array',
    ];

    public function connection()
    {
        return $this->belongsTo(CrmIntegration::class, 'connection_id');
    }
}
