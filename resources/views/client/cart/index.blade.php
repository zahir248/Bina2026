@extends('layouts.client.app')

@section('title', 'BINA')

@section('content')
<div class="cart-page-container">
    <div class="container">
        <div class="cart-page-content-wrapper">
            <!-- Page Title -->
            <div class="cart-page-header">
                <h1 class="cart-page-title">Confirm Your Selection</h1>
                <p class="cart-page-subtitle">Please check your selection and click 'Confirm & Checkout' when ready.</p>
            </div>

            <div class="cart-page-content">
            <!-- Left Section - Cart Items -->
            <div class="cart-items-section">
                <div class="cart-items-card">
                    <!-- Cart Header -->
                    <div class="cart-items-header">
                        <div class="cart-items-title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M7 18c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.15.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12L8.1 13h7.45c.75 0 1.41-.41 1.75-1.03L21.7 4H5.21l-.94-2H1zm16 16c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" fill="currentColor"/>
                            </svg>
                            <span>Items In Cart</span>
                        </div>
                        @if($carts->isNotEmpty())
                        <div class="cart-items-actions">
                            <form method="POST" action="{{ route('cart.clear') }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to remove all items from cart?');">
                                @csrf
                                <button type="submit" class="cart-action-btn cart-remove-btn">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z" fill="currentColor"/>
                                    </svg>
                                    Remove
                                </button>
                            </form>
                            <button type="button" class="cart-action-btn cart-edit-btn" id="editCartBtn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" fill="currentColor"/>
                                </svg>
                                <span class="edit-cart-text">Edit Cart</span>
                            </button>
                        </div>
                        @endif
                    </div>

                    <!-- Cart Items -->
                    @if($carts->isNotEmpty())
                    <form id="cart-quantities-form" method="POST" action="{{ url('/cart/update-quantities') }}" data-cart-url="{{ url('/cart') }}">
                        @csrf
                    @endif
                    <div class="cart-items-list">
                        @forelse($carts as $cart)
                            @php
                                $totalInEvent = $carts->where('event_id', $cart->event_id)->sum('quantity');
                                $maxForThisItem = isset($event['ticket_stock']) && $event['ticket_stock'] !== null
                                    ? max(0, (int) $event['ticket_stock'] - $totalInEvent + $cart->quantity)
                                    : 99;
                            @endphp
                            @php
                                $cartUnitPrice = $cart->ticket->getPriceForQuantity($cart->quantity);
                                $cartLineTotal = $cartUnitPrice * $cart->quantity;
                                $quantityDiscountJson = json_encode($cart->ticket->quantity_discount ?? []);
                                $ticketBasePrice = (float) $cart->ticket->price;
                            @endphp
                            <div class="cart-item" data-cart-id="{{ $cart->id }}" data-unit-price="{{ $cartUnitPrice }}" data-max-qty="{{ $maxForThisItem }}" data-quantity-discount="{{ $quantityDiscountJson }}" data-base-price="{{ $ticketBasePrice }}">
                                <div class="cart-item-details">
                                    <input type="hidden" name="quantities[{{ $cart->id }}]" value="{{ $cart->quantity }}" class="cart-qty-input" data-cart-id="{{ $cart->id }}">
                                    <div class="cart-item-name">{{ isset($event['category']) && $event['category'] ? $event['category'] . ' - ' . $cart->ticket->name : $cart->ticket->name }}</div>
                                    <div class="cart-item-date">Date: {{ $event['date_time_formatted'] ?? '' }}</div>
                                </div>
                                <div class="cart-item-qty">
                                    <span class="cart-item-qty-display">x{{ $cart->quantity }}</span>
                                    <div class="cart-item-qty-controls cart-item-qty-controls-hidden">
                                        <button type="button" class="cart-qty-btn cart-qty-minus" aria-label="Decrease" data-action="minus" @if($cart->quantity <= 1) disabled @endif>−</button>
                                        <span class="cart-item-qty-value">{{ $cart->quantity }}</span>
                                        <button type="button" class="cart-qty-btn cart-qty-plus" aria-label="Increase" data-action="plus" @if($cart->quantity >= $maxForThisItem) disabled @endif>+</button>
                                    </div>
                                </div>
                                <div class="cart-item-price" data-price-el>
                                    RM {{ number_format($cartLineTotal, 2) }}
                                </div>
                                <button type="button" class="cart-item-remove cart-item-remove-hidden" aria-label="Remove item" data-cart-id="{{ $cart->id }}" data-destroy-url="{{ route('cart.destroy', $cart->id) }}">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z" fill="currentColor"/>
                                    </svg>
                                </button>
                            </div>
                        @empty
                            <div class="cart-empty">
                                <p>Your cart is empty.</p>
                                <a href="{{ route('home') }}" class="btn-continue-browsing">Continue Browsing</a>
                            </div>
                        @endforelse
                    </div>
                    @if($carts->isNotEmpty())
                    </form>
                    @endif

                    @if($carts->isNotEmpty())
                    @foreach($carts as $cart)
                    <form id="destroy-form-{{ $cart->id }}" method="POST" action="{{ route('cart.destroy', $cart->id) }}" style="display: none;" class="cart-destroy-form">
                        @csrf
                        @method('DELETE')
                    </form>
                    @endforeach
                    @endif

                    @if($carts->isNotEmpty())
                        <!-- Promo Code and Total Section -->
                        <div class="cart-promo-total-wrapper">
                            <div class="cart-promo-divider"></div>
                            <div class="cart-promo-total-content">
                                <!-- Promo Code Section -->
                                <div class="cart-promo-section">
                                    <div class="cart-promo-content">
                                        @if($appliedPromo ?? null)
                                            <p class="cart-promo-label">Promo applied: <strong>{{ $appliedPromo->code }}</strong> (－RM {{ number_format($discountAmount ?? 0, 2) }})</p>
                                            <form method="POST" action="{{ route('cart.removePromo') }}" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="cart-promo-remove-btn">Remove</button>
                                            </form>
                                        @else
                                            <p class="cart-promo-label">Have promo code? Enter it here.</p>
                                            <form method="POST" action="{{ route('cart.applyPromo') }}" class="cart-promo-form">
                                                @csrf
                                                <div class="cart-promo-input-group">
                                                    <input type="text" name="promo_code" class="cart-promo-input" placeholder="Promo Code" value="{{ old('promo_code') }}" required>
                                                    <button type="submit" class="cart-promo-btn">Apply</button>
                                                </div>
                                            </form>
                                        @endif
                                    </div>
                                </div>

                                <!-- Total Amount -->
                                <div class="cart-total-section @if($appliedPromo ?? null) has-promo @endif">
                                    @if($appliedPromo ?? null)
                                        <div class="cart-total-row">
                                            <span class="cart-total-label">Subtotal</span>
                                            <span>RM {{ number_format($totalAmount, 2) }}</span>
                                        </div>
                                        <div class="cart-total-row cart-total-discount">
                                            <span class="cart-total-label">Discount</span>
                                            <span>－RM {{ number_format($discountAmount ?? 0, 2) }}</span>
                                        </div>
                                    @endif
                                    <div class="cart-total-row cart-total-final">
                                        <span class="cart-total-label">Total Amount</span>
                                        <span class="cart-total-amount">RM {{ number_format($totalAfterDiscount ?? $totalAmount, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Action Buttons (outside Items In Cart card; only when cart has items) -->
                @if($carts->isNotEmpty())
                <div class="cart-actions">
                    <a href="{{ route('home') }}" class="btn-continue-browsing">Continue Browsing</a>
                    <button type="button" class="btn-confirm-checkout">Confirm & Checkout</button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Match body/main background to cart page so no strip shows when content is short */
.cart-page,
.cart-page main {
    background: #F9FAFB;
}

.cart-page-container {
    padding: 4rem 0 3rem;
    min-height: calc(100vh - 60px);
    background: #F9FAFB;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.cart-page-content-wrapper {
    padding: 0 2.5rem 2.5rem 2.5rem;
    max-width: 1200px;
    margin: 0 auto;
}

.cart-page-header {
    text-align: center;
    margin-bottom: 2rem;
    padding-top: 1rem;
}

.cart-page-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 0.5rem;
    font-family: 'Playfair Display', serif;
    line-height: 1.2;
}

.cart-page-subtitle {
    font-size: 0.9375rem;
    color: #6B7280;
    font-family: 'Inter', sans-serif;
    line-height: 1.4;
}

.cart-page-content {
    max-width: 800px;
    margin: 0 auto;
}

.cart-items-card {
    background: #fff;
    border-radius: 0.5rem;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #E5E7EB;
    font-family: 'Inter', sans-serif;
}

.cart-items-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--border-color);
}

.cart-items-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    font-size: 0.9375rem;
    color: var(--text-dark);
    font-family: 'Inter', sans-serif;
}

.cart-items-title svg {
    width: 18px;
    height: 18px;
}

.cart-items-actions {
    display: flex;
    gap: 0.75rem;
}

.cart-action-btn {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border: none;
    background: transparent;
    color: #6B7280;
    cursor: pointer;
    font-size: 0.8125rem;
    font-weight: 500;
    text-decoration: none;
    transition: color 0.2s;
    font-family: 'Inter', sans-serif;
}

.cart-action-btn svg {
    width: 14px;
    height: 14px;
}

.cart-action-btn:hover {
    color: #111827;
}

.cart-remove-btn {
    color: #DC2626;
}

.cart-remove-btn:hover {
    color: #991B1B;
}

.cart-edit-btn {
    color: var(--primary-color);
}

.cart-edit-btn:hover {
    color: var(--primary-dark);
}

.cart-items-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.cart-item {
    display: grid;
    grid-template-columns: 1fr auto auto auto;
    gap: 1rem;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #E5E7EB;
}

.cart-item:last-child {
    border-bottom: none;
}

.cart-item-details {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    min-width: 0;
}

.cart-item-name {
    font-weight: 600;
    font-size: 0.9375rem;
    color: var(--text-dark);
    font-family: 'Inter', sans-serif;
    line-height: 1.3;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.cart-item-date {
    font-size: 0.8125rem;
    color: #6B7280;
    font-family: 'Inter', sans-serif;
}

.cart-item-qty {
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--text-dark);
    font-family: 'Inter', sans-serif;
    min-width: 2.5rem;
    margin-right: 1.5rem;
    text-align: right;
    display: flex;
    align-items: center;
    justify-content: flex-end;
}

.cart-item-qty-display {
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--text-dark);
    font-family: 'Inter', sans-serif;
}

.cart-item-qty-controls.cart-item-qty-controls-hidden {
    display: none;
}

.cart-items-card.editing .cart-item-qty-display {
    display: none;
}

.cart-items-card.editing .cart-item-qty-controls.cart-item-qty-controls-hidden {
    display: flex;
}

.cart-item-qty-controls {
    align-items: center;
    justify-content: flex-end;
    gap: 0.75rem;
}

.cart-item-qty-controls .cart-item-qty-value {
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--text-dark);
    font-family: 'Inter', sans-serif;
    min-width: 1.5rem;
    text-align: center;
    line-height: 1;
}

.cart-qty-form {
    display: inline-flex;
}

.cart-qty-btn {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #E5E7EB;
    background: #F9FAFB;
    color: #374151;
    font-size: 1.125rem;
    font-weight: 600;
    line-height: 1;
    cursor: pointer;
    border-radius: 0.375rem;
    padding: 0;
    transition: all 0.2s;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.cart-qty-btn:hover:not(:disabled) {
    background: #F3F4F6;
    border-color: #D1D5DB;
    color: var(--text-dark);
}

.cart-qty-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.cart-item-price {
    font-weight: 600;
    color: var(--text-dark);
    font-size: 0.9375rem;
    font-family: 'Inter', sans-serif;
    white-space: nowrap;
    text-align: right;
}

.cart-item-remove {
    background: transparent;
    border: none;
    color: #DC2626;
    cursor: pointer;
    padding: 0.375rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    border-radius: 0.25rem;
    opacity: 0.7;
}

.cart-item-remove-hidden {
    display: none;
}

.cart-items-card.editing .cart-item-remove-hidden {
    display: flex;
}

.cart-item-remove svg {
    width: 16px;
    height: 16px;
}

.cart-item-remove:hover {
    opacity: 1;
    background: rgba(220, 38, 38, 0.1);
}

.cart-promo-total-wrapper {
    margin-top: 0.5rem;
    padding-top: 0.5rem;
}

.cart-promo-divider {
    border-top: 1px dashed #D1D5DB;
    margin-bottom: 0.5rem;
}

.cart-promo-total-content {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 1.5rem;
    align-items: flex-end;
}

.cart-promo-section {
    flex: 1;
}

.cart-promo-label {
    font-size: 0.8125rem;
    color: #6B7280;
    margin-bottom: 0.5rem;
    font-weight: 500;
    font-family: 'Inter', sans-serif;
}

.cart-promo-input-group {
    display: flex;
    gap: 0;
    align-items: stretch;
}

.cart-promo-input {
    width: 200px;
    max-width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #D1D5DB;
    border-radius: 0.375rem 0 0 0.375rem;
    border-right: none;
    font-size: 0.8125rem;
    font-family: 'Inter', sans-serif;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.cart-promo-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(255, 152, 0, 0.1);
}

.cart-promo-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1rem;
    background: var(--primary-color);
    color: #fff;
    border: 1px solid var(--primary-color);
    border-radius: 0 0.375rem 0.375rem 0;
    font-weight: 600;
    font-size: 0.75rem;
    cursor: pointer;
    transition: background 0.2s;
    font-family: 'Inter', sans-serif;
    white-space: nowrap;
}

.cart-promo-btn svg {
    width: 12px;
    height: 12px;
}

.cart-promo-btn:hover {
    background: var(--primary-dark);
}

.cart-total-section {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.375rem;
    min-width: 180px;
    padding-top: 1.625rem;
}

.cart-total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    gap: 1rem;
}

.cart-total-row.cart-total-discount {
    color: #059669;
    font-size: 0.9375rem;
}

.cart-total-row.cart-total-final {
    padding-top: 0;
    border-top: none;
    margin-top: 0;
}

.cart-total-section.has-promo .cart-total-row.cart-total-final {
    padding-top: 0.5rem;
    border-top: 1px solid var(--border-color);
    margin-top: 0.25rem;
}

.cart-promo-remove-btn {
    background: none;
    border: none;
    color: #DC2626;
    cursor: pointer;
    font-size: 0.8125rem;
    font-weight: 500;
    padding: 0.25rem 0;
    text-decoration: underline;
}

.cart-promo-remove-btn:hover {
    color: #991B1B;
}

.cart-promo-form {
    margin: 0;
}

.cart-total-label {
    font-size: 0.8125rem;
    color: #6B7280;
    font-weight: 500;
    font-family: 'Inter', sans-serif;
    white-space: nowrap;
}

.cart-total-amount {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-dark);
    font-family: 'Inter', sans-serif;
    white-space: nowrap;
}

.cart-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-top: 1.25rem;
}

.btn-continue-browsing {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.875rem;
    transition: color 0.2s;
    font-family: 'Inter', sans-serif;
    padding: 0.625rem 0;
}

.btn-continue-browsing:hover {
    color: var(--primary-dark);
}

.btn-confirm-checkout {
    padding: 0.625rem 1.75rem;
    background: var(--primary-color);
    color: #fff;
    border: none;
    border-radius: 0.375rem;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: background 0.2s, transform 0.2s;
    font-family: 'Inter', sans-serif;
    box-shadow: 0 2px 8px rgba(255, 152, 0, 0.25);
}

.btn-confirm-checkout:hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 152, 0, 0.35);
}

.cart-empty {
    text-align: center;
    padding: 4rem 2rem;
    color: #6B7280;
    font-family: 'Inter', sans-serif;
}

.cart-empty p {
    font-size: 1.125rem;
    margin-bottom: 1rem;
}

.cart-empty .btn-continue-browsing {
    display: inline-block;
    margin-top: 0.25rem;
}

@media (max-width: 1024px) {
    .cart-page-title {
        font-size: 2rem;
    }
    
    .cart-items-card {
        padding: 1.5rem;
    }
}

@media (max-width: 768px) {
    .cart-page-container {
        padding: 2rem 0;
    }
    
    .cart-page-header {
        margin-bottom: 2rem;
        padding: 0;
    }
    
    .cart-page-title {
        font-size: 1.75rem;
    }
    
    .cart-items-card {
        padding: 1.25rem;
    }
    
    .cart-promo-total-content {
        grid-template-columns: 1fr;
        gap: 1.25rem;
    }
    
    .cart-total-section {
        align-items: flex-start;
        min-width: auto;
    }
    
    .cart-actions {
        flex-direction: column;
        gap: 1rem;
    }
    
    .btn-confirm-checkout {
        width: 100%;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editCartBtn = document.getElementById('editCartBtn');
    const cartItemsCard = document.querySelector('.cart-items-card');
    const editCartText = document.querySelector('.edit-cart-text');
    const cartQuantitiesForm = document.getElementById('cart-quantities-form');
    
    if (editCartBtn && cartItemsCard) {
        editCartBtn.addEventListener('click', function() {
            if (cartItemsCard.classList.contains('editing')) {
                if (cartQuantitiesForm) {
                    const formData = new FormData(cartQuantitiesForm);
                    const cartUrl = cartQuantitiesForm.getAttribute('data-cart-url') || '/cart';
                    editCartBtn.disabled = true;
                    editCartText.textContent = 'Saving...';
                    fetch(cartQuantitiesForm.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(function(res) {
                        if (res.redirected) {
                            window.location.href = res.url;
                            return;
                        }
                        if (res.ok) {
                            window.location.href = cartUrl;
                            return;
                        }
                        return res.json().catch(function() { return {}; }).then(function(data) {
                            window.location.href = cartUrl;
                        });
                    })
                    .catch(function() {
                        window.location.href = cartUrl;
                    })
                    .finally(function() {
                        editCartBtn.disabled = false;
                        if (editCartText) editCartText.textContent = 'Done';
                    });
                }
                return;
            }
            cartItemsCard.classList.add('editing');
            editCartText.textContent = 'Done';
        });
    }
    
    function getPriceForQuantity(discounts, basePrice, qty) {
        if (!discounts || !Array.isArray(discounts) || discounts.length === 0) return basePrice;
        var exactMatch = null, rangeMatch = null, moreThanMatch = null, lessThanMatch = null;
        for (var i = 0; i < discounts.length; i++) {
            var d = discounts[i], type = d.type, price = parseFloat(d.price) || 0;
            if (type === 'exact' && parseInt(d.quantity, 10) === qty) exactMatch = price;
            if (type === 'range') {
                var min = parseInt(d.min_quantity, 10) || 0, max = parseInt(d.max_quantity, 10) || 0;
                if (qty >= min && qty <= max) rangeMatch = price;
            }
            if (type === 'more_than') {
                var th = parseInt(d.quantity, 10) || 0;
                if (qty > th && (!moreThanMatch || th > moreThanMatch.q)) moreThanMatch = { price: price, q: th };
            }
            if (type === 'less_than') {
                var th = parseInt(d.quantity, 10) || 0;
                if (qty < th && (!lessThanMatch || th < lessThanMatch.q)) lessThanMatch = { price: price, q: th };
            }
        }
        if (exactMatch !== null) return exactMatch;
        if (rangeMatch !== null) return rangeMatch;
        if (moreThanMatch !== null) return moreThanMatch.price;
        if (lessThanMatch !== null) return lessThanMatch.price;
        return basePrice;
    }

    document.querySelectorAll('.cart-qty-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const action = this.getAttribute('data-action');
            const row = this.closest('.cart-item');
            if (!row) return;
            const input = row.querySelector('.cart-qty-input');
            const valueEl = row.querySelector('.cart-item-qty-value');
            const priceEl = row.querySelector('[data-price-el]');
            const basePrice = parseFloat(row.getAttribute('data-base-price')) || 0;
            const maxQty = parseInt(row.getAttribute('data-max-qty'), 10) || 99;
            let discounts = [];
            try {
                var qd = row.getAttribute('data-quantity-discount');
                if (qd) discounts = JSON.parse(qd);
            } catch (e) {}
            let qty = parseInt(input.value, 10) || 1;
            if (action === 'minus') {
                if (qty <= 1) return;
                qty--;
            } else {
                if (qty >= maxQty) return;
                qty++;
            }
            input.value = qty;
            valueEl.textContent = qty;
            const unitPrice = getPriceForQuantity(discounts, basePrice, qty);
            row.setAttribute('data-unit-price', unitPrice);
            if (priceEl) {
                priceEl.textContent = 'RM ' + (unitPrice * qty).toFixed(2);
            }
            row.querySelector('.cart-qty-minus').disabled = (qty <= 1);
            row.querySelector('.cart-qty-plus').disabled = (qty >= maxQty);
        });
    });

    document.querySelectorAll('.cart-item-remove[data-cart-id]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!confirm('Remove this item from cart?')) return;
            var cartId = this.getAttribute('data-cart-id');
            var form = document.getElementById('destroy-form-' + cartId);
            if (form) form.submit();
        });
    });
});
</script>
@endpush


@endsection
