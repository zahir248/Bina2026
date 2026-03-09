@extends('layouts.admin.app')

@section('title', 'Reports')

@push('styles')
<style>
    .form-label { font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem; }
    .form-control-sm, .form-select-sm { font-size: 0.875rem; }
    .btn-admin-success { background-color: #10b981; color: white; }
    .btn-admin-success:hover { background-color: #059669; color: white; }
</style>
@endpush

@section('content')
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">Report</h3>
            <a href="{{ route('admin.reports.export', array_filter(['event' => $eventFilter ?? ''])) }}"
               class="btn-admin btn-admin-success"
               title="Export to Excel">
                <i class="bi bi-download"></i>
            </a>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports') }}" class="mb-4" id="filterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="event" class="form-label">Event</label>
                        <select class="form-select form-select-sm" id="event" name="event">
                            <option value="">All events</option>
                            @foreach($events ?? [] as $event)
                                <option value="{{ $event->id }}" {{ (string)($eventFilter ?? '') === (string)$event->id ? 'selected' : '' }}>
                                    {{ $event->name }} ({{ $event->category->name ?? 'Uncategorized' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>

            @if($selectedEvent)
                <p class="text-muted mb-3">
                    <strong>{{ $selectedEvent->name }}</strong>
                    @if($selectedEvent->category)
                        <span class="text-muted">({{ $selectedEvent->category->name }})</span>
                    @endif
                    — {{ $orderCount }} order(s)
                </p>
            @endif

            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon green">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <div class="stat-value">RM {{ number_format($totalRevenueCents / 100, 2) }}</div>
                        <div class="stat-label">{{ $selectedEvent ? 'Total revenue (this event)' : 'Total revenue (paid orders)' }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon green">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                        <div class="stat-value">RM {{ number_format($revenueExcludingFeeCents / 100, 2) }}</div>
                        <div class="stat-label">{{ $selectedEvent ? 'Revenue excl. fee (this event)' : 'Revenue excl. processing fee' }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon orange">
                            <i class="bi bi-credit-card-2-back"></i>
                        </div>
                        <div class="stat-value">RM {{ number_format($totalProcessingFeeCents / 100, 2) }}</div>
                        <div class="stat-label">{{ $selectedEvent ? 'Processing fees (this event)' : 'Total payment processing fees' }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon blue">
                            <i class="bi bi-cart-check"></i>
                        </div>
                        <div class="stat-value">{{ number_format($orderCount) }}</div>
                        <div class="stat-label">{{ $selectedEvent ? 'Orders (this event)' : 'Paid orders' }}</div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon purple">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="stat-value">{{ number_format($totalParticipants) }}</div>
                        <div class="stat-label">{{ $selectedEvent ? 'Total participants (this event)' : 'Total participants' }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon orange">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="stat-value">{{ $totalStock !== null && $totalStock !== '' ? number_format($totalStock) : '—' }}</div>
                        <div class="stat-label">{{ $selectedEvent ? 'Total stock (this event)' : 'Total stock (all events)' }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon orange">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </div>
                        <div class="stat-value">{{ number_format($refundedCount) }}</div>
                        <div class="stat-label">Refunded orders</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon orange">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div class="stat-value">RM {{ number_format($refundedAmountCents / 100, 2) }}</div>
                        <div class="stat-label">Refunded amount</div>
                    </div>
                </div>
            </div>

            <div class="admin-card" style="margin-bottom: 0;">
                <div class="card-header">
                    <h3 class="card-title">Ticket type statistics</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">Ticket type</th>
                                    @if(!$selectedEvent)
                                        <th class="text-center">Event</th>
                                    @endif
                                    <th class="text-center">Total sold</th>
                                    <th class="text-center">Total sales (excl. fee)</th>
                                    <th class="text-center">Total sales (with fee)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ticketStats as $stat)
                                    <tr>
                                        <td class="text-center">{{ $stat->ticket_name }}</td>
                                        @if(!$selectedEvent)
                                            <td class="text-center">{{ $stat->event_display ?? $stat->event_name ?? '—' }}</td>
                                        @endif
                                        <td class="text-center">{{ number_format($stat->total_sold) }}</td>
                                        <td class="text-center">RM {{ number_format($stat->total_sales_cents / 100, 2) }}</td>
                                        <td class="text-center">RM {{ number_format(($stat->total_sales_with_fee_cents ?? 0) / 100, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $selectedEvent ? 5 : 6 }}" class="text-muted text-center py-4">
                                            No ticket sales {{ $selectedEvent ? 'for this event.' : 'yet.' }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var filterForm = document.getElementById('filterForm');
    var eventSelect = document.getElementById('event');
    if (eventSelect && filterForm) {
        eventSelect.addEventListener('change', function() { filterForm.submit(); });
    }
});
</script>
@endpush
