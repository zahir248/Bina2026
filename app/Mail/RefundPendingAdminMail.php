<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RefundPendingAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $reviewUrl;

    public function __construct(
        public Order $order
    ) {
        $this->order->load(['items.ticket', 'items.event']);
        $this->reviewUrl = url(route('admin.orders', ['refund_orders' => 1]));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New refund request – pending review (BINA)',
            replyTo: [config('mail.from.address')],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.refund-pending-admin',
        );
    }
}
