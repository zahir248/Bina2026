@extends('layouts.admin.app')

@section('title', 'Events')

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

    /* Content cards grid: title and description side by side */
    .content-card-row .content-card-title-input {
        flex: 0 0 180px;
        min-width: 140px;
    }
    .content-card-row .content-card-desc-input {
        flex: 1 1 200px;
        min-width: 180px;
    }
</style>
@endpush

@section('content')
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">Events</h3>
            <button class="btn-admin btn-admin-primary" data-bs-toggle="modal" data-bs-target="#createEventModal">
                <i class="bi bi-plus-circle"></i>
                Add Event
            </button>
        </div>
        <div class="card-body">
            <!-- Filter Section -->
            <form method="GET" action="{{ route('admin.events.index') }}" class="mb-4" id="filterForm">
                <div class="row g-3 align-items-end">
                    <!-- Search Input -->
                    <div class="col-md-4">
                        <label for="search" class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Search</label>
                        <input type="text" 
                               class="form-control form-control-sm" 
                               id="search" 
                               name="search" 
                               placeholder="Search by name or description..." 
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
                            <th class="text-center">Name</th>
                            <th class="text-center">Category</th>
                            <th class="text-center">Start Date</th>
                            <th class="text-center">End Date</th>
                            <th class="text-center">Ticket Stock</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                            <tr style="cursor: pointer;" 
                                class="event-row"
                                data-event-id="{{ $event->id }}"
                                data-name="{{ $event->name }}"
                                data-description="{{ $event->description ?? '' }}"
                                data-category="{{ $event->category->name ?? '-' }}"
                                data-location="{{ $event->location }}"
                                data-google-maps-address="{{ $event->google_maps_address ?? '' }}"
                                data-waze-location-address="{{ $event->waze_location_address ?? '' }}"
                                data-start-datetime="{{ $event->start_datetime->format('M d, Y H:i') }}"
                                data-end-datetime="{{ $event->end_datetime->format('M d, Y H:i') }}"
                                data-ticket-stock="{{ $event->ticket_stock ?? '-' }}"
                                data-status="{{ $event->status }}"
                                data-created-at="{{ $event->created_at->format('M d, Y') }}"
                                data-images="{{ is_array($event->images) ? json_encode($event->images) : '[]' }}">
                                <td class="text-center">{{ $loop->iteration + ($events->currentPage() - 1) * $events->perPage() }}</td>
                                <td class="text-center">{{ $event->name }}</td>
                                <td class="text-center">{{ $event->category->name ?? '-' }}</td>
                                <td class="text-center">{{ $event->start_datetime->format('M d, Y H:i') }}</td>
                                <td class="text-center">{{ $event->end_datetime->format('M d, Y H:i') }}</td>
                                <td class="text-center">
                                    @if($event->ticket_stock !== null)
                                        {{ $event->ticket_stock }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $event->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($event->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        @if($event->status === 'active')
                                            <button class="btn btn-sm btn-outline-success" title="Edit" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editEventModal"
                                                    data-event-id="{{ $event->id }}"
                                                    data-name="{{ $event->name }}"
                                                    data-description="{{ $event->description ?? '' }}"
                                                    data-category-id="{{ $event->event_category_id }}"
                                                    data-location="{{ $event->location }}"
                                                    data-google-maps-address="{{ $event->google_maps_address ?? '' }}"
                                                    data-waze-location-address="{{ $event->waze_location_address ?? '' }}"
                                                    data-start-datetime="{{ $event->start_datetime->format('Y-m-d\TH:i') }}"
                                                    data-end-datetime="{{ $event->end_datetime->format('Y-m-d\TH:i') }}"
                                                    data-ticket-stock="{{ $event->ticket_stock ?? '' }}"
                                                    data-images="{{ is_array($event->images) ? json_encode($event->images) : '[]' }}"
                                                    data-status="{{ $event->status }}"
                                                    data-content-before-tickets="{{ base64_encode($event->content_before_tickets ?? '') }}"
                                                    data-content-cards="{{ base64_encode(json_encode($event->content_cards ?? [])) }}"
                                                    data-content-cards-heading="{{ e($event->content_cards_heading ?? '') }}"
                                                    data-content-cards-subheading="{{ e($event->content_cards_subheading ?? '') }}"
                                                    data-content-list-heading="{{ e($event->content_list_heading ?? '') }}"
                                                    data-content-list-items="{{ base64_encode(json_encode($event->content_list_items ?? [])) }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        @endif
                                        <button class="btn btn-sm {{ $event->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }}" 
                                                title="{{ $event->status === 'active' ? 'Deactivate' : 'Activate' }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteEventModal"
                                                data-event-id="{{ $event->id }}"
                                                data-name="{{ $event->name }}"
                                                data-status="{{ $event->status }}">
                                            <i class="bi {{ $event->status === 'active' ? 'bi-trash' : 'bi-arrow-counterclockwise' }}"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">No events found.</td>
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

    <!-- Create Event Modal -->
    <div class="modal fade" id="createEventModal" tabindex="-1" aria-labelledby="createEventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createEventModalLabel">Create New Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.events.store') }}" method="POST" id="createEventForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
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
                                <label for="create_category" class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm @error('event_category_id', 'create') is-invalid @enderror" 
                                        id="create_category" 
                                        name="event_category_id" 
                                        required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('event_category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('event_category_id', 'create')
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
                        
                        <div class="mb-2">
                            <label for="create_content_before_tickets" class="form-label">Content before ticket section</label>
                            <div class="d-flex gap-2 align-items-start mb-1">
                                <textarea class="form-control form-control-sm @error('content_before_tickets', 'create') is-invalid @enderror flex-grow-1" 
                                          id="create_content_before_tickets" 
                                          name="content_before_tickets" 
                                          rows="4" 
                                          placeholder="Optional: HTML and images. Shown centered between event info and ticket section. Use the button below to upload images.">{{ old('content_before_tickets') }}</textarea>
                                <div class="d-flex flex-column gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="create_upload_content_image_btn" title="Upload image">
                                        Upload image
                                    </button>
                                    <input type="file" id="create_content_image_input" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="d-none">
                                </div>
                            </div>
                            <small class="form-text text-muted">Shown on the event page between the event info and ticket section. HTML supported. Upload images to insert them.</small>
                            @error('content_before_tickets', 'create')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Content cards grid</label>
                            <small class="form-text text-muted d-block mb-2">Optional header and subheader appear above the cards on the event page.</small>
                            <div class="row mb-2">
                                <div class="col-12 mb-1">
                                    <input type="text" class="form-control form-control-sm" id="create_content_cards_heading" name="content_cards_heading" value="{{ old('content_cards_heading') }}" placeholder="Section heading (e.g. Explore Our Technology Categories)">
                                </div>
                                <div class="col-12">
                                    <input type="text" class="form-control form-control-sm" id="create_content_cards_subheading" name="content_cards_subheading" value="{{ old('content_cards_subheading') }}" placeholder="Subheading (e.g. Discover the future of construction and design...)">
                                </div>
                            </div>
                            <small class="form-text text-muted d-block mb-2">Add cards with title and description (shown in a grid above the ticket section).</small>
                            <div id="create_content_cards_rows"></div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="create_add_card_btn">
                                <i class="bi bi-plus-lg"></i> Add card
                            </button>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Important points / list section</label>
                            <small class="form-text text-muted d-block mb-2">Optional heading and bullet list shown below the content cards (e.g. "IMPORTANT POINTS IN THIS SESSION :").</small>
                            <input type="text" class="form-control form-control-sm mb-2" id="create_content_list_heading" name="content_list_heading" value="{{ old('content_list_heading') }}" placeholder="Section heading (e.g. IMPORTANT POINTS IN THIS SESSION :)">
                            <div id="create_content_list_rows"></div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="create_add_list_item_btn">
                                <i class="bi bi-plus-lg"></i> Add list item
                            </button>
                        </div>
                        
                        <div class="mb-2">
                            <label for="create_location" class="form-label">Location <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control form-control-sm @error('location', 'create') is-invalid @enderror" 
                                   id="create_location" 
                                   name="location" 
                                   value="{{ old('location') }}" 
                                   required>
                            @error('location', 'create')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="create_google_maps_address" class="form-label">Google Map Address</label>
                                <input type="text" 
                                       class="form-control form-control-sm @error('google_maps_address', 'create') is-invalid @enderror" 
                                       id="create_google_maps_address" 
                                       name="google_maps_address" 
                                       value="{{ old('google_maps_address') }}" 
                                       placeholder="https://maps.google.com/...">
                                @error('google_maps_address', 'create')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-2">
                                <label for="create_waze_location_address" class="form-label">Waze Address</label>
                                <input type="text" 
                                       class="form-control form-control-sm @error('waze_location_address', 'create') is-invalid @enderror" 
                                       id="create_waze_location_address" 
                                       name="waze_location_address" 
                                       value="{{ old('waze_location_address') }}" 
                                       placeholder="https://waze.com/ul?q=...">
                                @error('waze_location_address', 'create')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="create_start_datetime" class="form-label">Start Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" 
                                       class="form-control form-control-sm @error('start_datetime', 'create') is-invalid @enderror" 
                                       id="create_start_datetime" 
                                       name="start_datetime" 
                                       value="{{ old('start_datetime') }}" 
                                       required>
                                @error('start_datetime', 'create')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-2">
                                <label for="create_end_datetime" class="form-label">End Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" 
                                       class="form-control form-control-sm @error('end_datetime', 'create') is-invalid @enderror" 
                                       id="create_end_datetime" 
                                       name="end_datetime" 
                                       value="{{ old('end_datetime') }}" 
                                       required>
                                @error('end_datetime', 'create')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            <label for="create_ticket_stock" class="form-label">Ticket Stock</label>
                            <input type="number" 
                                   class="form-control form-control-sm @error('ticket_stock', 'create') is-invalid @enderror" 
                                   id="create_ticket_stock" 
                                   name="ticket_stock" 
                                   value="{{ old('ticket_stock') }}" 
                                   min="0">
                            @error('ticket_stock', 'create')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-2">
                            <label for="create_images" class="form-label">Images</label>
                            <input type="file" 
                                   class="form-control form-control-sm @error('images', 'create') is-invalid @enderror" 
                                   id="create_images" 
                                   name="images[]" 
                                   multiple 
                                   accept="image/*">
                            <small class="form-text text-muted">You can select multiple images</small>
                            @error('images', 'create')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('images.*', 'create')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer" style="padding: 0.75rem 1rem;">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-success">Create Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Event Modal -->
    <div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-height: 90vh; margin: 1.75rem auto; display: flex; flex-direction: column;">
            <div class="modal-content" style="max-height: 90vh; display: flex; flex-direction: column; overflow: hidden;">
                <div class="modal-header" style="flex: 0 0 auto;">
                    <h5 class="modal-title" id="editEventModalLabel">Edit Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editEventForm" method="POST" action="" enctype="multipart/form-data" style="display: flex; flex-direction: column; height: 100%;">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_event_id" name="event_id" value="">
                    <div class="modal-body" style="flex: 1 1 auto; overflow-y: auto; overflow-x: hidden; min-height: 0; max-height: calc(90vh - 150px);">
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
                                <label for="edit_category" class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm @error('event_category_id', 'edit') is-invalid @enderror" 
                                        id="edit_category" 
                                        name="event_category_id" 
                                        required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('event_category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('event_category_id', 'edit')
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
                        
                        <div class="mb-2">
                            <label for="edit_content_before_tickets" class="form-label">Content before ticket section</label>
                            <div class="d-flex gap-2 align-items-start mb-1">
                                <textarea class="form-control form-control-sm @error('content_before_tickets', 'edit') is-invalid @enderror flex-grow-1" 
                                          id="edit_content_before_tickets" 
                                          name="content_before_tickets" 
                                          rows="4" 
                                          placeholder="Optional: HTML and images. Shown centered between event info and ticket section. Use the button below to upload images."></textarea>
                                <div class="d-flex flex-column gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="edit_upload_content_image_btn" title="Upload image">
                                        Upload image
                                    </button>
                                    <input type="file" id="edit_content_image_input" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="d-none">
                                </div>
                            </div>
                            <small class="form-text text-muted">Shown on the event page between the event info and ticket section. HTML supported. Upload images to insert them.</small>
                            @error('content_before_tickets', 'edit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Content cards grid</label>
                            <small class="form-text text-muted d-block mb-2">Optional header and subheader appear above the cards on the event page.</small>
                            <div class="row mb-2">
                                <div class="col-12 mb-1">
                                    <input type="text" class="form-control form-control-sm" id="edit_content_cards_heading" name="content_cards_heading" placeholder="Section heading">
                                </div>
                                <div class="col-12">
                                    <input type="text" class="form-control form-control-sm" id="edit_content_cards_subheading" name="content_cards_subheading" placeholder="Subheading">
                                </div>
                            </div>
                            <small class="form-text text-muted d-block mb-2">Add cards with title and description (shown in a grid above the ticket section).</small>
                            <div id="edit_content_cards_rows"></div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="edit_add_card_btn">
                                <i class="bi bi-plus-lg"></i> Add card
                            </button>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Important points / list section</label>
                            <small class="form-text text-muted d-block mb-2">Optional heading and bullet list shown below the content cards.</small>
                            <input type="text" class="form-control form-control-sm mb-2" id="edit_content_list_heading" name="content_list_heading" placeholder="Section heading">
                            <div id="edit_content_list_rows"></div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="edit_add_list_item_btn">
                                <i class="bi bi-plus-lg"></i> Add list item
                            </button>
                        </div>
                        
                        <div class="mb-2">
                            <label for="edit_location" class="form-label">Location <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control form-control-sm @error('location', 'edit') is-invalid @enderror" 
                                   id="edit_location" 
                                   name="location" 
                                   value="{{ old('location') }}" 
                                   required>
                            @error('location', 'edit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="edit_google_maps_address" class="form-label">Google Map Address</label>
                                <input type="text" 
                                       class="form-control form-control-sm @error('google_maps_address', 'edit') is-invalid @enderror" 
                                       id="edit_google_maps_address" 
                                       name="google_maps_address" 
                                       value="{{ old('google_maps_address') }}" 
                                       placeholder="https://maps.google.com/...">
                                @error('google_maps_address', 'edit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-2">
                                <label for="edit_waze_location_address" class="form-label">Waze Address</label>
                                <input type="text" 
                                       class="form-control form-control-sm @error('waze_location_address', 'edit') is-invalid @enderror" 
                                       id="edit_waze_location_address" 
                                       name="waze_location_address" 
                                       value="{{ old('waze_location_address') }}" 
                                       placeholder="https://waze.com/ul?q=...">
                                @error('waze_location_address', 'edit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="edit_start_datetime" class="form-label">Start Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" 
                                       class="form-control form-control-sm @error('start_datetime', 'edit') is-invalid @enderror" 
                                       id="edit_start_datetime" 
                                       name="start_datetime" 
                                       value="{{ old('start_datetime') }}" 
                                       required>
                                @error('start_datetime', 'edit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-2">
                                <label for="edit_end_datetime" class="form-label">End Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" 
                                       class="form-control form-control-sm @error('end_datetime', 'edit') is-invalid @enderror" 
                                       id="edit_end_datetime" 
                                       name="end_datetime" 
                                       value="{{ old('end_datetime') }}" 
                                       required>
                                @error('end_datetime', 'edit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            <label for="edit_ticket_stock" class="form-label">Ticket Stock</label>
                            <input type="number" 
                                   class="form-control form-control-sm @error('ticket_stock', 'edit') is-invalid @enderror" 
                                   id="edit_ticket_stock" 
                                   name="ticket_stock" 
                                   value="{{ old('ticket_stock') }}" 
                                   min="0">
                            @error('ticket_stock', 'edit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-2">
                            <label class="form-label">Current Images</label>
                            <div id="edit_current_images" class="mb-2">
                                <p class="text-muted small">No current images</p>
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            <label for="edit_images" class="form-label">Add New Images</label>
                            <input type="file" 
                                   class="form-control form-control-sm @error('images', 'edit') is-invalid @enderror" 
                                   id="edit_images" 
                                   name="images[]" 
                                   multiple 
                                   accept="image/*">
                            <small class="form-text text-muted">You can select multiple images. Leave empty to keep existing images.</small>
                            @error('images', 'edit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('images.*', 'edit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Hidden input to store removed image paths -->
                        <input type="hidden" id="edit_removed_images" name="removed_images" value="">
                    </div>
                    <div class="modal-footer" style="flex: 0 0 auto; padding: 0.75rem 1rem; border-top: 1px solid #dee2e6; margin-top: auto;">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-success">Update Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Event Detail Modal -->
    <div class="modal fade" id="eventDetailModal" tabindex="-1" aria-labelledby="eventDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventDetailModalLabel">Event Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Name:</strong>
                        </div>
                        <div class="col-md-8" id="detail_name">
                            -
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Category:</strong>
                        </div>
                        <div class="col-md-8" id="detail_category">
                            -
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Description:</strong>
                        </div>
                        <div class="col-md-8" id="detail_description">
                            -
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Location:</strong>
                        </div>
                        <div class="col-md-8" id="detail_location">
                            -
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Google Map Address:</strong>
                        </div>
                        <div class="col-md-8" id="detail_google_maps_address" style="word-wrap: break-word; overflow-wrap: break-word; max-width: 100%;">
                            -
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Waze Address:</strong>
                        </div>
                        <div class="col-md-8" id="detail_waze_location_address" style="word-wrap: break-word; overflow-wrap: break-word; max-width: 100%;">
                            -
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Start Date & Time:</strong>
                        </div>
                        <div class="col-md-8" id="detail_start_datetime">
                            -
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>End Date & Time:</strong>
                        </div>
                        <div class="col-md-8" id="detail_end_datetime">
                            -
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Ticket Stock:</strong>
                        </div>
                        <div class="col-md-8" id="detail_ticket_stock">
                            -
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Status:</strong>
                        </div>
                        <div class="col-md-8" id="detail_status">
                            -
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Created At:</strong>
                        </div>
                        <div class="col-md-8" id="detail_created_at">
                            -
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Images:</strong>
                        </div>
                        <div class="col-12 mt-2" id="detail_images">
                            <p class="text-muted">No images available</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Event Modal -->
    <div class="modal fade" id="deleteEventModal" tabindex="-1" aria-labelledby="deleteEventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteEventModalLabel">Update Event Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="deleteEventForm" method="POST">
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
    <style>
        #eventDetailModal .modal-body {
            overflow-x: hidden;
        }
        #eventDetailModal .modal-body .col-md-8 {
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-width: 100%;
        }
        
        #createEventModal .modal-dialog {
            max-height: 90vh;
            margin: 1.75rem auto;
        }
        #createEventModal .modal-content {
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        #createEventModal .modal-header {
            flex: 0 0 auto;
        }
        #createEventModal .modal-body {
            flex: 1 1 auto;
            overflow-y: auto;
            overflow-x: hidden;
            min-height: 0;
            max-height: calc(90vh - 150px);
        }
        #createEventModal .modal-footer {
            flex: 0 0 auto;
        }
        
        #editEventModal .modal-dialog {
            max-height: 90vh;
            margin: 1.75rem auto;
            display: flex;
            flex-direction: column;
        }
        #editEventModal .modal-content {
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        #editEventModal .modal-header {
            flex: 0 0 auto;
            border-bottom: 1px solid #dee2e6;
        }
        #editEventModal .modal-body {
            flex: 1 1 auto;
            overflow-y: auto;
            overflow-x: hidden;
            min-height: 0;
            max-height: calc(90vh - 150px);
        }
        #editEventModal .modal-footer {
            flex: 0 0 auto;
            border-top: 1px solid #dee2e6;
            margin-top: auto;
        }
        #editEventModal form {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
    </style>
    <script>
        // Handle row click to show detail modal
        document.addEventListener('DOMContentLoaded', function() {
            const eventRows = document.querySelectorAll('.event-row');
            eventRows.forEach(row => {
                row.addEventListener('click', function(e) {
                    // Don't trigger if clicking on action buttons or links
                    if (e.target.closest('button') || 
                        e.target.closest('td:last-child') || 
                        e.target.closest('a') ||
                        e.target.tagName === 'BUTTON' ||
                        e.target.tagName === 'A') {
                        return;
                    }
                    
                    const eventId = this.getAttribute('data-event-id');
                    const name = this.getAttribute('data-name');
                    const description = this.getAttribute('data-description');
                    const category = this.getAttribute('data-category');
                    const location = this.getAttribute('data-location');
                    const googleMapsAddress = this.getAttribute('data-google-maps-address') || '';
                    const wazeLocationAddress = this.getAttribute('data-waze-location-address') || '';
                    const startDatetime = this.getAttribute('data-start-datetime');
                    const endDatetime = this.getAttribute('data-end-datetime');
                    const ticketStock = this.getAttribute('data-ticket-stock');
                    const status = this.getAttribute('data-status');
                    const createdAt = this.getAttribute('data-created-at');
                    const imagesJson = this.getAttribute('data-images');
                    
                    // Parse images
                    let images = [];
                    try {
                        images = JSON.parse(imagesJson || '[]');
                    } catch (e) {
                        images = [];
                    }
                    
                    // Populate detail modal
                    document.getElementById('detail_name').textContent = name || '-';
                    document.getElementById('detail_category').textContent = category || '-';
                    document.getElementById('detail_description').textContent = description || '-';
                    document.getElementById('detail_location').textContent = location || '-';
                    document.getElementById('detail_google_maps_address').textContent = googleMapsAddress || '-';
                    document.getElementById('detail_waze_location_address').textContent = wazeLocationAddress || '-';
                    document.getElementById('detail_start_datetime').textContent = startDatetime || '-';
                    document.getElementById('detail_end_datetime').textContent = endDatetime || '-';
                    document.getElementById('detail_ticket_stock').textContent = ticketStock || '-';
                    
                    // Status badge
                    const statusEl = document.getElementById('detail_status');
                    if (status === 'active') {
                        statusEl.innerHTML = '<span class="badge bg-success">Active</span>';
                    } else {
                        statusEl.innerHTML = '<span class="badge bg-danger">Inactive</span>';
                    }
                    
                    document.getElementById('detail_created_at').textContent = createdAt || '-';
                    
                    // Display images
                    const imagesContainer = document.getElementById('detail_images');
                    if (images && images.length > 0) {
                        let imagesHTML = '<div class="row g-2" style="display: flex; flex-wrap: wrap;">';
                        images.forEach(imagePath => {
                            const imageUrl = '{{ url("storage/serve") }}/' + imagePath;
                            imagesHTML += `
                                <div class="col-md-3 col-sm-6 col-6" style="display: flex;">
                                    <img src="${imageUrl}" alt="Event Image" class="img-fluid rounded" style="max-height: 150px; width: 100%; object-fit: cover; cursor: pointer; border: 1px solid #dee2e6;" onclick="window.open('${imageUrl}', '_blank')">
                                </div>
                            `;
                        });
                        imagesHTML += '</div>';
                        imagesContainer.innerHTML = imagesHTML;
                    } else {
                        imagesContainer.innerHTML = '<p class="text-muted">No images available</p>';
                    }
                    
                    // Show modal
                    const detailModal = new bootstrap.Modal(document.getElementById('eventDetailModal'));
                    detailModal.show();
                });
            });
        });
        
        // Function to remove image from edit modal
        window.removeEditImage = function(imagePath) {
            // Find and remove the image element from DOM using data attribute
            const imageElements = document.querySelectorAll('[data-image-path]');
            imageElements.forEach(element => {
                if (element.getAttribute('data-image-path') === imagePath) {
                    element.remove();
                }
            });
            
            // Add to removed images list
            const removedInput = document.getElementById('edit_removed_images');
            let removedImages = [];
            
            if (removedInput && removedInput.value) {
                try {
                    removedImages = JSON.parse(removedInput.value);
                } catch (e) {
                    removedImages = [];
                }
            }
            
            if (!removedImages.includes(imagePath)) {
                removedImages.push(imagePath);
                if (removedInput) {
                    removedInput.value = JSON.stringify(removedImages);
                }
            }
            
            // Check if no images left
            const currentImagesContainer = document.getElementById('edit_current_images');
            if (currentImagesContainer) {
                const remainingImages = currentImagesContainer.querySelectorAll('[data-image-path]');
                if (remainingImages.length === 0) {
                    currentImagesContainer.innerHTML = '<p class="text-muted small">No current images</p>';
                }
            }
        };

        // Content-before-tickets image upload (create & edit)
        document.addEventListener('DOMContentLoaded', function() {
            const uploadUrl = '{{ route("admin.events.upload-content-image") }}';
            const csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';

            function setupContentImageUpload(btnId, inputId, textareaId) {
                const btn = document.getElementById(btnId);
                const input = document.getElementById(inputId);
                const textarea = document.getElementById(textareaId);
                if (!btn || !input || !textarea) return;

                btn.addEventListener('click', function() { input.click(); });

                input.addEventListener('change', function() {
                    const file = this.files[0];
                    if (!file) return;

                    const formData = new FormData();
                    formData.append('image', file);
                    formData.append('_token', csrfToken);

                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Uploading...';

                    fetch(uploadUrl, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(function(res) {
                        return res.json().then(function(data) {
                            if (!res.ok) {
                                var err = new Error(data.message || data.error || 'Upload failed.');
                                err.data = data;
                                err.status = res.status;
                                throw err;
                            }
                            return data;
                        });
                    })
                    .then(function(data) {
                        if (data.url) {
                            const imgTag = '<img src="' + data.url + '" alt="">';
                            const start = textarea.selectionStart, end = textarea.selectionEnd;
                            const text = textarea.value;
                            const before = text.substring(0, start), after = text.substring(end);
                            textarea.value = before + imgTag + after;
                            textarea.selectionStart = textarea.selectionEnd = start + imgTag.length;
                            textarea.focus();
                        }
                    })
                    .catch(function(err) {
                        var msg = 'Upload failed. Please try again.';
                        if (err.data && err.data.errors && typeof err.data.errors === 'object') {
                            var firstKey = Object.keys(err.data.errors)[0];
                            if (firstKey) msg = err.data.errors[firstKey][0] || msg;
                        } else if (err.message) msg = err.message;
                        alert(msg);
                    })
                    .finally(function() {
                        btn.disabled = false;
                        btn.innerHTML = 'Upload image';
                        input.value = '';
                    });
                });
            }

            setupContentImageUpload('create_upload_content_image_btn', 'create_content_image_input', 'create_content_before_tickets');
            setupContentImageUpload('edit_upload_content_image_btn', 'edit_content_image_input', 'edit_content_before_tickets');
        });

        // Content cards grid (create + edit)
        (function() {
            function cardRowHtml(index, cardData) {
                cardData = cardData || {};
                var title = (cardData.title || '').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                var desc = (cardData.description || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                return '<div class="content-card-row mb-2 p-2 border rounded d-flex flex-wrap gap-2 align-items-start" data-index="' + index + '">' +
                    '<input type="text" class="form-control form-control-sm content-card-title-input" name="content_cards[' + index + '][title]" placeholder="Card title" value="' + title + '">' +
                    '<textarea class="form-control form-control-sm content-card-desc-input" name="content_cards[' + index + '][description]" rows="2" placeholder="Description">' + desc + '</textarea>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger card-remove-btn flex-shrink-0" title="Remove"><i class="bi bi-trash"></i></button>' +
                    '</div>';
            }

            function nextCreateIndex() {
                var rows = document.querySelectorAll('#create_content_cards_rows .content-card-row');
                return rows.length;
            }
            function nextEditIndex() {
                var rows = document.querySelectorAll('#edit_content_cards_rows .content-card-row');
                return rows.length;
            }

            document.addEventListener('DOMContentLoaded', function() {
                var createRows = document.getElementById('create_content_cards_rows');
                var editRows = document.getElementById('edit_content_cards_rows');
                var createAddBtn = document.getElementById('create_add_card_btn');
                var editAddBtn = document.getElementById('edit_add_card_btn');

                if (createAddBtn && createRows) {
                    createAddBtn.addEventListener('click', function() {
                        var idx = nextCreateIndex();
                        createRows.insertAdjacentHTML('beforeend', cardRowHtml(idx, {}));
                    });
                }
                if (editAddBtn && editRows) {
                    editAddBtn.addEventListener('click', function() {
                        var idx = nextEditIndex();
                        editRows.insertAdjacentHTML('beforeend', cardRowHtml(idx, {}));
                    });
                }

                createRows && createRows.addEventListener('click', function(e) {
                    var removeBtn = e.target.closest('.card-remove-btn');
                    if (removeBtn) removeBtn.closest('.content-card-row').remove();
                });
                editRows && editRows.addEventListener('click', function(e) {
                    var removeBtn = e.target.closest('.card-remove-btn');
                    if (removeBtn) removeBtn.closest('.content-card-row').remove();
                });

                var editModal = document.getElementById('editEventModal');
                if (editModal && editRows) {
                    editModal.addEventListener('show.bs.modal', function(event) {
                        var button = event.relatedTarget;
                        if (!button) return;
                        var cardsAttr = button.getAttribute('data-content-cards') || '';
                        editRows.innerHTML = '';
                        try {
                            var cards = cardsAttr ? JSON.parse(atob(cardsAttr)) : [];
                            if (Array.isArray(cards)) {
                                cards.forEach(function(card, i) {
                                    editRows.insertAdjacentHTML('beforeend', cardRowHtml(i, {
                                        title: card.title || '',
                                        description: card.description || ''
                                    }));
                                });
                            }
                        } catch (err) {}
});
                }
            });
        })();

        // Important points / list section (create + edit)
        document.addEventListener('DOMContentLoaded', function() {
            function listItemRowHtml(index, value) {
                value = (value || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                return '<div class="content-list-item-row mb-1 d-flex gap-1 align-items-center">' +
                    '<input type="text" class="form-control form-control-sm flex-grow-1" name="content_list_items[' + index + ']" value="' + value + '" placeholder="List item">' +
                    '<button type="button" class="btn btn-sm btn-outline-danger list-item-remove-btn"><i class="bi bi-trash"></i></button>' +
                    '</div>';
            }
            function nextListIndex(containerId) {
                var container = document.getElementById(containerId);
                return container ? container.querySelectorAll('.content-list-item-row').length : 0;
            }
            var createListRows = document.getElementById('create_content_list_rows');
            var editListRows = document.getElementById('edit_content_list_rows');
            var createAddListBtn = document.getElementById('create_add_list_item_btn');
            var editAddListBtn = document.getElementById('edit_add_list_item_btn');
            if (createAddListBtn && createListRows) {
                createAddListBtn.addEventListener('click', function() {
                    var idx = nextListIndex('create_content_list_rows');
                    createListRows.insertAdjacentHTML('beforeend', listItemRowHtml(idx, ''));
                });
            }
            if (editAddListBtn && editListRows) {
                editAddListBtn.addEventListener('click', function() {
                    var idx = nextListIndex('edit_content_list_rows');
                    editListRows.insertAdjacentHTML('beforeend', listItemRowHtml(idx, ''));
                });
            }
            createListRows && createListRows.addEventListener('click', function(e) {
                var btn = e.target.closest('.list-item-remove-btn');
                if (btn) btn.closest('.content-list-item-row').remove();
            });
            editListRows && editListRows.addEventListener('click', function(e) {
                var btn = e.target.closest('.list-item-remove-btn');
                if (btn) btn.closest('.content-list-item-row').remove();
            });
        });

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
                    }, 500); // Wait 500ms after user stops typing
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
        
        // Auto-open modals if there are validation errors
        document.addEventListener('DOMContentLoaded', function() {
            @if($errors->getBag('create')->any())
                const createModal = new bootstrap.Modal(document.getElementById('createEventModal'));
                createModal.show();
            @endif

            @if($errors->getBag('edit')->any())
                const editModal = new bootstrap.Modal(document.getElementById('editEventModal'));
                // Try to get event ID from the edit button that might have been clicked
                const editButton = document.querySelector('[data-bs-target="#editEventModal"][data-event-id]');
                const editForm = document.getElementById('editEventForm');
                if (editButton && editForm) {
                    const eventId = editButton.getAttribute('data-event-id');
                    if (eventId) {
                        const eventIdInput = document.getElementById('edit_event_id');
                        if (eventIdInput) {
                            eventIdInput.value = eventId;
                        }
                        editForm.action = '{{ route("admin.events.update", ":id") }}'.replace(':id', eventId);
                    }
                }
                editModal.show();
            @endif
        });

        // Handle Edit Modal
        document.addEventListener('DOMContentLoaded', function() {
            const editModal = document.getElementById('editEventModal');
            if (editModal) {
                // Clear form action when modal is hidden
                editModal.addEventListener('hidden.bs.modal', function() {
                    const form = document.getElementById('editEventForm');
                    if (form) {
                        form.action = '';
                    }
                });

                editModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    if (!button) return;
                    
                    const eventId = button.getAttribute('data-event-id');
                    const name = button.getAttribute('data-name');
                    const description = button.getAttribute('data-description');
                    const categoryId = button.getAttribute('data-category-id');
                    const location = button.getAttribute('data-location');
                    const googleMapsAddress = button.getAttribute('data-google-maps-address') || '';
                    const wazeLocationAddress = button.getAttribute('data-waze-location-address') || '';
                    const startDatetime = button.getAttribute('data-start-datetime');
                    const endDatetime = button.getAttribute('data-end-datetime');
                    const ticketStock = button.getAttribute('data-ticket-stock');
                    const status = button.getAttribute('data-status');

                    if (!eventId) {
                        console.error('Event ID not found');
                        return;
                    }

                    const form = document.getElementById('editEventForm');
                    if (!form) {
                        console.error('Edit form not found');
                        return;
                    }
                    
                    // Store event ID in hidden input
                    const eventIdInput = document.getElementById('edit_event_id');
                    if (eventIdInput) {
                        eventIdInput.value = eventId;
                    }
                    
                    // Always set the form action with the correct event ID
                    form.action = '{{ route("admin.events.update", ":id") }}'.replace(':id', eventId);

                    document.getElementById('edit_name').value = name || '';
                    document.getElementById('edit_description').value = description || '';
                    const contentBeforeTicketsAttr = button.getAttribute('data-content-before-tickets') || '';
                    try {
                        document.getElementById('edit_content_before_tickets').value = contentBeforeTicketsAttr ? atob(contentBeforeTicketsAttr) : '';
                    } catch (e) {
                        document.getElementById('edit_content_before_tickets').value = '';
                    }
                    document.getElementById('edit_content_cards_heading').value = button.getAttribute('data-content-cards-heading') || '';
                    document.getElementById('edit_content_cards_subheading').value = button.getAttribute('data-content-cards-subheading') || '';
                    var listHeading = button.getAttribute('data-content-list-heading') || '';
                    document.getElementById('edit_content_list_heading').value = listHeading;
                    var listItemsAttr = button.getAttribute('data-content-list-items') || '';
                    var editListRows = document.getElementById('edit_content_list_rows');
                    editListRows.innerHTML = '';
                    try {
                        var listItems = listItemsAttr ? JSON.parse(atob(listItemsAttr)) : [];
                        if (Array.isArray(listItems)) {
                            listItems.forEach(function(text, i) {
                                var escaped = (text || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                                editListRows.insertAdjacentHTML('beforeend', '<div class="content-list-item-row mb-1 d-flex gap-1 align-items-center"><input type="text" class="form-control form-control-sm flex-grow-1" name="content_list_items[' + i + ']" value="' + escaped + '" placeholder="List item"><button type="button" class="btn btn-sm btn-outline-danger list-item-remove-btn"><i class="bi bi-trash"></i></button></div>');
                            });
                        }
                    } catch (e) {}
                    document.getElementById('edit_category').value = categoryId || '';
                    document.getElementById('edit_location').value = location || '';
                    document.getElementById('edit_google_maps_address').value = googleMapsAddress || '';
                    document.getElementById('edit_waze_location_address').value = wazeLocationAddress || '';
                    document.getElementById('edit_start_datetime').value = startDatetime || '';
                    document.getElementById('edit_end_datetime').value = endDatetime || '';
                    document.getElementById('edit_ticket_stock').value = ticketStock || '';
                    
                    // Reset file input and removed images
                    document.getElementById('edit_images').value = '';
                    document.getElementById('edit_removed_images').value = '';
                    
                    // Display current images
                    const imagesJson = button.getAttribute('data-images');
                    const currentImagesContainer = document.getElementById('edit_current_images');
                    let currentImages = [];
                    
                    try {
                        currentImages = JSON.parse(imagesJson || '[]');
                    } catch (e) {
                        currentImages = [];
                    }
                    
                    if (currentImages && currentImages.length > 0) {
                        let imagesHTML = '<div class="row g-2">';
                        currentImages.forEach((imagePath, index) => {
                            const imageUrl = '{{ url("storage/serve") }}/' + imagePath;
                            const fileName = imagePath.split('/').pop();
                            const escapedPath = imagePath.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                            imagesHTML += `
                                <div class="col-md-6 mb-2" id="edit_image_${index}" data-image-path="${escapedPath}">
                                    <div class="d-flex align-items-center border rounded p-2" style="background-color: #f8f9fa;">
                                        <img src="${imageUrl}" alt="${fileName}" class="rounded me-2" style="width: 60px; height: 60px; object-fit: cover; border: 1px solid #dee2e6;">
                                        <div class="flex-grow-1">
                                            <small class="d-block text-truncate" style="max-width: 200px;" title="${fileName}">${fileName}</small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeEditImage('${escapedPath}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            `;
                        });
                        imagesHTML += '</div>';
                        currentImagesContainer.innerHTML = imagesHTML;
                    } else {
                        currentImagesContainer.innerHTML = '<p class="text-muted small">No current images</p>';
                    }
                });
            }

            // Handle Delete Modal
            const deleteModal = document.getElementById('deleteEventModal');
            if (deleteModal) {
                // Clear modal content when hidden
                deleteModal.addEventListener('hidden.bs.modal', function() {
                    const deleteMessage = document.getElementById('delete_message');
                    if (deleteMessage) deleteMessage.innerHTML = '';
                });

                deleteModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const eventId = button.getAttribute('data-event-id');
                    const name = button.getAttribute('data-name');
                    const currentStatus = button.getAttribute('data-status');

                    const form = document.getElementById('deleteEventForm');
                    form.action = '{{ route("admin.events.destroy", ":id") }}'.replace(':id', eventId);

                    const modalTitle = document.getElementById('deleteEventModalLabel');
                    const deleteMessage = document.getElementById('delete_message');
                    const submitBtn = document.getElementById('delete_submit_btn');
                    
                    if (!modalTitle || !deleteMessage || !submitBtn) {
                        console.error('Modal elements not found');
                        return;
                    }
                    
                    // Check if event is active (normalize for comparison)
                    const isActive = currentStatus && currentStatus.toLowerCase().trim() === 'active';
                    
                    // Update all modal content with fresh data
                    if (isActive) {
                        modalTitle.textContent = 'Deactivate Event';
                        deleteMessage.innerHTML = 'Are you sure you want to <strong>deactivate</strong> event <strong>' + name + '</strong>?';
                        submitBtn.textContent = 'Deactivate Event';
                        submitBtn.className = 'btn btn-danger';
                    } else {
                        modalTitle.textContent = 'Activate Event';
                        deleteMessage.innerHTML = 'Are you sure you want to <strong>activate</strong> event <strong>' + name + '</strong>?';
                        submitBtn.textContent = 'Activate Event';
                        submitBtn.className = 'btn btn-success';
                    }
                });
            }
        });
    </script>
    @endpush
@endsection
