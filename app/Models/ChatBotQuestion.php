<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatBotQuestion extends Model
{
    protected $fillable = ['industry_id', 'question'];

    public function industry(): BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }
}
