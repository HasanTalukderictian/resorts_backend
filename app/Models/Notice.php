<?php
// app/Models/Notice.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    use HasFactory;

    protected $table = 'notices';

    protected $fillable = [
        'text',
        'status',
        'created_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Scope for active notices
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    // Scope for inactive notices
    public function scopeInactive($query)
    {
        return $query->where('status', 'Inactive');
    }

    // Accessor for formatted created date
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('Y-m-d');
    }

    // Mutator for notice text (trim before saving)
    public function setTextAttribute($value)
    {
        $this->attributes['text'] = trim($value);
    }
}
