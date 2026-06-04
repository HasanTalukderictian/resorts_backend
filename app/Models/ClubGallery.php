<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubGallery extends Model
{
    use HasFactory;

    protected $table ='club_galleries';

    protected $fillable = [ 'title', 'image'];
}
