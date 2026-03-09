@extends('layouts.admin.app')

@section('title', 'Event Participants')
@section('page-title', 'Event Participants')

@push('styles')
<style>
    .form-label {
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    .form-control-sm, .form-select-sm {
        font-size: 0.875rem;
    }
    .table th.text-center,
    .table td.text-center {
        text-align: center !important;
        vertical-align: middle !important;
    }
    .table th { text-align: center !important; vertical-align: middle !important; }
    .table td { text-align: center !important; vertical-align: middle !important; }
    .btn-admin-success { background-color: #10b981; color: white; }
    .btn-admin-success:hover { background-color: #059669; color: white; }
</style>
@endpush

@section('content')
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">Event Participants</h3>
            <a href="{{ route('admin.event-participants.export', array_filter(['event' => $eventFilter ?? '', 'search' => $search ?? ''])) }}"
               class="btn-admin btn-admin-success"
               title="Export to PDF">
                <i class="bi bi-download"></i>
            </a>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.event-participants') }}" class="mb-4" id="filterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Search</label>
                        <input type="text"
                               class="form-control form-control-sm"
                               id="search"
                               name="search"
                               placeholder="Search by name, email, NRIC, contact..."
                               value="{{ $search ?? '' }}"
                               autocomplete="off">
                    </div>
                    <div class="col-md-4">
                        <label for="event" class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Event</label>
                        <select class="form-select form-select-sm" id="event" name="event">
                            <option value="">Select an event</option>
                            @foreach($events ?? [] as $event)
                                <option value="{{ $event->id }}" {{ (string)($eventFilter ?? '') === (string)$event->id ? 'selected' : '' }}>
                                    {{ $event->name }} ({{ $event->category->name ?? 'Uncategorized' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>

            @if(!empty($eventFilter) && $selectedEvent)
                <p class="text-muted mb-3">
                    <strong>{{ $selectedEvent->name }}</strong>
                    @if($selectedEvent->category)
                        <span class="text-muted">({{ $selectedEvent->category->name }})</span>
                    @endif
                    — {{ $participants->count() }} ticket holder(s)
                </p>
            @endif

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="text-center">NO.</th>
                            <th class="text-center">Name</th>
                            <th class="text-center">Email</th>
                            <th class="text-center">Gender</th>
                            <th class="text-center">NRIC/Passport</th>
                            <th class="text-center">Contact</th>
                            <th class="text-center">Company</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($participants as $p)
                            @php
                                $h = $p->holder;
                                $order = $p->order;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td class="text-center">{{ $h['full_name'] ?? '-' }}</td>
                                <td class="text-center">{{ $h['email'] ?? '-' }}</td>
                                <td class="text-center">{{ isset($h['gender']) ? ucfirst($h['gender']) : '-' }}</td>
                                <td class="text-center">{{ $h['nric_passport'] ?? '-' }}</td>
                                <td class="text-center">{{ $h['contact_number'] ?? '-' }}</td>
                                <td class="text-center">{{ $h['company_name'] ?? '-' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.orders', ['search' => $order->stripe_payment_intent_id ?? $order->id]) }}" class="btn btn-sm btn-outline-primary" title="View order">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    @if(!empty($eventFilter))
                                        No ticket holders found for this event.
                                    @else
                                        Select an event to view participants.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var filterForm = document.getElementById('filterForm');
    var eventSelect = document.getElementById('event');
    var searchInput = document.getElementById('search');
    if (eventSelect) eventSelect.addEventListener('change', function() { filterForm.submit(); });
    if (searchInput && filterForm) {
        var searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() { filterForm.submit(); }, 350);
        });
    }
});
</script>
@endpush
