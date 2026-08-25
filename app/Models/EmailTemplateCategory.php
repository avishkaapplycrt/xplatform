<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplateCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'slug',
        'color',
        'bg_color',
        'sort_order',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function templates()
    {
        return $this->hasMany(EmailTemplate::class, 'category', 'slug');
    }
}