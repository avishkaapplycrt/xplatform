<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A predefined question for Ask Mira Premium. See the
 * create_askmirap_predefined_prompts_table migration for what each
 * placement and trigger means.
 */
class AskMiraPremiumPrompt extends Model
{
    protected $table = 'askmirap_predefined_prompts';

    protected $fillable = [
        'placement',
        'category',
        'question',
        'trigger',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePlacement($query, string $placement)
    {
        return $query->where('placement', $placement);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
