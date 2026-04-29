<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'subtitle', 'images', 'is_active', 'slug'];

    // images কলামকে অটোমেটিক অ্যারেতে রূপান্তর করবে
    protected $casts = [
        'images' => 'array',
    ];
}
