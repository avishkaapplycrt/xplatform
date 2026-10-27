<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClient;
use Illuminate\Database\Eloquent\Model;

class CrmContact extends Model
{
    // Belongs to the client who owns the CRM connection it was synced from (client_id).
    use BelongsToClient;

    /** Owner of the CRM connection it was synced from. */
    public function clientIdFromParent(): ?int
    {
        $id = CrmIntegration::withoutGlobalScopes()->whereKey($this->connection_id)->value('client_id');

        return $id === null ? null : (int) $id;
    }

    protected $fillable = [
        'connection_id',
        'provider',
        'external_id',
        'email',
        'first_name',
        'last_name',
        'company',
        'last_activity_at',
        'raw_data',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'raw_data'          => 'array',
    ];

    public function connection()
    {
        return $this->belongsTo(CrmIntegration::class, 'connection_id');
    }
}
