@extends('layouts.admin.app')

@section('title', !empty($refundOrdersFilter) ? 'Refund Orders' : 'Orders')

@push('styles')
<style>
    .form-label {
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    .form-control-sm,
    .form-select-sm {
        font-size: 0.875rem;
    }

    /* Table centering */
    .table th.text-center,
    .table td.text-center {
        text-align: center !important;
        vertical-align: middle !important;
    }

    .table th {
        text-align: center !important;
        vertical-align: middle !important;
    }

    .table td {
        text-align: center !important;
        vertical-align: middle !important;
    }

    .btn-admin-success {
        background-color: #10b981;
        color: white;
    }

    .btn-admin-success:hover {
        background-color: #059669;
        color: white;
    }
</style>
@endpush

@section('content')
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">{{ !empty($refundOrdersFilter) ? 'Refund Orders' : 'Orders' }}</h3>
            <a href="{{ route('admin.orders.export', array_filter([
                'search' => $search ?? '',
                'status' => $statusFilter ?? '',
                'payment_method' => $paymentFilter ?? '',
                'refund_status' => $refundStatusFilter ?? '',
                'refund_orders' => $refundOrdersFilter ?? '',
                'event' => $eventFilter ?? '',
                'date_from' => $dateFromFilter ?? '',
                'date_to' => $dateToFilter ?? '',
            ])) }}"
               class="btn-admin btn-admin-success"
               title="Export filtered orders to Excel">
                <i class="bi bi-download"></i>
            </a>
        </div>
        <div class="card-body">
            <!-- Filter Section -->
            <form method="GET" action="{{ route('admin.orders') }}" class="mb-4" id="filterForm">
                @if(!empty($refundOrdersFilter))
                    <input type="hidden" name="refund_orders" value="1">
                @endif
                <div class="row g-3 align-items-end">
                    <!-- Search Input -->
                    <div class="col-md-4">
                        <label for="search" class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Search</label>
                        <input type="text"
                               class="form-control form-control-sm"
                               id="search"
                               name="search"
                               placeholder="Search by reference number or customer name/email..."
                               value="{{ $search ?? '' }}"
                               autocomplete="off">
                    </div>

                    @if(!empty($refundOrdersFilter))
                    <!-- Refund status filter (Refund Orders page only) -->
                    <div class="col-md-4">
                        <label for="refund_status" class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Refund status</label>
                        <select class="form-select form-select-sm" id="refund_status" name="refund_status">
                            <option value="">All</option>
                            <option value="pending" {{ ($refundStatusFilter ?? '') === 'pending' ? 'selected' : '' }}>Reviewing</option>
                            <option value="approved" {{ ($refundStatusFilter ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ ($refundStatusFilter ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    @else
                    <!-- Order status filter (All Orders page only) -->
                    <div class="col-md-4">
                        <label for="status" class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Status</label>
                        <select class="form-select form-select-sm" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="pending" {{ ($statusFilter ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ ($statusFilter ?? '') === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="cancelled" {{ ($statusFilter ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="refunded" {{ ($statusFilter ?? '') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>
                    @endif

                    <!-- Payment Method Filter -->
                    <div class="col-md-4">
                        <label for="payment_method" class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Payment Method</label>
                        <select class="form-select form-select-sm" id="payment_method" name="payment_method">
                            <option value="">All Payment Methods</option>
                            <option value="card" {{ ($paymentFilter ?? '') === 'card' ? 'selected' : '' }}>Card</option>
                            <option value="fpx" {{ ($paymentFilter ?? '') === 'fpx' ? 'selected' : '' }}>FPX</option>
                        </select>
                    </div>

                    <!-- Event Filter -->
                    <div class="col-md-4">
                        <label for="event" class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Event</label>
                        <select class="form-select form-select-sm" id="event" name="event">
                            <option value="">All Events</option>
                            @foreach($events ?? [] as $event)
                                <option value="{{ $event->id }}" {{ (string)($eventFilter ?? '') === (string)$event->id ? 'selected' : '' }}>{{ $event->name }} ({{ $event->category->name ?? 'Uncategorized' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date range -->
                    <div class="col-md-4">
                        <label for="date_from" class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">From date</label>
                        <input type="date"
                               class="form-control form-control-sm"
                               id="date_from"
                               name="date_from"
                               value="{{ $dateFromFilter ?? '' }}">
                    </div>
                    <div class="col-md-4">
                        <label for="date_to" class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">To date</label>
                        <input type="date"
                               class="form-control form-control-sm"
                               id="date_to"
                               name="date_to"
                               value="{{ $dateToFilter ?? '' }}">
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="text-center">NO.</th>
                            <th class="text-center">Reference</th>
                            <th class="text-center">Buyer</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Payment</th>
                            <th class="text-center">Created At</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            @php
                                $buyer = $order->buyer_snapshot ?? [];
                                $buyerName = $buyer['buyer_name'] ?? $order->user->name ?? '-';
                                $buyerEmail = $buyer['buyer_email'] ?? $order->user->email ?? '-';
                            @endphp
                            <tr>
                                <td class="text-center">{{ $loop->iteration + ($orders->currentPage() - 1) * $orders->perPage() }}</td>
                                <td class="text-center"><span class="text-nowrap">{{ $order->stripe_payment_intent_id ?? '-' }}</span></td>
                                <td class="text-center">
                                    <div>{{ $buyerName }}</div>
                                    <small class="text-muted">{{ $buyerEmail }}</small>
                                </td>
                                <td class="text-center">RM {{ number_format($order->total_amount_cents / 100, 2) }}</td>
                                <td class="text-center">
                                    @if(!empty($refundOrdersFilter) || !empty($refundStatusFilter))
                                        <span class="badge {{ $order->refund_status === 'pending' ? 'bg-warning text-dark' : 'bg-info text-dark' }}">
                                            {{ $order->refund_status === 'pending' ? 'Reviewing' : ucfirst($order->refund_status ?? 'refunded') }}
                                        </span>
                                    @else
                                        <span class="badge {{ $order->status === 'paid' ? 'bg-success' : ($order->status === 'cancelled' ? 'bg-secondary' : ($order->status === 'refunded' ? 'bg-info text-dark' : 'bg-warning text-dark')) }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $order->payment_method ? ucfirst($order->payment_method) : '-' }}</td>
                                <td class="text-center">{{ $order->created_at->format('M d, Y') }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-order-view" title="View order details"
                                            data-reference="{{ $order->stripe_payment_intent_id ?? $order->id }}"
                                            data-modal-url="{{ route('admin.orders.modal', $order) }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#orderDetailModal">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @if(in_array($order->status, ['paid', 'refunded'], true))
                                        <a href="{{ route('admin.orders.receipt', $order) }}"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="Download receipt">
                                            <i class="bi bi-receipt"></i>
                                        </a>
                                        @php
                                            $holders = $order->ticket_holders_snapshot ?? [];
                                            $qrUrls = [];
                                            for ($i = 0; $i < count($holders); $i++) {
                                                $qrUrls[] = route('admin.orders.qrCode', ['order' => $order, 'index' => $i]);
                                            }
                                        @endphp
                                        @if(count($qrUrls) > 0)
                                            <button type="button" class="btn btn-sm btn-outline-secondary btn-download-qr-codes" title="Download QR codes (one per ticket holder)" data-qr-urls="{{ json_encode($qrUrls) }}">
                                                <i class="bi bi-qr-code"></i>
                                            </button>
                                        @endif
                                    @endif
                                    @if((!empty($refundOrdersFilter) || !empty($refundStatusFilter)) && $order->refund_status === 'pending')
                                        <form method="POST" action="{{ route('admin.orders.refund.approve', $order) }}" style="display:inline-block; margin-left: 4px;"
                                              onsubmit="return confirm('Approve this refund request? This will process a real refund in Stripe.');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Approve refund"><i class="bi bi-check-lg"></i></button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.orders.refund.reject', $order) }}" style="display:inline-block; margin-left: 4px;"
                                              onsubmit="return confirm('Reject this refund request? No money will be refunded.');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Reject refund"><i class="bi bi-x-lg"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $orders->links() }}
            </div>
        </div>
    </div>

    <!-- Order Detail Modal -->
    <div class="modal fade" id="orderDetailModal" tabindex="-1" aria-labelledby="orderDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderDetailModalLabel">Reference: <span id="orderDetailModalReference">-</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="orderDetailModalBody">
                    <div class="text-center py-4 text-muted" id="orderDetailModalLoading">
                        <div class="spinner-border spinner-border-sm" role="status"></div>
                        <span class="ms-2">Loading...</span>
                    </div>
                    <div id="orderDetailModalContent" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var filterForm = document.getElementById('filterForm');
    if (filterForm) {
        var statusSelect = document.getElementById('status');
        var refundStatusSelect = document.getElementById('refund_status');
        var paymentSelect = document.getElementById('payment_method');
        var searchInput = document.getElementById('search');
        var eventSelect = document.getElementById('event');
        var dateFromInput = document.getElementById('date_from');
        var dateToInput = document.getElementById('date_to');
        if (statusSelect) statusSelect.addEventListener('change', function() { filterForm.submit(); });
        if (refundStatusSelect) refundStatusSelect.addEventListener('change', function() { filterForm.submit(); });
        if (paymentSelect) paymentSelect.addEventListener('change', function() { filterForm.submit(); });
        if (eventSelect) eventSelect.addEventListener('change', function() { filterForm.submit(); });
        if (dateFromInput) dateFromInput.addEventListener('change', function() { filterForm.submit(); });
        if (dateToInput) dateToInput.addEventListener('change', function() { filterForm.submit(); });
        if (searchInput) {
            var searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() { filterForm.submit(); }, 350);
            });
        }
    }

    var orderDetailModal = document.getElementById('orderDetailModal');
    if (!orderDetailModal) return;
    var modalReferenceEl = document.getElementById('orderDetailModalReference');
    var modalBodyContent = document.getElementById('orderDetailModalContent');
    var modalLoading = document.getElementById('orderDetailModalLoading');

    orderDetailModal.addEventListener('show.bs.modal', function(ev) {
        var button = ev.relatedTarget;
        if (!button || !button.classList.contains('btn-order-view')) return;
        var reference = button.getAttribute('data-reference');
        var modalUrl = button.getAttribute('data-modal-url');
        if (!modalUrl) return;
        modalReferenceEl.textContent = reference || '-';
        modalLoading.style.display = 'block';
        modalBodyContent.style.display = 'none';
        modalBodyContent.innerHTML = '';
        fetch(modalUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
            .then(function(r) { return r.text(); })
            .then(function(html) {
                modalLoading.style.display = 'none';
                modalBodyContent.innerHTML = html;
                modalBodyContent.style.display = 'block';
            })
            .catch(function() {
                modalLoading.innerHTML = '<span class="text-danger">Failed to load order details.</span>';
                modalLoading.style.display = 'block';
            });
    });

    document.querySelectorAll('.btn-download-qr-codes').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var urlsJson = this.getAttribute('data-qr-urls');
            if (!urlsJson) return;
            var urls = [];
            try {
                urls = JSON.parse(urlsJson);
            } catch (e) {
                return;
            }
            urls.forEach(function(url, i) {
                setTimeout(function() {
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = '';
                    a.style.display = 'none';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                }, i * 300);
            });
        });
    });
});
</script>
@endpush
