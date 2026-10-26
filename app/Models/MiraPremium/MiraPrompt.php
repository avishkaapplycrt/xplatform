<?php

namespace App\Models\MiraPremium;

use Illuminate\Database\Eloquent\Model;

/** A question in a project's AI visibility prompt set. */
class MiraPrompt extends Model
{
    protected $table = 'mira_prompts';

    protected $fillable = ['project_id', 'prompt', 'topic', 'source', 'is_active', 'keyword', 'search_volume', 'volume_checked_at', 'tags'];

    protected $casts = ['is_active' => 'boolean', 'tags' => 'array', 'search_volume' => 'integer', 'volume_checked_at' => 'datetime'];
}
