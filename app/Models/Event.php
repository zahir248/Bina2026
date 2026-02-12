<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'name',
        'description',
        'event_category_id',
        'location',
        'google_maps_address',
        'waze_location_address',
        'start_datetime',
        'end_datetime',
        'ticket_stock',
        'images',
        'status',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'images' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }

    /**
     * Generate URL slug from event name (must match EventController logic for routing).
     */
    public static function nameToSlug(string $name): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'event_id');
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'ticket_event', 'event_id', 'ticket_id');
    }
}
