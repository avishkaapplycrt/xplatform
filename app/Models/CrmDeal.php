<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmDeal extends Model
{
    protected $fillable = [
        'connection_id',
        'provider',
        'external_id',
        'name',
        'value',
        'stage',
        'status',
        'close_date',
        'raw_data',
    ];

    protected $casts = [
        'close_date' => 'datetime',
        'raw_data'   => 'array',
    ];

    public function connection()
    {
        return $this->belongsTo(CrmIntegration::class, 'connection_id');
    }
}
