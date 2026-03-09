<?php

namespace App\Mail;

use App\Models\Order;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class TicketHolderPaymentSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public int $holderIndex
    ) {
        $this->order->load(['items.ticket', 'items.event', 'promoCode']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your ticket QR code – BINA',
            replyTo: [config('mail.from.address')],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-holder-payment-success',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $holders = $this->order->ticket_holders_snapshot ?? [];
        if ($this->holderIndex < 0 || $this->holderIndex >= count($holders)) {
            return [];
        }

        $ref = $this->order->stripe_payment_intent_id ?? (string) $this->order->id;
        $safeRef = preg_replace('/[^a-zA-Z0-9_-]/', '-', $ref);
        $holderName = $holders[$this->holderIndex]['full_name'] ?? 'holder-' . ($this->holderIndex + 1);
        $holderSlug = Str::slug($holderName);
        $filename = 'qr-code-' . $safeRef . '-' . $holderSlug . '.png';

        $index = $this->holderIndex;
        return [
            Attachment::fromData(function () use ($index) {
                $holders = $this->order->ticket_holders_snapshot ?? [];
                $ref = $this->order->stripe_payment_intent_id ?? (string) $this->order->id;
                $holderName = $holders[$index]['full_name'] ?? 'holder-' . ($index + 1);
                $data = "reference: {$ref}\nfull name: {$holderName}";
                $writer = new PngWriter();
                $qrCode = new QrCode(data: $data, size: 300);
                $result = $writer->write($qrCode, null, null, []);
                return $result->getString();
            }, $filename)->withMime('image/png'),
        ];
    }
}
