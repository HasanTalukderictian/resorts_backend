<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testominal extends Model
{
    use HasFactory;

    protected $table ='testimonials';
    protected $fillable = ['name', 'image', 'source', 'stars', 'text'];

    // Image-er full URL paoar jonno (React-e subidha hobe)
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
