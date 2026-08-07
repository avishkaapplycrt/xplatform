<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NpsResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'survey_id',
        'customer_id',
        'score',
        'feedback',
        'category',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'score' => 'integer'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function survey()
    {
        return $this->belongsTo(NpsSurvey::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
