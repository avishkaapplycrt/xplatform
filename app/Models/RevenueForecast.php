<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevenueForecast extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'forecast_date',
        'expected_revenue',
        'confidence_level',
        'notes',
        'is_active'
    ];

    protected $casts = [
        'forecast_date' => 'date',
        'expected_revenue' => 'decimal:2',
        'confidence_level' => 'integer',
        'is_active' => 'boolean'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
