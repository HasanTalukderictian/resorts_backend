<?php

namespace App\Models;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DBUser extends Model
{
    use HasFactory;

    use HasApiTokens;
    protected $table ='dbusers';
        protected $fillable = [
        'name',
        'email',
        'role',
        'status',
        'password'
    ];
}
