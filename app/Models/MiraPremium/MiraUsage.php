<?php

namespace App\Models\MiraPremium;

use Illuminate\Database\Eloquent\Model;

/** Metered usage counter: one row per subject / metric / period. */
class MiraUsage extends Model
{
    protected $table = 'mira_usage';

    protected $fillable = ['subject', 'metric', 'period', 'used'];

    protected $casts = ['used' => 'integer'];
}
