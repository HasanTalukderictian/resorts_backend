<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Partner extends Model
{
    protected $table = 'partners';

    protected $fillable = [
        'title',
        'description',
        'website',
        'image',
        'status',
        'sort_order',
        'contact_email',
        'contact_phone',
        'click_count'
    ];

    protected $casts = [
        'status' => 'string',
        'sort_order' => 'integer',
        'click_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Accessor for image URL
    public function getImageUrlAttribute()
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return Storage::url($this->image);
        }
        return null;
    }

    // Scope for active affiliates
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope for ordered affiliates
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc');
    }

    // Increment click count
    public function incrementClickCount()
    {
        $this->increment('click_count');
    }
}
