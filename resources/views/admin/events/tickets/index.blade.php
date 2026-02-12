@extends('layouts.admin.app')

@section('title', 'Tickets')

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
    
    .ticket-image {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 50%;
        border: 1px solid #dee2e6;
    }
</style>
@endpush

@section('content')
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">Tickets</h3>
            <button class="btn-admin btn-admin-primary" data-bs-toggle="modal" data-bs-target="#createTicketModal">
                <i class="bi bi-plus-circle"></i>
                Add Ticket
            </button>
        </div>
        <div class="card-body">
            <!-- Filter Section -->
            <form method="GET" action="{{ route('admin.events.tickets') }}" class="mb-4" id="filterForm">
                <div class="row g-3 align-items-end">
                    <!-- Search Input -->
                    <div class="col-md-4">
                        <label for="search" class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Search</label>
                        <input type="text" 
                               class="form-control form-control-sm" 
                               id="search" 
                               name="search" 
                               placeholder="Search by name, description, or remarks..." 
                               value="{{ $search ?? '' }}"
                               autocomplete="off">
                    </div>
                    
                    <!-- Event Filter -->
                    <div class="col-md-4">
                        <label for="event" class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Event</label>
                        <select class="form-select form-select-sm" id="event" name="event">
                            <option value="">All Events</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" {{ ($eventFilter ?? '') == $event->id ? 'selected' : '' }}>{{ $event->name }}</option>
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
                            <th class="text-center">Image</th>
                            <th class="text-center">Name</th>
                            <th class="text-center">Events</th>
                            <th class="text-center">Price (RM)</th>
                            <th class="text-center">Quantity Discount</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr>
                                <td class="text-center">{{ $loop->iteration + ($tickets->currentPage() - 1) * $tickets->perPage() }}</td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center">
                                        @if($ticket->image)
                                            <a href="{{ storage_asset($ticket->image) }}" target="_blank" rel="noopener noreferrer">
                                                <img src="{{ storage_asset($ticket->image) }}" alt="{{ $ticket->name }}" class="ticket-image" style="cursor: pointer;">
                                            </a>
                                        @else
                                            @php
                                                // Get initials from name
                                                $nameParts = explode(' ', $ticket->name);
                                                $initials = '';
                                                if (count($nameParts) >= 2) {
                                                    // First letter of first name + first letter of last name
                                                    $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1));
                                                } else {
                                                    // If only one word, take first 2 letters
                                                    $initials = strtoupper(substr($ticket->name, 0, 2));
                                                }
                                            @endphp
                                            <div class="ticket-image bg-primary d-flex align-items-center justify-content-center text-white fw-bold" style="font-size: 0.75rem; border-radius: 50%; width: 40px; height: 40px;">
                                                {{ $initials }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">{{ $ticket->name }}</td>
                                <td class="text-center">
                                    @if($ticket->events->count() > 0)
                                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                                            @foreach($ticket->events as $event)
                                                <span class="badge bg-info">{{ $event->name }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ number_format($ticket->price, 2) }}</td>
                                <td class="text-center">
                                    @if($ticket->quantity_discount && is_array($ticket->quantity_discount) && count($ticket->quantity_discount) > 0)
                                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                                            @foreach($ticket->quantity_discount as $discount)
                                                @php
                                                    $type = $discount['type'] ?? 'exact';
                                                    $price = $discount['price'] ?? 0;
                                                    $displayText = '';
                                                    
                                                    if ($type === 'range') {
                                                        $minQty = $discount['min_quantity'] ?? 0;
                                                        $maxQty = $discount['max_quantity'] ?? 0;
                                                        $displayText = "{$minQty} - {$maxQty}	RM " . number_format($price, 2);
                                                    } elseif ($type === 'more_than') {
                                                        $qty = $discount['quantity'] ?? 0;
                                                        $displayText = ">{$qty}	RM " . number_format($price, 2);
                                                    } elseif ($type === 'less_than') {
                                                        $qty = $discount['quantity'] ?? 0;
                                                        $displayText = "<{$qty}	RM " . number_format($price, 2);
                                                    } else {
                                                        // exact
                                                        $qty = $discount['quantity'] ?? 0;
                                                        $displayText = "{$qty}	RM " . number_format($price, 2);
                                                    }
                                                @endphp
                                                <span class="badge bg-warning">{{ $displayText }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $ticket->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($ticket->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        @if($ticket->status === 'active')
                                            <button class="btn btn-sm btn-outline-success" title="Edit" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editTicketModal"
                                                    data-ticket-id="{{ $ticket->id }}"
                                                    data-name="{{ $ticket->name }}"
                                                    data-description="{{ $ticket->description ?? '' }}"
                                                    data-price="{{ $ticket->price }}"
                                                    data-quantity-discount="{{ $ticket->quantity_discount && is_array($ticket->quantity_discount) ? implode("\n", array_map(function($d) { 
                                                        $type = $d['type'] ?? 'exact';
                                                        $price = $d['price'] ?? 0;
                                                        if ($type === 'range') {
                                                            $minQty = $d['min_quantity'] ?? 0;
                                                            $maxQty = $d['max_quantity'] ?? 0;
                                                            return "{$minQty}-{$maxQty},{$price}";
                                                        } elseif ($type === 'more_than') {
                                                            $qty = $d['quantity'] ?? 0;
                                                            return ">{$qty},{$price}";
                                                        } elseif ($type === 'less_than') {
                                                            $qty = $d['quantity'] ?? 0;
                                                            return "<{$qty},{$price}";
                                                        } else {
                                                            $qty = $d['quantity'] ?? 0;
                                                            return "{$qty},{$price}";
                                                        }
                                                    }, $ticket->quantity_discount)) : '' }}"
                                                    data-remarks="{{ $ticket->remarks ?? '' }}"
                                                    data-image="{{ $ticket->image ?? '' }}"
                                                    data-events="{{ $ticket->events->pluck('id')->toJson() }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        @endif
                                        <button class="btn btn-sm {{ $ticket->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }}" 
                                                title="{{ $ticket->status === 'active' ? 'Deactivate' : 'Activate' }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteTicketModal"
                                                data-ticket-id="{{ $ticket->id }}"
                                                data-name="{{ $ticket->name }}"
                                                data-status="{{ $ticket->status }}">
                                            <i class="bi {{ $ticket->status === 'active' ? 'bi-trash' : 'bi-arrow-counterclockwise' }}"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No tickets found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $tickets->links() }}
            </div>
        </div>
    </div>

    <!-- Create Ticket Modal -->
    <div class="modal fade" id="createTicketModal" tabindex="-1" aria-labelledby="createTicketModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-height: 90vh; margin: 1.75rem auto; display: flex; flex-direction: column;">
            <div class="modal-content" style="max-height: 90vh; display: flex; flex-direction: column; overflow: hidden;">
                <div class="modal-header" style="flex: 0 0 auto; border-bottom: 1px solid #dee2e6;">
                    <h5 class="modal-title" id="createTicketModalLabel">Create New Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.events.tickets.store') }}" method="POST" id="createTicketForm" enctype="multipart/form-data" style="display: flex; flex-direction: column; flex: 1 1 auto; min-height: 0;">
                    @csrf
                    <div class="modal-body" style="flex: 1 1 auto; overflow-y: auto; overflow-x: hidden; min-height: 0; max-height: calc(90vh - 150px);">
                        <div class="row">
                            <div class="col-md-6 mb-2">
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
                            
                            <div class="col-md-6 mb-2">
                                <label for="create_price" class="form-label">Price (RM) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       step="0.01" 
                                       min="0"
                                       class="form-control form-control-sm @error('price', 'create') is-invalid @enderror" 
                                       id="create_price" 
                                       name="price" 
                                       value="{{ old('price') }}" 
                                       required>
                                @error('price', 'create')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            <label for="create_events" class="form-label">Events <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm @error('events', 'create') is-invalid @enderror" 
                                    id="create_events" 
                                    name="events[]" 
                                    multiple 
                                    required>
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
                        
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="create_quantity_discount" class="form-label">Quantity Discount</label>
                                <textarea class="form-control form-control-sm @error('quantity_discount', 'create') is-invalid @enderror" 
                                          id="create_quantity_discount" 
                                          name="quantity_discount" 
                                          rows="3"
                                          placeholder="e.g.,&#10;2,10&#10;2-3,50&#10;>4,20&#10;<2,10">{{ old('quantity_discount') }}</textarea>
                                <small class="form-text text-muted">One per line. Formats: 2,10 (exact) | 2-3,50 (range) | >4,20 (more than 4) | <2,10 (less than 2)</small>
                                @error('quantity_discount', 'create')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-2">
                                <label for="create_remarks" class="form-label">Remarks</label>
                                <textarea class="form-control form-control-sm @error('remarks', 'create') is-invalid @enderror" 
                                          id="create_remarks" 
                                          name="remarks" 
                                          rows="3">{{ old('remarks') }}</textarea>
                                @error('remarks', 'create')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            <label for="create_image" class="form-label">Image</label>
                            <input type="file" 
                                   class="form-control form-control-sm @error('image', 'create') is-invalid @enderror" 
                                   id="create_image" 
                                   name="image" 
                                   accept="image/*">
                            @error('image', 'create')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer" style="flex: 0 0 auto; border-top: 1px solid #dee2e6; margin-top: auto; padding: 0.75rem 1rem;">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-success">Create Ticket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Ticket Modal -->
    <div class="modal fade" id="editTicketModal" tabindex="-1" aria-labelledby="editTicketModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-height: 90vh; margin: 1.75rem auto; display: flex; flex-direction: column;">
            <div class="modal-content" style="max-height: 90vh; display: flex; flex-direction: column; overflow: hidden;">
                <div class="modal-header" style="flex: 0 0 auto; border-bottom: 1px solid #dee2e6;">
                    <h5 class="modal-title" id="editTicketModalLabel">Edit Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editTicketForm" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; flex: 1 1 auto; min-height: 0;">
                    @csrf
                    @method('PUT')
                    <div class="modal-body" style="flex: 1 1 auto; overflow-y: auto; overflow-x: hidden; min-height: 0; max-height: calc(90vh - 150px);">
                        <div class="mb-2">
                            <label class="form-label">Current Image</label>
                            <div id="edit_current_image" class="mb-2">
                                <p class="text-muted small">No image</p>
                            </div>
                        </div>
                        
                        <!-- Hidden input to store removed image path -->
                        <input type="hidden" id="edit_removed_image" name="removed_image" value="">
                        
                        <div class="row">
                            <div class="col-md-6 mb-2">
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
                            
                            <div class="col-md-6 mb-2">
                                <label for="edit_price" class="form-label">Price (RM) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       step="0.01" 
                                       min="0"
                                       class="form-control form-control-sm @error('price', 'edit') is-invalid @enderror" 
                                       id="edit_price" 
                                       name="price" 
                                       value="{{ old('price') }}" 
                                       required>
                                @error('price', 'edit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            <label for="edit_events" class="form-label">Events <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm @error('events', 'edit') is-invalid @enderror" 
                                    id="edit_events" 
                                    name="events[]" 
                                    multiple 
                                    required>
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
                        
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="edit_quantity_discount" class="form-label">Quantity Discount</label>
                                <textarea class="form-control form-control-sm @error('quantity_discount', 'edit') is-invalid @enderror" 
                                          id="edit_quantity_discount" 
                                          name="quantity_discount" 
                                          rows="3"
                                          placeholder="e.g.,&#10;2,10&#10;2-3,50&#10;>4,20&#10;<2,10">{{ old('quantity_discount') }}</textarea>
                                <small class="form-text text-muted">One per line. Formats: 2,10 (exact) | 2-3,50 (range) | >4,20 (more than 4) | <2,10 (less than 2)</small>
                                @error('quantity_discount', 'edit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-2">
                                <label for="edit_remarks" class="form-label">Remarks</label>
                                <textarea class="form-control form-control-sm @error('remarks', 'edit') is-invalid @enderror" 
                                          id="edit_remarks" 
                                          name="remarks" 
                                          rows="3">{{ old('remarks') }}</textarea>
                                @error('remarks', 'edit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            <label for="edit_image" class="form-label">Add New Image</label>
                            <input type="file" 
                                   class="form-control form-control-sm @error('image', 'edit') is-invalid @enderror" 
                                   id="edit_image" 
                                   name="image" 
                                   accept="image/*">
                            <small class="form-text text-muted">You can select a new image. Leave empty to keep existing image.</small>
                            @error('image', 'edit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer" style="flex: 0 0 auto; border-top: 1px solid #dee2e6; margin-top: auto; padding: 0.75rem 1rem;">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-success">Update Ticket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Ticket Modal -->
    <div class="modal fade" id="deleteTicketModal" tabindex="-1" aria-labelledby="deleteTicketModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteTicketModalLabel">Update Ticket Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="deleteTicketForm" method="POST">
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
                    }, 500);
                });
            }
            
            // Auto-submit on event change
            if (eventSelect) {
                eventSelect.addEventListener('change', function() {
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
        
        // Auto-open modals if there are validation errors
        document.addEventListener('DOMContentLoaded', function() {
            @if($errors->getBag('create')->any())
                const createModal = new bootstrap.Modal(document.getElementById('createTicketModal'));
                createModal.show();
            @endif

            @if($errors->getBag('edit')->any())
                const editModal = new bootstrap.Modal(document.getElementById('editTicketModal'));
                editModal.show();
            @endif
        });

        // Handle Edit Modal
        document.addEventListener('DOMContentLoaded', function() {
            const editModal = document.getElementById('editTicketModal');
            if (editModal) {
                editModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const ticketId = button.getAttribute('data-ticket-id');
                    const name = button.getAttribute('data-name');
                    const description = button.getAttribute('data-description');
                    const price = button.getAttribute('data-price');
                    const quantityDiscount = button.getAttribute('data-quantity-discount');
                    const remarks = button.getAttribute('data-remarks');
                    const image = button.getAttribute('data-image');
                    const eventsJson = button.getAttribute('data-events');

                    const form = document.getElementById('editTicketForm');
                    form.action = '{{ route("admin.events.tickets.update", ":id") }}'.replace(':id', ticketId);

                    document.getElementById('edit_name').value = name || '';
                    document.getElementById('edit_description').value = description || '';
                    document.getElementById('edit_price').value = price || '';
                    document.getElementById('edit_quantity_discount').value = quantityDiscount || '';
                    document.getElementById('edit_remarks').value = remarks || '';
                    
                    // Reset file input and removed image
                    document.getElementById('edit_image').value = '';
                    const removedImageInput = document.getElementById('edit_removed_image');
                    if (removedImageInput) {
                        removedImageInput.value = '';
                    }
                    
                    // Display current image with remove button
                    const imageContainer = document.getElementById('edit_current_image');
                    if (image) {
                        const imageUrl = '{{ url("storage/serve") }}/' + image;
                        const fileName = image.split('/').pop();
                        const escapedPath = image.replace(/\\/g, '\\\\').replace(/'/g, "\\'");
                        imageContainer.innerHTML = `
                            <div class="d-flex align-items-center border rounded p-2" style="background-color: #f8f9fa;">
                                <img src="${imageUrl}" alt="${name}" class="rounded me-2" style="width: 60px; height: 60px; object-fit: cover; border: 1px solid #dee2e6;">
                                <div class="flex-grow-1">
                                    <small class="d-block text-truncate" style="max-width: 200px;" title="${fileName}">${fileName}</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeEditTicketImage('${escapedPath}')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        `;
                    } else {
                        imageContainer.innerHTML = '<p class="text-muted small">No image</p>';
                    }
                    
                    // Set selected events
                    let selectedEvents = [];
                    try {
                        selectedEvents = JSON.parse(eventsJson || '[]');
                    } catch (e) {
                        selectedEvents = [];
                    }
                    
                    const eventsSelect = document.getElementById('edit_events');
                    if (eventsSelect) {
                        Array.from(eventsSelect.options).forEach(option => {
                            option.selected = selectedEvents.includes(parseInt(option.value));
                        });
                    }
                });
            }

            // Handle Delete Modal
            const deleteModal = document.getElementById('deleteTicketModal');
            if (deleteModal) {
                // Clear modal content when hidden
                deleteModal.addEventListener('hidden.bs.modal', function() {
                    const deleteMessage = document.getElementById('delete_message');
                    if (deleteMessage) deleteMessage.innerHTML = '';
                });

                deleteModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const ticketId = button.getAttribute('data-ticket-id');
                    const name = button.getAttribute('data-name');
                    const currentStatus = button.getAttribute('data-status');

                    const form = document.getElementById('deleteTicketForm');
                    form.action = '{{ route("admin.events.tickets.destroy", ":id") }}'.replace(':id', ticketId);

                    const modalTitle = document.getElementById('deleteTicketModalLabel');
                    const deleteMessage = document.getElementById('delete_message');
                    const submitBtn = document.getElementById('delete_submit_btn');
                    
                    if (!modalTitle || !deleteMessage || !submitBtn) {
                        console.error('Modal elements not found');
                        return;
                    }
                    
                    // Check if ticket is active (normalize for comparison)
                    const isActive = currentStatus && currentStatus.toLowerCase().trim() === 'active';
                    
                    // Update all modal content with fresh data
                    if (isActive) {
                        modalTitle.textContent = 'Deactivate Ticket';
                        deleteMessage.innerHTML = 'Are you sure you want to <strong>deactivate</strong> ticket <strong>' + name + '</strong>?';
                        submitBtn.textContent = 'Deactivate Ticket';
                        submitBtn.className = 'btn btn-danger';
                    } else {
                        modalTitle.textContent = 'Activate Ticket';
                        deleteMessage.innerHTML = 'Are you sure you want to <strong>activate</strong> ticket <strong>' + name + '</strong>?';
                        submitBtn.textContent = 'Activate Ticket';
                        submitBtn.className = 'btn btn-success';
                    }
                });
            }
        });
        
        // Function to remove image from edit ticket modal
        window.removeEditTicketImage = function(imagePath) {
            // Hide the image container
            const imageContainer = document.getElementById('edit_current_image');
            if (imageContainer) {
                imageContainer.innerHTML = '<p class="text-muted small">Image will be removed on save</p>';
            }
            
            // Store the removed image path
            const removedInput = document.getElementById('edit_removed_image');
            if (removedInput) {
                removedInput.value = imagePath;
            }
        };
    </script>
    @endpush
@endsection
