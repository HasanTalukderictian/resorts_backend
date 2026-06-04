<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubInfo extends Model
{
    use HasFactory;

    protected $table ='club_infos';

    protected $fillable = [ 'club_name', 'club_history', 'image', 'club_phone'];
}
