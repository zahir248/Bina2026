<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EventPersonnel extends Model
{
    protected $table = 'event_personnel';

    protected $fillable = [
        'name',
        'role',
        'position',
        'company',
        'image',
        'status',
    ];

    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(Schedule::class, 'event_personnel_schedule', 'event_personnel_id', 'schedule_id');
    }
}
