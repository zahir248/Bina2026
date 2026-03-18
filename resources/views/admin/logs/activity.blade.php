@extends('layouts.admin.app')

@section('title', 'Checkout Activity Log')
@section('page-title', 'Checkout Activity Log')

@push('styles')
<style>
    .form-label { font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem; }
    .form-control-sm, .form-select-sm { font-size: 0.875rem; }
    .table th, .table td { text-align: center !important; vertical-align: middle !important; }
    .table td.text-start { text-align: left !important; }
    .log-message { max-width: 260px; }
</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">Checkout &amp; Payment Activity</h3>
        </div>
        <div class="card-body">
            <!-- Filter Section -->
            <form class="mb-4" id="activityFilterForm">
                <div class="row g-3 align-items-end">
                    <!-- User Email Filter (dropdown; required to see logs) -->
                    <div class="col-md-4">
                        <label for="user_email" class="form-label">User email</label>
                        <select id="user_email"
                                name="user_id"
                                class="form-select form-select-sm">
                            <option value="">Select user...</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ (string)$selectedUserId === (string)$user->id ? 'selected' : '' }}>
                                    {{ $user->email }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Status Filter -->
                    <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select form-select-sm" id="status" name="status">
                            <option value="">All</option>
                            <option value="success" {{ ($statusFilter ?? '') === 'success' ? 'selected' : '' }}>Success</option>
                            <option value="failed" {{ ($statusFilter ?? '') === 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="pending" {{ ($statusFilter ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="started" {{ ($statusFilter ?? '') === 'started' ? 'selected' : '' }}>Started</option>
                        </select>
                    </div>
                    <!-- Flow Filter -->
                    <div class="col-md-4">
                        <label for="flow" class="form-label">Flow</label>
                        <select class="form-select form-select-sm" id="flow" name="flow">
                            <option value="">All</option>
                            <option value="checkout" {{ ($flowFilter ?? '') === 'checkout' ? 'selected' : '' }}>Checkout</option>
                            <option value="repay" {{ ($flowFilter ?? '') === 'repay' ? 'selected' : '' }}>Repay</option>
                        </select>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Created at</th>
                            <th>Reference</th>
                            <th>Flow</th>
                            <th>Action</th>
                            <th>Status</th>
                            <th class="text-start">Message / Payload</th>
                        </tr>
                    </thead>
                    <tbody id="activity-log-body">
                        @forelse($logs as $index => $log)
                            <tr class="activity-log-row"
                                data-user-id="{{ $log->user_id ?? '' }}"
                                data-status="{{ $log->status ?? '' }}"
                                data-flow="{{ $log->flow ?? '' }}">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $log->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    @if($log->stripe_payment_intent_id)
                                        <code class="small">{{ $log->stripe_payment_intent_id }}</code>
                                    @elseif($log->order_id)
                                        <span>#{{ $log->order_id }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ ucfirst($log->flow ?? '-') }}</td>
                                <td>{{ $log->action }}</td>
                                <td>
                                    @php
                                        $status = $log->status ?? '';
                                        $badgeClass = 'bg-secondary';
                                        if ($status === 'success') $badgeClass = 'bg-success';
                                        elseif ($status === 'failed') $badgeClass = 'bg-danger';
                                        elseif ($status === 'pending') $badgeClass = 'bg-warning text-dark';
                                        elseif ($status === 'started') $badgeClass = 'bg-info text-dark';
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $status !== '' ? ucfirst($status) : '-' }}</span>
                                </td>
                                <td class="text-start log-message">
                                    @if($log->message)
                                        <div class="mb-1">{{ \Illuminate\Support\Str::limit($log->message, 120) }}</div>
                                    @endif
                                    @if(is_array($log->payload))
                                        <details>
                                            <summary class="text-muted small">View payload</summary>
                                            <pre class="small mt-1 mb-0 text-muted" style="white-space: pre-wrap;">{{ json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </details>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            {{-- When there are no logs at all in DB, we still show a placeholder later via JS --}}
                        @endforelse
                        <tr id="activity-placeholder-row">
                            <td colspan="9" class="text-center align-middle text-muted" style="height: 200px;">
                                <span id="activity-placeholder-text">
                                    Please select a user email to view activity logs.
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
<!-- jQuery must be loaded before Select2; integrity removed to avoid CSP hash mismatch -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var userEmailSelect = document.getElementById('user_email');
    var statusSelect = document.getElementById('status');
    var flowSelect = document.getElementById('flow');

    // Initialize Select2 only when both jQuery and the plugin are available
    if (window.jQuery && typeof jQuery.fn.select2 !== 'undefined' && userEmailSelect) {
        jQuery(userEmailSelect)
            .select2({
                width: '100%',
                placeholder: 'Select user...',
                allowClear: true,
            })
            .on('change', function () {
                applyFilters();
            });
    } else if (userEmailSelect) {
        userEmailSelect.addEventListener('change', function() {
            applyFilters();
        });
    }
    if (statusSelect) statusSelect.addEventListener('change', function() { applyFilters(); });
    if (flowSelect) flowSelect.addEventListener('change', function() { applyFilters(); });

    function applyFilters() {
        var selectedUserId = userEmailSelect ? userEmailSelect.value : '';
        var selectedStatus = statusSelect ? statusSelect.value : '';
        var selectedFlow = flowSelect ? flowSelect.value : '';

        var rows = document.querySelectorAll('table.table tbody tr.activity-log-row');
        var placeholderRow = document.getElementById('activity-placeholder-row');
        var placeholderText = document.getElementById('activity-placeholder-text');

        // If no user selected, hide all data rows and show placeholder
        if (!selectedUserId) {
            rows.forEach(function (row) {
                row.style.display = 'none';
            });
            if (placeholderRow) {
                if (placeholderText) {
                    placeholderText.textContent = 'Please select a user email to view activity logs.';
                }
                placeholderRow.style.display = '';
            }
            return;
        }

        var anyVisible = false;
        rows.forEach(function (row) {
            var rowUserId = row.getAttribute('data-user-id') || '';
            var rowStatus = (row.getAttribute('data-status') || '').toLowerCase();
            var rowFlow = (row.getAttribute('data-flow') || '').toLowerCase();

            var visible = true;
            if (selectedUserId && rowUserId !== selectedUserId) visible = false;
            if (visible && selectedStatus && rowStatus !== selectedStatus.toLowerCase()) visible = false;
            if (visible && selectedFlow && rowFlow !== selectedFlow.toLowerCase()) visible = false;

            row.style.display = visible ? '' : 'none';
            if (visible) anyVisible = true;
        });

        if (placeholderRow) {
            if (placeholderText) {
                placeholderText.textContent = anyVisible
                    ? ''
                    : 'No activity logs found for the selected user.';
            }
            placeholderRow.style.display = anyVisible ? 'none' : '';
        }
    }

    // Initial apply to respect any default selections
    applyFilters();
});
</script>
@endpush

