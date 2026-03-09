<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment unsuccessful</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f4f4f5; }
        .wrapper { max-width: 600px; margin: 0 auto; padding: 24px; }
        .card { background: #fff; border-radius: 8px; padding: 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        h1 { font-size: 22px; margin: 0 0 16px 0; color: #111; }
        p { margin: 0 0 12px 0; }
        .highlight { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px 16px; margin: 20px 0; border-radius: 0 4px 4px 0; }
        .footer { margin-top: 24px; font-size: 13px; color: #6b7280; }
        .reference { font-family: monospace; background: #f3f4f6; padding: 2px 6px; border-radius: 4px; }
        a.btn { display: inline-block; margin-top: 12px; padding: 10px 20px; background: #ff9800; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: 500; }
        a.btn:hover { background: #e68900; color: #ffffff !important; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <h1>Payment unsuccessful</h1>
            <p>Hi {{ $order->buyer_snapshot['buyer_name'] ?? 'there' }},</p>
            <p>Your payment could not be completed. Your order has been saved and is <strong>pending payment</strong>.</p>
            <div class="highlight">
                <strong>Order reference:</strong> <span class="reference">{{ $order->stripe_payment_intent_id ?? $order->id }}</span>
            </div>
            <p>You can complete the payment from your <strong>Purchase History</strong> (To Pay tab). Your order details and ticket selection are reserved.</p>
            <p><a href="{{ url(route('profile.purchaseHistory', ['tab' => 'to_pay'])) }}" class="btn" style="display: inline-block; margin-top: 12px; padding: 10px 20px; background-color: #ff9800; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 500;"><span style="color: #ffffff;">Go to Purchase History</span></a></p>
            <p>If you have any questions or need help, please contact us.</p>
            <div class="footer">
                <p>This is an automated message. Please do not reply directly to this email.</p>
            </div>
        </div>
    </div>
</body>
</html>
