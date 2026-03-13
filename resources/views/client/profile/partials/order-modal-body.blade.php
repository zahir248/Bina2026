<div class="client-order-modal-body">
    <!-- Order Summary -->
    <div class="client-order-modal-section">
        <h5 class="client-order-modal-heading">Order Summary</h5>
        <table class="client-order-modal-table">
            <tr>
                <td class="client-order-modal-muted">Reference</td>
                <td><span class="text-break">{{ $order->stripe_payment_intent_id ?? '-' }}</span></td>
            </tr>
            <tr>
                <td class="client-order-modal-muted">Status</td>
                <td>
                    <span class="purchase-status-badge purchase-status-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
                </td>
            </tr>
            <tr>
                <td class="client-order-modal-muted">Total</td>
                <td>RM {{ number_format($order->total_amount_cents / 100, 2) }} ({{ strtoupper($order->currency) }})</td>
            </tr>
            <tr>
                <td class="client-order-modal-muted">Payment method</td>
                <td>{{ $order->payment_method ? ucfirst($order->payment_method) : '-' }}</td>
            </tr>
            <tr>
                <td class="client-order-modal-muted">Date</td>
                <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
            </tr>
            @if($order->promoCode)
                <tr>
                    <td class="client-order-modal-muted">Promo code</td>
                    <td>{{ $order->promoCode->code }}</td>
                </tr>
            @endif
            @if($order->affiliateCode)
                <tr>
                    <td class="client-order-modal-muted">Affiliate</td>
                    <td>{{ $order->affiliateCode->code }}</td>
                </tr>
            @endif
        </table>
    </div>

    @if($order->refund_status || $order->reason || (is_array($order->refund_proof_paths) && count($order->refund_proof_paths) > 0))
    <div class="client-order-modal-section">
        <h5 class="client-order-modal-heading">
            {{ $order->status === 'cancelled' && !$order->refund_status ? 'Cancellation Summary' : 'Refund Summary' }}
        </h5>
        <table class="client-order-modal-table">
            @if($order->refund_status)
            <tr>
                <td class="client-order-modal-muted">Refund status</td>
                <td>
                    <span class="purchase-status-badge {{ $order->refund_status === 'pending' ? 'purchase-status-pending' : ($order->refund_status === 'approved' ? 'purchase-status-paid' : ($order->refund_status === 'rejected' ? 'purchase-status-rejected' : 'purchase-status-refunded')) }}">
                        {{ $order->refund_status === 'pending' ? 'Reviewing' : ucfirst($order->refund_status) }}
                    </span>
                    @if($order->refund_status === 'approved')
                        <span class="text-muted small d-block mt-1">Refund can take 5–10 business days to show up in your account.</span>
                    @endif
                </td>
            </tr>
            @endif
            @if($order->reason)
            <tr>
                <td class="client-order-modal-muted">Reason</td>
                <td><span class="text-break">{{ $order->reason }}</span></td>
            </tr>
            @endif
            @if(is_array($order->refund_proof_paths) && count($order->refund_proof_paths) > 0)
            <tr>
                <td class="client-order-modal-muted">Refund proof</td>
                <td>
                    @foreach($order->refund_proof_paths as $idx => $path)
                        <div>
                            <a href="{{ route('storage.serve', ['path' => $path]) }}" target="_blank">View image {{ $idx + 1 }}</a>
                        </div>
                    @endforeach
                </td>
            </tr>
            @endif
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

        // Fee breakdown labels (same logic as checkout) for payment method description
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
            if ($feeFpxLabel) {
                $feeDescriptionLines[] = $feeFpxLabel;
            }
        } elseif ($paymentMethod === 'card') {
            if ($feeDomesticLabel) {
                $feeDescriptionLines[] = $feeDomesticLabel;
            }
            if ($feeInternationalExtra) {
                $feeDescriptionLines[] = $feeInternationalExtra;
            }
            $feeDescriptionLines[] = $feeCurrencyNote;
        } else {
            if ($feeDomesticLabel) {
                $feeDescriptionLines[] = $feeDomesticLabel;
            }
            if ($feeInternationalExtra) {
                $feeDescriptionLines[] = $feeInternationalExtra;
            }
            $feeDescriptionLines[] = $feeCurrencyNote;
            if ($feeFpxLabel) {
                $feeDescriptionLines[] = $feeFpxLabel;
            }
        }
    @endphp
    <!-- Payment Summary -->
    <div class="client-order-modal-section">
        <h5 class="client-order-modal-heading">Payment Summary</h5>
        <table class="client-order-modal-table client-order-modal-table-bordered">
            <tbody>
                <tr>
                    <td class="client-order-modal-muted" style="width: 180px;">Subtotal</td>
                    <td style="text-align: right;">RM {{ number_format($subtotalCents / 100, 2) }}</td>
                </tr>
                @if($discountCents > 0)
                    <tr>
                        <td class="client-order-modal-muted">Promo discount ({{ $order->promoCode->code ?? '' }})</td>
                        <td style="text-align: right;">- RM {{ number_format($discountCents / 100, 2) }}</td>
                    </tr>
                @endif
                @if($processingFeeCents > 0)
                    <tr>
                        <td class="client-order-modal-muted">Payment processing fee</td>
                        <td style="text-align: right;">RM {{ number_format($processingFeeCents / 100, 2) }}</td>
                    </tr>
                    @if(count($feeDescriptionLines) > 0)
                        <tr>
                            <td colspan="2" class="client-order-modal-fee-breakdown">
                                <span class="client-order-modal-muted" style="font-size: 0.85em;">Includes:</span>
                                <ul class="client-order-modal-fee-list">
                                    @foreach($feeDescriptionLines as $line)
                                        <li>{{ $line }}</li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @endif
                @endif
                <tr>
                    <td class="client-order-modal-muted" style="font-weight: 600;">Total</td>
                    <td style="text-align: right; font-weight: 600;">RM {{ number_format($order->total_amount_cents / 100, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Buyer Details -->
    @php $buyer = $order->buyer_snapshot ?? []; @endphp
    <div class="client-order-modal-section">
        <h5 class="client-order-modal-heading">Buyer Details</h5>
        <table class="client-order-modal-table client-order-modal-table-bordered">
            <tbody>
                <tr><td class="client-order-modal-muted" style="width: 140px;">Name</td><td>{{ $buyer['buyer_name'] ?? '-' }}</td></tr>
                <tr><td class="client-order-modal-muted">Email</td><td>{{ $buyer['buyer_email'] ?? '-' }}</td></tr>
                <tr><td class="client-order-modal-muted">Gender</td><td>{{ ucfirst($buyer['buyer_gender'] ?? '-') }}</td></tr>
                <tr><td class="client-order-modal-muted">NRIC/Passport</td><td>{{ $buyer['buyer_nric_passport'] ?? '-' }}</td></tr>
                <tr><td class="client-order-modal-muted">Contact</td><td>{{ $buyer['buyer_contact'] ?? '-' }}</td></tr>
                <tr><td class="client-order-modal-muted">Country</td><td>{{ $buyer['buyer_country'] ?? '-' }}</td></tr>
                <tr><td class="client-order-modal-muted">Address</td><td>{{ $buyer['buyer_street_address'] ?? '-' }}, {{ $buyer['buyer_town_city'] ?? '' }} {{ $buyer['buyer_state'] ?? '' }} {{ $buyer['buyer_postcode_zip'] ?? '' }}</td></tr>
                <tr><td class="client-order-modal-muted">Category</td><td>{{ ucfirst($buyer['buyer_category'] ?? '-') }}</td></tr>
                @if(!empty($buyer['buyer_student_id']) || !empty($buyer['buyer_academy_institution']))
                    <tr><td class="client-order-modal-muted">Student ID</td><td>{{ $buyer['buyer_student_id'] ?? '-' }}</td></tr>
                    <tr><td class="client-order-modal-muted">Academy/Institution</td><td>{{ $buyer['buyer_academy_institution'] ?? '-' }}</td></tr>
                @endif
                @if(!empty($buyer['buyer_company_name']) || !empty($buyer['buyer_business_registration_number']))
                    <tr><td class="client-order-modal-muted">Company</td><td>{{ $buyer['buyer_company_name'] ?? '-' }}</td></tr>
                    <tr><td class="client-order-modal-muted">Business reg. no.</td><td>{{ $buyer['buyer_business_registration_number'] ?? '-' }}</td></tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Order Items -->
    <div class="client-order-modal-section">
        <h5 class="client-order-modal-heading">Order Items</h5>
        <div class="client-order-modal-table-wrap">
            <table class="client-order-modal-table client-order-modal-table-bordered client-order-modal-table-order-items">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Ticket</th>
                        <th>Qty</th>
                        <th>Unit price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->event->name ?? '-' }}</td>
                            <td>{{ $item->ticket->name ?? '-' }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>RM {{ number_format($item->unit_price_cents / 100, 2) }}</td>
                            <td>RM {{ number_format($item->unit_price_cents * $item->quantity / 100, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Ticket Holders -->
    @php $holders = $order->ticket_holders_snapshot ?? []; @endphp
    @if(count($holders) > 0)
        <div class="client-order-modal-section">
            <h5 class="client-order-modal-heading">Ticket Holders</h5>
            <div class="client-order-modal-table-wrap">
                <table class="client-order-modal-table client-order-modal-table-bordered">
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
        </div>
    @endif
</div>
