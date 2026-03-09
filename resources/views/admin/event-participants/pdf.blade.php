<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $selectedEvent->name ?? 'Event Participants' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #333; line-height: 1.35; }
        .event-category { font-size: 14px; margin: 0 0 4px 0; color: #1e293b; font-weight: 700; text-align: center; }
        .event-name { font-size: 10px; color: #64748b; text-align: center; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        table th, table td { border: 1px solid #374151; padding: 4px 5px; text-align: center; vertical-align: middle; }
        table th { background: #e5e7eb; font-weight: 600; }
        .text-center { text-align: center; }
        .purchaser-name { font-weight: bold; }
        .buyer-details { white-space: pre-line; }
        .signature-cell { min-width: 24px; height: 28px; }
    </style>
</head>
<body>
    @if($selectedEvent && $selectedEvent->category)
        <p class="event-category">{{ $selectedEvent->category->name }}</p>
    @endif
    <p class="event-name">{{ $selectedEvent ? $selectedEvent->name : 'Event Participants' }}</p>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 22px;">No.</th>
                <th rowspan="2" style="width: 60px;">Reference number</th>
                <th rowspan="2" style="width: 52px;">Order date</th>
                <th rowspan="2" style="width: 95px;">Buyer details</th>
                <th rowspan="2" style="width: 75px;">Participant name</th>
                <th rowspan="2" style="width: 75px;">Email</th>
                <th rowspan="2" style="width: 40px;">Gender</th>
                <th rowspan="2" style="width: 55px;">NRIC/Passport</th>
                <th rowspan="2" style="width: 45px;">Contact</th>
                <th rowspan="2" style="width: 70px;">Company</th>
                <th rowspan="2" style="width: 50px;">Date/Time</th>
                <th colspan="2" style="width: 50px;">Signature</th>
            </tr>
            <tr>
                <th style="width: 25px;">AM</th>
                <th style="width: 25px;">PM</th>
            </tr>
        </thead>
        <tbody>
            @forelse($participants as $i => $p)
                @php
                    $h = $p->holder;
                    $order = $p->order;
                    $buyer = $order->buyer_snapshot ?? [];
                    $buyerName = $buyer['buyer_name'] ?? $order->user->name ?? '-';
                    $buyerEmail = $buyer['buyer_email'] ?? $order->user->email ?? '-';
                    $buyerPhone = $buyer['buyer_contact'] ?? '-';
                    $buyerId = $buyer['buyer_nric_passport'] ?? '-';
                    $eventDateTime = $selectedEvent && $selectedEvent->start_datetime
                        ? $selectedEvent->start_datetime->format('d/m/Y')
                        : '-';
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $order->stripe_payment_intent_id ?? $order->id }}</td>
                    <td>{{ $order->created_at->format('d M Y') }}</td>
                    <td class="buyer-details"><span class="purchaser-name">{{ $buyerName }}</span>{{ "\n" . $buyerEmail . "\n" . $buyerPhone . "\n" . $buyerId }}</td>
                    <td>{{ $h['full_name'] ?? '-' }}</td>
                    <td>{{ $h['email'] ?? '-' }}</td>
                    <td>{{ isset($h['gender']) ? ucfirst($h['gender']) : '-' }}</td>
                    <td>{{ $h['nric_passport'] ?? '-' }}</td>
                    <td>{{ $h['contact_number'] ?? '-' }}</td>
                    <td>{{ $h['company_name'] ?? '-' }}</td>
                    <td>{{ $eventDateTime }}</td>
                    <td class="signature-cell"></td>
                    <td class="signature-cell"></td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" style="padding: 16px;">No participants to display.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
