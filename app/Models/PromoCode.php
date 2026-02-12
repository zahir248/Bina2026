<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PromoCode extends Model
{
    protected $fillable = [
        'name',
        'description',
        'code',
        'discount',
        'status',
    ];

    protected $casts = [
        'discount' => 'decimal:2',
    ];

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'promo_code_event', 'promo_code_id', 'event_id');
    }
}
