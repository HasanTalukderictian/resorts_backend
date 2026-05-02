<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyOffer extends Model
{
    use HasFactory;

    protected $fillable = [
    'title',
    'brand_name',
    'whatsapp_number',
    'description',
    'features',
    'slider_images'
];

protected $casts = [
    'features' => 'array',
    'slider_images' => 'array',
];
}
