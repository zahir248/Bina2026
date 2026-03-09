<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund request declined</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f4f4f5; }
        .wrapper { max-width: 600px; margin: 0 auto; padding: 24px; }
        .card { background: #fff; border-radius: 8px; padding: 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        h1 { font-size: 22px; margin: 0 0 16px 0; color: #111; }
        p { margin: 0 0 12px 0; }
        .highlight { background: #fef2f2; border-left: 4px solid #ef4444; padding: 12px 16px; margin: 20px 0; border-radius: 0 4px 4px 0; }
        .footer { margin-top: 24px; font-size: 13px; color: #6b7280; }
        .reference { font-family: monospace; background: #e5e7eb; padding: 2px 6px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <h1>Refund request declined</h1>
            <p>Hi {{ $order->buyer_snapshot['buyer_name'] ?? 'there' }},</p>
            <p>After review, we are unable to approve your refund request for the following order.</p>
            <div class="highlight">
                <strong>Order reference:</strong> <span class="reference">{{ $order->stripe_payment_intent_id ?? $order->id }}</span>
            </div>
            <p>No payment has been refunded. If you believe this was a mistake or would like to discuss further, please contact us.</p>
            <div class="footer">
                <p>This is an automated message. Please do not reply directly to this email.</p>
            </div>
        </div>
    </div>
</body>
</html>
