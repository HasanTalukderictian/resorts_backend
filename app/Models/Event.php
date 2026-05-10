<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'event_datetime',
        'thumb_img',
        'main_img',
        'date_day',
        'date_month',
        'main_title',
        'subtitle',
        'posted_by',
        'comments_count',
        'features',
        'description',
        'status',
        'view_count'
    ];

    protected $casts = [
        'features' => 'array',
        'event_datetime' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $appends = ['formatted_datetime', 'feature_list'];

    // Boot method to auto-generate slug
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->title . '-' . time());
            }
        });
    }

    // Accessor for formatted datetime
    public function getFormattedDatetimeAttribute()
    {
        return $this->event_datetime ? $this->event_datetime->format('h:i A, D d M Y') : null;
    }

    // Accessor for feature list
    public function getFeatureListAttribute()
    {
        return is_array($this->features) ? $this->features : [];
    }

    // Scope for active events
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope for upcoming events
    public function scopeUpcoming($query)
    {
        return $query->where('event_datetime', '>', now());
    }

    // Scope for past events
    public function scopePast($query)
    {
        return $query->where('event_datetime', '<', now());
    }

    // Increment view count
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }
}
