<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataSource extends Model
{
    protected $fillable = [
        'analysis_layer_id',
        'name',
        'subtitle',
    ];

    public function analysisLayer()
    {
        return $this->belongsTo(AnalysisLayer::class);
    }
}