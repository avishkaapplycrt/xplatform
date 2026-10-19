<?php

namespace App\Models\MiraPremium;

use Illuminate\Database\Eloquent\Model;

/** A saved Ask Mira chat turn for a subscriber, per project. */
class MiraMessage extends Model
{
    protected $table = 'mira_messages';

    protected $fillable = ['client_id', 'project_id', 'role', 'content'];
}
