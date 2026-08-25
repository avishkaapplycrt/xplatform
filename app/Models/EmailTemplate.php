<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'subject',
        'body',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function categoryModel()
    {
        return $this->belongsTo(EmailTemplateCategory::class, 'category', 'slug');
    }

    // Get category display name
    public function getCategoryNameAttribute(): string
    {
        $category = EmailTemplateCategory::where('slug', $this->category)
            ->where('client_id', $this->client_id)
            ->first();
        return $category?->name ?? ucfirst($this->category);
    }

    // Get category colors
    public function getCategoryColorsAttribute(): array
    {
        $category = EmailTemplateCategory::where('slug', $this->category)
            ->where('client_id', $this->client_id)
            ->first();
        return [
            'color' => $category?->color ?? '#374151',
            'bg' => $category?->bg_color ?? '#f3f4f6',
        ];
    }
}