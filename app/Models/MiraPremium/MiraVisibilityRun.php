<?php

namespace App\Models\MiraPremium;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** One pass of a project's prompt set through the AI visibility engine. */
class MiraVisibilityRun extends Model
{
    protected $table = 'mira_visibility_runs';

    protected $fillable = [
        'uuid', 'project_id', 'status', 'model', 'prompts_count', 'answered_count', 'mentions', 'citations',
        'share_of_voice', 'summary', 'error_message', 'started_at', 'finished_at',
    ];

    protected $casts = [
        'summary'        => 'array',
        'share_of_voice' => 'float',
        'started_at'     => 'datetime',
        'finished_at'    => 'datetime',
    ];

    public function answers(): HasMany
    {
        return $this->hasMany(MiraVisibilityAnswer::class, 'run_id');
    }
}
