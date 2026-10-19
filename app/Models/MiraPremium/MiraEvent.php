<?php

namespace App\Models\MiraPremium;

use Illuminate\Database\Eloquent\Model;

/** A first-party product analytics event from the Mira Premium page. */
class MiraEvent extends Model
{
    protected $table = 'mira_events';

    public $timestamps = false;

    protected $fillable = ['name', 'subject', 'tier', 'props', 'url', 'referrer', 'created_at'];

    protected $casts = ['props' => 'array', 'created_at' => 'datetime'];
}
