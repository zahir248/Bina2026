<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class TicketScannerController extends Controller
{
    /**
     * Show the ticket scanner page (admin only).
     */
    public function index()
    {
        return view('admin.scanner.index');
    }

    /**
     * Validate a scanned ticket. Expects raw QR text (reference + full name lines).
     * Does not store anything; only checks if the ticket is valid.
     */
    public function validateTicket(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payload' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid or missing scan data.',
            ], 422);
        }

        $payload = trim($request->input('payload'));
        $reference = null;
        $fullName = null;

        foreach (preg_split("/\r\n|\n|\r/", $payload) as $line) {
            $line = trim($line);
            if (stripos($line, 'reference:') === 0) {
                // Normalize format like "reference: value"
                $parts = explode(':', $line, 2);
                $reference = isset($parts[1]) ? trim($parts[1]) : null;
            }
            if (stripos($line, 'full name:') === 0) {
                $parts = explode(':', $line, 2);
                $fullName = isset($parts[1]) ? trim($parts[1]) : null;
            }
        }

        Log::info('ticket-scanner.payload', [
            'raw' => $payload,
            'reference_parsed' => $reference,
            'full_name_parsed' => $fullName,
        ]);

        if (empty($reference) || empty($fullName)) {
            return response()->json([
                'valid' => false,
                'message' => 'QR code format not recognized. Please scan a valid ticket QR code.',
            ]);
        }

        $order = Order::where('stripe_payment_intent_id', $reference)
            ->orWhere('id', $reference)
            ->first();

        if (!$order) {
            return response()->json([
                'valid' => false,
                'message' => 'Ticket not found.',
            ]);
        }

        if ($order->status !== 'paid') {
            return response()->json([
                'valid' => false,
                'message' => 'This ticket is not valid for entry (order not paid).',
            ]);
        }

        $order->loadMissing(['items.ticket', 'items.event']);

        $holders = $order->ticket_holders_snapshot ?? [];
        $matchedIndex = null;
        $matchedHolder = null;
        $fullNameLower = mb_strtolower($fullName);

        foreach ($holders as $i => $holder) {
            $holderName = $holder['full_name'] ?? '';
            if (trim(mb_strtolower($holderName)) === $fullNameLower) {
                $matchedIndex = $i;
                $matchedHolder = $holder;
                break;
            }
        }

        if ($matchedIndex === null || $matchedHolder === null) {
            return response()->json([
                'valid' => false,
                'message' => 'Name on ticket does not match this order.',
            ]);
        }

        // If refund approved/completed, ticket is NOT valid for entry.
        if (in_array($order->refund_status, ['approved', 'completed'], true)) {
            return response()->json([
                'valid' => false,
                'message' => 'This ticket is not valid for entry (refund approved).',
            ]);
        }

        $events = [];
        foreach ($order->items as $item) {
            $events[] = [
                'event_name' => $item->event->name ?? null,
                'event_category' => $item->event->category->name ?? null,
                'ticket_type' => $item->ticket->name ?? null,
                'quantity' => (int) $item->quantity,
            ];
        }

        return response()->json([
            'valid' => true,
            'message' => 'Ticket is valid.',
            'holder' => [
                'full_name' => $matchedHolder['full_name'] ?? '',
                'email' => $matchedHolder['email'] ?? '',
                'gender' => $matchedHolder['gender'] ?? '',
                'nric_passport' => $matchedHolder['nric_passport'] ?? '',
                'contact_number' => $matchedHolder['contact_number'] ?? '',
                'company_name' => $matchedHolder['company_name'] ?? '',
            ],
            'order' => [
                'id' => $order->id,
                'reference' => $reference,
                'status' => $order->status,
                'events' => $events,
            ],
        ]);
    }
}
