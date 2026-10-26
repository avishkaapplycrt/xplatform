<?php

namespace App\Models\MiraPremium;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A keyword a Mira Premium project tracks in Google, checked once a day (RankTracker). */
class MiraTrackedKeyword extends Model
{
    protected $fillable = ['project_id', 'keyword', 'volume', 'difficulty', 'intent', 'last_checked_at'];

    protected $casts = ['last_checked_at' => 'datetime'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(MiraProject::class, 'project_id');
    }

    public function checks(): HasMany
    {
        return $this->hasMany(MiraRankCheck::class, 'tracked_keyword_id');
    }
}
