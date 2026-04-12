<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Industry extends Model
{
    protected $fillable = [
        'name',
        'signals',
        'predictions',
        'is_custom',
        'client_id',
    ];

    protected $casts = [
        'signals'     => 'array',
        'predictions' => 'array',
        'is_custom'   => 'boolean',
    ];

    public function predictionModels()
    {
        return $this->belongsToMany(Prediction::class, 'industry_prediction');
    }
}
