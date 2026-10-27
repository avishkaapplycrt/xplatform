<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClient;
use Illuminate\Database\Eloquent\Model;

class InstagramAccountInsight extends Model
{
    // Belongs to the client who owns the Instagram connection it was synced from (client_id).
    use BelongsToClient;

    /** Owner of the Instagram connection it was synced from. */
    public function clientIdFromParent(): ?int
    {
        $id = SocialIntegration::withoutGlobalScopes()->whereKey($this->social_integration_id)->value('client_id');

        return $id === null ? null : (int) $id;
    }

    protected $fillable = [
        'social_integration_id', 'date', 'followers_count', 'reach', 'profile_views',
    ];

    protected $casts = ['date' => 'date'];

    public function socialIntegration()
    {
        return $this->belongsTo(SocialIntegration::class);
    }
}
