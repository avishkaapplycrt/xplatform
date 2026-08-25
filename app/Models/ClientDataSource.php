<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class ClientDataSource extends Model
{
    protected $fillable = [
        'client_id',
        'connection_name',
        'db_type',
        'host',
        'port',
        'database_name',
        'username',
        'password',
        'ssl_ca',
        'connection_options',
        'status',
        'last_error',
        'schema_mapping',
        'custom_queries',
        'last_tested_at',
        'connected_at',
        'last_sync_at',
    ];

    protected $casts = [
        'connection_options' => 'json',
        'schema_mapping' => 'json',
        'custom_queries' => 'json',
        'last_tested_at' => 'datetime',
        'connected_at' => 'datetime',
        'last_sync_at' => 'datetime',
    ];

    // Auto-encrypt sensitive fields
    public function setHostAttribute($value)
    {
        $this->attributes['host'] = Crypt::encryptString($value);
    }

    public function getHostAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function setPortAttribute($value)
    {
        $this->attributes['port'] = Crypt::encryptString((string) $value);
    }

    public function getPortAttribute($value)
    {
        return $value ? (int) Crypt::decryptString($value) : null;
    }

    public function setDatabaseNameAttribute($value)
    {
        $this->attributes['database_name'] = Crypt::encryptString($value);
    }

    public function getDatabaseNameAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function setUsernameAttribute($value)
    {
        $this->attributes['username'] = Crypt::encryptString($value);
    }

    public function getUsernameAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Crypt::encryptString($value);
    }

    public function getPasswordAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(AnalyticsMetric::class, 'data_source_id');
    }

    public function insights(): HasMany
    {
        return $this->hasMany(BusinessInsight::class, 'data_source_id');
    }

    public function testConnection(): bool
    {
        try {
            $connectionName = 'temp_test_' . $this->id . '_' . time();

            config(["database.connections.{$connectionName}" => [
                'driver' => $this->db_type === 'postgresql' ? 'pgsql' : $this->db_type,
                'host' => $this->host,
                'port' => $this->port,
                'database' => $this->database_name,
                'username' => $this->username,
                'password' => $this->password,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'options' => $this->connection_options ?? [],
            ]]);

            DB::connection($connectionName)->getPdo();
            DB::disconnect($connectionName);

            $this->update([
                'status' => 'connected',
                'last_tested_at' => now(),
                'connected_at' => $this->connected_at ?? now(),
                'last_error' => null,
            ]);

            return true;

        } catch (\Exception $e) {
            $this->update([
                'status' => 'failed',
                'last_tested_at' => now(),
                'last_error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function getDynamicConnectionConfig(): array
    {
        return [
            'driver' => $this->db_type === 'postgresql' ? 'pgsql' : $this->db_type,
            'host' => $this->host,
            'port' => $this->port,
            'database' => $this->database_name,
            'username' => $this->username,
            'password' => $this->password,
            'charset' => 'utf8mb4',
            'options' => array_merge(
                [\PDO::ATTR_TIMEOUT => 5],
                $this->connection_options ?? []
            ),
        ];
    }
}