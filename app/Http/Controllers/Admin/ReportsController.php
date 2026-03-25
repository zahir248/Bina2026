<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ReportExport;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    private function hideTestPaymentDataEnabled(): bool
    {
        return Setting::get(SettingsController::KEY_HIDE_TEST_PAYMENT_DATA_IN_ADMIN, '0') === '1';
    }

    public function index(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $eventFilter = (string) $request->get('event', '');
        $events = Event::with('category')->orderBy('name')->get();
        $selectedEvent = null;

        if (!empty($eventFilter)) {
            $selectedEvent = Event::with('category')->find($eventFilter);
        }

        $orderIdsForEvent = null;
        if ($selectedEvent) {
            $orderIdsForEvent = OrderItem::where('event_id', $eventFilter)
                ->pluck('order_id')
                ->unique()
                ->values();
        }

        $baseQuery = Order::query()->where('status', 'paid');
        if ($this->hideTestPaymentDataEnabled()) {
            $baseQuery->where('stripe_test_mode', false);
        }
        if ($orderIdsForEvent !== null) {
            $baseQuery->whereIn('id', $orderIdsForEvent);
        }

        [$revenueExcludingFeeCents, $totalProcessingFeeCents] = $this->getRevenueExcludingFeeAndFees($eventFilter);

        // When an event is selected, total revenue = event's share of what was charged (revenue excl. fee + fees).
        // When no event, total revenue = sum of order total_amount_cents.
        $totalRevenueCents = !empty($eventFilter)
            ? $revenueExcludingFeeCents + $totalProcessingFeeCents
            : $this->getRevenueCents($eventFilter);
        $orderCount = (clone $baseQuery)->count();

        $refundedQuery = Order::query()->whereIn('refund_status', ['approved', 'completed']);
        if ($this->hideTestPaymentDataEnabled()) {
            $refundedQuery->where('stripe_test_mode', false);
        }
        if ($orderIdsForEvent !== null) {
            $refundedQuery->whereIn('id', $orderIdsForEvent);
        }
        $refundedCount = (clone $refundedQuery)->count();
        $refundedAmountCents = (clone $refundedQuery)->sum('total_amount_cents');

        $totalParticipants = $this->getTotalParticipants($eventFilter);
        $totalStock = $selectedEvent !== null
            ? $selectedEvent->ticket_stock
            : Event::sum('ticket_stock');

        $ticketStats = $this->getTicketTypeStats($eventFilter);

        return view('admin.reports.index', [
            'totalRevenueCents' => $totalRevenueCents,
            'revenueExcludingFeeCents' => $revenueExcludingFeeCents,
            'totalProcessingFeeCents' => $totalProcessingFeeCents,
            'orderCount' => $orderCount,
            'refundedCount' => $refundedCount,
            'refundedAmountCents' => $refundedAmountCents,
            'totalParticipants' => $totalParticipants,
            'totalStock' => $totalStock,
            'events' => $events,
            'eventFilter' => $eventFilter,
            'selectedEvent' => $selectedEvent,
            'ticketStats' => $ticketStats,
        ]);
    }

    /**
     * Export report to Excel (all summary statistics + ticket type statistics table).
     */
    public function export(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $eventFilter = (string) $request->get('event', '');
        $selectedEvent = !empty($eventFilter) ? Event::with('category')->find($eventFilter) : null;

        $orderIdsForEvent = null;
        if ($selectedEvent) {
            $orderIdsForEvent = OrderItem::where('event_id', $eventFilter)->pluck('order_id')->unique()->values();
        }

        $baseQuery = Order::query()->where('status', 'paid');
        if ($this->hideTestPaymentDataEnabled()) {
            $baseQuery->where('stripe_test_mode', false);
        }
        if ($orderIdsForEvent !== null) {
            $baseQuery->whereIn('id', $orderIdsForEvent);
        }

        [$revenueExcludingFeeCents, $totalProcessingFeeCents] = $this->getRevenueExcludingFeeAndFees($eventFilter);
        $totalRevenueCents = !empty($eventFilter)
            ? $revenueExcludingFeeCents + $totalProcessingFeeCents
            : $this->getRevenueCents($eventFilter);
        $orderCount = (clone $baseQuery)->count();

        $refundedQuery = Order::query()->whereIn('refund_status', ['approved', 'completed']);
        if ($this->hideTestPaymentDataEnabled()) {
            $refundedQuery->where('stripe_test_mode', false);
        }
        if ($orderIdsForEvent !== null) {
            $refundedQuery->whereIn('id', $orderIdsForEvent);
        }
        $refundedCount = (clone $refundedQuery)->count();
        $refundedAmountCents = (clone $refundedQuery)->sum('total_amount_cents');

        $totalParticipants = $this->getTotalParticipants($eventFilter);
        $totalStock = $selectedEvent !== null ? $selectedEvent->ticket_stock : Event::sum('ticket_stock');
        $ticketStats = $this->getTicketTypeStats($eventFilter);
        $includeEventColumn = empty($eventFilter);

        $filenameSuffix = '';
        if ($selectedEvent) {
            $eventLabel = $selectedEvent->name . ($selectedEvent->category ? ' (' . $selectedEvent->category->name . ')' : '');
            $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '-', $eventLabel);
            $safeName = trim(preg_replace('/-+/', '-', $safeName), '-');
            $filenameSuffix = '-' . $safeName;
        }
        $filename = 'report' . $filenameSuffix . '-' . now()->format('Y-m-d-His') . '.xlsx';

        return Excel::download(
            new ReportExport(
                $totalRevenueCents,
                $revenueExcludingFeeCents,
                $totalProcessingFeeCents,
                $orderCount,
                $totalParticipants,
                $totalStock,
                $refundedCount,
                $refundedAmountCents,
                $ticketStats,
                $includeEventColumn
            ),
            $filename,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    /**
     * Ticket type statistics: total sold, total sales (excl. fee), total sales (with fee) per ticket type.
     * When event filter is set, show all tickets for that event (including 0 sold/sales); otherwise ticket+event combos with sales only.
     */
    private function getTicketTypeStats(string $eventFilter): \Illuminate\Support\Collection
    {
        $salesQuery = OrderItem::query()
            ->whereHas('order', function ($q) {
                $q->where('status', 'paid');
                if ($this->hideTestPaymentDataEnabled()) {
                    $q->where('stripe_test_mode', false);
                }
            })
            ->selectRaw('ticket_id, event_id, SUM(quantity) as total_sold, SUM(unit_price_cents * quantity) as total_sales_cents')
            ->groupBy('ticket_id', 'event_id');

        if (!empty($eventFilter)) {
            $salesQuery->where('event_id', $eventFilter);
        }

        $salesRows = $salesQuery->get();
        $withFeeByKey = $this->getTicketTypeSalesWithFeeCents($eventFilter);

        if (!empty($eventFilter)) {
            $event = Event::with('tickets')->find($eventFilter);
            if (!$event) {
                return collect();
            }
            $salesByTicket = $salesRows->keyBy('ticket_id');
            return $event->tickets->map(function ($ticket) use ($salesByTicket, $withFeeByKey, $eventFilter) {
                $row = $salesByTicket->get($ticket->id);
                $key = "{$ticket->id}-{$eventFilter}";
                return (object) [
                    'ticket_name' => $ticket->name,
                    'event_name' => null,
                    'total_sold' => $row ? (int) $row->total_sold : 0,
                    'total_sales_cents' => $row ? (int) $row->total_sales_cents : 0,
                    'total_sales_with_fee_cents' => $withFeeByKey[$key] ?? 0,
                ];
            });
        }

        $ticketIds = $salesRows->pluck('ticket_id')->unique()->filter()->values();
        $eventIds = $salesRows->pluck('event_id')->unique()->filter()->values();
        $tickets = Ticket::whereIn('id', $ticketIds)->get()->keyBy('id');
        $eventsList = Event::with('category')->whereIn('id', $eventIds)->get()->keyBy('id');

        return $salesRows->map(function ($row) use ($tickets, $eventsList, $withFeeByKey) {
            $ticket = $tickets->get($row->ticket_id);
            $event = $eventsList->get($row->event_id);
            $key = "{$row->ticket_id}-{$row->event_id}";
            $eventDisplay = $event
                ? $event->name . ' (' . ($event->category->name ?? 'Uncategorized') . ')'
                : '—';
            return (object) [
                'ticket_name' => $ticket ? $ticket->name : 'Unknown',
                'event_name' => $event ? $event->name : null,
                'event_display' => $eventDisplay,
                'total_sold' => (int) $row->total_sold,
                'total_sales_cents' => (int) $row->total_sales_cents,
                'total_sales_with_fee_cents' => $withFeeByKey[$key] ?? 0,
            ];
        });
    }

    /**
     * Per (ticket_id, event_id): allocated share of order total_amount_cents (what was charged, including fee).
     * Key format: "ticket_id-event_id".
     *
     * @return array<string, int>
     */
    private function getTicketTypeSalesWithFeeCents(string $eventFilter): array
    {
        $orders = Order::query()
            ->where('status', 'paid')
            ->with('items');

        if ($this->hideTestPaymentDataEnabled()) {
            $orders->where('stripe_test_mode', false);
        }

        if (!empty($eventFilter)) {
            $orders->whereHas('items', fn ($q) => $q->where('event_id', $eventFilter));
        }

        $withFeeByKey = [];

        foreach ($orders->get() as $order) {
            $subtotalCents = $order->items->sum(fn ($i) => $i->unit_price_cents * $i->quantity);
            if ($subtotalCents <= 0) {
                continue;
            }
            $totalChargedCents = $order->total_amount_cents;
            foreach ($order->items as $item) {
                $itemCents = $item->unit_price_cents * $item->quantity;
                $allocated = (int) round($totalChargedCents * ($itemCents / $subtotalCents));
                $key = "{$item->ticket_id}-{$item->event_id}";
                $withFeeByKey[$key] = ($withFeeByKey[$key] ?? 0) + $allocated;
            }
        }

        return $withFeeByKey;
    }

    /**
     * Revenue excluding payment processing fee (subtotal after promo) and total processing fees.
     * Uses same logic as OrdersExport: afterDiscountCents = subtotal - promo discount; fee = total - afterDiscount.
     * When event filter is set, allocates proportionally by event's share of order subtotal.
     *
     * @return array{0: int, 1: int} [revenueExcludingFeeCents, totalProcessingFeeCents]
     */
    private function getRevenueExcludingFeeAndFees(string $eventFilter): array
    {
        $orders = Order::query()
            ->where('status', 'paid')
            ->with(['items', 'promoCode']);

        if ($this->hideTestPaymentDataEnabled()) {
            $orders->where('stripe_test_mode', false);
        }

        if (!empty($eventFilter)) {
            $orders->whereHas('items', fn ($q) => $q->where('event_id', $eventFilter));
        }

        $revenueExcludingFeeCents = 0;
        $totalFeeCents = 0;

        foreach ($orders->get() as $order) {
            $subtotalCents = $order->items->sum(fn ($i) => $i->unit_price_cents * $i->quantity);
            $discountCents = 0;
            if ($order->promoCode) {
                $discountCents = min((int) round($order->promoCode->discount * 100), $subtotalCents);
            }
            $afterDiscountCents = $subtotalCents - $discountCents;
            $orderFeeCents = max(0, $order->total_amount_cents - $afterDiscountCents);

            if (empty($eventFilter)) {
                $revenueExcludingFeeCents += $afterDiscountCents;
                $totalFeeCents += $orderFeeCents;
                continue;
            }

            $eventSubtotalCents = $order->items->where('event_id', $eventFilter)->sum(fn ($i) => $i->unit_price_cents * $i->quantity);
            if ($subtotalCents <= 0) {
                continue;
            }
            $ratio = $eventSubtotalCents / $subtotalCents;
            $revenueExcludingFeeCents += (int) round($afterDiscountCents * $ratio);
            $totalFeeCents += (int) round($orderFeeCents * $ratio);
        }

        return [$revenueExcludingFeeCents, $totalFeeCents];
    }

    /**
     * Total participants = sum of ticket quantities sold (paid orders), optionally for one event.
     */
    private function getTotalParticipants(string $eventFilter): int
    {
        $query = OrderItem::query()
            ->whereHas('order', function ($q) {
                $q->where('status', 'paid');
                if ($this->hideTestPaymentDataEnabled()) {
                    $q->where('stripe_test_mode', false);
                }
            });
        if (!empty($eventFilter)) {
            $query->where('event_id', $eventFilter);
        }
        return (int) $query->sum('quantity');
    }

    /**
     * Revenue in cents: when filtering by event, sum (unit_price_cents * quantity) for that event's items in paid orders; otherwise sum order total_amount_cents.
     */
    private function getRevenueCents(string $eventFilter): int
    {
        if (empty($eventFilter)) {
            $query = Order::query()
                ->where('status', 'paid');

            if ($this->hideTestPaymentDataEnabled()) {
                $query->where('stripe_test_mode', false);
            }

            return (int) $query->sum('total_amount_cents');
        }

        return (int) (OrderItem::query()
            ->where('event_id', $eventFilter)
            ->whereHas('order', function ($q) {
                $q->where('status', 'paid');
                if ($this->hideTestPaymentDataEnabled()) {
                    $q->where('stripe_test_mode', false);
                }
            })
            ->selectRaw('SUM(unit_price_cents * quantity) as total')
            ->value('total') ?? 0);
    }
}
