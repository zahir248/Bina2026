<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ticket extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'quantity_discount',
        'remarks',
        'image',
        'status',
    ];

    protected $casts = [
        'quantity_discount' => 'array',
        'price' => 'decimal:2',
    ];

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'ticket_event', 'ticket_id', 'event_id');
    }

    /**
     * Get the effective unit price for a given quantity (respects quantity_discount tiers).
     */
    public function getPriceForQuantity(int $quantity): float
    {
        $discounts = $this->quantity_discount ?? [];
        if (!is_array($discounts) || empty($discounts)) {
            return (float) $this->price;
        }

        $exactMatch = null;
        $rangeMatch = null;
        $moreThanMatch = null; // { price, quantity } - keep largest threshold that matches
        $lessThanMatch = null;  // { price, quantity } - keep smallest threshold that matches

        foreach ($discounts as $d) {
            $type = $d['type'] ?? null;
            $price = (float) ($d['price'] ?? 0);

            if ($type === 'exact' && (int)($d['quantity'] ?? 0) === $quantity) {
                $exactMatch = $price;
            }
            if ($type === 'range') {
                $min = (int) ($d['min_quantity'] ?? 0);
                $max = (int) ($d['max_quantity'] ?? 0);
                if ($quantity >= $min && $quantity <= $max) {
                    $rangeMatch = $price;
                }
            }
            if ($type === 'more_than') {
                $threshold = (int) ($d['quantity'] ?? 0);
                if ($quantity > $threshold) {
                    if ($moreThanMatch === null || $threshold > $moreThanMatch['quantity']) {
                        $moreThanMatch = ['price' => $price, 'quantity' => $threshold];
                    }
                }
            }
            if ($type === 'less_than') {
                $threshold = (int) ($d['quantity'] ?? 0);
                if ($quantity < $threshold) {
                    if ($lessThanMatch === null || $threshold < $lessThanMatch['quantity']) {
                        $lessThanMatch = ['price' => $price, 'quantity' => $threshold];
                    }
                }
            }
        }

        if ($exactMatch !== null) {
            return $exactMatch;
        }
        if ($rangeMatch !== null) {
            return $rangeMatch;
        }
        if ($moreThanMatch !== null) {
            return $moreThanMatch['price'];
        }
        if ($lessThanMatch !== null) {
            return $lessThanMatch['price'];
        }

        return (float) $this->price;
    }
}
