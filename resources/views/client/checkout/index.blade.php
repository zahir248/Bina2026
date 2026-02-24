@extends('layouts.client.app')

@section('title', 'Checkout - BINA')

@section('content')
<div class="checkout-page-container">
    <div class="container">
        <div class="checkout-page-content-wrapper">
            <!-- Page Title -->
            <div class="checkout-page-header">
                <h1 class="checkout-page-title">Checkout</h1>
                <p class="checkout-page-subtitle">Please review your details and complete your purchase.</p>
            </div>

            <div class="checkout-page-content">
                @php
                    $buyerProfileForJs = [
                        'name' => $buyer->name ?? '',
                        'email' => $buyer->email ?? '',
                        'contact_number' => $buyer->contact_number ?? '',
                        'gender' => $buyer->gender ?? '',
                        'nric_passport' => $buyer->nric_passport ?? '',
                        'country_region' => $buyer->country_region ?? '',
                        'country_display' => isset($countriesRegions[$buyer->country_region ?? '']) ? $countriesRegions[$buyer->country_region ?? ''] : ($buyer->country_region ?? ''),
                        'street_address' => $buyer->street_address ?? '',
                        'town_city' => $buyer->town_city ?? '',
                        'state' => $buyer->state ?? '',
                        'postcode_zip' => $buyer->postcode_zip ?? '',
                    ];
                @endphp
                <script>window.checkoutProfileData = @json($buyerProfileForJs);</script>
                <div class="checkout-panels">
                        <!-- Left Panel - Buyer Details, Ticket Holder Details, Affiliate -->
                        <div class="checkout-left-panel">
                            <form method="POST" action="#" id="checkout-form" class="checkout-form">
                                @csrf
                            <!-- Buyer Details Section -->
                            <div class="checkout-section-card">
                                <div class="checkout-section-header checkout-section-header-with-checkbox">
                                    <h2 class="checkout-section-title">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" fill="currentColor"/>
                                        </svg>
                                        <span>Buyer Details</span>
                                    </h2>
                                    <label class="checkout-use-profile-label">
                                        <input type="checkbox" id="use_profile_data" name="use_profile_data" class="checkout-use-profile-checkbox" value="1">
                                        <span>Use my profile details</span>
                                    </label>
                                </div>
                                <div class="checkout-section-body">
                                    <div class="checkout-form-group checkout-form-group-full">
                                        <label for="buyer_name" class="checkout-form-label">Full Name <span class="checkout-label-required">*</span></label>
                                        <input type="text" id="buyer_name" name="buyer_name" class="checkout-form-input" value="{{ old('buyer_name') }}" required>
                                    </div>
                                    <div class="checkout-form-row checkout-form-row-two">
                                        <div class="checkout-form-group">
                                            <label for="buyer_email" class="checkout-form-label">Email Address <span class="checkout-label-required">*</span></label>
                                            <input type="email" id="buyer_email" name="buyer_email" class="checkout-form-input" value="{{ old('buyer_email') }}" required>
                                        </div>
                                        <div class="checkout-form-group">
                                            <label for="buyer_gender" class="checkout-form-label">Gender <span class="checkout-label-required">*</span></label>
                                            <select id="buyer_gender" name="buyer_gender" class="checkout-form-input" required>
                                                <option value="">Select Gender</option>
                                                <option value="male" {{ old('buyer_gender') == 'male' ? 'selected' : '' }}>Male</option>
                                                <option value="female" {{ old('buyer_gender') == 'female' ? 'selected' : '' }}>Female</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="checkout-form-row checkout-form-row-two">
                                        <div class="checkout-form-group">
                                            <label for="buyer_nric_passport" class="checkout-form-label">NRIC/Passport Number <span class="checkout-label-required">*</span></label>
                                            <input type="text" id="buyer_nric_passport" name="buyer_nric_passport" class="checkout-form-input" value="{{ old('buyer_nric_passport') }}" required>
                                        </div>
                                        <div class="checkout-form-group">
                                            <label for="buyer_contact" class="checkout-form-label">Contact Number <span class="checkout-label-required">*</span></label>
                                            <input type="tel" id="buyer_contact" name="buyer_contact" class="checkout-form-input" value="{{ old('buyer_contact') }}" required>
                                        </div>
                                    </div>
                                    <div class="checkout-form-group checkout-form-group-full checkout-country-dropdown-wrap">
                                        <span id="buyer_country_label" class="checkout-form-label">Country/Region <span class="checkout-label-required">*</span></span>
                                        <input type="hidden" name="buyer_country" id="buyer_country" value="{{ old('buyer_country') }}">
                                        <div class="checkout-country-trigger checkout-form-input" id="buyer_country_trigger" tabindex="0" role="combobox" aria-expanded="false" aria-haspopup="listbox" aria-labelledby="buyer_country_label">
                                            @php
                                                $buyerCountryValue = old('buyer_country');
                                                $buyerCountryDisplay = $buyerCountryValue && isset($countriesRegions[$buyerCountryValue]) ? $countriesRegions[$buyerCountryValue] : ($buyerCountryValue ?: 'Select Country/Region');
                                            @endphp
                                            <span class="checkout-country-trigger-text" id="buyer_country_display">{{ $buyerCountryDisplay }}</span>
                                            <span class="checkout-country-trigger-arrow">▼</span>
                                        </div>
                                        <div class="checkout-country-panel" id="buyer_country_panel" role="listbox" aria-hidden="true">
                                            <div class="checkout-country-search-wrap">
                                                <input type="text" class="checkout-country-search" id="buyer_country_search" placeholder="Search country..." autocomplete="off">
                                            </div>
                                            <div class="checkout-country-options" id="buyer_country_options">
                                                @foreach($countriesRegions ?? [] as $value => $label)
                                                    <div class="checkout-country-option{{ $buyerCountryValue == $value ? ' checkout-country-option-selected' : '' }}" data-value="{{ $value }}" data-label="{{ $label }}" role="option">{{ $label }}</div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="checkout-form-group checkout-form-group-full">
                                        <label for="buyer_street_address" class="checkout-form-label">Street Address <span class="checkout-label-required">*</span></label>
                                        <input type="text" id="buyer_street_address" name="buyer_street_address" class="checkout-form-input" value="{{ old('buyer_street_address') }}" required>
                                    </div>
                                    <div class="checkout-form-row checkout-form-row-three">
                                        <div class="checkout-form-group">
                                            <label for="buyer_town_city" class="checkout-form-label">Town/City <span class="checkout-label-required">*</span></label>
                                            <input type="text" id="buyer_town_city" name="buyer_town_city" class="checkout-form-input" value="{{ old('buyer_town_city') }}" required>
                                        </div>
                                        <div class="checkout-form-group">
                                            <label for="buyer_state" class="checkout-form-label">State <span class="checkout-label-required">*</span></label>
                                            <input type="text" id="buyer_state" name="buyer_state" class="checkout-form-input" value="{{ old('buyer_state') }}" required>
                                        </div>
                                        <div class="checkout-form-group">
                                            <label for="buyer_postcode_zip" class="checkout-form-label">Postcode/Zip <span class="checkout-label-required">*</span></label>
                                            <input type="text" id="buyer_postcode_zip" name="buyer_postcode_zip" class="checkout-form-input" value="{{ old('buyer_postcode_zip') }}" required>
                                        </div>
                                    </div>
                                    <div class="checkout-form-group checkout-form-group-full">
                                        <label for="buyer_category" class="checkout-form-label">Category <span class="checkout-label-required">*</span></label>
                                        <select id="buyer_category" name="buyer_category" class="checkout-form-input" required>
                                            <option value="">Select Category</option>
                                            <option value="individual" {{ old('buyer_category') == 'individual' ? 'selected' : '' }}>Individual</option>
                                            <option value="academician" {{ old('buyer_category') == 'academician' ? 'selected' : '' }}>Academician</option>
                                            <option value="organization" {{ old('buyer_category') == 'organization' ? 'selected' : '' }}>Organization</option>
                                        </select>
                                    </div>
                                    <div class="checkout-academician-fields" id="checkout_academician_fields" style="{{ old('buyer_category') == 'academician' ? '' : 'display: none;' }}">
                                        <div class="checkout-form-row checkout-form-row-two">
                                            <div class="checkout-form-group">
                                                <label for="buyer_student_id" class="checkout-form-label">Student Identification Number <span class="checkout-label-required">*</span></label>
                                                <input type="text" id="buyer_student_id" name="buyer_student_id" class="checkout-form-input" value="{{ old('buyer_student_id') }}">
                                            </div>
                                            <div class="checkout-form-group">
                                                <label for="buyer_academy_institution" class="checkout-form-label">Academy/Institution <span class="checkout-label-required">*</span></label>
                                                <input type="text" id="buyer_academy_institution" name="buyer_academy_institution" class="checkout-form-input" value="{{ old('buyer_academy_institution') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="checkout-organization-fields" id="checkout_organization_fields" style="{{ old('buyer_category') == 'organization' ? '' : 'display: none;' }}">
                                        <div class="checkout-form-row checkout-form-row-two">
                                            <div class="checkout-form-group">
                                                <label for="buyer_company_name" class="checkout-form-label">Company Name <span class="checkout-label-required">*</span></label>
                                                <input type="text" id="buyer_company_name" name="buyer_company_name" class="checkout-form-input" value="{{ old('buyer_company_name') }}">
                                            </div>
                                            <div class="checkout-form-group">
                                                <label for="buyer_business_registration_number" class="checkout-form-label">Business Registration Number <span class="checkout-label-required">*</span></label>
                                                <input type="text" id="buyer_business_registration_number" name="buyer_business_registration_number" class="checkout-form-input" value="{{ old('buyer_business_registration_number') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Ticket Holder Details Section -->
                            <div class="checkout-section-card">
                                <div class="checkout-section-header">
                                    <h2 class="checkout-section-title">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M20 6h-2.18c.11-.31.18-.65.18-1a2.996 2.996 0 0 0-5.5-1.65l-.5.67-.5-.68C10.96 2.54 10 2 9 2 7.34 2 6 3.34 6 5c0 .35.07.69.18 1H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-5-2c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM9 4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm11 15H4v-2h16v2zm0-5H4V8h5.08L7 10.83 8.62 12 11 8.76l1-1.36 1 1.36L15.38 12 17 10.83 14.92 8H20v6z" fill="currentColor"/>
                                        </svg>
                                        <span>Ticket Holder Details</span>
                                    </h2>
                                </div>
                                <div class="checkout-section-body">
                                    @php
                                        $totalTickets = $carts->sum('quantity');
                                        $ticketHolderIndex = 0;
                                    @endphp
                                    <p class="checkout-info-text">Total tickets: <strong>{{ $totalTickets }}</strong></p>
                                    
                                    @foreach($carts as $cartIndex => $cart)
                                        @for($i = 0; $i < $cart->quantity; $i++)
                                            @php
                                                $ticketHolderIndex++;
                                            @endphp
                                            <div class="checkout-ticket-group" data-ticket-index="{{ $ticketHolderIndex }}">
                                                <div class="checkout-ticket-header">
                                                    <strong>{{ isset($event['category']) && $event['category'] ? $event['category'] . ' - ' . $cart->ticket->name : $cart->ticket->name }}</strong>
                                                    <span class="checkout-ticket-qty">Ticket {{ $ticketHolderIndex }}</span>
                                                </div>
                                                <div class="checkout-ticket-details">
                                                    <input type="hidden" name="ticket_holders[{{ $ticketHolderIndex }}][cart_id]" value="{{ $cart->id }}">
                                                    <input type="hidden" name="ticket_holders[{{ $ticketHolderIndex }}][ticket_id]" value="{{ $cart->ticket_id }}">
                                                    
                                                    <div class="checkout-form-group">
                                                        <label for="holder_name_{{ $ticketHolderIndex }}" class="checkout-form-label">Full Name <span class="checkout-label-required">*</span></label>
                                                        <input type="text" id="holder_name_{{ $ticketHolderIndex }}" name="ticket_holders[{{ $ticketHolderIndex }}][full_name]" class="checkout-form-input" value="{{ old("ticket_holders.{$ticketHolderIndex}.full_name", '') }}" required>
                                                    </div>

                                                    <div class="checkout-form-row checkout-form-row-two">
                                                        <div class="checkout-form-group">
                                                            <label for="holder_email_{{ $ticketHolderIndex }}" class="checkout-form-label">Email Address <span class="checkout-label-required">*</span></label>
                                                            <input type="email" id="holder_email_{{ $ticketHolderIndex }}" name="ticket_holders[{{ $ticketHolderIndex }}][email]" class="checkout-form-input" value="{{ old("ticket_holders.{$ticketHolderIndex}.email", '') }}" required>
                                                        </div>
                                                        <div class="checkout-form-group">
                                                            <label for="holder_gender_{{ $ticketHolderIndex }}" class="checkout-form-label">Gender <span class="checkout-label-required">*</span></label>
                                                            <select id="holder_gender_{{ $ticketHolderIndex }}" name="ticket_holders[{{ $ticketHolderIndex }}][gender]" class="checkout-form-input" required>
                                                                <option value="">Select Gender</option>
                                                                <option value="male" {{ old("ticket_holders.{$ticketHolderIndex}.gender") == 'male' ? 'selected' : '' }}>Male</option>
                                                                <option value="female" {{ old("ticket_holders.{$ticketHolderIndex}.gender") == 'female' ? 'selected' : '' }}>Female</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="checkout-form-row checkout-form-row-two">
                                                        <div class="checkout-form-group">
                                                            <label for="holder_nric_{{ $ticketHolderIndex }}" class="checkout-form-label">NRIC/Passport Number <span class="checkout-label-required">*</span></label>
                                                            <input type="text" id="holder_nric_{{ $ticketHolderIndex }}" name="ticket_holders[{{ $ticketHolderIndex }}][nric_passport]" class="checkout-form-input" value="{{ old("ticket_holders.{$ticketHolderIndex}.nric_passport", '') }}" required>
                                                        </div>
                                                        <div class="checkout-form-group">
                                                            <label for="holder_contact_{{ $ticketHolderIndex }}" class="checkout-form-label">Contact Number <span class="checkout-label-required">*</span></label>
                                                            <input type="tel" id="holder_contact_{{ $ticketHolderIndex }}" name="ticket_holders[{{ $ticketHolderIndex }}][contact_number]" class="checkout-form-input" value="{{ old("ticket_holders.{$ticketHolderIndex}.contact_number", '') }}" required>
                                                        </div>
                                                    </div>

                                                    <div class="checkout-form-group">
                                                        <label for="holder_company_{{ $ticketHolderIndex }}" class="checkout-form-label">Company Name <span class="checkout-label-optional">(optional)</span></label>
                                                        <input type="text" id="holder_company_{{ $ticketHolderIndex }}" name="ticket_holders[{{ $ticketHolderIndex }}][company_name]" class="checkout-form-input" value="{{ old("ticket_holders.{$ticketHolderIndex}.company_name", '') }}">
                                                    </div>
                                                </div>
                                            </div>
                                        @endfor
                                    @endforeach
                                </div>
                            </div>
                    </form>
                    <!-- Affiliate Section: its own form so Apply works without filling buyer/ticket details (not inside main form) -->
                    <div class="checkout-section-card" id="checkout-affiliate">
                        <div class="checkout-section-header">
                            <h2 class="checkout-section-title">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M11 17H7q-2.075 0-3.537-1.462Q2 14.075 2 12t1.463-3.538Q4.925 7 7 7h4v2H7q-1.25 0-2.125.875T4 12q0 1.25.875 2.125T7 15h4zm-3-4v-2h8v2zm5 7v-2h4q1.25 0 2.125-.875T20 12q0-1.25-.875-2.125T17 9h-4V7h4q2.075 0 3.538 1.462Q22 9.925 22 12t-1.462 3.538Q19.075 17 17 17z" fill="currentColor"/>
                                </svg>
                                <span>Affiliate</span>
                            </h2>
                        </div>
                        <div class="checkout-section-body">
                            @if($appliedAffiliate ?? null)
                                <p class="checkout-affiliate-label">Affiliate applied: <strong>{{ $appliedAffiliate->code }}</strong></p>
                                <form method="POST" action="{{ route('checkout.removeAffiliate') }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="checkout-affiliate-remove-btn">Remove</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('checkout.applyAffiliate') }}" class="checkout-affiliate-form">
                                    @csrf
                                    <div class="checkout-affiliate-input-group">
                                        <input type="text" id="checkout_affiliate_code" name="affiliate_code" class="checkout-affiliate-input" placeholder="Affiliate Code" value="{{ old('affiliate_code') }}" autocomplete="off">
                                        <button type="submit" class="checkout-affiliate-btn">Apply</button>
                                    </div>
                                </form>
                            @endif
                            <p class="checkout-info-text" style="margin-top: 0.5rem; margin-bottom: 0;">If you have an affiliate code, enter it here to support your affiliate.</p>
                        </div>
                    </div>
                        </div>
                    <!-- Right Panel - Order Summary (unchanged) -->
                    <div class="checkout-right-panel" id="checkout-right-panel">
                        <div class="checkout-summary-card" id="checkout-order-summary-card">
                            <div class="checkout-summary-header">
                                <h2 class="checkout-section-title">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M7 18c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.15.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12L8.1 13h7.45c.75 0 1.41-.41 1.75-1.03L21.7 4H5.21l-.94-2H1zm16 16c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" fill="currentColor"/>
                                    </svg>
                                    <span>Order Summary</span>
                                </h2>
                            </div>
                            <div class="checkout-summary-body">
                                <!-- Cart Items Summary -->
                                <div class="checkout-items-summary">
                                    @foreach($carts as $cart)
                                        @php
                                            $cartUnitPrice = $cart->ticket->getPriceForQuantity($cart->quantity);
                                            $cartLineTotal = $cartUnitPrice * $cart->quantity;
                                        @endphp
                                        <div class="checkout-item-row">
                                            <div class="checkout-item-info">
                                                <div class="checkout-item-name">{{ isset($event['category']) && $event['category'] ? $event['category'] . ' - ' . $cart->ticket->name : $cart->ticket->name }}</div>
                                                <div class="checkout-item-meta">x{{ $cart->quantity }}</div>
                                            </div>
                                            <div class="checkout-item-price">RM {{ number_format($cartLineTotal, 2) }}</div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Promo Code Display -->
                                @if($appliedPromo ?? null)
                                <div class="checkout-promo-section">
                                    <div class="checkout-promo-info">
                                        <span class="checkout-promo-label">Promo Code Applied:</span>
                                        <span class="checkout-promo-code">{{ $appliedPromo->code }}</span>
                                    </div>
                                </div>
                                @endif

                                <!-- Total Amount -->
                                <div class="checkout-total-section @if($appliedPromo ?? null) has-promo @endif">
                                    @if($appliedPromo ?? null)
                                        <div class="checkout-total-row">
                                            <span class="checkout-total-label">Subtotal</span>
                                            <span>RM {{ number_format($totalAmount, 2) }}</span>
                                        </div>
                                        <div class="checkout-total-row checkout-total-discount">
                                            <span class="checkout-total-label">Discount</span>
                                            <span>－RM {{ number_format($discountAmount ?? 0, 2) }}</span>
                                        </div>
                                    @endif
                                    <div class="checkout-total-row checkout-total-final">
                                        <span class="checkout-total-label">Total Amount</span>
                                        <span class="checkout-total-amount">RM {{ number_format($totalAfterDiscount ?? $totalAmount, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                    
                    <!-- Submit Button (submits main form by id; main form contains only buyer + ticket holder) -->
                    <div class="checkout-actions">
                        <a href="{{ route('cart.index') }}" class="btn-back-to-cart">Back to Cart</a>
                        <button type="submit" form="checkout-form" class="btn-complete-purchase">Complete Purchase</button>
                    </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Match body/main background to checkout page */
.checkout-page,
.checkout-page main {
    background: #F9FAFB;
}

.checkout-page-container {
    padding: 4rem 0 3rem;
    min-height: calc(100vh - 60px);
    background: #F9FAFB;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.checkout-page-content-wrapper {
    padding: 0 2.5rem 2.5rem 2.5rem;
    max-width: 1200px;
    margin: 0 auto;
}

.checkout-page-header {
    text-align: center;
    margin-bottom: 2rem;
    padding-top: 1rem;
}

.checkout-flash {
    margin-top: 0.75rem;
    font-size: 0.875rem;
    font-family: 'Inter', sans-serif;
}
.checkout-flash-success { color: #059669; }
.checkout-flash-error { color: #DC2626; }

.checkout-page-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 0.5rem;
    font-family: 'Playfair Display', serif;
    line-height: 1.2;
}

.checkout-page-subtitle {
    font-size: 0.9375rem;
    color: #6B7280;
    font-family: 'Inter', sans-serif;
    line-height: 1.4;
}

.checkout-page-content {
    max-width: 100%;
    margin: 0 auto;
}

.checkout-panels {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 2rem;
    align-items: flex-start;
}

.checkout-left-panel {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Allow sticky/fixed to work: no overflow on ancestors */
.checkout-page-container,
.checkout-page-container .container,
.checkout-page-content-wrapper,
.checkout-page-content,
#checkout-form,
.checkout-panels {
    overflow: visible;
}

/* Form wraps buyer + ticket holder; same spacing between them as between other left-panel sections */
#checkout-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.checkout-right-panel {
    position: relative;
    align-self: flex-start;
}

.checkout-summary-card {
    max-height: calc(100vh - 6rem);
    overflow-y: auto;
}

/* JS applies .is-sticky-fixed when scrolling to keep summary in view */
.checkout-summary-card.is-sticky-fixed {
    position: fixed;
    z-index: 100;
    top: 5rem;
    /* left and width set by JS from panel position */
}

.checkout-section-card,
.checkout-summary-card {
    background: #fff;
    border-radius: 0.5rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #E5E7EB;
    font-family: 'Inter', sans-serif;
}

.checkout-section-header,
.checkout-summary-header {
    margin-bottom: 1.25rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--border-color);
}

.checkout-section-header-with-checkbox {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}

.checkout-use-profile-label {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-dark);
    font-family: 'Inter', sans-serif;
    margin: 0;
}

.checkout-use-profile-checkbox {
    width: 1rem;
    height: 1rem;
    accent-color: var(--primary-color);
    cursor: pointer;
}

.checkout-section-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    font-size: 1rem;
    color: var(--text-dark);
    font-family: 'Inter', sans-serif;
    margin: 0;
}

.checkout-section-title svg {
    width: 20px;
    height: 20px;
    color: var(--primary-color);
}

.checkout-section-body {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.checkout-form-row {
    display: grid;
    gap: 1rem;
}

.checkout-form-row-two {
    grid-template-columns: 1fr 1fr;
}

.checkout-form-row-three {
    grid-template-columns: 1fr 1fr 1fr;
}

.checkout-form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.checkout-form-label {
    font-size: 0.875rem;
    color: var(--text-dark);
    font-weight: 500;
    font-family: 'Inter', sans-serif;
}

.checkout-form-label .checkout-label-required {
    color: #DC2626;
    font-weight: 600;
}

.checkout-form-label .checkout-label-optional {
    color: #6B7280;
    font-weight: 400;
    font-size: 0.8125rem;
}

/* Country dropdown (same as profile page) */
.checkout-country-dropdown-wrap {
    position: relative;
}

.checkout-country-trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    user-select: none;
}

.checkout-country-trigger-text {
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.checkout-country-trigger-arrow {
    flex-shrink: 0;
    margin-left: 0.5rem;
    font-size: 0.65rem;
    color: #6B7280;
    transition: transform 0.2s;
}

.checkout-country-dropdown-wrap.is-open .checkout-country-trigger-arrow {
    transform: rotate(180deg);
}

.checkout-country-panel {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    margin-top: 2px;
    max-height: 320px;
    background: #fff;
    border: 1px solid #D1D5DB;
    border-radius: 0.375rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 50;
    display: none;
    flex-direction: column;
}

.checkout-country-dropdown-wrap.is-open .checkout-country-panel {
    display: flex;
}

.checkout-country-search-wrap {
    flex-shrink: 0;
    padding: 0.5rem;
    border-bottom: 1px solid #E5E7EB;
}

.checkout-country-search {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #D1D5DB;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-family: 'Inter', sans-serif;
    color: var(--text-dark);
    background: #fff;
}

.checkout-country-search:focus {
    outline: none;
    border-color: var(--primary-color);
}

.checkout-country-search::placeholder {
    color: #9CA3AF;
}

.checkout-country-options {
    overflow-y: auto;
    max-height: 260px;
}

.checkout-country-option {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    font-family: 'Inter', sans-serif;
    color: var(--text-dark);
    cursor: pointer;
    transition: background 0.15s;
}

.checkout-country-option:hover {
    background: #F3F4F6;
}

.checkout-country-option-selected,
.checkout-country-option.checkout-country-option-selected:hover {
    background: rgba(255, 152, 0, 0.12);
    color: var(--primary-dark);
    font-weight: 500;
}

.checkout-form-input {
    width: 100%;
    padding: 0.625rem 0.75rem;
    border: 1px solid #D1D5DB;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-family: 'Inter', sans-serif;
    color: var(--text-dark);
    background: #fff;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.checkout-form-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(255, 152, 0, 0.1);
}

.checkout-detail-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    padding: 0.5rem 0;
}

.checkout-detail-label {
    font-size: 0.875rem;
    color: #6B7280;
    font-weight: 500;
    font-family: 'Inter', sans-serif;
    min-width: 100px;
}

.checkout-detail-value {
    font-size: 0.875rem;
    color: var(--text-dark);
    font-weight: 500;
    font-family: 'Inter', sans-serif;
    text-align: right;
    flex: 1;
}

.checkout-info-text {
    font-size: 0.875rem;
    color: #6B7280;
    font-family: 'Inter', sans-serif;
    margin: 0;
    line-height: 1.5;
}

/* Affiliate section: same pattern as cart promo (input + Apply / applied + Remove) */
.checkout-affiliate-label {
    font-size: 0.875rem;
    color: var(--text-dark);
    font-family: 'Inter', sans-serif;
    margin-bottom: 0.5rem;
}
.checkout-affiliate-input-group {
    display: flex;
    gap: 0;
    align-items: stretch;
    max-width: 320px;
}
.checkout-affiliate-input {
    flex: 1;
    min-width: 0;
    padding: 0.5rem 0.75rem;
    border: 1px solid #D1D5DB;
    border-radius: 0.375rem 0 0 0.375rem;
    border-right: none;
    font-size: 0.875rem;
    font-family: 'Inter', sans-serif;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.checkout-affiliate-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(255, 152, 0, 0.1);
}
.checkout-affiliate-btn {
    padding: 0.5rem 1rem;
    background: var(--primary-color);
    color: #fff;
    border: 1px solid var(--primary-color);
    border-radius: 0 0.375rem 0.375rem 0;
    font-weight: 600;
    font-size: 0.8125rem;
    cursor: pointer;
    transition: background 0.2s;
    font-family: 'Inter', sans-serif;
    white-space: nowrap;
}
.checkout-affiliate-btn:hover {
    background: var(--primary-dark);
}
.checkout-affiliate-remove-btn {
    background: none;
    border: none;
    color: #DC2626;
    cursor: pointer;
    font-size: 0.8125rem;
    font-weight: 500;
    padding: 0.25rem 0;
    text-decoration: underline;
}
.checkout-affiliate-remove-btn:hover {
    color: #B91C1C;
}

.checkout-ticket-group {
    padding: 1rem;
    background: #F9FAFB;
    border-radius: 0.375rem;
    border: 1px solid #E5E7EB;
}

.checkout-ticket-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #E5E7EB;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.checkout-ticket-header strong {
    font-size: 0.9375rem;
    color: var(--text-dark);
    font-family: 'Inter', sans-serif;
}

.checkout-ticket-header-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.checkout-ticket-qty {
    font-size: 0.875rem;
    color: #6B7280;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
}

.checkout-ticket-details {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.checkout-summary-body {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.checkout-items-summary {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    max-height: 300px;
    overflow-y: auto;
    padding-right: 0.5rem;
}

.checkout-item-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid #E5E7EB;
}

.checkout-item-row:last-child {
    border-bottom: none;
}

.checkout-item-info {
    flex: 1;
    min-width: 0;
}

.checkout-item-name {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-dark);
    font-family: 'Inter', sans-serif;
    margin-bottom: 0.25rem;
    line-height: 1.3;
}

.checkout-item-meta {
    font-size: 0.8125rem;
    color: #6B7280;
    font-family: 'Inter', sans-serif;
}

.checkout-item-price {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-dark);
    font-family: 'Inter', sans-serif;
    white-space: nowrap;
}

.checkout-promo-section {
    padding: 0.75rem;
    background: #F0FDF4;
    border: 1px solid #86EFAC;
    border-radius: 0.375rem;
}

.checkout-promo-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
}

.checkout-promo-label {
    font-size: 0.8125rem;
    color: #166534;
    font-weight: 500;
    font-family: 'Inter', sans-serif;
}

.checkout-promo-code {
    font-size: 0.8125rem;
    color: #166534;
    font-weight: 700;
    font-family: 'Inter', sans-serif;
}

.checkout-total-section {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding-top: 1rem;
    border-top: 1px solid #E5E7EB;
}

.checkout-total-section.has-promo {
    padding-top: 0.75rem;
}

.checkout-total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.checkout-total-row.checkout-total-discount {
    color: #059669;
    font-size: 0.9375rem;
}

.checkout-total-row.checkout-total-final {
    padding-top: 0.75rem;
    border-top: 1px solid #E5E7EB;
    margin-top: 0.25rem;
}

.checkout-total-label {
    font-size: 0.9375rem;
    color: #6B7280;
    font-weight: 500;
    font-family: 'Inter', sans-serif;
}

.checkout-total-amount {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-dark);
    font-family: 'Inter', sans-serif;
    white-space: nowrap;
}

.checkout-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #E5E7EB;
}

.btn-back-to-cart {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.875rem;
    transition: color 0.2s;
    font-family: 'Inter', sans-serif;
    padding: 0.625rem 0;
}

.btn-back-to-cart:hover {
    color: var(--primary-dark);
}

.btn-complete-purchase {
    padding: 0.75rem 2rem;
    background: var(--primary-color);
    color: #fff;
    border: none;
    border-radius: 0.375rem;
    font-weight: 600;
    font-size: 0.9375rem;
    cursor: pointer;
    transition: background 0.2s, transform 0.2s;
    font-family: 'Inter', sans-serif;
    box-shadow: 0 2px 8px rgba(255, 152, 0, 0.25);
}

.btn-complete-purchase:hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 152, 0, 0.35);
}

.btn-complete-purchase:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

@media (max-width: 1024px) {
    .checkout-panels {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    .checkout-right-panel {
        position: static;
    }

    .checkout-page-title {
        font-size: 1.75rem;
    }
}

@media (max-width: 768px) {
    .checkout-page-container {
        padding: 2rem 0;
    }
    
    .checkout-page-content-wrapper {
        padding: 0 1rem 1rem 1rem;
    }
    
    .checkout-page-header {
        margin-bottom: 1.5rem;
        padding: 0;
    }
    
    .checkout-page-title {
        font-size: 1.5rem;
    }
    
    .checkout-section-card,
    .checkout-summary-card {
        padding: 1.25rem;
    }

    .checkout-detail-row {
        flex-direction: column;
        gap: 0.25rem;
    }

    .checkout-detail-value {
        text-align: left;
    }

    .checkout-form-row-two,
    .checkout-form-row-three {
        grid-template-columns: 1fr;
    }

    .checkout-actions {
        flex-direction: column;
        gap: 1rem;
    }

    .btn-complete-purchase {
        width: 100%;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkoutForm = document.getElementById('checkout-form');
    
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            // Form validation will be handled by HTML5 validation
            // You can add additional custom validation here if needed
        });
    }

    // When URL has #checkout-affiliate, scroll to affiliate section and focus input (after Apply/Remove redirect)
    if (window.location.hash === '#checkout-affiliate') {
        var affiliateSection = document.getElementById('checkout-affiliate');
        var affiliateInput = document.getElementById('checkout_affiliate_code');
        if (affiliateSection) {
            affiliateSection.scrollIntoView({ behavior: 'instant', block: 'start' });
            if (affiliateInput) setTimeout(function() { affiliateInput.focus(); }, 100);
        }
    }

    // Buyer country dropdown (same as profile page)
    var buyerCountryTrigger = document.getElementById('buyer_country_trigger');
    var buyerCountryPanel = document.getElementById('buyer_country_panel');
    var buyerCountryInput = document.getElementById('buyer_country');
    var buyerCountryDisplay = document.getElementById('buyer_country_display');
    var buyerCountrySearch = document.getElementById('buyer_country_search');
    var buyerCountryOptions = document.getElementById('buyer_country_options');
    var buyerCountryWrap = buyerCountryTrigger && buyerCountryTrigger.closest('.checkout-country-dropdown-wrap');

    function buyerCountryOpen() {
        if (buyerCountryWrap) buyerCountryWrap.classList.add('is-open');
        if (buyerCountryPanel) buyerCountryPanel.setAttribute('aria-hidden', 'false');
        if (buyerCountryTrigger) buyerCountryTrigger.setAttribute('aria-expanded', 'true');
    }
    function buyerCountryClose() {
        if (buyerCountryWrap) buyerCountryWrap.classList.remove('is-open');
        if (buyerCountryPanel) buyerCountryPanel.setAttribute('aria-hidden', 'true');
        if (buyerCountryTrigger) buyerCountryTrigger.setAttribute('aria-expanded', 'false');
    }
    function filterBuyerCountries() {
        var q = (buyerCountrySearch && buyerCountrySearch.value) ? buyerCountrySearch.value.trim().toLowerCase() : '';
        var options = buyerCountryOptions ? buyerCountryOptions.querySelectorAll('.checkout-country-option') : [];
        options.forEach(function(opt) {
            var label = (opt.getAttribute('data-label') || opt.textContent || '').toLowerCase();
            opt.style.display = !q || label.indexOf(q) !== -1 ? '' : 'none';
        });
    }

    if (buyerCountryTrigger) {
        buyerCountryTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            var isOpen = buyerCountryWrap.classList.toggle('is-open');
            buyerCountryPanel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
            buyerCountryTrigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            if (isOpen) {
                if (buyerCountrySearch) {
                    buyerCountrySearch.value = '';
                    buyerCountrySearch.focus();
                    filterBuyerCountries();
                }
            }
        });
    }
    if (buyerCountrySearch) {
        buyerCountrySearch.addEventListener('input', filterBuyerCountries);
        buyerCountrySearch.addEventListener('keydown', function(e) { e.stopPropagation(); });
    }
    if (buyerCountryOptions) {
        buyerCountryOptions.querySelectorAll('.checkout-country-option').forEach(function(opt) {
            opt.addEventListener('click', function(e) {
                e.preventDefault();
                var val = this.getAttribute('data-value');
                var text = this.getAttribute('data-label') || this.textContent;
                if (buyerCountryInput) buyerCountryInput.value = val;
                if (buyerCountryDisplay) buyerCountryDisplay.textContent = text || 'Select Country/Region';
                buyerCountryOptions.querySelectorAll('.checkout-country-option').forEach(function(o) { o.classList.remove('checkout-country-option-selected'); });
                this.classList.add('checkout-country-option-selected');
                if (buyerCountrySearch) buyerCountrySearch.value = '';
                buyerCountryClose();
            });
        });
    }
    document.addEventListener('click', function(e) {
        if (buyerCountryWrap && !buyerCountryWrap.contains(e.target)) buyerCountryClose();
    });

    // Category: show/hide academician and organization fields
    var buyerCategorySelect = document.getElementById('buyer_category');
    var academicianFieldsBlock = document.getElementById('checkout_academician_fields');
    var buyerStudentId = document.getElementById('buyer_student_id');
    var buyerAcademyInstitution = document.getElementById('buyer_academy_institution');
    var organizationFieldsBlock = document.getElementById('checkout_organization_fields');
    var buyerCompanyName = document.getElementById('buyer_company_name');
    var buyerBusinessRegNumber = document.getElementById('buyer_business_registration_number');

    function toggleAcademicianFields() {
        var isAcademician = buyerCategorySelect && buyerCategorySelect.value === 'academician';
        if (academicianFieldsBlock) {
            academicianFieldsBlock.style.display = isAcademician ? '' : 'none';
        }
        if (buyerStudentId) {
            buyerStudentId.required = isAcademician;
            if (!isAcademician) buyerStudentId.value = '';
        }
        if (buyerAcademyInstitution) {
            buyerAcademyInstitution.required = isAcademician;
            if (!isAcademician) buyerAcademyInstitution.value = '';
        }
    }
    function toggleOrganizationFields() {
        var isOrganization = buyerCategorySelect && buyerCategorySelect.value === 'organization';
        if (organizationFieldsBlock) {
            organizationFieldsBlock.style.display = isOrganization ? '' : 'none';
        }
        if (buyerCompanyName) {
            buyerCompanyName.required = isOrganization;
            if (!isOrganization) buyerCompanyName.value = '';
        }
        if (buyerBusinessRegNumber) {
            buyerBusinessRegNumber.required = isOrganization;
            if (!isOrganization) buyerBusinessRegNumber.value = '';
        }
    }
    function toggleCategoryFields() {
        toggleAcademicianFields();
        toggleOrganizationFields();
    }
    if (buyerCategorySelect) {
        buyerCategorySelect.addEventListener('change', toggleCategoryFields);
        toggleCategoryFields();
    }

    // Use profile data checkbox
    var useProfileCheckbox = document.getElementById('use_profile_data');
    if (useProfileCheckbox && typeof window.checkoutProfileData !== 'undefined') {
        useProfileCheckbox.addEventListener('change', function() {
            var data = window.checkoutProfileData;
            var nameEl = document.getElementById('buyer_name');
            var emailEl = document.getElementById('buyer_email');
            var contactEl = document.getElementById('buyer_contact');
            var genderEl = document.getElementById('buyer_gender');
            var nricEl = document.getElementById('buyer_nric_passport');
            var streetEl = document.getElementById('buyer_street_address');
            var townEl = document.getElementById('buyer_town_city');
            var stateEl = document.getElementById('buyer_state');
            var postcodeEl = document.getElementById('buyer_postcode_zip');

            if (this.checked) {
                if (nameEl) nameEl.value = data.name || '';
                if (emailEl) emailEl.value = data.email || '';
                if (contactEl) contactEl.value = data.contact_number || '';
                if (genderEl) genderEl.value = data.gender || '';
                if (nricEl) nricEl.value = data.nric_passport || '';
                if (streetEl) streetEl.value = data.street_address || '';
                if (townEl) townEl.value = data.town_city || '';
                if (stateEl) stateEl.value = data.state || '';
                if (postcodeEl) postcodeEl.value = data.postcode_zip || '';
                if (buyerCountryInput) buyerCountryInput.value = data.country_region || '';
                if (buyerCountryDisplay) buyerCountryDisplay.textContent = data.country_display || data.country_region || 'Select Country/Region';
                if (buyerCountryOptions) {
                    buyerCountryOptions.querySelectorAll('.checkout-country-option').forEach(function(o) {
                        o.classList.toggle('checkout-country-option-selected', o.getAttribute('data-value') === (data.country_region || ''));
                    });
                }
            } else {
                if (nameEl) nameEl.value = '';
                if (emailEl) emailEl.value = '';
                if (contactEl) contactEl.value = '';
                if (genderEl) genderEl.value = '';
                if (nricEl) nricEl.value = '';
                if (streetEl) streetEl.value = '';
                if (townEl) townEl.value = '';
                if (stateEl) stateEl.value = '';
                if (postcodeEl) postcodeEl.value = '';
                if (buyerCountryInput) buyerCountryInput.value = '';
                if (buyerCountryDisplay) buyerCountryDisplay.textContent = 'Select Country/Region';
                if (buyerCountryOptions) {
                    buyerCountryOptions.querySelectorAll('.checkout-country-option').forEach(function(o) {
                        o.classList.remove('checkout-country-option-selected');
                    });
                }
            }
        });
    }

    // Order summary sticky: follow scroll when CSS sticky is broken (e.g. by overflow on body)
    (function() {
        var panel = document.getElementById('checkout-right-panel');
        var card = document.getElementById('checkout-order-summary-card');
        if (!panel || !card) return;
        var stickyTop = 80;
        var ticking = false;

        function updateSticky() {
            if (window.innerWidth <= 1024) {
                card.classList.remove('is-sticky-fixed');
                card.style.left = '';
                card.style.width = '';
                panel.style.minHeight = '';
                return;
            }
            var rect = panel.getBoundingClientRect();
            if (rect.top <= stickyTop) {
                card.classList.add('is-sticky-fixed');
                card.style.left = rect.left + 'px';
                card.style.width = rect.width + 'px';
                panel.style.minHeight = (rect.height) + 'px';
            } else {
                card.classList.remove('is-sticky-fixed');
                card.style.left = '';
                card.style.width = '';
                panel.style.minHeight = '';
            }
        }

        function requestTick() {
            if (!ticking) {
                requestAnimationFrame(function() {
                    updateSticky();
                    ticking = false;
                });
                ticking = true;
            }
        }

        window.addEventListener('scroll', requestTick, { passive: true });
        window.addEventListener('resize', requestTick);
        updateSticky();
    })();
});
</script>
@endpush
@endsection
