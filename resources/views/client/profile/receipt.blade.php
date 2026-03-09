<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $order->stripe_payment_intent_id ?? $order->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .invoice-header {
            margin-bottom: 24px;
            padding: 12px 0;
            text-align: center;
            background: #000000;
            color: #ffffff;
        }
        .invoice-title { font-size: 24px; font-weight: bold; color: #ffffff; margin: 0 0 6px 0; letter-spacing: 0.05em; }
        .invoice-subtitle { font-size: 10px; color: #D1D5DB; margin: 0; }
        .invoice-meta { margin-bottom: 20px; }
        .invoice-meta table { width: 100%; }
        .invoice-meta td { padding: 2px 0; vertical-align: top; }
        .invoice-meta .label { color: #6b7280; width: 120px; }
        h3 { font-size: 12px; margin: 16px 0 8px 0; color: #111; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.items th, table.items td { border: 1px solid #e5e7eb; padding: 6px 8px; }
        table.items th { background: #f9fafb; font-weight: 600; font-size: 10px; }
        table.items td { font-size: 10px; }
        table.items.items-centered th, table.items.items-centered td { text-align: center; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals { margin-top: 16px; }
        .totals table { width: 100%; max-width: 320px; margin-left: auto; border-collapse: collapse; }
        .totals td { padding: 4px 8px; border: 1px solid #e5e7eb; }
        .totals .total-row { font-weight: bold; background: #F9FAFB; color: #1F2937; font-size: 12px; }
        .fee-breakdown { background: #f9fafb; padding: 6px 8px; border: 1px solid #e5e7eb; border-top: none; font-size: 9px; color: #6b7280; }
        .fee-breakdown ul { margin: 4px 0 0 14px; padding: 0; }
        .footer { margin-top: 28px; font-size: 9px; color: #6b7280; text-align: center; }
    </style>
</head>
<body>
    <div class="invoice-header">
        @if(!empty($logoDataUri))
            <img src="{!! $logoDataUri !!}" alt="BINA" style="max-height:48px; width:auto; margin:0 auto 6px auto; display:block;" />
        @else
            <h1 class="invoice-title">BINA</h1>
        @endif
    </div>

    <div class="invoice-meta">
        <table>
            <tr>
                <td class="label">Reference</td>
                <td>{{ $order->stripe_payment_intent_id ?? $order->id }}</td>
            </tr>
            <tr>
                <td class="label">Date</td>
                <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
            </tr>
            @php
                $receiptStatus = $order->status === 'refunded' ? 'Paid' : ucfirst($order->status);
            @endphp
            <tr>
                <td class="label">Status</td>
                <td>{{ $receiptStatus }}</td>
            </tr>
            <tr>
                <td class="label">Payment method</td>
                <td>{{ $order->payment_method ? ucfirst($order->payment_method) : '-' }}</td>
            </tr>
        </table>
    </div>

    @php $buyer = $order->buyer_snapshot ?? []; @endphp
    <h3>Bill To</h3>
    <table class="invoice-meta">
        <tr><td class="label">Name</td><td>{{ $buyer['buyer_name'] ?? '-' }}</td></tr>
        <tr><td class="label">Email</td><td>{{ $buyer['buyer_email'] ?? '-' }}</td></tr>
        <tr><td class="label">Contact</td><td>{{ $buyer['buyer_contact'] ?? '-' }}</td></tr>
        <tr><td class="label">Address</td><td>{{ trim(implode(', ', array_filter([$buyer['buyer_street_address'] ?? '', $buyer['buyer_town_city'] ?? '', $buyer['buyer_state'] ?? '', $buyer['buyer_postcode_zip'] ?? '', $buyer['buyer_country'] ?? '']))) ?: '-' }}</td></tr>
    </table>

    <h3>Order Items</h3>
    <table class="items items-centered">
        <thead>
            <tr>
                <th>Event</th>
                <th>Ticket</th>
                <th>Qty</th>
                <th>Unit price (RM)</th>
                <th>Subtotal (RM)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->event->name ?? '-' }}</td>
                <td>{{ $item->ticket->name ?? '-' }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->unit_price_cents / 100, 2) }}</td>
                <td>{{ number_format($item->unit_price_cents * $item->quantity / 100, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @php
        $subtotalCents = $order->items->sum(fn($i) => $i->unit_price_cents * $i->quantity);
        $discountCents = 0;
        if ($order->promoCode) {
            $discountCents = min((int) round($order->promoCode->discount * 100), $subtotalCents);
        }
        $afterDiscountCents = $subtotalCents - $discountCents;
        $processingFeeCents = max(0, $order->total_amount_cents - $afterDiscountCents);

        // Fee breakdown labels (same as purchase history order modal)
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
    <div class="totals">
        <table>
            <tr>
                <td>Subtotal</td>
                <td class="text-right">RM {{ number_format($subtotalCents / 100, 2) }}</td>
            </tr>
            @if($discountCents > 0)
            <tr>
                <td>Promo discount ({{ $order->promoCode->code ?? '' }})</td>
                <td class="text-right">- RM {{ number_format($discountCents / 100, 2) }}</td>
            </tr>
            @endif
            @if($processingFeeCents > 0)
            <tr>
                <td>Payment processing fee</td>
                <td class="text-right">RM {{ number_format($processingFeeCents / 100, 2) }}</td>
            </tr>
            @if(count($feeDescriptionLines) > 0)
            <tr>
                <td colspan="2" class="fee-breakdown">
                    <span>Includes:</span>
                    <ul>
                        @foreach($feeDescriptionLines as $line)
                            <li>{{ $line }}</li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            @endif
            @endif
            <tr class="total-row">
                <td>Total ({{ strtoupper($order->currency) }})</td>
                <td class="text-right">RM {{ number_format($order->total_amount_cents / 100, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>This is an automated receipt. Thank you for your purchase.</p>
    </div>
</body>
</html>

