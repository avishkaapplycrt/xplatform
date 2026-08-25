<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatBotUser extends Model
{
    protected $fillable = ['name', 'email', 'website_url'];
}
