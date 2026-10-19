<?php

namespace App\Models\MiraPremium;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A subscriber's tracked website in Mira Premium: the site, how its brand is
 * named in AI answers, and the competitors it is measured against.
 */
class MiraProject extends Model
{
    protected $table = 'mira_projects';

    protected $fillable = ['client_id', 'name', 'url', 'domain', 'brand_terms', 'competitors', 'last_scanned_at'];

    protected $casts = [
        'brand_terms'     => 'array',
        'competitors'     => 'array',
        'last_scanned_at' => 'datetime',
    ];

    public function scans(): HasMany
    {
        return $this->hasMany(MiraScan::class, 'project_id');
    }

    public function prompts(): HasMany
    {
        return $this->hasMany(MiraPrompt::class, 'project_id');
    }

    public function visibilityRuns(): HasMany
    {
        return $this->hasMany(MiraVisibilityRun::class, 'project_id');
    }

    /** Brand terms, falling back to the project name. */
    public function brandTerms(): array
    {
        $terms = array_values(array_filter(array_map('trim', (array) $this->brand_terms)));

        return $terms !== [] ? $terms : [$this->name];
    }

    /** @return list<array{name: string, domain: string}> */
    public function competitorList(): array
    {
        return array_values(array_filter((array) $this->competitors, fn ($c) => is_array($c) && trim($c['name'] ?? '') !== ''));
    }
}
