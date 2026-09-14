<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One logged exchange from the public marketing chatbot — kept only for
 * visitors who were signed in as a client at the time they asked, so the
 * business can see what its own signed-in customers are asking Mira.
 */
class ChatBot extends Model
{
    protected $table = 'chat_bots';

    protected $fillable = ['question', 'answer', 'user_id'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'user_id');
    }
}
