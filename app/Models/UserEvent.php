<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEvent extends Model
{
    // Signed-in clients only see their own tracked events.
    use BelongsToClient;

    protected $fillable = ['client_id', 'behavioral_profile_id', 'email', 'event_type'];

    protected $casts = ['created_at' => 'datetime'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function behavioralProfile(): BelongsTo
    {
        return $this->belongsTo(BehavioralProfile::class);
    }
}
