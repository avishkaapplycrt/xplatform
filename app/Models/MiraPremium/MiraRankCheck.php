<?php

namespace App\Models\MiraPremium;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One day's Google position for a tracked keyword, plus the competitors' positions. */
class MiraRankCheck extends Model
{
    protected $fillable = ['tracked_keyword_id', 'checked_on', 'position', 'url', 'competitors', 'serp_features'];

    protected $casts = [
        'checked_on'    => 'date',
        'competitors'   => 'array',
        'serp_features' => 'array',
    ];

    public function keyword(): BelongsTo
    {
        return $this->belongsTo(MiraTrackedKeyword::class, 'tracked_keyword_id');
    }
}
