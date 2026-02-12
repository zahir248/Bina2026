@extends('layouts.admin.app')

@section('title', 'Event Categories')

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
            <h3 class="card-title">Event Categories</h3>
            <button class="btn-admin btn-admin-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                <i class="bi bi-plus-circle"></i>
                Add Category
            </button>
        </div>
        <div class="card-body">
            <!-- Filter Section -->
            <form method="GET" action="{{ route('admin.events.categories') }}" class="mb-4" id="filterForm">
                <div class="row g-3 align-items-end">
                    <!-- Search Input -->
                    <div class="col-md-6">
                        <label for="search" class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Search</label>
                        <input type="text" 
                               class="form-control form-control-sm" 
                               id="search" 
                               name="search" 
                               placeholder="Search by name or description..." 
                               value="{{ $search ?? '' }}"
                               autocomplete="off">
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="col-md-6">
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
                            <th class="text-center">Description</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Created At</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td class="text-center">{{ $loop->iteration + ($categories->currentPage() - 1) * $categories->perPage() }}</td>
                                <td class="text-center">{{ $category->name }}</td>
                                <td class="text-center">{{ $category->description ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $category->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($category->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    {{ $category->created_at->format('M d, Y') }}
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        @if($category->status === 'active')
                                            <button class="btn btn-sm btn-outline-success" title="Edit" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editCategoryModal"
                                                    data-category-id="{{ $category->id }}"
                                                    data-name="{{ $category->name }}"
                                                    data-description="{{ $category->description ?? '' }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        @endif
                                        <button class="btn btn-sm {{ $category->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }}" 
                                                title="{{ $category->status === 'active' ? 'Deactivate' : 'Activate' }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteCategoryModal"
                                                data-category-id="{{ $category->id }}"
                                                data-name="{{ $category->name }}"
                                                data-status="{{ $category->status }}">
                                            <i class="bi {{ $category->status === 'active' ? 'bi-trash' : 'bi-arrow-counterclockwise' }}"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No categories found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $categories->links() }}
            </div>
        </div>
    </div>

    <!-- Create Category Modal -->
    <div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createCategoryModalLabel">Create New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.events.categories.store') }}" method="POST" id="createCategoryForm">
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
                        <button type="submit" class="btn btn-sm btn-success">Create Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editCategoryForm" method="POST">
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
                        <button type="submit" class="btn btn-sm btn-success">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Category Modal -->
    <div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteCategoryModalLabel">Update Category Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="deleteCategoryForm" method="POST">
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
                const createModal = new bootstrap.Modal(document.getElementById('createCategoryModal'));
                createModal.show();
            @endif

            @if($errors->getBag('edit')->any())
                const editModal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
                editModal.show();
            @endif
        });

        // Handle Edit Modal
        document.addEventListener('DOMContentLoaded', function() {
            const editModal = document.getElementById('editCategoryModal');
            if (editModal) {
                editModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const categoryId = button.getAttribute('data-category-id');
                    const name = button.getAttribute('data-name');
                    const description = button.getAttribute('data-description');

                    const form = document.getElementById('editCategoryForm');
                    form.action = '{{ route("admin.events.categories.update", ":id") }}'.replace(':id', categoryId);

                    document.getElementById('edit_name').value = name;
                    document.getElementById('edit_description').value = description || '';
                });
            }

            // Handle Delete Modal
            const deleteModal = document.getElementById('deleteCategoryModal');
            if (deleteModal) {
                // Clear modal content when hidden
                deleteModal.addEventListener('hidden.bs.modal', function() {
                    const deleteMessage = document.getElementById('delete_message');
                    if (deleteMessage) deleteMessage.innerHTML = '';
                });

                deleteModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const categoryId = button.getAttribute('data-category-id');
                    const name = button.getAttribute('data-name');
                    const currentStatus = button.getAttribute('data-status');

                    const form = document.getElementById('deleteCategoryForm');
                    form.action = '{{ route("admin.events.categories.destroy", ":id") }}'.replace(':id', categoryId);

                    const modalTitle = document.getElementById('deleteCategoryModalLabel');
                    const deleteMessage = document.getElementById('delete_message');
                    const submitBtn = document.getElementById('delete_submit_btn');
                    
                    if (!modalTitle || !deleteMessage || !submitBtn) {
                        console.error('Modal elements not found');
                        return;
                    }
                    
                    // Check if category is active (normalize for comparison)
                    const isActive = currentStatus && currentStatus.toLowerCase().trim() === 'active';
                    
                    // Update all modal content with fresh data
                    if (isActive) {
                        modalTitle.textContent = 'Deactivate Category';
                        deleteMessage.innerHTML = 'Are you sure you want to <strong>deactivate</strong> category <strong>' + name + '</strong>?';
                        submitBtn.textContent = 'Deactivate Category';
                        submitBtn.className = 'btn btn-danger';
                    } else {
                        modalTitle.textContent = 'Activate Category';
                        deleteMessage.innerHTML = 'Are you sure you want to <strong>activate</strong> category <strong>' + name + '</strong>?';
                        submitBtn.textContent = 'Activate Category';
                        submitBtn.className = 'btn btn-success';
                    }
                });
            }
        });
    </script>
    @endpush
@endsection
