<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A visitor who gave their details in the homepage Ask Mira "Before we
 * analyze your site" form. One row per browser (visitor_id comes from a
 * long-lived cookie); a repeat visitor's row is updated with their latest
 * details. Written by PublicChatController::recordVisitor().
 */
class AskMiraVisitor extends Model
{
    protected $table = 'askmira_visitors';

    // The table has a `date` column instead of created_at/updated_at.
    public $timestamps = false;

    protected $fillable = [
        'visitor_id',
        'name',
        'email',
        'website',
        'date',
        'device',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];
}
