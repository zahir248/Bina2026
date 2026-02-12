<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Schedule extends Model
{
    protected $fillable = [
        'name',
        'description',
        'event_id',
        'start_time',
        'end_time',
        'session',
        'status',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function personnel(): BelongsToMany
    {
        return $this->belongsToMany(EventPersonnel::class, 'event_personnel_schedule', 'schedule_id', 'event_personnel_id');
    }
}
