<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerSegment extends Model
{
    use HasFactory;

    protected $table = 'customer_segments';

    protected $fillable = [
        'client_id',
        'name',
        'description',
        'rules',
        'customer_count',
        'is_active'
    ];

    protected $casts = [
        'rules' => 'array',
        'customer_count' => 'integer',
        'is_active' => 'boolean'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_segment')
            ->withTimestamps();
    }
}
