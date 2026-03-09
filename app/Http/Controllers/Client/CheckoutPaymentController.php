<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\AffiliateCode;
use App\Models\Cart;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PromoCode;
use App\Mail\PaymentFailedMail;
use App\Mail\PaymentSuccessMail;
use App\Mail\TicketHolderPaymentSuccessMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class CheckoutPaymentController extends Controller
{
    /**
     * Create a Stripe PaymentIntent only (no order yet). Checkout data is stored in session.
     * Order is created only when payment succeeds in success().
     */
    public function createPaymentIntent(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $rules = [
            'payment_method_type' => 'required|in:card,fpx',
            'buyer_name' => 'required|string|max:255',
            'buyer_email' => 'required|email',
            'buyer_gender' => 'required|in:male,female',
            'buyer_nric_passport' => 'required|string|max:50',
            'buyer_contact' => 'required|string|max:30',
            'buyer_country' => 'required|string|max:100',
            'buyer_street_address' => 'required|string|max:500',
            'buyer_town_city' => 'required|string|max:100',
            'buyer_state' => 'required|string|max:100',
            'buyer_postcode_zip' => 'required|string|max:20',
            'buyer_category' => 'required|string|max:50',
            'buyer_student_id' => 'nullable|string|max:100',
            'buyer_academy_institution' => 'nullable|string|max:255',
            'buyer_company_name' => 'nullable|string|max:255',
            'buyer_business_registration_number' => 'nullable|string|max:100',
            'ticket_holders' => 'required|array',
            'ticket_holders.*.full_name' => 'required|string|max:255',
            'ticket_holders.*.email' => 'required|email',
            'ticket_holders.*.gender' => 'required|in:male,female',
            'ticket_holders.*.nric_passport' => 'required|string|max:50',
            'ticket_holders.*.contact_number' => 'required|string|max:30',
            'ticket_holders.*.company_name' => 'nullable|string|max:255',
            'ticket_holders.*.cart_id' => 'nullable|integer',
            'ticket_holders.*.ticket_id' => 'nullable|integer',
        ];

        if ($request->input('buyer_category') === 'academician') {
            $rules['buyer_student_id'] = 'required|string|max:100';
            $rules['buyer_academy_institution'] = 'required|string|max:255';
        }
        if ($request->input('buyer_category') === 'organization') {
            $rules['buyer_company_name'] = 'required|string|max:255';
            $rules['buyer_business_registration_number'] = 'required|string|max:100';
        }

        $request->validate($rules);

        $carts = Cart::with(['ticket', 'event'])
            ->where('user_id', Auth::id())
            ->get();

        if ($carts->isEmpty()) {
            return response()->json(['error' => 'Your cart is empty.'], 400);
        }

        $totalAmount = 0;
        $firstEventId = $carts->first()->event_id;
        $appliedPromo = null;
        $discountAmount = 0;

        foreach ($carts as $cart) {
            $unitPrice = $cart->ticket->getPriceForQuantity($cart->quantity);
            $totalAmount += $unitPrice * $cart->quantity;
        }

        if (session()->has('cart_promo_code_id') && $totalAmount > 0) {
            $promo = PromoCode::with('events')->find(session('cart_promo_code_id'));
            if ($promo && $promo->status === 'active') {
                $appliesToEvent = $promo->events->isEmpty()
                    || $promo->events->contains('id', $firstEventId);
                if ($appliesToEvent) {
                    $appliedPromo = $promo;
                    $discountAmount = min((float) $promo->discount, $totalAmount);
                }
            }
        }

        $totalAfterDiscount = max(0, $totalAmount - $discountAmount);
        $subtotalCents = (int) round($totalAfterDiscount * 100);

        $feeFixedCents = (int) config('services.stripe.fee_fixed_cents', 0);
        $feePercentageDomestic = (float) config('services.stripe.fee_percentage', 0);
        $feePercentageInternational = (float) config('services.stripe.fee_percentage_international', 0);
        $paymentMethodType = $request->input('payment_method_type');
        if ($paymentMethodType === 'fpx') {
            $feePercentage = $feePercentageDomestic;
        } else {
            $isInternational = (bool) $request->input('is_international', true);
            $feePercentage = $isInternational ? ($feePercentageInternational ?: $feePercentageDomestic) : $feePercentageDomestic;
        }
        $processingFeeCents = $feePercentage > 0
            ? (int) round($subtotalCents * $feePercentage / 100) + $feeFixedCents
            : $feeFixedCents;
        $amountCents = $subtotalCents + $processingFeeCents;

        if ($amountCents < 50) {
            return response()->json(['error' => 'Amount must be at least RM 0.50.'], 400);
        }

        $buyerSnapshot = [
            'buyer_name' => $request->input('buyer_name'),
            'buyer_email' => $request->input('buyer_email'),
            'buyer_gender' => $request->input('buyer_gender'),
            'buyer_nric_passport' => $request->input('buyer_nric_passport'),
            'buyer_contact' => $request->input('buyer_contact'),
            'buyer_country' => $request->input('buyer_country'),
            'buyer_street_address' => $request->input('buyer_street_address'),
            'buyer_town_city' => $request->input('buyer_town_city'),
            'buyer_state' => $request->input('buyer_state'),
            'buyer_postcode_zip' => $request->input('buyer_postcode_zip'),
            'buyer_category' => $request->input('buyer_category'),
            'buyer_student_id' => $request->input('buyer_student_id'),
            'buyer_academy_institution' => $request->input('buyer_academy_institution'),
            'buyer_company_name' => $request->input('buyer_company_name'),
            'buyer_business_registration_number' => $request->input('buyer_business_registration_number'),
        ];

        $ticketHoldersRaw = $request->input('ticket_holders', []);
        $ticketHoldersSnapshot = array_values(array_map(function ($holder) {
            return [
                'full_name' => $holder['full_name'] ?? null,
                'email' => $holder['email'] ?? null,
                'gender' => $holder['gender'] ?? null,
                'nric_passport' => $holder['nric_passport'] ?? null,
                'contact_number' => $holder['contact_number'] ?? null,
                'company_name' => $holder['company_name'] ?? null,
                'cart_id' => isset($holder['cart_id']) ? (int) $holder['cart_id'] : null,
                'ticket_id' => isset($holder['ticket_id']) ? (int) $holder['ticket_id'] : null,
            ];
        }, $ticketHoldersRaw));

        $orderItemsPayload = [];
        foreach ($carts as $cart) {
            $unitPrice = $cart->ticket->getPriceForQuantity($cart->quantity);
            $unitCents = (int) round($unitPrice * 100);
            $orderItemsPayload[] = [
                'ticket_id' => $cart->ticket_id,
                'event_id' => $cart->event_id,
                'quantity' => $cart->quantity,
                'unit_price_cents' => $unitCents,
            ];
        }

        $stripeSecret = config('services.stripe.secret');
        if (empty($stripeSecret)) {
            return response()->json(['error' => 'Stripe is not configured.'], 500);
        }

        try {
            $stripe = new StripeClient($stripeSecret);
            $paymentMethodTypes = $request->input('payment_method_type') === 'fpx' ? ['fpx'] : ['card'];

            $intent = $stripe->paymentIntents->create([
                'amount' => $amountCents,
                'currency' => 'myr',
                'payment_method_types' => $paymentMethodTypes,
                'metadata' => [
                    'user_id' => (string) Auth::id(),
                ],
            ]);

            session()->put('checkout_pending_payment_intent_id', $intent->id);
            session()->put('checkout_pending_data', [
                'user_id' => Auth::id(),
                'subtotal_cents' => $subtotalCents, // before processing fee (for failed orders)
                'total_amount_cents' => $amountCents, // total charged (subtotal + processing fee)
                'currency' => 'myr',
                'payment_method' => $request->input('payment_method_type'),
                'buyer_snapshot' => $buyerSnapshot,
                'ticket_holders_snapshot' => $ticketHoldersSnapshot,
                'promo_code_id' => $appliedPromo?->id,
                'affiliate_code_id' => session('checkout_affiliate_code_id'),
                'order_items' => $orderItemsPayload,
                'client_secret' => $intent->client_secret,
            ]);

            return response()->json([
                'clientSecret' => $intent->client_secret,
                'paymentIntentId' => $intent->id,
            ]);
        } catch (ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Unable to create payment. Please try again.'], 500);
        }
    }

    /**
     * Update PaymentIntent amount when card country is Malaysia (domestic fee).
     * Called from frontend after createPaymentMethod returns card.country.
     */
    public function updateIntentAmount(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        $request->validate([
            'payment_intent_id' => 'required|string',
            'card_country' => 'required|string|size:2',
        ]);

        $paymentIntentId = $request->input('payment_intent_id');
        $cardCountry = strtoupper($request->input('card_country'));

        if (session('checkout_pending_payment_intent_id') !== $paymentIntentId) {
            return response()->json(['error' => 'Invalid session.'], 400);
        }

        $pending = session('checkout_pending_data');
        if (!$pending || (int) ($pending['user_id'] ?? 0) !== (int) Auth::id()) {
            return response()->json(['error' => 'Invalid session.'], 400);
        }

        if ($cardCountry !== 'MY') {
            return response()->json(['success' => true, 'updated' => false]);
        }

        $subtotalCents = 0;
        foreach ($pending['order_items'] ?? [] as $item) {
            $subtotalCents += ((int) ($item['unit_price_cents'] ?? 0)) * ((int) ($item['quantity'] ?? 0));
        }

        $feeFixedCents = (int) config('services.stripe.fee_fixed_cents', 0);
        $feePercentageDomestic = (float) config('services.stripe.fee_percentage', 0);
        $processingFeeCents = $feePercentageDomestic > 0
            ? (int) round($subtotalCents * $feePercentageDomestic / 100) + $feeFixedCents
            : $feeFixedCents;
        $newAmountCents = $subtotalCents + $processingFeeCents;

        if ($newAmountCents < 50) {
            return response()->json(['error' => 'Amount too low.'], 400);
        }

        $stripeSecret = config('services.stripe.secret');
        if (empty($stripeSecret)) {
            return response()->json(['error' => 'Stripe not configured.'], 500);
        }

        try {
            $stripe = new StripeClient($stripeSecret);
            $intent = $stripe->paymentIntents->retrieve($paymentIntentId);
            if ($intent->status !== 'requires_payment_method') {
                return response()->json(['success' => true, 'updated' => false]);
            }
            $stripe->paymentIntents->update($paymentIntentId, ['amount' => $newAmountCents]);
            $pending['total_amount_cents'] = $newAmountCents;
            session()->put('checkout_pending_data', $pending);
            return response()->json(['success' => true, 'updated' => true, 'amount_cents' => $newAmountCents]);
        } catch (ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Unable to update amount.'], 500);
        }
    }

    /**
     * Get Stripe client_secret for repaying a pending order.
     * Reuses the same PaymentIntent when we have a stored client_secret (no new PI, no duplicate in Stripe).
     * Otherwise creates a new PaymentIntent and cancels the old one.
     */
    public function repayCreateIntent(Request $request, Order $order)
    {
        if (!Auth::check() || $order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }
        if ($order->status !== 'pending') {
            return response()->json(['error' => 'This order is not pending payment.'], 400);
        }

        $request->validate(['payment_method_type' => 'required|in:card,fpx']);

        $stripeSecret = config('services.stripe.secret');
        if (empty($stripeSecret)) {
            return response()->json(['error' => 'Stripe is not configured.'], 500);
        }

        $stripe = new StripeClient($stripeSecret);
        $existingPiId = $order->stripe_payment_intent_id;

        // Order may store subtotal only (amount_excludes_fee) when created from failed payment; add fee on repay
        $subtotalCents = (int) $order->total_amount_cents;
        $feeFixedCents = (int) config('services.stripe.fee_fixed_cents', 0);
        $feePctDomestic = (float) config('services.stripe.fee_percentage', 0);
        $feePctInternational = (float) config('services.stripe.fee_percentage_international', 0);
        if ($feePctInternational <= 0) {
            $feePctInternational = $feePctDomestic;
        }
        $repayMethod = $request->input('payment_method_type');
        $feePercentage = $repayMethod === 'fpx' ? $feePctDomestic : ($feePctInternational ?: $feePctDomestic);
        $processingFeeCents = $feePercentage > 0
            ? (int) round($subtotalCents * $feePercentage / 100) + $feeFixedCents
            : $feeFixedCents;
        $amountCents = $order->amount_excludes_fee
            ? $subtotalCents + $processingFeeCents
            : $subtotalCents;

        $currency = $order->currency ?? 'myr';
        if ($amountCents < 50) {
            return response()->json(['error' => 'Amount is too low to pay.'], 400);
        }

        $paymentMethodTypes = $repayMethod === 'fpx' ? ['fpx'] : ['card'];

        // Reuse existing PaymentIntent when possible (same amount, same method) – then success() updates same order via Repay flow 1
        if ($existingPiId && $order->stripe_client_secret_encrypted) {
            try {
                $clientSecret = Crypt::decryptString($order->stripe_client_secret_encrypted);
                $intent = $stripe->paymentIntents->retrieve($existingPiId);
                $status = $intent->status ?? '';
                $intentAmount = (int) ($intent->amount ?? 0);
                $intentTypes = $intent->payment_method_types ?? [];
                $methodMatches = ($repayMethod === 'fpx' && in_array('fpx', $intentTypes))
                    || ($repayMethod === 'card' && in_array('card', $intentTypes));
                if (in_array($status, ['requires_payment_method', 'requires_confirmation'], true)
                    && ! empty($clientSecret)
                    && $intentAmount === $amountCents
                    && $methodMatches
                ) {
                    return response()->json(['clientSecret' => $clientSecret]);
                }
            } catch (\Throwable $e) {
                // Decrypt or retrieve failed; fall through to create new
            }
        }

        // Cancel old PaymentIntent so Stripe stays clean, then create new one
        if ($existingPiId) {
            try {
                $stripe->paymentIntents->cancel($existingPiId);
            } catch (\Throwable $e) {
                // Ignore (e.g. already canceled)
            }
        }

        try {
            $intent = $stripe->paymentIntents->create([
                'amount' => $amountCents,
                'currency' => strtolower($currency),
                'payment_method_types' => $paymentMethodTypes,
                'metadata' => [
                    'user_id' => (string) Auth::id(),
                    'order_id' => (string) $order->id,
                    'repay' => '1',
                ],
            ]);

            session()->put('repay_order_id', $order->id);
            session()->put('repay_payment_intent_id', $intent->id);

            return response()->json(['clientSecret' => $intent->client_secret]);
        } catch (ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Unable to create payment. Please try again.'], 500);
        }
    }

    /**
     * Payment success return URL - verify payment, create order (or complete repay), then redirect.
     * Repay is detected from PaymentIntent metadata (repay=1, order_id) so it works even if session is lost on redirect.
     */
    public function success(Request $request)
    {
        $paymentIntentId = $request->query('payment_intent');
        if (!$paymentIntentId) {
            return redirect()->route('profile.purchaseHistory', ['tab' => 'to_pay'])->with('error', 'Invalid return from payment. Please check your Purchase History.');
        }

        $stripeSecret = config('services.stripe.secret');
        if (empty($stripeSecret)) {
            return redirect()->route('profile.purchaseHistory', ['tab' => 'to_pay'])->with('error', 'Payment could not be verified.');
        }

        try {
            $stripe = new StripeClient($stripeSecret);
            $intent = $stripe->paymentIntents->retrieve($paymentIntentId);
        } catch (\Throwable $e) {
            return redirect()->route('profile.purchaseHistory', ['tab' => 'to_pay'])->with('error', 'Could not verify payment.');
        }

        // Repay flow 1: same PaymentIntent reused (order already has this stripe_payment_intent_id)
        $existingPendingOrder = Order::where('stripe_payment_intent_id', $paymentIntentId)
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->first();

        if ($existingPendingOrder && $intent->status === 'succeeded') {
            try {
                DB::beginTransaction();
                $newMethod = $intent->payment_method_types[0] ?? $existingPendingOrder->payment_method;
                // Repay flow 1: same PaymentIntent reused, so method never changed; just mark paid, no reason
                $existingPendingOrder->update([
                    'status' => 'paid',
                    'total_amount_cents' => $intent->amount,
                    'payment_method' => $newMethod,
                    'stripe_client_secret_encrypted' => null,
                    'reason' => null,
                ]);
                $existingPendingOrder->load('items');
                foreach ($existingPendingOrder->items->groupBy('event_id') as $eventId => $items) {
                    $event = Event::find($eventId);
                    if ($event && $event->ticket_stock !== null) {
                        $quantitySold = $items->sum('quantity');
                        $newStock = max(0, (int) $event->ticket_stock - $quantitySold);
                        $event->update(['ticket_stock' => $newStock]);
                    }
                }
                if ($existingPendingOrder->affiliate_code_id) {
                    AffiliateCode::where('id', $existingPendingOrder->affiliate_code_id)->increment('total_conversion');
                }
                DB::commit();
                $this->sendPaymentSuccessEmail($existingPendingOrder);
                return redirect()->route('profile.purchaseHistory', ['tab' => 'completed'])->with('success', 'Payment successful. Thank you.');
            } catch (\Throwable $e) {
                DB::rollBack();
            }
        }

        if ($existingPendingOrder) {
            $this->sendPaymentFailedEmail($existingPendingOrder);
            return redirect()->route('profile.purchaseHistory', ['tab' => 'to_pay'])->with('error', 'Payment was unsuccessful. Please try again.');
        }

        // Repay flow 2: new PaymentIntent created for repay (metadata order_id + repay, or session when metadata not available e.g. card in-page)
        $repayOrderId = null;
        $metadata = $intent->metadata;
        if ($metadata) {
            $repayVal = is_array($metadata) ? ($metadata['repay'] ?? null) : ($metadata->repay ?? $metadata['repay'] ?? null);
            $orderIdVal = is_array($metadata) ? ($metadata['order_id'] ?? null) : ($metadata->order_id ?? $metadata['order_id'] ?? null);
            if ($repayVal === '1' && !empty($orderIdVal)) {
                $repayOrderId = (int) $orderIdVal;
            }
        }
        if ($repayOrderId === null && session('repay_payment_intent_id') === $paymentIntentId && session('repay_order_id')) {
            $repayOrderId = (int) session('repay_order_id');
        }
        $isRepay = $repayOrderId !== null && Auth::check();

        if ($isRepay && $repayOrderId && Auth::check()) {
            session()->forget(['repay_order_id', 'repay_payment_intent_id']);

            $order = Order::where('id', $repayOrderId)->where('user_id', Auth::id())->first();
            if ($order && $order->status === 'pending') {
                if ($intent->status === 'succeeded') {
                    try {
                        DB::beginTransaction();
                        $newMethod = $intent->payment_method_types[0] ?? $order->payment_method;
                        $oldMethod = $order->payment_method ? strtolower($order->payment_method) : '';
                        $newMethodNorm = $newMethod ? strtolower($newMethod) : '';
                        $paymentMethodChanged = $oldMethod && $newMethodNorm && $oldMethod !== $newMethodNorm;

                        if ($paymentMethodChanged) {
                            // Old order: update status to cancelled and set reason; do not replace reference or payment details
                            $reason = 'Cancelled by system due to change payment method (' . ($oldMethod === 'fpx' ? 'FPX' : 'Card') . ' to ' . ($newMethodNorm === 'fpx' ? 'FPX' : 'Card') . '). Reference number for the new order is ' . $paymentIntentId;
                            $order->update([
                                'status' => 'cancelled',
                                'stripe_client_secret_encrypted' => null,
                                'reason' => $reason,
                            ]);

                            // New order: create with new reference, same details, no reason
                            $newOrder = Order::create([
                                'user_id' => $order->user_id,
                                'total_amount_cents' => $intent->amount,
                                'amount_excludes_fee' => false,
                                'currency' => $order->currency ?? 'myr',
                                'status' => 'paid',
                                'stripe_payment_intent_id' => $paymentIntentId,
                                'payment_method' => $newMethod,
                                'buyer_snapshot' => $order->buyer_snapshot ?? [],
                                'ticket_holders_snapshot' => $order->ticket_holders_snapshot ?? [],
                                'promo_code_id' => $order->promo_code_id,
                                'affiliate_code_id' => $order->affiliate_code_id,
                                'reason' => null,
                            ]);
                            foreach ($order->items as $item) {
                                OrderItem::create([
                                    'order_id' => $newOrder->id,
                                    'ticket_id' => $item->ticket_id,
                                    'event_id' => $item->event_id,
                                    'quantity' => $item->quantity,
                                    'unit_price_cents' => $item->unit_price_cents,
                                ]);
                            }
                            $newOrder->load('items');
                            foreach ($newOrder->items->groupBy('event_id') as $eventId => $items) {
                                $event = Event::find($eventId);
                                if ($event && $event->ticket_stock !== null) {
                                    $quantitySold = $items->sum('quantity');
                                    $newStock = max(0, (int) $event->ticket_stock - $quantitySold);
                                    $event->update(['ticket_stock' => $newStock]);
                                }
                            }
                            if ($newOrder->affiliate_code_id) {
                                AffiliateCode::where('id', $newOrder->affiliate_code_id)->increment('total_conversion');
                            }
                            DB::commit();
                            $this->sendPaymentSuccessEmail($newOrder);
                            return redirect()->route('profile.purchaseHistory', ['tab' => 'completed'])->with('success', 'Payment successful. Thank you.');
                        } else {
                            // Same payment method: update existing order to paid as before
                            $order->update([
                                'status' => 'paid',
                                'total_amount_cents' => $intent->amount,
                                'stripe_payment_intent_id' => $paymentIntentId,
                                'payment_method' => $newMethod,
                                'stripe_client_secret_encrypted' => null,
                                'reason' => null,
                            ]);
                            $order->load('items');
                            foreach ($order->items->groupBy('event_id') as $eventId => $items) {
                                $event = Event::find($eventId);
                                if ($event && $event->ticket_stock !== null) {
                                    $quantitySold = $items->sum('quantity');
                                    $newStock = max(0, (int) $event->ticket_stock - $quantitySold);
                                    $event->update(['ticket_stock' => $newStock]);
                                }
                            }
                            if ($order->affiliate_code_id) {
                                AffiliateCode::where('id', $order->affiliate_code_id)->increment('total_conversion');
                            }
                            DB::commit();
                            $this->sendPaymentSuccessEmail($order);
                            return redirect()->route('profile.purchaseHistory', ['tab' => 'completed'])->with('success', 'Payment successful. Thank you.');
                        }
                    } catch (\Throwable $e) {
                        DB::rollBack();
                    }
                }
            }

            if ($order) {
                $this->sendPaymentFailedEmail($order);
            }
            return redirect()->route('profile.purchaseHistory', ['tab' => 'to_pay'])->with('error', 'Payment was unsuccessful. Please try again.');
        }

        // Checkout flow: create order from session pending data (only when session matches)
        if (session('checkout_pending_payment_intent_id') !== $paymentIntentId) {
            session()->forget(['checkout_pending_payment_intent_id', 'checkout_pending_data', 'checkout_affiliate_code_id', 'checkout_affiliate_code']);
            return redirect()->route('profile.purchaseHistory', ['tab' => 'to_pay'])->with('error', 'Session expired or invalid. Please try again from Purchase History.');
        }

        $pending = session('checkout_pending_data');
        if (!$pending || (int) ($pending['user_id'] ?? 0) !== (int) Auth::id()) {
            session()->forget(['checkout_pending_payment_intent_id', 'checkout_pending_data', 'checkout_affiliate_code_id', 'checkout_affiliate_code']);
            return redirect()->route('profile.purchaseHistory', ['tab' => 'to_pay'])->with('error', 'Invalid session. Please try again from Purchase History.');
        }

        // $intent already retrieved above for both flows

        $createOrderFromPending = function (string $status, ?int $totalAmountCents = null, bool $amountExcludesFee = false) use ($pending, $paymentIntentId) {
            $amount = $totalAmountCents ?? $pending['total_amount_cents'];
            $order = Order::create([
                'user_id' => $pending['user_id'],
                'total_amount_cents' => $amount,
                'amount_excludes_fee' => $amountExcludesFee,
                'currency' => $pending['currency'] ?? 'myr',
                'status' => $status,
                'stripe_payment_intent_id' => $paymentIntentId,
                'payment_method' => $pending['payment_method'] ?? null,
                'buyer_snapshot' => $pending['buyer_snapshot'] ?? [],
                'ticket_holders_snapshot' => $pending['ticket_holders_snapshot'] ?? [],
                'promo_code_id' => $pending['promo_code_id'] ?? null,
                'affiliate_code_id' => $pending['affiliate_code_id'] ?? null,
            ]);
            foreach ($pending['order_items'] ?? [] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'ticket_id' => $item['ticket_id'],
                    'event_id' => $item['event_id'],
                    'quantity' => $item['quantity'],
                    'unit_price_cents' => $item['unit_price_cents'],
                ]);
            }
            return $order;
        };

        if ($intent->status === 'succeeded') {
            try {
                DB::beginTransaction();
                $order = $createOrderFromPending('paid');
                $order->load('items');
                foreach ($order->items->groupBy('event_id') as $eventId => $items) {
                    $event = Event::find($eventId);
                    if ($event && $event->ticket_stock !== null) {
                        $quantitySold = $items->sum('quantity');
                        $newStock = max(0, (int) $event->ticket_stock - $quantitySold);
                        $event->update(['ticket_stock' => $newStock]);
                    }
                }
                if ($order->affiliate_code_id) {
                    AffiliateCode::where('id', $order->affiliate_code_id)->increment('total_conversion');
                }
                Cart::where('user_id', Auth::id())->delete();
                session()->forget(['cart_promo_code_id', 'cart_promo_code', 'cart_promo_discount', 'checkout_pending_payment_intent_id', 'checkout_pending_data', 'checkout_affiliate_code_id', 'checkout_affiliate_code']);
                DB::commit();
                $this->sendPaymentSuccessEmail($order);
                return redirect()->route('profile.purchaseHistory', ['tab' => 'completed'])->with('success', 'Payment successful. Thank you for your purchase.');
            } catch (\Throwable $e) {
                DB::rollBack();
                return redirect()->route('profile.purchaseHistory', ['tab' => 'to_pay'])->with('error', 'Order could not be completed. Please contact support.');
            }
        }

        // Payment failed or not completed: create order as pending with subtotal only (no fee); fee applied on repay
        try {
            DB::beginTransaction();
            $pendingAmount = isset($pending['subtotal_cents']) ? (int) $pending['subtotal_cents'] : null;
            $order = $createOrderFromPending('pending', $pendingAmount, $pendingAmount !== null);
            if (!empty($pending['client_secret'])) {
                $order->update(['stripe_client_secret_encrypted' => Crypt::encryptString($pending['client_secret'])]);
            }
            Cart::where('user_id', Auth::id())->delete();
            session()->forget(['cart_promo_code_id', 'cart_promo_code', 'cart_promo_discount', 'checkout_pending_payment_intent_id', 'checkout_pending_data', 'checkout_affiliate_code_id', 'checkout_affiliate_code']);
            DB::commit();
            $this->sendPaymentFailedEmail($order);
        } catch (\Throwable $e) {
            DB::rollBack();
        }

        if (in_array($intent->status, ['canceled', 'requires_payment_method'], true)) {
            return redirect()->route('profile.purchaseHistory', ['tab' => 'to_pay'])->with('error', 'Payment was unsuccessful. Please try again.');
        }

        return redirect()->route('profile.purchaseHistory', ['tab' => 'to_pay'])->with('info', 'Payment is still processing.');
    }

    /**
     * Send payment success email to the buyer (receipt + all QR codes) and to each ticket holder (their QR + buyer contact details).
     */
    private function sendPaymentSuccessEmail(Order $order): void
    {
        $buyerEmail = $order->buyer_snapshot['buyer_email'] ?? $order->user?->email;
        if (!empty($buyerEmail)) {
            try {
                Mail::to($buyerEmail)->send(new PaymentSuccessMail($order));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $holders = $order->ticket_holders_snapshot ?? [];
        foreach ($holders as $i => $h) {
            $holderEmail = $h['email'] ?? null;
            if (empty($holderEmail) || !filter_var($holderEmail, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            try {
                Mail::to($holderEmail)->send(new TicketHolderPaymentSuccessMail($order, $i));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    /**
     * Send payment failed / order pending email to the buyer.
     */
    private function sendPaymentFailedEmail(Order $order): void
    {
        $email = $order->buyer_snapshot['buyer_email'] ?? $order->user?->email;
        if (empty($email)) {
            return;
        }
        try {
            Mail::to($email)->send(new PaymentFailedMail($order));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
