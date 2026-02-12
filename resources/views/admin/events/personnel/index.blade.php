@extends('layouts.admin.app')

@section('title', 'Event Personnel')

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
    
    .personnel-image {
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
            <h3 class="card-title">Event Personnel</h3>
            <button class="btn-admin btn-admin-primary" data-bs-toggle="modal" data-bs-target="#createPersonnelModal">
                <i class="bi bi-plus-circle"></i>
                Add Personnel
            </button>
        </div>
        <div class="card-body">
            <!-- Filter Section -->
            <form method="GET" action="{{ route('admin.events.personnel') }}" class="mb-4" id="filterForm">
                <div class="row g-3 align-items-end">
                    <!-- Search Input -->
                    <div class="col-md-4">
                        <label for="search" class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Search</label>
                        <input type="text" 
                               class="form-control form-control-sm" 
                               id="search" 
                               name="search" 
                               placeholder="Search by name, position, or company..." 
                               value="{{ $search ?? '' }}"
                               autocomplete="off">
                    </div>
                    
                    <!-- Role Filter -->
                    <div class="col-md-4">
                        <label for="role" class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Role</label>
                        <select class="form-select form-select-sm" id="role" name="role">
                            <option value="">All Roles</option>
                            <option value="host" {{ ($roleFilter ?? '') === 'host' ? 'selected' : '' }}>Host</option>
                            <option value="moderator" {{ ($roleFilter ?? '') === 'moderator' ? 'selected' : '' }}>Moderator</option>
                            <option value="speaker" {{ ($roleFilter ?? '') === 'speaker' ? 'selected' : '' }}>Speaker</option>
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
                            <th class="text-center">Role</th>
                            <th class="text-center">Position</th>
                            <th class="text-center">Company</th>
                            <th class="text-center">Schedules</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($personnel as $person)
                            <tr>
                                <td class="text-center">{{ $loop->iteration + ($personnel->currentPage() - 1) * $personnel->perPage() }}</td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center">
                                        @if($person->image)
                                            <a href="{{ storage_asset($person->image) }}" target="_blank" rel="noopener noreferrer">
                                                <img src="{{ storage_asset($person->image) }}" alt="{{ $person->name }}" class="personnel-image" style="cursor: pointer;">
                                            </a>
                                        @else
                                            @php
                                                // Get initials from name
                                                $nameParts = explode(' ', $person->name);
                                                $initials = '';
                                                if (count($nameParts) >= 2) {
                                                    // First letter of first name + first letter of last name
                                                    $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1));
                                                } else {
                                                    // If only one word, take first 2 letters
                                                    $initials = strtoupper(substr($person->name, 0, 2));
                                                }
                                            @endphp
                                            <div class="personnel-image bg-primary d-flex align-items-center justify-content-center text-white fw-bold" style="font-size: 0.75rem;">
                                                {{ $initials }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">{{ $person->name }}</td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ ucfirst($person->role) }}</span>
                                </td>
                                <td class="text-center">{{ $person->position ?? '-' }}</td>
                                <td class="text-center">{{ $person->company ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">{{ $person->schedules->count() }} schedule(s)</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $person->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($person->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        @if($person->status === 'active')
                                            <button class="btn btn-sm btn-outline-success" title="Edit" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editPersonnelModal"
                                                    data-personnel-id="{{ $person->id }}"
                                                    data-name="{{ $person->name }}"
                                                    data-role="{{ $person->role }}"
                                                    data-position="{{ $person->position ?? '' }}"
                                                    data-company="{{ $person->company ?? '' }}"
                                                    data-image="{{ $person->image ?? '' }}"
                                                    data-schedules="{{ $person->schedules->pluck('id')->toJson() }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        @endif
                                        <button class="btn btn-sm {{ $person->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }}" 
                                                title="{{ $person->status === 'active' ? 'Deactivate' : 'Activate' }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deletePersonnelModal"
                                                data-personnel-id="{{ $person->id }}"
                                                data-name="{{ $person->name }}"
                                                data-status="{{ $person->status }}">
                                            <i class="bi {{ $person->status === 'active' ? 'bi-trash' : 'bi-arrow-counterclockwise' }}"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No personnel found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $personnel->links() }}
            </div>
        </div>
    </div>

    <!-- Create Personnel Modal -->
    <div class="modal fade" id="createPersonnelModal" tabindex="-1" aria-labelledby="createPersonnelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createPersonnelModalLabel">Create New Personnel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.events.personnel.store') }}" method="POST" id="createPersonnelForm" enctype="multipart/form-data">
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
                                <label for="create_role" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm @error('role', 'create') is-invalid @enderror" 
                                        id="create_role" 
                                        name="role" 
                                        required>
                                    <option value="">Select Role</option>
                                    <option value="host" {{ old('role') === 'host' ? 'selected' : '' }}>Host</option>
                                    <option value="moderator" {{ old('role') === 'moderator' ? 'selected' : '' }}>Moderator</option>
                                    <option value="speaker" {{ old('role') === 'speaker' ? 'selected' : '' }}>Speaker</option>
                                </select>
                                @error('role', 'create')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="create_position" class="form-label">Position</label>
                                <input type="text" 
                                       class="form-control form-control-sm @error('position', 'create') is-invalid @enderror" 
                                       id="create_position" 
                                       name="position" 
                                       value="{{ old('position') }}">
                                @error('position', 'create')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-2">
                                <label for="create_company" class="form-label">Company</label>
                                <input type="text" 
                                       class="form-control form-control-sm @error('company', 'create') is-invalid @enderror" 
                                       id="create_company" 
                                       name="company" 
                                       value="{{ old('company') }}">
                                @error('company', 'create')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            <label for="create_schedules" class="form-label">Schedules <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm @error('schedules', 'create') is-invalid @enderror" 
                                    id="create_schedules" 
                                    name="schedules[]" 
                                    multiple 
                                    required>
                                @foreach($schedules as $schedule)
                                    <option value="{{ $schedule->id }}" {{ in_array($schedule->id, old('schedules', [])) ? 'selected' : '' }}>
                                        {{ $schedule->name }} - {{ $schedule->event->name }} ({{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}@if($schedule->end_time) - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}@endif)
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Hold Ctrl (or Cmd on Mac) to select multiple schedules</small>
                            @error('schedules', 'create')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('schedules.*', 'create')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                    <div class="modal-footer" style="padding: 0.75rem 1rem;">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-success">Create Personnel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Personnel Modal -->
    <div class="modal fade" id="editPersonnelModal" tabindex="-1" aria-labelledby="editPersonnelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPersonnelModalLabel">Edit Personnel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editPersonnelForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
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
                                <label for="edit_role" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm @error('role', 'edit') is-invalid @enderror" 
                                        id="edit_role" 
                                        name="role" 
                                        required>
                                    <option value="">Select Role</option>
                                    <option value="host" {{ old('role') === 'host' ? 'selected' : '' }}>Host</option>
                                    <option value="moderator" {{ old('role') === 'moderator' ? 'selected' : '' }}>Moderator</option>
                                    <option value="speaker" {{ old('role') === 'speaker' ? 'selected' : '' }}>Speaker</option>
                                </select>
                                @error('role', 'edit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="edit_position" class="form-label">Position</label>
                                <input type="text" 
                                       class="form-control form-control-sm @error('position', 'edit') is-invalid @enderror" 
                                       id="edit_position" 
                                       name="position" 
                                       value="{{ old('position') }}">
                                @error('position', 'edit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-2">
                                <label for="edit_company" class="form-label">Company</label>
                                <input type="text" 
                                       class="form-control form-control-sm @error('company', 'edit') is-invalid @enderror" 
                                       id="edit_company" 
                                       name="company" 
                                       value="{{ old('company') }}">
                                @error('company', 'edit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            <label for="edit_schedules" class="form-label">Schedules <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm @error('schedules', 'edit') is-invalid @enderror" 
                                    id="edit_schedules" 
                                    name="schedules[]" 
                                    multiple 
                                    required>
                                @foreach($schedules as $schedule)
                                    <option value="{{ $schedule->id }}" {{ in_array($schedule->id, old('schedules', [])) ? 'selected' : '' }}>
                                        {{ $schedule->name }} - {{ $schedule->event->name }} ({{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}@if($schedule->end_time) - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}@endif)
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Hold Ctrl (or Cmd on Mac) to select multiple schedules</small>
                            @error('schedules', 'edit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('schedules.*', 'edit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                    <div class="modal-footer" style="padding: 0.75rem 1rem;">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-success">Update Personnel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Personnel Modal -->
    <div class="modal fade" id="deletePersonnelModal" tabindex="-1" aria-labelledby="deletePersonnelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deletePersonnelModalLabel">Update Personnel Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="deletePersonnelForm" method="POST">
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
            const roleSelect = document.getElementById('role');
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
            
            // Auto-submit on role change
            if (roleSelect) {
                roleSelect.addEventListener('change', function() {
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
                const createModal = new bootstrap.Modal(document.getElementById('createPersonnelModal'));
                createModal.show();
            @endif

            @if($errors->getBag('edit')->any())
                const editModal = new bootstrap.Modal(document.getElementById('editPersonnelModal'));
                editModal.show();
            @endif
        });

        // Handle Edit Modal
        document.addEventListener('DOMContentLoaded', function() {
            const editModal = document.getElementById('editPersonnelModal');
            if (editModal) {
                editModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const personnelId = button.getAttribute('data-personnel-id');
                    const name = button.getAttribute('data-name');
                    const role = button.getAttribute('data-role');
                    const position = button.getAttribute('data-position');
                    const company = button.getAttribute('data-company');
                    const image = button.getAttribute('data-image');
                    const schedulesJson = button.getAttribute('data-schedules');

                    const form = document.getElementById('editPersonnelForm');
                    form.action = '{{ route("admin.events.personnel.update", ":id") }}'.replace(':id', personnelId);

                    document.getElementById('edit_name').value = name || '';
                    document.getElementById('edit_role').value = role || '';
                    document.getElementById('edit_position').value = position || '';
                    document.getElementById('edit_company').value = company || '';
                    
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
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeEditPersonnelImage('${escapedPath}')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        `;
                    } else {
                        imageContainer.innerHTML = '<p class="text-muted small">No image</p>';
                    }
                    
                    // Set selected schedules
                    let selectedSchedules = [];
                    try {
                        selectedSchedules = JSON.parse(schedulesJson || '[]');
                    } catch (e) {
                        selectedSchedules = [];
                    }
                    
                    const schedulesSelect = document.getElementById('edit_schedules');
                    if (schedulesSelect) {
                        Array.from(schedulesSelect.options).forEach(option => {
                            option.selected = selectedSchedules.includes(parseInt(option.value));
                        });
                    }
                });
            }

            // Handle Delete Modal
            const deleteModal = document.getElementById('deletePersonnelModal');
            if (deleteModal) {
                // Clear modal content when hidden
                deleteModal.addEventListener('hidden.bs.modal', function() {
                    const deleteMessage = document.getElementById('delete_message');
                    if (deleteMessage) deleteMessage.innerHTML = '';
                });

                deleteModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const personnelId = button.getAttribute('data-personnel-id');
                    const name = button.getAttribute('data-name');
                    const currentStatus = button.getAttribute('data-status');

                    const form = document.getElementById('deletePersonnelForm');
                    form.action = '{{ route("admin.events.personnel.destroy", ":id") }}'.replace(':id', personnelId);

                    const modalTitle = document.getElementById('deletePersonnelModalLabel');
                    const deleteMessage = document.getElementById('delete_message');
                    const submitBtn = document.getElementById('delete_submit_btn');
                    
                    if (!modalTitle || !deleteMessage || !submitBtn) {
                        console.error('Modal elements not found');
                        return;
                    }
                    
                    // Check if personnel is active (normalize for comparison)
                    const isActive = currentStatus && currentStatus.toLowerCase().trim() === 'active';
                    
                    // Update all modal content with fresh data
                    if (isActive) {
                        modalTitle.textContent = 'Deactivate Personnel';
                        deleteMessage.innerHTML = 'Are you sure you want to <strong>deactivate</strong> personnel <strong>' + name + '</strong>?';
                        submitBtn.textContent = 'Deactivate Personnel';
                        submitBtn.className = 'btn btn-danger';
                    } else {
                        modalTitle.textContent = 'Activate Personnel';
                        deleteMessage.innerHTML = 'Are you sure you want to <strong>activate</strong> personnel <strong>' + name + '</strong>?';
                        submitBtn.textContent = 'Activate Personnel';
                        submitBtn.className = 'btn btn-success';
                    }
                });
            }
        });
        
        // Function to remove image from edit personnel modal
        window.removeEditPersonnelImage = function(imagePath) {
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
