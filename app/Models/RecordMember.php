<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecordMember extends Model
{
    use HasFactory;
    protected $table = 'record_members';

    protected $fillable = [ 'member', 'revenue', 'expericence', 'amenities'];
}
