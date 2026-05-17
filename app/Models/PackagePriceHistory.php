<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackagePriceHistory extends Model
{
    protected $fillable = [
        'package_id',
        'old_price',
        'new_price',
        'discount',
        'final_price',
        'changed_at'
    ];

    protected $casts = [
        'changed_at' => 'datetime'
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
