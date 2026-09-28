<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentPredefinedPrompt extends Model
{
    protected $table = 'agents_pre_defined_prompts';

    protected $fillable = [
        'agent',
        'step_title',
        'slug',
        'label',
        'sort_order',
        'is_active',
        'is_mock_master',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_mock_master' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeForAgent($query, string $agent)
    {
        return $query->where('agent', $agent);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('step_title')->orderBy('sort_order');
    }

    public function scopeMockMaster($query)
    {
        return $query->where('is_mock_master', true);
    }
}
