<?php

namespace App\Models\MiraPremium;

use Illuminate\Database\Eloquent\Model;

/** A question in a project's AI visibility prompt set. */
class MiraPrompt extends Model
{
    protected $table = 'mira_prompts';

    protected $fillable = ['project_id', 'prompt', 'topic', 'source', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
