<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your ticket QR code</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f4f4f5; }
        .wrapper { max-width: 600px; margin: 0 auto; padding: 24px; }
        .card { background: #fff; border-radius: 8px; padding: 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        h1 { font-size: 22px; margin: 0 0 16px 0; color: #111; }
        p { margin: 0 0 12px 0; }
        .buyer-details { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 14px 18px; margin: 16px 0; font-size: 15px; }
        .buyer-details p { margin: 4px 0; }
        .buyer-details .label { color: #6b7280; margin-right: 6px; }
        .footer { margin-top: 24px; font-size: 13px; color: #6b7280; }
    </style>
</head>
<body>
    @php
        $holders = $order->ticket_holders_snapshot ?? [];
        $holderName = $holders[$holderIndex]['full_name'] ?? 'there';
        $buyer = $order->buyer_snapshot ?? [];
        $buyerName = $buyer['buyer_name'] ?? '-';
        $buyerEmail = $buyer['buyer_email'] ?? '-';
        $buyerContact = $buyer['buyer_contact'] ?? '-';
    @endphp
    <div class="wrapper">
        <div class="card">
            <h1>Your ticket QR code</h1>
            <p>Hi {{ $holderName }},</p>
            <p>Your ticket QR code is attached to this email. Please keep it for attendance.</p>
            <p><strong>Buyer contact details</strong> (for reference):</p>
            <div class="buyer-details">
                <p><span class="label">Name:</span> {{ $buyerName }}</p>
                <p><span class="label">Email:</span> {{ $buyerEmail }}</p>
                <p><span class="label">Contact:</span> {{ $buyerContact }}</p>
            </div>
            <p>If you have any questions, please contact the buyer or reach out to us.</p>
            <div class="footer">
                <p>This is an automated message. Please do not reply directly to this email.</p>
            </div>
        </div>
    </div>
</body>
</html>
