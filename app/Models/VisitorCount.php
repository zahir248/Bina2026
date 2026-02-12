<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorCount extends Model
{
    protected $fillable = [
        'visit_date',
        'daily_count',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'daily_count' => 'integer',
    ];
}
