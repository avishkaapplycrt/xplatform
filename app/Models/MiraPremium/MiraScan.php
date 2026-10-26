<?php

namespace App\Models\MiraPremium;

use Illuminate\Database\Eloquent\Model;

/**
 * One site audit or competitor comparison. The row is the stored result of
 * the two-phase protocol: submit creates it (queued), RunMiraScan fills it.
 */
class MiraScan extends Model
{
    protected $table = 'mira_scans';

    protected $fillable = [
        'uuid', 'client_id', 'project_id', 'subject', 'url', 'competitor_url', 'kind', 'mode', 'status',
        'error_code', 'error_message', 'overall', 'grade', 'competitor_overall', 'stage_scores',
        'issue_counts', 'report', 'started_at', 'finished_at',
    ];

    protected $casts = [
        'stage_scores' => 'array',
        'issue_counts' => 'array',
        'started_at'   => 'datetime',
        'finished_at'  => 'datetime',
        'overall'      => 'integer',
        'competitor_overall' => 'integer',
    ];

    public function reportData(): ?array
    {
        return $this->report ? json_decode($this->report, true) : null;
    }

    public function isFinished(): bool
    {
        return in_array($this->status, ['done', 'failed'], true);
    }
}
