<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund request pending</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f4f4f5; }
        .wrapper { max-width: 600px; margin: 0 auto; padding: 24px; }
        .card { background: #fff; border-radius: 8px; padding: 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        h1 { font-size: 22px; margin: 0 0 16px 0; color: #111; }
        p { margin: 0 0 12px 0; }
        .highlight { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px 16px; margin: 20px 0; border-radius: 0 4px 4px 0; }
        .footer { margin-top: 24px; font-size: 13px; color: #6b7280; }
        .reference { font-family: monospace; background: #e5e7eb; padding: 2px 6px; border-radius: 4px; }
        a.btn { display: inline-block; margin-top: 12px; padding: 10px 20px; background-color: #3b82f6; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 500; }
        a.btn:hover { background-color: #2563eb; color: #ffffff; }
        table.info { width: 100%; border-collapse: collapse; font-size: 14px; margin: 12px 0; }
        table.info td { padding: 6px 0; vertical-align: top; }
        table.info td:first-child { color: #6b7280; width: 140px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <h1>New refund request</h1>
            <p>A customer has submitted a refund request. Please review and approve or reject it.</p>
            <div class="highlight">
                <strong>Order reference:</strong> <span class="reference">{{ $order->stripe_payment_intent_id ?? $order->id }}</span>
            </div>
            @php $buyer = $order->buyer_snapshot ?? []; @endphp
            <table class="info">
                <tr><td>Buyer name</td><td>{{ $buyer['buyer_name'] ?? '-' }}</td></tr>
                <tr><td>Buyer email</td><td>{{ $buyer['buyer_email'] ?? '-' }}</td></tr>
                <tr><td>Total</td><td>RM {{ number_format($order->total_amount_cents / 100, 2) }}</td></tr>
            </table>
            @if(!empty($order->reason))
            <p><strong>Reason for refund:</strong></p>
            <p>{{ $order->reason }}</p>
            @endif
            <p><a href="{{ $reviewUrl }}" class="btn">Review in admin panel</a></p>
            <div class="footer">
                <p>This is an automated notification. Do not reply to this email.</p>
            </div>
        </div>
    </div>
</body>
</html>
