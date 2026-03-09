<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class OrdersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    private int $rowNumber = 1;

    public function __construct(
        private Collection $orders,
        private bool $refundFocus = false
    ) {}

    public function collection(): Collection
    {
        return $this->orders;
    }

    public function headings(): array
    {
        if ($this->refundFocus) {
            return [
                'No.',
                // Refund-focused: refund details first
                'Refund Status',
                'Reason (refund/cancel)',
                'Refund Proof',
                'Reference',
                'Order Date',
                'Status',
                'Payment Method',
                'Currency',
                'Total (RM)',
                'Subtotal (RM)',
                'Promo Discount (RM)',
                'Processing Fee (RM)',
                'Promo Code',
                'Affiliate Code',
                // Buyer details
                'Buyer Name',
                'Buyer Email',
                'Buyer Gender',
                'Buyer NRIC/Passport',
                'Buyer Contact',
                'Buyer Country',
                'Buyer Address',
                'Buyer Category',
                'Buyer Student ID',
                'Buyer Academy/Institution',
                'Buyer Company',
                'Buyer Business Reg No',
                'Account Name',
                'Account Email',
                'Order Items (Event, Ticket, Qty, Unit Price, Subtotal)',
                'Ticket Holders (Name, Email, Gender, NRIC, Contact, Company)',
            ];
        }

        return [
            'No.',
            'Reference',
            'Order Date',
            'Status',
            'Refund Status',
            'Payment Method',
            'Currency',
            'Reason (refund/cancel)',
            'Refund Proof',
            'Promo Code',
            'Affiliate Code',
            'Subtotal (RM)',
            'Promo Discount (RM)',
            'Processing Fee (RM)',
            'Total (RM)',
            'Buyer Name',
            'Buyer Email',
            'Buyer Gender',
            'Buyer NRIC/Passport',
            'Buyer Contact',
            'Buyer Country',
            'Buyer Address',
            'Buyer Category',
            'Buyer Student ID',
            'Buyer Academy/Institution',
            'Buyer Company',
            'Buyer Business Reg No',
            'Account Name',
            'Account Email',
            'Order Items (Event, Ticket, Qty, Unit Price, Subtotal)',
            'Ticket Holders (Name, Email, Gender, NRIC, Contact, Company)',
        ];
    }

    public function map($order): array
    {
        $buyer = $order->buyer_snapshot ?? [];
        $buyerAddress = trim(implode(', ', array_filter([
            $buyer['buyer_street_address'] ?? '',
            $buyer['buyer_town_city'] ?? '',
            $buyer['buyer_state'] ?? '',
            $buyer['buyer_postcode_zip'] ?? '',
        ]))) ?: '-';
        if (!empty($buyer['buyer_country'] ?? '')) {
            $buyerAddress = $buyerAddress . ', ' . ($buyer['buyer_country'] ?? '');
        }
        if ($buyerAddress === ', ') {
            $buyerAddress = '-';
        }

        // Payment summary (same logic as modal/receipt)
        $subtotalCents = $order->items->sum(fn ($i) => $i->unit_price_cents * $i->quantity);
        $discountCents = 0;
        if ($order->promoCode) {
            $discountCents = min((int) round($order->promoCode->discount * 100), $subtotalCents);
        }
        $afterDiscountCents = $subtotalCents - $discountCents;
        $processingFeeCents = max(0, $order->total_amount_cents - $afterDiscountCents);

        $subtotalRm = number_format($subtotalCents / 100, 2);
        $discountRm = number_format($discountCents / 100, 2);
        $feeRm = number_format($processingFeeCents / 100, 2);
        $totalRm = $order->total_amount_cents ? number_format($order->total_amount_cents / 100, 2) : '0.00';

        $refundProof = '-';
        if (is_array($order->refund_proof_paths) && count($order->refund_proof_paths) > 0) {
            $refundProof = implode('; ', $order->refund_proof_paths);
        }

        $orderItemsText = $order->items->map(function ($item) {
            $eventName = $item->event?->name ?? 'Event';
            $categoryName = $item->event?->category?->name ?? 'Uncategorized';
            $eventDisplay = "{$eventName} ({$categoryName})";
            $ticketName = $item->ticket?->name ?? 'Ticket';
            $unitPrice = number_format($item->unit_price_cents / 100, 2);
            $lineTotal = number_format($item->unit_price_cents * $item->quantity / 100, 2);
            return "{$eventDisplay} | {$ticketName} x {$item->quantity} @ RM {$unitPrice} = RM {$lineTotal}";
        })->implode("\n");

        $holders = $order->ticket_holders_snapshot ?? [];
        $holdersText = collect($holders)->map(function ($h, $i) {
            $num = $i + 1;
            $name = $h['full_name'] ?? '-';
            $email = $h['email'] ?? '-';
            $gender = ucfirst($h['gender'] ?? '-');
            $nric = $h['nric_passport'] ?? '-';
            $contact = $h['contact_number'] ?? '-';
            $company = $h['company_name'] ?? '-';
            return "#{$num}: {$name} | {$email} | {$gender} | {$nric} | {$contact} | {$company}";
        })->implode("\n");
        if ($holdersText === '') {
            $holdersText = '-';
        }

        $baseRow = [
            $this->rowNumber++,
            $order->stripe_payment_intent_id ?? '-',
            $order->created_at?->format('Y-m-d H:i') ?? '-',
            $order->status ?? '-',
            $order->refund_status ?? '-',
            $order->payment_method ? ucfirst($order->payment_method) : '-',
            strtoupper($order->currency ?? 'MYR'),
            $order->reason ?? '-',
            $refundProof,
            $order->promoCode?->code ?? '-',
            $order->affiliateCode?->code ?? '-',
            $subtotalRm,
            $discountCents > 0 ? "- {$discountRm}" : '0.00',
            $feeRm,
            $totalRm,
            $buyer['buyer_name'] ?? $order->user?->name ?? '-',
            $buyer['buyer_email'] ?? $order->user?->email ?? '-',
            ucfirst($buyer['buyer_gender'] ?? '-'),
            $buyer['buyer_nric_passport'] ?? '-',
            $buyer['buyer_contact'] ?? '-',
            $buyer['buyer_country'] ?? '-',
            $buyerAddress,
            ucfirst($buyer['buyer_category'] ?? '-'),
            $buyer['buyer_student_id'] ?? '-',
            $buyer['buyer_academy_institution'] ?? '-',
            $buyer['buyer_company_name'] ?? '-',
            $buyer['buyer_business_registration_number'] ?? '-',
            $order->user?->name ?? '-',
            $order->user?->email ?? '-',
            $orderItemsText,
            $holdersText,
        ];

        if ($this->refundFocus) {
            // Refund-focused order: No., Refund Status, Reason, Refund Proof, Reference, Order Date, Status, Payment, Currency, Total, Subtotal, Discount, Fee, Promo, Affiliate, then buyer/account/items/holders
            return [
                $baseRow[0],   // No.
                $baseRow[4],  // Refund Status
                $baseRow[7],  // Reason
                $baseRow[8],  // Refund Proof
                $baseRow[1],  // Reference
                $baseRow[2],  // Order Date
                $baseRow[3],  // Status
                $baseRow[5],  // Payment Method
                $baseRow[6],  // Currency
                $baseRow[14], // Total (RM)
                $baseRow[11], // Subtotal (RM)
                $baseRow[12], // Promo Discount
                $baseRow[13], // Processing Fee
                $baseRow[9],  // Promo Code
                $baseRow[10], // Affiliate Code
                $baseRow[15],
                $baseRow[16],
                $baseRow[17],
                $baseRow[18],
                $baseRow[19],
                $baseRow[20],
                $baseRow[21],
                $baseRow[22],
                $baseRow[23],
                $baseRow[24],
                $baseRow[25],
                $baseRow[26],
                $baseRow[27],
                $baseRow[28],
                $baseRow[29],
                $baseRow[30],
            ];
        }

        return $baseRow;
    }
}
