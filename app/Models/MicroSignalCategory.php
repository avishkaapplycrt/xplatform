<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MicroSignalCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'sort_order',
    ];

    public function microSignals(): HasMany
    {
        return $this->hasMany(MicroSignal::class, 'micro_signal_category_id');
    }
}