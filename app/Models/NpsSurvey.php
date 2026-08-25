<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NpsSurvey extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'question',
        'send_to',
        'segment_id',
        'scheduled_at',
        'sent_at',
        'sent_count',
        'is_active'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'sent_count' => 'integer',
        'is_active' => 'boolean'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function responses()
    {
        return $this->hasMany(NpsResponse::class, 'survey_id');
    }

    public function segment()
    {
        return $this->belongsTo(CustomerSegment::class, 'segment_id');
    }
}
