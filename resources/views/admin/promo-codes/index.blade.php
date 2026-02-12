@extends('layouts.admin.app')

@section('title', 'Promo Codes')

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
</style>
@endpush

@section('content')
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">Promo Codes</h3>
            <button class="btn-admin btn-admin-primary" data-bs-toggle="modal" data-bs-target="#createPromoCodeModal">
                <i class="bi bi-plus-circle"></i>
                Add Promo Code
            </button>
        </div>
        <div class="card-body">
            <!-- Filter Section -->
            <form method="GET" action="{{ route('admin.promo-codes') }}" class="mb-4" id="filterForm">
                <div class="row g-3 align-items-end">
                    <!-- Search Input -->
                    <div class="col-md-4">
                        <label for="search" class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Search</label>
                        <input type="text" 
                               class="form-control form-control-sm" 
                               id="search" 
                               name="search" 
                               placeholder="Search by name, description, or code..." 
                               value="{{ $search ?? '' }}"
                               autocomplete="off">
                    </div>
                    
                    <!-- Event Filter -->
                    <div class="col-md-4">
                        <label for="event" class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Event</label>
                        <select class="form-select form-select-sm" id="event" name="event">
                            <option value="">All Events</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" {{ ($eventFilter ?? '') == $event->id ? 'selected' : '' }}>
                                    {{ $event->name }}
                                </option>
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
                            <th class="text-center">Name</th>
                            <th class="text-center">Code</th>
                            <th class="text-center">Discount (RM)</th>
                            <th class="text-center">Events</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Created At</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($promoCodes as $promoCode)
                            <tr>
                                <td class="text-center">{{ $loop->iteration + ($promoCodes->currentPage() - 1) * $promoCodes->perPage() }}</td>
                                <td class="text-center">{{ $promoCode->name }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $promoCode->code }}</span>
                                </td>
                                <td class="text-center">
                                    @if($promoCode->discount)
                                        {{ number_format($promoCode->discount, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($promoCode->events->count() > 0)
                                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                                            @foreach($promoCode->events as $event)
                                                <span class="badge bg-secondary">{{ $event->name }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $promoCode->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($promoCode->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    {{ $promoCode->created_at->format('M d, Y') }}
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        @if($promoCode->status === 'active')
                                            <button class="btn btn-sm btn-outline-success" title="Edit" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editPromoCodeModal"
                                                    data-promo-code-id="{{ $promoCode->id }}"
                                                    data-name="{{ $promoCode->name }}"
                                                    data-description="{{ $promoCode->description ?? '' }}"
                                                    data-code="{{ $promoCode->code }}"
                                                    data-discount="{{ $promoCode->discount ?? '' }}"
                                                    data-events="{{ $promoCode->events->pluck('id')->toJson() }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        @endif
                                        <button class="btn btn-sm {{ $promoCode->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }}" 
                                                title="{{ $promoCode->status === 'active' ? 'Deactivate' : 'Activate' }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deletePromoCodeModal"
                                                data-promo-code-id="{{ $promoCode->id }}"
                                                data-name="{{ $promoCode->name }}"
                                                data-status="{{ $promoCode->status }}">
                                            <i class="bi {{ $promoCode->status === 'active' ? 'bi-trash' : 'bi-arrow-counterclockwise' }}"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No promo codes found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $promoCodes->links() }}
            </div>
        </div>
    </div>

    <!-- Create Promo Code Modal -->
    <div class="modal fade" id="createPromoCodeModal" tabindex="-1" aria-labelledby="createPromoCodeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createPromoCodeModalLabel">Create New Promo Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.promo-codes.store') }}" method="POST" id="createPromoCodeForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-2">
                            <label for="create_name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control form-control-sm @error('name', 'create') is-invalid @enderror" 
                                   id="create_name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required>
                            @error('name', 'create')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-2">
                            <label for="create_code" class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control form-control-sm @error('code', 'create') is-invalid @enderror" 
                                   id="create_code" 
                                   name="code" 
                                   value="{{ old('code') }}" 
                                   required>
                            @error('code', 'create')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="create_discount" class="form-label">Discount (RM)</label>
                                <input type="number" 
                                       step="0.01" 
                                       min="0"
                                       class="form-control form-control-sm @error('discount', 'create') is-invalid @enderror" 
                                       id="create_discount" 
                                       name="discount" 
                                       value="{{ old('discount') }}">
                                @error('discount', 'create')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-2">
                                <label for="create_events" class="form-label">Events</label>
                                <select class="form-select form-select-sm @error('events', 'create') is-invalid @enderror" 
                                        id="create_events" 
                                        name="events[]" 
                                        multiple>
                                    @foreach($events as $event)
                                        <option value="{{ $event->id }}" {{ in_array($event->id, old('events', [])) ? 'selected' : '' }}>
                                            {{ $event->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Hold Ctrl (or Cmd on Mac) to select multiple events</small>
                                @error('events', 'create')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @error('events.*', 'create')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            <label for="create_description" class="form-label">Description</label>
                            <textarea class="form-control form-control-sm @error('description', 'create') is-invalid @enderror" 
                                      id="create_description" 
                                      name="description" 
                                      rows="3">{{ old('description') }}</textarea>
                            @error('description', 'create')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer" style="padding: 0.75rem 1rem;">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-success">Create Promo Code</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Promo Code Modal -->
    <div class="modal fade" id="editPromoCodeModal" tabindex="-1" aria-labelledby="editPromoCodeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPromoCodeModalLabel">Edit Promo Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editPromoCodeForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-2">
                            <label for="edit_name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control form-control-sm @error('name', 'edit') is-invalid @enderror" 
                                   id="edit_name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required>
                            @error('name', 'edit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-2">
                            <label for="edit_code" class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control form-control-sm @error('code', 'edit') is-invalid @enderror" 
                                   id="edit_code" 
                                   name="code" 
                                   value="{{ old('code') }}" 
                                   required>
                            @error('code', 'edit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="edit_discount" class="form-label">Discount (RM)</label>
                                <input type="number" 
                                       step="0.01" 
                                       min="0"
                                       class="form-control form-control-sm @error('discount', 'edit') is-invalid @enderror" 
                                       id="edit_discount" 
                                       name="discount" 
                                       value="{{ old('discount') }}">
                                @error('discount', 'edit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-2">
                                <label for="edit_events" class="form-label">Events</label>
                                <select class="form-select form-select-sm @error('events', 'edit') is-invalid @enderror" 
                                        id="edit_events" 
                                        name="events[]" 
                                        multiple>
                                    @foreach($events as $event)
                                        <option value="{{ $event->id }}" {{ in_array($event->id, old('events', [])) ? 'selected' : '' }}>
                                            {{ $event->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Hold Ctrl (or Cmd on Mac) to select multiple events</small>
                                @error('events', 'edit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @error('events.*', 'edit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control form-control-sm @error('description', 'edit') is-invalid @enderror" 
                                      id="edit_description" 
                                      name="description" 
                                      rows="3">{{ old('description') }}</textarea>
                            @error('description', 'edit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer" style="padding: 0.75rem 1rem;">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-success">Update Promo Code</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Promo Code Modal -->
    <div class="modal fade" id="deletePromoCodeModal" tabindex="-1" aria-labelledby="deletePromoCodeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deletePromoCodeModalLabel">Update Promo Code Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="deletePromoCodeForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <p id="delete_message"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn" id="delete_submit_btn">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Real-time filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('filterForm');
            const searchInput = document.getElementById('search');
            const eventSelect = document.getElementById('event');
            const statusSelect = document.getElementById('status');
            
            let searchTimeout;
            
            // Auto-submit on search input (with debounce)
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function() {
                        filterForm.submit();
                    }, 500); // Wait 500ms after user stops typing
                });
            }
            
            // Auto-submit on event/status change
            if (eventSelect) {
                eventSelect.addEventListener('change', function() {
                    filterForm.submit();
                });
            }
            
            if (statusSelect) {
                statusSelect.addEventListener('change', function() {
                    filterForm.submit();
                });
            }
        });
        
        // Auto-open modals if there are validation errors
        document.addEventListener('DOMContentLoaded', function() {
            @if($errors->getBag('create')->any())
                const createModal = new bootstrap.Modal(document.getElementById('createPromoCodeModal'));
                createModal.show();
            @endif

            @if($errors->getBag('edit')->any())
                const editModal = new bootstrap.Modal(document.getElementById('editPromoCodeModal'));
                editModal.show();
            @endif
        });

        // Handle Edit Modal
        document.addEventListener('DOMContentLoaded', function() {
            const editModal = document.getElementById('editPromoCodeModal');
            if (editModal) {
                editModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const promoCodeId = button.getAttribute('data-promo-code-id');
                    const name = button.getAttribute('data-name');
                    const description = button.getAttribute('data-description');
                    const code = button.getAttribute('data-code');
                    const discount = button.getAttribute('data-discount');
                    const eventsJson = button.getAttribute('data-events');

                    const form = document.getElementById('editPromoCodeForm');
                    form.action = '{{ route("admin.promo-codes.update", ":id") }}'.replace(':id', promoCodeId);

                    document.getElementById('edit_name').value = name || '';
                    document.getElementById('edit_description').value = description || '';
                    document.getElementById('edit_code').value = code || '';
                    document.getElementById('edit_discount').value = discount || '';
                    
                    // Set selected events
                    if (eventsJson) {
                        try {
                            const eventIds = JSON.parse(eventsJson);
                            const eventSelect = document.getElementById('edit_events');
                            if (eventSelect) {
                                // Clear previous selections
                                Array.from(eventSelect.options).forEach(option => {
                                    option.selected = false;
                                });
                                
                                // Set new selections
                                eventIds.forEach(eventId => {
                                    const option = eventSelect.querySelector(`option[value="${eventId}"]`);
                                    if (option) {
                                        option.selected = true;
                                    }
                                });
                            }
                        } catch (e) {
                            console.error('Error parsing events JSON:', e);
                        }
                    }
                });
            }

            // Handle Delete Modal
            const deleteModal = document.getElementById('deletePromoCodeModal');
            if (deleteModal) {
                // Clear modal content when hidden
                deleteModal.addEventListener('hidden.bs.modal', function() {
                    const deleteMessage = document.getElementById('delete_message');
                    if (deleteMessage) deleteMessage.innerHTML = '';
                });

                deleteModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const promoCodeId = button.getAttribute('data-promo-code-id');
                    const name = button.getAttribute('data-name');
                    const currentStatus = button.getAttribute('data-status');

                    const form = document.getElementById('deletePromoCodeForm');
                    form.action = '{{ route("admin.promo-codes.destroy", ":id") }}'.replace(':id', promoCodeId);

                    const modalTitle = document.getElementById('deletePromoCodeModalLabel');
                    const deleteMessage = document.getElementById('delete_message');
                    const submitBtn = document.getElementById('delete_submit_btn');
                    
                    if (!modalTitle || !deleteMessage || !submitBtn) {
                        console.error('Modal elements not found');
                        return;
                    }
                    
                    // Check if promo code is active (normalize for comparison)
                    const isActive = currentStatus && currentStatus.toLowerCase().trim() === 'active';
                    
                    // Update all modal content with fresh data
                    if (isActive) {
                        modalTitle.textContent = 'Deactivate Promo Code';
                        deleteMessage.innerHTML = 'Are you sure you want to <strong>deactivate</strong> promo code <strong>' + name + '</strong>?';
                        submitBtn.textContent = 'Deactivate Promo Code';
                        submitBtn.className = 'btn btn-danger';
                    } else {
                        modalTitle.textContent = 'Activate Promo Code';
                        deleteMessage.innerHTML = 'Are you sure you want to <strong>activate</strong> promo code <strong>' + name + '</strong>?';
                        submitBtn.textContent = 'Activate Promo Code';
                        submitBtn.className = 'btn btn-success';
                    }
                });
            }
        });
    </script>
    @endpush
@endsection
