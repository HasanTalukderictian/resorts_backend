<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestRecord extends Model
{
    use HasFactory;
    protected $table= 'invest_records';
    protected $fillable = [ 'title', 'desc'];
}
