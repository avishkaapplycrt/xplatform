<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * For tables with their own client_id column: the connections
 * (crm_integrations, social_integrations), the data synced through them
 * (crm_contacts, crm_deals, instagram_*, slack_*), email logs and more.
 *
 * While a client is signed in, every query on the model only sees that
 * client's rows, and new rows are saved under that client. With nobody
 * signed in (queued jobs, console commands, webhooks, the admin side) the
 * model is not filtered — those callers pick rows by id or use forClient().
 */
trait BelongsToClient
{
    public static function bootBelongsToClient(): void
    {
        static::addGlobalScope('client', function (Builder $query) {
            if ($clientId = static::currentClientId()) {
                $query->where($query->getModel()->qualifyColumn('client_id'), $clientId);
            }
        });

        // New rows get their owner from the connection they belong to (models
        // that define clientIdFromParent()), else from the signed-in client —
        // so rows saved by queued syncs, with nobody signed in, still have one.
        static::creating(function ($model) {
            if (empty($model->client_id) && method_exists($model, 'clientIdFromParent')) {
                $model->client_id = $model->clientIdFromParent();
            }
            if (empty($model->client_id) && ($clientId = static::currentClientId())) {
                $model->client_id = $clientId;
            }
        });
    }

    /** The signed-in client's id, or null when no client is signed in. */
    public static function currentClientId(): ?int
    {
        $id = Auth::guard('client')->id();

        return $id === null ? null : (int) $id;
    }

    /** One client's rows, whoever is signed in. */
    public function scopeForClient(Builder $query, $client): Builder
    {
        $clientId = is_object($client) ? $client->getKey() : $client;

        return $query->withoutGlobalScope('client')
            ->where($query->getModel()->qualifyColumn('client_id'), $clientId);
    }
}
