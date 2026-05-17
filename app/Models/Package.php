<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name',
        'old_price',
        'price',
        'discount',
        'final_price',
        'color',
        'status'
    ];

    public function histories()
    {
        return $this->hasMany(PackagePriceHistory::class);
    }
}
