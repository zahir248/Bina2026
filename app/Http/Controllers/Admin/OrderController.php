<?php

namespace App\Http\Controllers\Admin;

use App\Exports\OrdersExport;
use App\Http\Controllers\Controller;
use App\Mail\RefundApprovedMail;
use App\Mail\RefundRejectedMail;
use App\Models\Event;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $search = $request->get('search', '');
        $statusFilter = $request->get('status', '');
        $paymentFilter = $request->get('payment_method', '');
        $refundStatusFilter = $request->get('refund_status', '');
        $refundOrdersFilter = $request->get('refund_orders', '');
        $eventFilter = $request->get('event', '');
        $dateFromFilter = $request->get('date_from', '');
        $dateToFilter = $request->get('date_to', '');

        $events = Event::with('category')->orderBy('name')->get();
        $query = $this->buildOrdersQuery($request);
        $orders = $query->paginate(10)->withQueryString();

        return view('admin.orders.index', compact('orders', 'events', 'search', 'statusFilter', 'paymentFilter', 'refundStatusFilter', 'refundOrdersFilter', 'eventFilter', 'dateFromFilter', 'dateToFilter'));
    }

    /**
     * Build the filtered orders query (shared by index and export).
     */
    private function buildOrdersQuery(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $search = $request->get('search', '');
        $statusFilter = $request->get('status', '');
        $paymentFilter = $request->get('payment_method', '');
        $refundStatusFilter = $request->get('refund_status', '');
        $refundOrdersFilter = $request->get('refund_orders', '');
        $eventFilter = $request->get('event', '');
        $dateFromFilter = $request->get('date_from', '');
        $dateToFilter = $request->get('date_to', '');

        $query = Order::with(['user', 'items.ticket', 'items.event']);

        if (!empty($dateFromFilter)) {
            $query->whereDate('created_at', '>=', $dateFromFilter);
        }
        if (!empty($dateToFilter)) {
            $query->whereDate('created_at', '<=', $dateToFilter);
        }

        if (!empty($eventFilter)) {
            $query->whereHas('items', function ($q) use ($eventFilter) {
                $q->where('event_id', $eventFilter);
            });
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('stripe_payment_intent_id', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
                if (is_numeric($search)) {
                    $q->orWhere('id', $search);
                }
            });
        }

        if (!empty($statusFilter)) {
            $query->where('status', $statusFilter);
        }

        if (!empty($paymentFilter)) {
            $query->where('payment_method', $paymentFilter);
        }

        if (!empty($refundOrdersFilter)) {
            $query->whereNotNull('refund_status');
            if (!empty($refundStatusFilter)) {
                $query->where('refund_status', $refundStatusFilter);
            }
        } elseif (!empty($refundStatusFilter)) {
            $query->where('status', 'paid')->where('refund_status', $refundStatusFilter);
        }

        $query->orderBy('created_at', 'desc');

        return $query;
    }

    /**
     * Export orders to Excel based on current filters.
     */
    public function export(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $query = $this->buildOrdersQuery($request);
        $orders = $query->with(['promoCode', 'affiliateCode', 'items.event.category'])->get();

        $refundFocus = $request->filled('refund_orders');
        $eventFilter = (string) $request->get('event', '');
        $filenameSuffix = '';
        if (!empty($eventFilter)) {
            $selectedEvent = Event::with('category')->find($eventFilter);
            if ($selectedEvent) {
                $eventLabel = $selectedEvent->name . ($selectedEvent->category ? ' (' . $selectedEvent->category->name . ')' : '');
                $safeName = trim(preg_replace('/-+/', '-', preg_replace('/[^a-zA-Z0-9_-]/', '-', $eventLabel)), '-');
                $filenameSuffix = '-' . $safeName;
            }
        }
        $base = $refundFocus ? 'refund-orders' : 'orders';
        $filename = $base . $filenameSuffix . '-' . now()->format('Y-m-d-His') . '.xlsx';

        return Excel::download(new OrdersExport($orders, $refundFocus), $filename, \Maatwebsite\Excel\Excel::XLSX);
    }

    public function modal(Order $order)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        return view('admin.orders.partials.modal-body', compact('order'));
    }

    public function approveRefund(Request $request, Order $order)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        if ($order->status !== 'paid' || $order->refund_status !== 'pending') {
            return redirect()
                ->route('admin.orders', ['refund_orders' => 1])
                ->with('error', 'This refund request cannot be approved.');
        }

        $paymentIntentId = $order->stripe_payment_intent_id;
        if (empty($paymentIntentId)) {
            return redirect()
                ->route('admin.orders', ['refund_orders' => 1])
                ->with('error', 'This order has no payment to refund.');
        }

        $stripeSecret = config('services.stripe.secret');
        if (empty($stripeSecret)) {
            return redirect()
                ->route('admin.orders', ['refund_orders' => 1])
                ->with('error', 'Refunds are not configured. Please contact support.');
        }

        try {
            $stripe = new StripeClient($stripeSecret);
            $stripe->refunds->create([
                'payment_intent' => $paymentIntentId,
                // full refund by default; omit 'amount' to refund entire payment
            ]);
        } catch (ApiErrorException $e) {
            $message = $e->getMessage();
            if (stripos($message, 'already been refunded') !== false) {
                $message = 'This payment has already been refunded.';
            }
            return redirect()
                ->route('admin.orders', ['refund_orders' => 1])
                ->with('error', $message ?: 'Stripe refund failed. Please try again or contact support.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.orders', ['refund_orders' => 1])
                ->with('error', 'Refund could not be processed. Please try again or contact support.');
        }

        try {
            DB::beginTransaction();
            $order->load('items');
            foreach ($order->items->groupBy('event_id') as $eventId => $items) {
                $event = Event::find($eventId);
                if ($event && $event->ticket_stock !== null) {
                    $quantityRefunded = $items->sum('quantity');
                    $event->increment('ticket_stock', $quantityRefunded);
                }
            }
            $order->update([
                'status' => 'refunded',
                'refund_status' => 'approved',
            ]);
            DB::commit();
            $this->sendRefundApprovedEmail($order);
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()
                ->route('admin.orders', ['refund_orders' => 1])
                ->with('error', 'Payment was refunded but we could not update the order. Please contact support.');
        }

        return redirect()
            ->route('admin.orders', ['refund_orders' => 1])
            ->with('success', 'Refund has been approved and processed.');
    }

    public function rejectRefund(Order $order)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        if ($order->status !== 'paid' || $order->refund_status !== 'pending') {
            return redirect()
                ->route('admin.orders', ['refund_orders' => 1])
                ->with('error', 'This refund request cannot be rejected.');
        }

        $order->update([
            'refund_status' => 'rejected',
        ]);

        $this->sendRefundRejectedEmail($order);

        return redirect()
            ->route('admin.orders', ['refund_orders' => 1])
            ->with('success', 'Refund request has been rejected.');
    }

    private function sendRefundApprovedEmail(Order $order): void
    {
        $email = $order->buyer_snapshot['buyer_email'] ?? $order->user?->email;
        if (empty($email)) {
            return;
        }
        try {
            Mail::to($email)->send(new RefundApprovedMail($order));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function sendRefundRejectedEmail(Order $order): void
    {
        $email = $order->buyer_snapshot['buyer_email'] ?? $order->user?->email;
        if (empty($email)) {
            return;
        }
        try {
            Mail::to($email)->send(new RefundRejectedMail($order));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Download receipt PDF for a paid order (admin side).
     */
    public function downloadReceipt(Order $order)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        if (!in_array($order->status, ['paid', 'refunded'], true)) {
            abort(404, 'Receipt is only available for paid or refunded orders.');
        }

        $order->load(['items.ticket', 'items.event', 'promoCode']);

        $logoDataUri = self::receiptLogoDataUri(public_path('images/bina-logo.png'));

        $filename = 'receipt-' . preg_replace('/[^a-zA-Z0-9_-]/', '-', $order->stripe_payment_intent_id ?? (string) $order->id) . '.pdf';

        return Pdf::loadView('client.profile.receipt', compact('order', 'logoDataUri'))
            ->setPaper('a4')
            ->download($filename);
    }

    /**
     * Download one attendance QR code (by holder index) for a paid/refunded order as PNG (admin).
     */
    public function downloadQrCode(Order $order, int $index)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }
        if (!in_array($order->status, ['paid', 'refunded'], true)) {
            abort(404, 'QR code is only available for paid or refunded orders.');
        }

        $holders = $order->ticket_holders_snapshot ?? [];
        if ($index < 0 || $index >= count($holders)) {
            abort(404, 'Invalid ticket holder index.');
        }

        $ref = $order->stripe_payment_intent_id ?? (string) $order->id;
        $safeRef = preg_replace('/[^a-zA-Z0-9_-]/', '-', $ref);
        $holderName = $holders[$index]['full_name'] ?? 'holder-' . ($index + 1);
        $holderSlug = Str::slug($holderName);
        $data = "reference: {$ref}\nfull name: {$holderName}";

        $writer = new PngWriter();
        $qrCode = new QrCode(data: $data, size: 300);
        $result = $writer->write($qrCode, null, null, []);
        $pngString = $result->getString();

        $filename = 'qr-code-' . $safeRef . '-' . $holderSlug . '.png';
        return response($pngString, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Build a data URI for the receipt logo (small PNG) so Dompdf can embed it. Returns null if unavailable.
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
