<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyBenifit extends Model
{
    use HasFactory;
    protected $table ='property_benitfit';

    protected $fillable = [ 'title', 'desc'];
}
