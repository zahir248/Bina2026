<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'total_amount_cents',
        'amount_excludes_fee',
        'currency',
        'status',
        'stripe_payment_intent_id',
        'stripe_test_mode',
        'stripe_client_secret_encrypted',
        'payment_method',
        'buyer_snapshot',
        'ticket_holders_snapshot',
        'promo_code_id',
        'affiliate_code_id',
        'reason',
        'refund_status',
        'refund_proof_paths',
    ];

    protected $casts = [
        'stripe_test_mode' => 'boolean',
        'amount_excludes_fee' => 'boolean',
        'buyer_snapshot' => 'array',
        'ticket_holders_snapshot' => 'array',
        'refund_proof_paths' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class, 'promo_code_id');
    }

    public function affiliateCode(): BelongsTo
    {
        return $this->belongsTo(AffiliateCode::class, 'affiliate_code_id');
    }
}
