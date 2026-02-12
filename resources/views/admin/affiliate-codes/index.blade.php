@extends('layouts.admin.app')

@section('title', 'Affiliate Codes')

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
            <h3 class="card-title">Affiliate Codes</h3>
            <button class="btn-admin btn-admin-primary" data-bs-toggle="modal" data-bs-target="#createAffiliateCodeModal">
                <i class="bi bi-plus-circle"></i>
                Add Affiliate Code
            </button>
        </div>
        <div class="card-body">
            <!-- Filter Section -->
            <form method="GET" action="{{ route('admin.affiliate-codes') }}" class="mb-4" id="filterForm">
                <div class="row g-3 align-items-end">
                    <!-- Search Input -->
                    <div class="col-md-6">
                        <label for="search" class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Search</label>
                        <input type="text" 
                               class="form-control form-control-sm" 
                               id="search" 
                               name="search" 
                               placeholder="Search by name, description, code, or link..." 
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
                            <th class="text-center">Code</th>
                            <th class="text-center">Link</th>
                            <th class="text-center">Total Conversion</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Created At</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($affiliateCodes as $affiliateCode)
                            <tr>
                                <td class="text-center">{{ $loop->iteration + ($affiliateCodes->currentPage() - 1) * $affiliateCodes->perPage() }}</td>
                                <td class="text-center">{{ $affiliateCode->name }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $affiliateCode->code }}</span>
                                </td>
                                <td class="text-center">
                                    @if($affiliateCode->link)
                                        <a href="{{ $affiliateCode->link }}" target="_blank" class="text-decoration-none">
                                            <i class="bi bi-link-45deg"></i> View Link
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{ $affiliateCode->total_conversion ?? 0 }}
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $affiliateCode->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($affiliateCode->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    {{ $affiliateCode->created_at->format('M d, Y') }}
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        @if($affiliateCode->status === 'active')
                                            <button class="btn btn-sm btn-outline-success" title="Edit" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editAffiliateCodeModal"
                                                    data-affiliate-code-id="{{ $affiliateCode->id }}"
                                                    data-name="{{ $affiliateCode->name }}"
                                                    data-description="{{ $affiliateCode->description ?? '' }}"
                                                    data-code="{{ $affiliateCode->code }}"
                                                    data-link="{{ $affiliateCode->link ?? '' }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        @endif
                                        <button class="btn btn-sm {{ $affiliateCode->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }}" 
                                                title="{{ $affiliateCode->status === 'active' ? 'Deactivate' : 'Activate' }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteAffiliateCodeModal"
                                                data-affiliate-code-id="{{ $affiliateCode->id }}"
                                                data-name="{{ $affiliateCode->name }}"
                                                data-status="{{ $affiliateCode->status }}">
                                            <i class="bi {{ $affiliateCode->status === 'active' ? 'bi-trash' : 'bi-arrow-counterclockwise' }}"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No affiliate codes found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $affiliateCodes->links() }}
            </div>
        </div>
    </div>

    <!-- Create Affiliate Code Modal -->
    <div class="modal fade" id="createAffiliateCodeModal" tabindex="-1" aria-labelledby="createAffiliateCodeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createAffiliateCodeModalLabel">Create New Affiliate Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.affiliate-codes.store') }}" method="POST" id="createAffiliateCodeForm">
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
                        
                        <div class="mb-2">
                            <label for="create_link" class="form-label">Link</label>
                            <input type="url" 
                                   class="form-control form-control-sm @error('link', 'create') is-invalid @enderror" 
                                   id="create_link" 
                                   name="link" 
                                   value="{{ old('link') }}" 
                                   placeholder="https://example.com">
                            @error('link', 'create')
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
                        <button type="submit" class="btn btn-sm btn-success">Create Affiliate Code</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Affiliate Code Modal -->
    <div class="modal fade" id="editAffiliateCodeModal" tabindex="-1" aria-labelledby="editAffiliateCodeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAffiliateCodeModalLabel">Edit Affiliate Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editAffiliateCodeForm" method="POST">
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
                        
                        <div class="mb-2">
                            <label for="edit_link" class="form-label">Link</label>
                            <input type="url" 
                                   class="form-control form-control-sm @error('link', 'edit') is-invalid @enderror" 
                                   id="edit_link" 
                                   name="link" 
                                   value="{{ old('link') }}" 
                                   placeholder="https://example.com">
                            @error('link', 'edit')
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
                        <button type="submit" class="btn btn-sm btn-success">Update Affiliate Code</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Affiliate Code Modal -->
    <div class="modal fade" id="deleteAffiliateCodeModal" tabindex="-1" aria-labelledby="deleteAffiliateCodeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAffiliateCodeModalLabel">Update Affiliate Code Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="deleteAffiliateCodeForm" method="POST">
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
                const createModal = new bootstrap.Modal(document.getElementById('createAffiliateCodeModal'));
                createModal.show();
            @endif

            @if($errors->getBag('edit')->any())
                const editModal = new bootstrap.Modal(document.getElementById('editAffiliateCodeModal'));
                editModal.show();
            @endif
        });

        // Handle Edit Modal
        document.addEventListener('DOMContentLoaded', function() {
            const editModal = document.getElementById('editAffiliateCodeModal');
            if (editModal) {
                editModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const affiliateCodeId = button.getAttribute('data-affiliate-code-id');
                    const name = button.getAttribute('data-name');
                    const description = button.getAttribute('data-description');
                    const code = button.getAttribute('data-code');
                    const link = button.getAttribute('data-link');

                    const form = document.getElementById('editAffiliateCodeForm');
                    form.action = '{{ route("admin.affiliate-codes.update", ":id") }}'.replace(':id', affiliateCodeId);

                    document.getElementById('edit_name').value = name || '';
                    document.getElementById('edit_description').value = description || '';
                    document.getElementById('edit_code').value = code || '';
                    document.getElementById('edit_link').value = link || '';
                });
            }

            // Handle Delete Modal
            const deleteModal = document.getElementById('deleteAffiliateCodeModal');
            if (deleteModal) {
                // Clear modal content when hidden
                deleteModal.addEventListener('hidden.bs.modal', function() {
                    const deleteMessage = document.getElementById('delete_message');
                    if (deleteMessage) deleteMessage.innerHTML = '';
                });

                deleteModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const affiliateCodeId = button.getAttribute('data-affiliate-code-id');
                    const name = button.getAttribute('data-name');
                    const currentStatus = button.getAttribute('data-status');

                    const form = document.getElementById('deleteAffiliateCodeForm');
                    form.action = '{{ route("admin.affiliate-codes.destroy", ":id") }}'.replace(':id', affiliateCodeId);

                    const modalTitle = document.getElementById('deleteAffiliateCodeModalLabel');
                    const deleteMessage = document.getElementById('delete_message');
                    const submitBtn = document.getElementById('delete_submit_btn');
                    
                    if (!modalTitle || !deleteMessage || !submitBtn) {
                        console.error('Modal elements not found');
                        return;
                    }
                    
                    // Check if affiliate code is active (normalize for comparison)
                    const isActive = currentStatus && currentStatus.toLowerCase().trim() === 'active';
                    
                    // Update all modal content with fresh data
                    if (isActive) {
                        modalTitle.textContent = 'Deactivate Affiliate Code';
                        deleteMessage.innerHTML = 'Are you sure you want to <strong>deactivate</strong> affiliate code <strong>' + name + '</strong>?';
                        submitBtn.textContent = 'Deactivate Affiliate Code';
                        submitBtn.className = 'btn btn-danger';
                    } else {
                        modalTitle.textContent = 'Activate Affiliate Code';
                        deleteMessage.innerHTML = 'Are you sure you want to <strong>activate</strong> affiliate code <strong>' + name + '</strong>?';
                        submitBtn.textContent = 'Activate Affiliate Code';
                        submitBtn.className = 'btn btn-success';
                    }
                });
            }
        });
    </script>
    @endpush
@endsection
