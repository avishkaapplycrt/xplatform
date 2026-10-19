<?php

namespace App\Models\MiraPremium;

use Illuminate\Database\Eloquent\Model;

/** One prompt's AI answer in a visibility run, parsed for mentions and citations. */
class MiraVisibilityAnswer extends Model
{
    protected $table = 'mira_visibility_answers';

    protected $fillable = [
        'run_id', 'prompt_id', 'prompt', 'topic', 'answer', 'brand_mentioned', 'brand_cited', 'brand_position',
        'brands_mentioned', 'cited_urls', 'cited_domains', 'error', 'sentiment', 'brands_found', 'cited_position',
    ];

    protected $casts = [
        'brand_mentioned'  => 'boolean',
        'brand_cited'      => 'boolean',
        'brands_mentioned' => 'array',
        'cited_urls'       => 'array',
        'cited_domains'    => 'array',
        'brands_found'     => 'array',
    ];
}
