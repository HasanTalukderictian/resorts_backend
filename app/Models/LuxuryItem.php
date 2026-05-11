<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class LuxuryItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['title', 'image', 'features', 'status'];

    protected $casts = [
        'features' => 'array',
    ];

    // ইমেজ পাথের সাথে ডোমেইন যোগ করে ফুল URL রিটার্ন করবে
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset($this->image);
        }
        return asset('images/default-placeholder.png'); // যদি ছবি না থাকে
    }
}
