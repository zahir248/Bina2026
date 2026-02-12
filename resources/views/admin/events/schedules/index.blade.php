@extends('layouts.admin.app')

@section('title', 'Event Schedules')

@push('styles')
<style>
    .btn-admin-primary-modal {
        background-color: #3b82f6;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 500;
        transition: background-color 0.2s ease;
    }

    .btn-admin-primary-modal:hover {
        background-color: #2563eb;
        color: white;
    }

    .btn-sm.btn-admin-primary-modal {
        padding: 6px 12px;
        font-size: 0.875rem;
    }
    
    .modal-body, .modal-footer {
        padding: 1rem;
    }
    
    .form-label {
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    
    .form-control-sm, .form-select-sm {
        font-size: 0.875rem;
    }
    
    .invalid-feedback {
        font-size: 0.75rem;
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

    /* Schedule Editor Styles */
    .schedule-row {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
        background-color: #f8f9fa;
    }

    .schedule-row-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #dee2e6;
    }

    .btn-remove-schedule {
        color: #6c757d;
        border: none;
        background: none;
        font-size: 1.2rem;
        cursor: pointer;
        padding: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s ease;
    }

    .btn-remove-schedule:hover {
        color: #495057;
    }
    
    .btn-remove-schedule.active-eye {
        color: #0d6efd;
    }
    
    .btn-remove-schedule.active-eye:hover {
        color: #0a58ca;
    }
    
    .btn-remove-schedule.inactive-eye {
        color: #6c757d;
    }
    
    .btn-remove-schedule.inactive-eye:hover {
        color: #495057;
    }

    #manageSchedulesModal .modal-dialog {
        max-width: 900px;
    }

    #manageSchedulesModal .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }
</style>
@endpush

@section('content')
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">Event Schedules</h3>
        </div>
        <div class="card-body">
            <!-- Filter Section -->
            <form method="GET" action="{{ route('admin.events.schedules') }}" class="mb-4" id="filterForm">
                <div class="row g-3 align-items-end">
                    <!-- Search Input -->
                    <div class="col-md-4">
                        <label for="search" class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Search</label>
                        <input type="text" 
                               class="form-control form-control-sm" 
                               id="search" 
                               name="search" 
                               placeholder="Search by event name or description..." 
                               value="{{ $search ?? '' }}"
                               autocomplete="off">
                    </div>
                    
                    <!-- Category Filter -->
                    <div class="col-md-4">
                        <label for="category" class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Category</label>
                        <select class="form-select form-select-sm" id="category" name="category">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ ($categoryFilter ?? '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="col-md-4">
                        <label for="status" class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Status</label>
                        <select class="form-select form-select-sm" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="active" {{ ($statusFilter ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ ($statusFilter ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="text-center">NO.</th>
                            <th class="text-center">Event Name</th>
                            <th class="text-center">Category</th>
                            <th class="text-center">Start Date</th>
                            <th class="text-center">End Date</th>
                            <th class="text-center">Schedules Count</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                            <tr>
                                <td class="text-center">{{ $loop->iteration + ($events->currentPage() - 1) * $events->perPage() }}</td>
                                <td class="text-center">{{ $event->name }}</td>
                                <td class="text-center">{{ $event->category->name ?? '-' }}</td>
                                <td class="text-center">{{ $event->start_datetime->format('M d, Y H:i') }}</td>
                                <td class="text-center">{{ $event->end_datetime->format('M d, Y H:i') }}</td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $event->schedules_count ?? 0 }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $event->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($event->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary" 
                                            title="Manage Schedules" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#manageSchedulesModal"
                                            data-event-id="{{ $event->id }}"
                                            data-event-name="{{ $event->name }}">
                                        <i class="bi bi-calendar-check"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No events found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $events->links() }}
            </div>
        </div>
    </div>

    <!-- Manage Schedules Modal -->
    <div class="modal fade" id="manageSchedulesModal" tabindex="-1" aria-labelledby="manageSchedulesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="manageSchedulesModalLabel">Manage Schedules</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="manageSchedulesForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <h6 id="eventNameDisplay" class="text-muted"></h6>
                        </div>
                        <div id="schedulesContainer">
                            <!-- Schedule rows will be added here dynamically -->
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-success" id="addScheduleRow">
                            <i class="bi bi-plus-circle"></i> Add Schedule
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save Schedules</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let scheduleRowIndex = 0;

        // Real-time filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('filterForm');
            const searchInput = document.getElementById('search');
            const categorySelect = document.getElementById('category');
            const statusSelect = document.getElementById('status');
            
            let searchTimeout;
            
            // Auto-submit on search input (with debounce)
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function() {
                        filterForm.submit();
                    }, 500);
                });
            }
            
            // Auto-submit on category change
            if (categorySelect) {
                categorySelect.addEventListener('change', function() {
                    filterForm.submit();
                });
            }
            
            // Auto-submit on status change
            if (statusSelect) {
                statusSelect.addEventListener('change', function() {
                    filterForm.submit();
                });
            }
        });

        // Handle Manage Schedules Modal
        document.addEventListener('DOMContentLoaded', function() {
            const manageModal = document.getElementById('manageSchedulesModal');
            if (manageModal) {
                manageModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const eventId = button.getAttribute('data-event-id');
                    const eventName = button.getAttribute('data-event-name');

                    // Set event name
                    document.getElementById('eventNameDisplay').textContent = 'Event: ' + eventName;

                    // Set form action
                    const form = document.getElementById('manageSchedulesForm');
                    form.action = '{{ route("admin.events.schedules.save", ":id") }}'.replace(':id', eventId);

                    // Clear container
                    const container = document.getElementById('schedulesContainer');
                    container.innerHTML = '';
                    scheduleRowIndex = 0;

                    // Load existing schedules
                    fetch('{{ route("admin.events.schedules.get", ":id") }}'.replace(':id', eventId))
                        .then(response => response.json())
                        .then(data => {
                            if (data.schedules && data.schedules.length > 0) {
                                data.schedules.forEach(schedule => {
                                    addScheduleRow(schedule);
                                });
                            } else {
                                // Add one empty row
                                addScheduleRow();
                            }
                        })
                        .catch(error => {
                            console.error('Error loading schedules:', error);
                            addScheduleRow();
                        });
                });

                manageModal.addEventListener('hidden.bs.modal', function() {
                    // Clear container when modal is closed
                    document.getElementById('schedulesContainer').innerHTML = '';
                    scheduleRowIndex = 0;
                });
            }

            // Add Schedule Row button
            document.getElementById('addScheduleRow').addEventListener('click', function() {
                addScheduleRow();
            });
        });

        function addScheduleRow(schedule = null) {
            const container = document.getElementById('schedulesContainer');
            // Get current number of rows to determine the next index
            const currentRows = container.querySelectorAll('.schedule-row').length;
            const index = currentRows; // Use current row count as index (0-based)
            const rowId = 'schedule_row_' + scheduleRowIndex++;

            const row = document.createElement('div');
            row.className = 'schedule-row';
            row.id = rowId;

            const startTime = schedule ? schedule.start_time.substring(0, 5) : '';
            const endTime = schedule && schedule.end_time ? schedule.end_time.substring(0, 5) : '';
            const initialStatus = schedule ? (schedule.status || 'active') : 'active';
            const iconClass = initialStatus === 'inactive' ? 'bi-eye-slash' : 'bi-eye';
            const buttonTitle = initialStatus === 'inactive' ? 'Activate' : 'Mark as Inactive';
            const buttonOnClick = initialStatus === 'inactive' ? 'restoreScheduleRow' : 'removeScheduleRow';
            const rowOpacity = initialStatus === 'inactive' ? '0.5' : '1';

            row.innerHTML = `
                <div class="schedule-row-header">
                    <strong>Schedule ${currentRows + 1}</strong>
                    <button type="button" class="btn-remove-schedule" onclick="${buttonOnClick}('${rowId}')" title="${buttonTitle}">
                        <i class="bi ${iconClass}"></i>
                    </button>
                </div>
                <div class="row g-2">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control form-control-sm" 
                               name="schedules[${index}][name]" 
                               value="${schedule ? (schedule.name || '') : ''}" 
                               required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Session</label>
                        <input type="text" 
                               class="form-control form-control-sm" 
                               name="schedules[${index}][session]" 
                               value="${schedule ? (schedule.session || '') : ''}">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Start Time <span class="text-danger">*</span></label>
                        <input type="time" 
                               class="form-control form-control-sm" 
                               name="schedules[${index}][start_time]" 
                               value="${startTime}" 
                               required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">End Time</label>
                        <input type="time" 
                               class="form-control form-control-sm" 
                               name="schedules[${index}][end_time]" 
                               value="${endTime}">
                    </div>
                    <input type="hidden" name="schedules[${index}][status]" value="${schedule ? (schedule.status || 'active') : 'active'}" class="schedule-status-input">
                    <div class="col-12 mb-2">
                        <label class="form-label">Description</label>
                        <textarea class="form-control form-control-sm" 
                                  name="schedules[${index}][description]" 
                                  rows="2">${schedule ? (schedule.description || '') : ''}</textarea>
                    </div>
                </div>
            `;

            container.appendChild(row);
            
            // Set initial visual state if inactive
            if (initialStatus === 'inactive') {
                row.style.opacity = rowOpacity;
                row.style.backgroundColor = '#f8f9fa';
            }
            
            // Renumber all rows after adding to ensure proper sequential numbering
            renumberScheduleRows();
        }

        function removeScheduleRow(rowId) {
            const row = document.getElementById(rowId);
            if (row) {
                // Find the hidden status input in this row
                const statusInput = row.querySelector('input.schedule-status-input');
                if (statusInput) {
                    // Change status to inactive
                    statusInput.value = 'inactive';
                    
                    // Optionally hide the row or add visual indication
                    row.style.opacity = '0.5';
                    row.style.backgroundColor = '#f8f9fa';
                    
                    // Change the remove button to show closed eye icon and update onclick
                    const removeBtn = row.querySelector('.btn-remove-schedule:not(.btn-restore-schedule)');
                    if (removeBtn) {
                        const icon = removeBtn.querySelector('i');
                        if (icon) {
                            icon.className = 'bi bi-eye-slash';
                        }
                        removeBtn.title = 'Activate';
                        removeBtn.onclick = function() {
                            restoreScheduleRow(rowId);
                        };
                    }
                }
            }
        }

        function restoreScheduleRow(rowId) {
            const row = document.getElementById(rowId);
            if (row) {
                // Find the hidden status input in this row
                const statusInput = row.querySelector('input.schedule-status-input');
                if (statusInput) {
                    // Change status to active
                    statusInput.value = 'active';
                    
                    // Restore visual appearance
                    row.style.opacity = '1';
                    row.style.backgroundColor = '#f8f9fa';
                    
                    // Change the button back to open eye icon and update onclick
                    const removeBtn = row.querySelector('.btn-remove-schedule:not(.btn-restore-schedule)');
                    if (removeBtn) {
                        const icon = removeBtn.querySelector('i');
                        if (icon) {
                            icon.className = 'bi bi-eye';
                        }
                        removeBtn.title = 'Mark as Inactive';
                        removeBtn.onclick = function() {
                            removeScheduleRow(rowId);
                        };
                    }
                }
            }
        }

        function renumberScheduleRows() {
            const container = document.getElementById('schedulesContainer');
            const rows = container.querySelectorAll('.schedule-row');
            
            rows.forEach((row, index) => {
                // Update the header text
                const header = row.querySelector('.schedule-row-header strong');
                if (header) {
                    header.textContent = `Schedule ${index + 1}`;
                }
                
                // Update all input/select names to use new index
                const inputs = row.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    const name = input.getAttribute('name');
                    if (name && name.includes('[')) {
                        // Extract the field name (e.g., 'name', 'start_time', etc.)
                        const fieldMatch = name.match(/\[(\d+)\]\[(.+)\]/);
                        if (fieldMatch) {
                            const fieldName = fieldMatch[2];
                            input.setAttribute('name', `schedules[${index}][${fieldName}]`);
                        }
                    }
                });
            });
        }
    </script>
    @endpush
@endsection
