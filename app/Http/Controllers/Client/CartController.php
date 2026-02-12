<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Event;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Display the cart page
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to view your cart.');
        }

        $carts = Cart::with(['ticket', 'event'])
            ->where('user_id', Auth::id())
            ->get();

        // Group carts by event
        $events = $carts->groupBy('event_id');

        // Get event details for the first event (assuming single event per cart session)
        $event = null;
        $eventData = null;
        if ($events->isNotEmpty()) {
            $firstEventId = $events->keys()->first();
            $event = Event::with(['category', 'tickets'])
                ->find($firstEventId);
            
            if ($event) {
                // Format event data similar to EventController
                $images = [];
                if ($event->images && is_array($event->images) && count($event->images) > 0) {
                    foreach ($event->images as $imagePath) {
                        $images[] = storage_asset($imagePath);
                    }
                }
                if (empty($images)) {
                    $images = ['https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=800&h=1000&fit=crop'];
                }
                
                $eventData = [
                    'id' => $event->id,
                    'name' => $event->name,
                    'description' => $event->description,
                    'category' => $event->category ? $event->category->name : null,
                    'location' => $event->location,
                    'google_maps_address' => $event->google_maps_address,
                    'waze_location_address' => $event->waze_location_address,
                    'start_datetime' => $event->start_datetime,
                    'end_datetime' => $event->end_datetime,
                    'date_time_formatted' => $event->start_datetime->format('j M Y, g:iA'),
                    'image' => $images[0] ?? null,
                    'images' => $images,
                    'ticket_stock' => $event->ticket_stock ?? null,
                ];
            }
        }

        // Calculate total amount (respects quantity_discount tiers)
        $totalAmount = 0;
        foreach ($carts as $cart) {
            $unitPrice = $cart->ticket->getPriceForQuantity($cart->quantity);
            $totalAmount += $unitPrice * $cart->quantity;
        }

        // Check applied promo code from session
        $appliedPromo = null;
        $discountAmount = 0;
        $totalAfterDiscount = $totalAmount;
        $firstEventId = $events->isNotEmpty() ? $events->keys()->first() : null;

        if (session()->has('cart_promo_code_id') && $totalAmount > 0) {
            $promo = PromoCode::with('events')->find(session('cart_promo_code_id'));
            if ($promo && $promo->status === 'active') {
                $appliesToEvent = $promo->events->isEmpty()
                    || ($firstEventId && $promo->events->contains('id', $firstEventId));
                if ($appliesToEvent) {
                    $appliedPromo = $promo;
                    $discountAmount = min((float) $promo->discount, $totalAmount);
                    $totalAfterDiscount = max(0, $totalAmount - $discountAmount);
                } else {
                    session()->forget(['cart_promo_code_id', 'cart_promo_code', 'cart_promo_discount']);
                }
            } else {
                session()->forget(['cart_promo_code_id', 'cart_promo_code', 'cart_promo_discount']);
            }
        }

        return view('client.cart.index', [
            'carts' => $carts,
            'events' => $events,
            'event' => $eventData,
            'totalAmount' => $totalAmount,
            'appliedPromo' => $appliedPromo,
            'discountAmount' => $discountAmount,
            'totalAfterDiscount' => $totalAfterDiscount,
        ]);
    }

    /**
     * Apply promo code to cart
     */
    public function applyPromo(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $request->validate([
            'promo_code' => 'required|string|max:255',
        ]);

        $code = trim($request->input('promo_code'));
        if (empty($code)) {
            return redirect()->route('cart.index')->with('error', 'Please enter a promo code.');
        }

        $promo = PromoCode::with('events')->where('code', $code)->first();
        if (!$promo) {
            return redirect()->route('cart.index')->with('error', 'Invalid promo code.');
        }
        if ($promo->status !== 'active') {
            return redirect()->route('cart.index')->with('error', 'This promo code is no longer active.');
        }

        $carts = Cart::where('user_id', Auth::id())->get();
        if ($carts->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }
        $firstEventId = $carts->first()->event_id;
        $appliesToEvent = $promo->events->isEmpty() || $promo->events->contains('id', $firstEventId);
        if (!$appliesToEvent) {
            return redirect()->route('cart.index')->with('error', 'This promo code does not apply to the event in your cart.');
        }

        session()->put('cart_promo_code_id', $promo->id);
        session()->put('cart_promo_code', $promo->code);
        session()->put('cart_promo_discount', (float) $promo->discount);

        return redirect()->route('cart.index')->with('success', 'Promo code applied successfully.');
    }

    /**
     * Remove applied promo code from cart
     */
    public function removePromo()
    {
        session()->forget(['cart_promo_code_id', 'cart_promo_code', 'cart_promo_discount']);
        return redirect()->route('cart.index')->with('success', 'Promo code removed.');
    }

    /**
     * Add items to cart
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to add items to cart.');
        }

        $request->validate([
            'ticket_qty' => 'required|array',
            'ticket_qty.*' => 'required|integer|min:0',
            'event_id' => 'required|exists:events,id',
        ]);

        $eventId = $request->input('event_id');
        $ticketQuantities = $request->input('ticket_qty', []);

        // Add to existing quantities (same ticket + same event = increase quantity)
        foreach ($ticketQuantities as $ticketId => $quantity) {
            $quantity = (int) $quantity;
            if ($quantity <= 0) {
                continue;
            }

            $existing = Cart::where('user_id', Auth::id())
                ->where('event_id', $eventId)
                ->where('ticket_id', $ticketId)
                ->first();

            if ($existing) {
                $existing->update(['quantity' => $existing->quantity + $quantity]);
            } else {
                Cart::create([
                    'user_id' => Auth::id(),
                    'ticket_id' => $ticketId,
                    'event_id' => $eventId,
                    'quantity' => $quantity,
                ]);
            }
        }

        return redirect()->route('cart.index')->with('success', 'Items added to cart successfully.');
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $cart = Cart::with(['ticket', 'event'])->where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        $newQuantity = (int) $request->input('quantity');

        // Enforce event ticket stock
        if ($cart->event && $cart->event->ticket_stock !== null) {
            $totalInEvent = Cart::where('user_id', Auth::id())
                ->where('event_id', $cart->event_id)
                ->sum('quantity');
            $otherItemsQty = $totalInEvent - $cart->quantity;
            $maxForThisItem = max(0, (int) $cart->event->ticket_stock - $otherItemsQty);

            if ($newQuantity > $maxForThisItem) {
                return redirect()->route('cart.index')
                    ->with('error', 'Quantity cannot exceed available ticket stock (' . $maxForThisItem . ').');
            }
        }

        $cart->update(['quantity' => $newQuantity]);

        return redirect()->route('cart.index')->with('success', 'Quantity updated.');
    }

    /**
     * Update all cart item quantities (when user clicks Done)
     */
    public function updateQuantities(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $quantities = $request->input('quantities', []);
        if (!is_array($quantities)) {
            return redirect()->route('cart.index')->with('error', 'Invalid request.');
        }

        $carts = Cart::with(['ticket', 'event'])
            ->where('user_id', Auth::id())
            ->whereIn('id', array_keys($quantities))
            ->get();

        if ($carts->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'No items to update.');
        }

        // Validate and enforce event ticket stock per event
        $eventTotals = [];
        foreach ($carts as $cart) {
            $qty = (int) ($quantities[$cart->id] ?? 0);
            if ($qty < 1) {
                $qty = 1;
            }
            if ($qty > 99) {
                $qty = 99;
            }
            $eventTotals[$cart->event_id] = ($eventTotals[$cart->event_id] ?? 0) + $qty;
        }

        foreach ($eventTotals as $eventId => $total) {
            $event = Event::find($eventId);
            if ($event && $event->ticket_stock !== null && $total > (int) $event->ticket_stock) {
                return redirect()->route('cart.index')
                    ->with('error', 'Total quantity cannot exceed available ticket stock (' . $event->ticket_stock . ') for this event.');
            }
        }

        foreach ($carts as $cart) {
            $qty = (int) ($quantities[$cart->id] ?? $cart->quantity);
            if ($qty < 1) {
                $qty = 1;
            }
            if ($qty > 99) {
                $qty = 99;
            }
            $cart->update(['quantity' => $qty]);
        }

        return redirect()->route('cart.index')->with('success', 'Cart updated.');
    }

    /**
     * Remove item from cart
     */
    public function destroy($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $cart = Cart::where('user_id', Auth::id())
            ->findOrFail($id);

        $cart->delete();

        return redirect()->route('cart.index')->with('success', 'Item removed from cart.');
    }

    /**
     * Clear all cart items
     */
    public function clear()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        Cart::where('user_id', Auth::id())->delete();

        return redirect()->route('cart.index')->with('success', 'Cart cleared.');
    }
}
