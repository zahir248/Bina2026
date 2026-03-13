@extends('layouts.admin.app')

@section('title', 'Email Log')
@section('page-title', 'Email Log')

@push('styles')
<style>
    .form-label { font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem; }
    .form-control-sm, .form-select-sm { font-size: 0.875rem; }
    .table th, .table td { text-align: center !important; vertical-align: middle !important; }
    .table td.text-start { text-align: left !important; }
</style>
@endpush

@section('content')
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">Email log</h3>
        </div>
        <div class="card-body">
            <!-- Filter Section -->
            <form method="GET" action="{{ route('admin.logs.email') }}" class="mb-4" id="filterForm">
                <div class="row g-3 align-items-end">
                    <!-- Search Input -->
                    <div class="col-md-4">
                        <label for="search" class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Search</label>
                        <input type="text"
                               class="form-control form-control-sm"
                               id="search"
                               name="search"
                               placeholder="Subject, type, or recipient..."
                               value="{{ $search ?? '' }}"
                               autocomplete="off">
                    </div>
                    <!-- Status Filter -->
                    <div class="col-md-4">
                        <label for="status" class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Status</label>
                        <select class="form-select form-select-sm" id="status" name="status">
                            <option value="">All</option>
                            <option value="sent" {{ ($statusFilter ?? '') === 'sent' ? 'selected' : '' }}>Sent</option>
                            <option value="failed" {{ ($statusFilter ?? '') === 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="text-center">No.</th>
                            <th class="text-center">To</th>
                            <th class="text-center">Subject</th>
                            <th class="text-center">Type</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Sent at</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="text-center">{{ $loop->iteration + ($logs->currentPage() - 1) * $logs->perPage() }}</td>
                                <td class="text-start">
                                    @foreach($log->to as $addr)
                                        <span class="d-block">{{ $addr }}</span>
                                    @endforeach
                                </td>
                                <td class="text-start">{{ \Illuminate\Support\Str::limit($log->subject, 60) }}</td>
                                <td class="text-center">{{ $log->mailable_short_name }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $log->status === 'sent' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                </td>
                                <td class="text-center">{{ $log->sent_at?->format('M d, Y H:i') ?? $log->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No email logs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4 admin-pagination">
                {{ $logs->links('pagination.admin') }}
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
        var searchInput = document.getElementById('search');
        if (statusSelect) statusSelect.addEventListener('change', function() { filterForm.submit(); });
        if (searchInput) {
            var searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() { filterForm.submit(); }, 350);
            });
        }
    }
});
</script>
@endpush
