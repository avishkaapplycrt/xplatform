<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Industry extends Model
{
    protected $fillable = [
        'name',
        'is_custom',
        'client_id',
    ];

    protected $casts = [
        'is_custom' => 'boolean',
    ];

    public function predictionModels()
    {
        return $this->belongsToMany(Prediction::class, 'industry_prediction');
    }

    public function microSignals()
    {
        return $this->hasMany(MicroSignal::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }
}
