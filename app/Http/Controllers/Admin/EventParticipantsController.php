<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class EventParticipantsController extends Controller
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

        $search = (string) ($request->get('search') ?? '');
        $eventFilter = (string) ($request->get('event') ?? '');
        $events = Event::with('category')->orderBy('name')->get();

        [$participants, $selectedEvent] = $this->buildParticipants($eventFilter, $search);

        return view('admin.event-participants.index', compact('events', 'search', 'eventFilter', 'participants', 'selectedEvent'));
    }

    public function export(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $search = (string) ($request->get('search') ?? '');
        $eventFilter = (string) ($request->get('event') ?? '');
        [$participants, $selectedEvent] = $this->buildParticipants($eventFilter, $search);

        $eventLabel = $selectedEvent
            ? $selectedEvent->name . ($selectedEvent->category ? ' (' . $selectedEvent->category->name . ')' : '')
            : 'Event Participants';
        $safeName = trim(preg_replace('/-+/', '-', preg_replace('/[^a-zA-Z0-9_-]/', '-', $eventLabel)), '-');
        $filename = 'event-participants-' . $safeName . '-' . now()->format('Y-m-d-His') . '.pdf';

        return Pdf::loadView('admin.event-participants.pdf', compact('participants', 'selectedEvent'))
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }

    /**
     * @return array{0: Collection, 1: Event|null}
     */
    private function buildParticipants(string $eventFilter, string $search): array
    {
        $participants = collect();
        $selectedEvent = null;

        if (!empty($eventFilter)) {
            $selectedEvent = Event::with('category')->find($eventFilter);
            if ($selectedEvent) {
                $orderIds = OrderItem::where('event_id', $eventFilter)->pluck('order_id')->unique();
                $orders = Order::with(['items' => fn ($q) => $q->orderBy('id')->with(['ticket', 'event'])])
                    ->whereIn('id', $orderIds)
                    ->where('status', 'paid')
                    ->where(function ($q) {
                        $q->whereNull('refund_status')->orWhere('refund_status', 'rejected');
                    })
                    ->orderBy('created_at', 'desc')
                    ->get();

                if ($this->hideTestPaymentDataEnabled()) {
                    $orders = $orders->where('stripe_test_mode', false)->values();
                }

                foreach ($orders as $order) {
                    $holders = $order->ticket_holders_snapshot ?? [];
                    $holderIndex = 0;
                    foreach ($order->items as $item) {
                        $qty = (int) $item->quantity;
                        if ((int) $item->event_id === (int) $eventFilter) {
                            $ticketName = $item->ticket->name ?? 'Ticket';
                            for ($q = 0; $q < $qty && $holderIndex < count($holders); $q++) {
                                $participants->push((object) [
                                    'holder' => $holders[$holderIndex],
                                    'order' => $order,
                                    'ticket_name' => $ticketName,
                                ]);
                                $holderIndex++;
                            }
                        } else {
                            $holderIndex += $qty;
                        }
                    }
                }
            }
        }

        if (!empty($search)) {
            $searchLower = mb_strtolower($search);
            $participants = $participants->filter(function ($p) use ($searchLower) {
                $h = $p->holder;
                $fields = [
                    $h['full_name'] ?? '',
                    $h['email'] ?? '',
                    $h['nric_passport'] ?? '',
                    $h['contact_number'] ?? '',
                    $h['company_name'] ?? '',
                ];
                foreach ($fields as $field) {
                    if ($field !== '' && mb_strpos(mb_strtolower($field), $searchLower) !== false) {
                        return true;
                    }
                }
                return false;
            })->values();
        }

        return [$participants, $selectedEvent];
    }
}
