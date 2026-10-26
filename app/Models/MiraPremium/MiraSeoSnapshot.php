<?php

namespace App\Models\MiraPremium;

use Illuminate\Database\Eloquent\Model;

/** A stored provider response for the Authority / Search panels (SeoDataService). */
class MiraSeoSnapshot extends Model
{
    protected $table = 'mira_seo_snapshots';

    public $timestamps = false;

    protected $fillable = ['project_id', 'kind', 'provider', 'payload', 'fetched_at'];

    protected $casts = ['payload' => 'array', 'fetched_at' => 'datetime'];
}
