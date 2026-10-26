<?php

namespace App\Models\MiraPremium;

use Illuminate\Database\Eloquent\Model;

/**
 * A client's Google connection (read-only Search Console + Analytics 4).
 * Tokens are encrypted at rest with the app key and never leave the server.
 */
class MiraGoogleConnection extends Model
{
    protected $table = 'mira_google_connections';

    protected $fillable = ['client_id', 'google_sub', 'email', 'access_token', 'refresh_token', 'expires_at', 'scopes', 'status', 'connected_at'];

    protected $hidden = ['access_token', 'refresh_token'];

    protected $casts = [
        'access_token'  => 'encrypted',
        'refresh_token' => 'encrypted',
        'expires_at'    => 'datetime',
        'connected_at'  => 'datetime',
    ];

    public function hasScope(string $scope): bool
    {
        return in_array($scope, explode(' ', (string) $this->scopes), true);
    }
}
