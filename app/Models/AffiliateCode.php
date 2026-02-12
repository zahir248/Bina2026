<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateCode extends Model
{
    protected $fillable = [
        'name',
        'description',
        'code',
        'link',
        'total_conversion',
        'status',
    ];

    protected $casts = [
        'total_conversion' => 'integer',
    ];
}
