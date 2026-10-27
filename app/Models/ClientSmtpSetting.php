<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientSmtpSetting extends Model
{
    protected $fillable = [
        'client_id',
        'host',
        'port',
        'encryption',
        'username',
        'password',
        'from_name',
        'verified_at',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'password'    => 'encrypted',
        'port'        => 'integer',
        'verified_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /** Mailer config for Mail::build(). */
    public function mailerConfig(): array
    {
        return [
            'transport'  => 'smtp',
            'host'       => $this->host,
            'port'       => $this->port,
            'scheme'     => $this->encryption === 'ssl' ? 'smtps' : 'smtp',
            'encryption' => $this->encryption === 'none' ? null : $this->encryption,
            'username'   => $this->username,
            'password'   => $this->password,
            'timeout'    => 20,
        ];
    }
}
