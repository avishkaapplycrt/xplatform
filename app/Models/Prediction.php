<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prediction extends Model
{
    protected $fillable = ['name'];

    public function industries()
    {
        return $this->belongsToMany(Industry::class, 'industry_prediction');
    }
}