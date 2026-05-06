<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentPackage extends Model
{
    use HasFactory;

    protected $table ='investment_packages';
    protected $fillable = [
        'title', 'price', 'discount', 'land', 'building',
        'total_size', 'description', 'is_popular', 'is_sold_out'
    ];
}
