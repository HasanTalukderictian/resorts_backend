<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentBenefit extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'subtitle', 'benefits'];

    // JSON কে অটোমেটিক অ্যারেতে কাস্ট করার জন্য
    protected $casts = [
        'benefits' => 'array',
    ];
}
