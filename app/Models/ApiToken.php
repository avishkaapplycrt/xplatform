<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiToken extends Model
{
    protected $fillable = ['name', 'token', 'abilities', 'last_used_at'];
    
    protected $casts = [
        'abilities' => 'array',
        'last_used_at' => 'datetime',
    ];
}