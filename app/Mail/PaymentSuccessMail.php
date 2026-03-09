<?php

namespace App\Mail;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class PaymentSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order
    ) {
        $this->order->load(['items.ticket', 'items.event', 'promoCode']);
    }

    public function envelope(): Envelope
    {
        $buyerName = $this->order->buyer_snapshot['buyer_name'] ?? 'Customer';
        return new Envelope(
            subject: 'Payment successful – Your BINA receipt',
            replyTo: [config('mail.from.address')],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-success',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $logoDataUri = self::receiptLogoDataUri(public_path('images/bina-logo.png'));
        $pdf = Pdf::loadView('client.profile.receipt', [
            'order' => $this->order,
            'logoDataUri' => $logoDataUri,
        ])->setPaper('a4');

        $ref = $this->order->stripe_payment_intent_id ?? (string) $this->order->id;
        $safeRef = preg_replace('/[^a-zA-Z0-9_-]/', '-', $ref);

        $attachments = [
            Attachment::fromData(fn () => $pdf->output(), 'receipt-' . $safeRef . '.pdf')
                ->withMime('application/pdf'),
        ];

        $holders = $this->order->ticket_holders_snapshot ?? [];
        foreach ($holders as $i => $h) {
            $holderName = $h['full_name'] ?? 'holder-' . ($i + 1);
            $holderSlug = Str::slug($holderName);
            $filename = 'qr-code-' . $safeRef . '-' . $holderSlug . '.png';
            $index = $i;
            $attachments[] = Attachment::fromData(function () use ($index) {
                $holders = $this->order->ticket_holders_snapshot ?? [];
                $ref = $this->order->stripe_payment_intent_id ?? (string) $this->order->id;
                $holderName = $holders[$index]['full_name'] ?? 'holder-' . ($index + 1);
                $data = "reference: {$ref}\nfull name: {$holderName}";
                $writer = new PngWriter();
                $qrCode = new QrCode(data: $data, size: 300);
                $result = $writer->write($qrCode, null, null, []);
                return $result->getString();
            }, $filename)->withMime('image/png');
        }

        return $attachments;
    }

    /**
     * Build a data URI for the receipt logo so Dompdf can embed it. Returns null if unavailable.
     */
    private static function receiptLogoDataUri(string $path): ?string
    {
        if (!file_exists($path) || !is_readable($path)) {
            return null;
        }
        $maxHeight = 48;
        if (function_exists('imagecreatefrompng')) {
            $im = @imagecreatefrompng($path);
            if ($im !== false) {
                $w = imagesx($im);
                $h = imagesy($im);
                if ($w > 0 && $h > 0) {
                    $scale = min(1, $maxHeight / $h);
                    $nw = (int) round($w * $scale);
                    $nh = (int) round($h * $scale);
                    $out = imagecreatetruecolor($nw, $nh);
                    if ($out !== false) {
                        imagealphablending($out, false);
                        imagesavealpha($out, true);
                        $trans = imagecolorallocatealpha($out, 255, 255, 255, 127);
                        imagefill($out, 0, 0, $trans);
                        imagecopyresampled($out, $im, 0, 0, 0, 0, $nw, $nh, $w, $h);
                        ob_start();
                        imagepng($out, null, 9);
                        $png = ob_get_clean();
                        imagedestroy($out);
                        imagedestroy($im);
                        if ($png !== false && $png !== '') {
                            return 'data:image/png;base64,' . base64_encode($png);
                        }
                    }
                }
                imagedestroy($im);
            }
        }
        $raw = file_get_contents($path);
        if ($raw === false || $raw === '') {
            return null;
        }
        return 'data:image/png;base64,' . base64_encode($raw);
    }
}
