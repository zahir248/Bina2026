@php $order->load(['user', 'items.ticket', 'items.event', 'promoCode', 'affiliateCode']); @endphp
<!-- Order Summary -->
<div class="row mb-4">
    <div class="col-md-6">
        <h5 class="mb-3" style="font-size: 1rem; font-weight: 600;">Order Summary</h5>
        <table class="table table-sm">
            <tr>
                <td class="text-muted">Reference</td>
                <td><span class="text-break">{{ $order->stripe_payment_intent_id ?? '-' }}</span></td>
            </tr>
            <tr>
                <td class="text-muted">Status</td>
                <td>
                    <span class="badge {{ $order->status === 'paid' ? 'bg-success' : ($order->status === 'failed' ? 'bg-danger' : 'bg-warning text-dark') }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </td>
            </tr>
            <tr>
                <td class="text-muted">Total</td>
                <td>RM {{ number_format($order->total_amount_cents / 100, 2) }} ({{ strtoupper($order->currency) }})</td>
            </tr>
            <tr>
                <td class="text-muted">Payment method</td>
                <td>{{ $order->payment_method ? ucfirst($order->payment_method) : '-' }}</td>
            </tr>
            <tr>
                <td class="text-muted">Date</td>
                <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
            </tr>
            @if($order->promoCode)
                <tr>
                    <td class="text-muted">Promo code</td>
                    <td>{{ $order->promoCode->code }}</td>
                </tr>
            @endif
            @if($order->affiliateCode)
                <tr>
                    <td class="text-muted">Affiliate</td>
                    <td>{{ $order->affiliateCode->code }}</td>
                </tr>
            @endif
        </table>
    </div>
    <div class="col-md-6">
        <h5 class="mb-3" style="font-size: 1rem; font-weight: 600;">User</h5>
        <p class="mb-0"><strong>{{ $order->user->name ?? '-' }}</strong></p>
        <p class="mb-0 text-muted">{{ $order->user->email ?? '-' }}</p>
    </div>
</div>

@if($order->refund_status || $order->reason || (is_array($order->refund_proof_paths) && count($order->refund_proof_paths) > 0))
<h5 class="mb-3" style="font-size: 1rem; font-weight: 600;">
    {{ $order->status === 'cancelled' && !$order->refund_status ? 'Cancellation Summary' : 'Refund Summary' }}
</h5>
<div class="table-responsive mb-4">
    <table class="table table-sm">
        <tbody>
            @if($order->refund_status)
            <tr>
                <td class="text-muted" style="width: 200px;">Refund status</td>
                <td>
                    <span class="badge {{ $order->refund_status === 'pending' ? 'bg-warning text-dark' : 'bg-secondary' }}">
                        {{ $order->refund_status === 'pending' ? 'Reviewing' : ucfirst($order->refund_status) }}
                    </span>
                </td>
            </tr>
            @endif
            @if($order->reason)
            <tr>
                <td class="text-muted">Reason</td>
                <td><span class="text-break">{{ $order->reason }}</span></td>
            </tr>
            @endif
            @if(is_array($order->refund_proof_paths) && count($order->refund_proof_paths) > 0)
            <tr>
                <td class="text-muted">Refund proof</td>
                <td>
                    @foreach($order->refund_proof_paths as $idx => $path)
                        <div>
                            <a href="{{ route('storage.serve', ['path' => $path]) }}" target="_blank">View image {{ $idx + 1 }}</a>
                        </div>
                    @endforeach
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@endif

@php
    $subtotalCents = $order->items->sum(fn($i) => $i->unit_price_cents * $i->quantity);
    $discountCents = 0;
    if ($order->promoCode) {
        $discountCents = min((int) round($order->promoCode->discount * 100), $subtotalCents);
    }
    $afterDiscountCents = $subtotalCents - $discountCents;
    $processingFeeCents = max(0, $order->total_amount_cents - $afterDiscountCents);

    $feeFixedCents = (int) config('services.stripe.fee_fixed_cents', 0);
    $feePctDomestic = (float) config('services.stripe.fee_percentage', 0);
    $feePctInternational = (float) config('services.stripe.fee_percentage_international', 0);
    if ($feePctInternational <= 0) {
        $feePctInternational = $feePctDomestic;
    }
    $feeFixedRm = $feeFixedCents / 100;
    $pctDomesticStr = $feePctDomestic == (int) $feePctDomestic ? (int) $feePctDomestic : number_format($feePctDomestic, 1);
    $feeBaseLabel = ($feePctDomestic > 0 || $feeFixedCents > 0) ? $pctDomesticStr . '% + RM ' . number_format($feeFixedRm, 2) : null;
    $feeDomesticLabel = $feeBaseLabel ? $feeBaseLabel . ' per successful transaction for domestic cards' : null;
    $feeInternationalExtra = ($feePctInternational > 0 && $feePctInternational != $feePctDomestic)
        ? '+ ' . (($feePctInternational - $feePctDomestic) == (int)($feePctInternational - $feePctDomestic) ? (int)($feePctInternational - $feePctDomestic) : number_format($feePctInternational - $feePctDomestic, 1)) . '% for international cards'
        : null;
    $feeCurrencyNote = '+ 2% if currency conversion is required';
    $feeFpxLabel = $feeBaseLabel ? $feeBaseLabel . ' FPX' : null;

    $paymentMethod = strtolower($order->payment_method ?? '');
    $feeDescriptionLines = [];
    if ($paymentMethod === 'fpx') {
        if ($feeFpxLabel) $feeDescriptionLines[] = $feeFpxLabel;
    } elseif ($paymentMethod === 'card') {
        if ($feeDomesticLabel) $feeDescriptionLines[] = $feeDomesticLabel;
        if ($feeInternationalExtra) $feeDescriptionLines[] = $feeInternationalExtra;
        $feeDescriptionLines[] = $feeCurrencyNote;
    } else {
        if ($feeDomesticLabel) $feeDescriptionLines[] = $feeDomesticLabel;
        if ($feeInternationalExtra) $feeDescriptionLines[] = $feeInternationalExtra;
        $feeDescriptionLines[] = $feeCurrencyNote;
        if ($feeFpxLabel) $feeDescriptionLines[] = $feeFpxLabel;
    }
@endphp

<!-- Payment Summary -->
<h5 class="mb-2" style="font-size: 1rem; font-weight: 600;">Payment Summary</h5>
<div class="table-responsive mb-4">
    <table class="table table-bordered table-sm">
        <tbody>
            <tr>
                <td class="text-muted" style="width: 200px;">Subtotal</td>
                <td class="text-end">RM {{ number_format($subtotalCents / 100, 2) }}</td>
            </tr>
            @if($discountCents > 0)
                <tr>
                    <td class="text-muted">Promo discount ({{ $order->promoCode->code ?? '' }})</td>
                    <td class="text-end">- RM {{ number_format($discountCents / 100, 2) }}</td>
                </tr>
            @endif
            @if($processingFeeCents > 0)
                <tr>
                    <td class="text-muted">Payment processing fee</td>
                    <td class="text-end">RM {{ number_format($processingFeeCents / 100, 2) }}</td>
                </tr>
                @if(count($feeDescriptionLines) > 0)
                    <tr>
                        <td colspan="2" class="bg-light">
                            <span class="text-muted small">Includes:</span>
                            <ul class="mb-0 mt-1 ps-3 small text-muted">
                                @foreach($feeDescriptionLines as $line)
                                    <li>{{ $line }}</li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                @endif
            @endif
            <tr>
                <td class="text-muted fw-semibold">Total</td>
                <td class="text-end fw-semibold">RM {{ number_format($order->total_amount_cents / 100, 2) }}</td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Buyer Details -->
@php $buyer = $order->buyer_snapshot ?? []; @endphp
<h5 class="mb-2" style="font-size: 1rem; font-weight: 600;">Buyer Details</h5>
<div class="table-responsive mb-4">
    <table class="table table-bordered table-sm">
        <tbody>
            <tr><td class="text-muted" style="width: 200px;">Name</td><td>{{ $buyer['buyer_name'] ?? '-' }}</td></tr>
            <tr><td class="text-muted">Email</td><td>{{ $buyer['buyer_email'] ?? '-' }}</td></tr>
            <tr><td class="text-muted">Gender</td><td>{{ ucfirst($buyer['buyer_gender'] ?? '-') }}</td></tr>
            <tr><td class="text-muted">NRIC/Passport</td><td>{{ $buyer['buyer_nric_passport'] ?? '-' }}</td></tr>
            <tr><td class="text-muted">Contact</td><td>{{ $buyer['buyer_contact'] ?? '-' }}</td></tr>
            <tr><td class="text-muted">Country</td><td>{{ $buyer['buyer_country'] ?? '-' }}</td></tr>
            <tr><td class="text-muted">Address</td><td>{{ $buyer['buyer_street_address'] ?? '-' }}, {{ $buyer['buyer_town_city'] ?? '' }} {{ $buyer['buyer_state'] ?? '' }} {{ $buyer['buyer_postcode_zip'] ?? '' }}</td></tr>
            <tr><td class="text-muted">Category</td><td>{{ ucfirst($buyer['buyer_category'] ?? '-') }}</td></tr>
            @if(!empty($buyer['buyer_student_id']) || !empty($buyer['buyer_academy_institution']))
                <tr><td class="text-muted">Student ID</td><td>{{ $buyer['buyer_student_id'] ?? '-' }}</td></tr>
                <tr><td class="text-muted">Academy/Institution</td><td>{{ $buyer['buyer_academy_institution'] ?? '-' }}</td></tr>
            @endif
            @if(!empty($buyer['buyer_company_name']) || !empty($buyer['buyer_business_registration_number']))
                <tr><td class="text-muted">Company</td><td>{{ $buyer['buyer_company_name'] ?? '-' }}</td></tr>
                <tr><td class="text-muted">Business reg. no.</td><td>{{ $buyer['buyer_business_registration_number'] ?? '-' }}</td></tr>
            @endif
        </tbody>
    </table>
</div>

<!-- Order Items -->
<h5 class="mb-2" style="font-size: 1rem; font-weight: 600;">Order Items</h5>
<div class="table-responsive mb-4">
    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th>Event</th>
                <th>Ticket</th>
                <th class="text-center">Qty</th>
                <th class="text-end">Unit price</th>
                <th class="text-end">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->event->name ?? '-' }}</td>
                    <td>{{ $item->ticket->name ?? '-' }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-end">RM {{ number_format($item->unit_price_cents / 100, 2) }}</td>
                    <td class="text-end">RM {{ number_format($item->unit_price_cents * $item->quantity / 100, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@php $holders = $order->ticket_holders_snapshot ?? []; @endphp
@if(count($holders) > 0)
    <h5 class="mb-2" style="font-size: 1rem; font-weight: 600;">Ticket Holders</h5>
    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Gender</th>
                    <th>NRIC/Passport</th>
                    <th>Contact</th>
                    <th>Company</th>
                </tr>
            </thead>
            <tbody>
                @foreach($holders as $i => $h)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $h['full_name'] ?? '-' }}</td>
                        <td>{{ $h['email'] ?? '-' }}</td>
                        <td>{{ ucfirst($h['gender'] ?? '-') }}</td>
                        <td>{{ $h['nric_passport'] ?? '-' }}</td>
                        <td>{{ $h['contact_number'] ?? '-' }}</td>
                        <td>{{ $h['company_name'] ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
