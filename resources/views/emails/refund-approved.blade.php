<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund approved</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f4f4f5; }
        .wrapper { max-width: 600px; margin: 0 auto; padding: 24px; }
        .card { background: #fff; border-radius: 8px; padding: 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        h1 { font-size: 22px; margin: 0 0 16px 0; color: #111; }
        p { margin: 0 0 12px 0; }
        .highlight { background: #ecfdf5; border-left: 4px solid #10b981; padding: 12px 16px; margin: 20px 0; border-radius: 0 4px 4px 0; }
        .footer { margin-top: 24px; font-size: 13px; color: #6b7280; }
        .reference { font-family: monospace; background: #e5e7eb; padding: 2px 6px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <h1>Refund approved</h1>
            <p>Hi {{ $order->buyer_snapshot['buyer_name'] ?? 'there' }},</p>
            <p>Your refund request has been <strong>approved</strong>. We have processed the refund for your order.</p>
            <div class="highlight">
                <strong>Order reference:</strong> <span class="reference">{{ $order->stripe_payment_intent_id ?? $order->id }}</span>
            </div>
            <p>The refund has been sent to your original payment method. It may take <strong>5–10 business days</strong> to appear in your account, depending on your bank or card issuer.</p>
            <p>If you have any questions, please contact us.</p>
            <div class="footer">
                <p>This is an automated message. Please do not reply directly to this email.</p>
            </div>
        </div>
    </div>
</body>
</html>
