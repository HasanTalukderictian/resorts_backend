<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'author',
        'category',
        'status',
        'excerpt',
        'image',
        'introduction',
        'sections',
        'conclusion',
        'views',
        'likes',
        'read_time',
    ];

    protected $casts = [
        'sections' => 'array',
    ];
}
