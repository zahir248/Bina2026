@extends('layouts.admin.app')

@section('title', 'Users')
@section('page-title', 'Users')

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
    
    /* Filter Section */
    .form-label {
        color: #475569;
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    
    .form-control-sm, .form-select-sm {
        font-size: 0.875rem;
    }
    
    @media (max-width: 768px) {
        .row.g-3 > div {
            margin-bottom: 1rem;
        }
    }
</style>
@endpush

@section('content')
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">All Users</h3>
            <button class="btn-admin btn-admin-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="bi bi-plus-circle"></i>
                Add User
            </button>
        </div>
        <div class="card-body">
            <!-- Filter Section -->
            <form method="GET" action="{{ route('admin.users') }}" class="mb-4" id="filterForm">
                <div class="row g-3 align-items-end">
                    <!-- Search Input -->
                    <div class="col-md-4">
                        <label for="search" class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Search</label>
                        <input type="text" 
                               class="form-control form-control-sm" 
                               id="search" 
                               name="search" 
                               placeholder="Search by username, name, or email..." 
                               value="{{ $search ?? '' }}"
                               autocomplete="off">
                    </div>
                    
                    <!-- Role Filter -->
                    <div class="col-md-4">
                        <label for="role" class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Role</label>
                        <select class="form-select form-select-sm" id="role" name="role">
                            <option value="">All Roles</option>
                            <option value="admin" {{ ($roleFilter ?? '') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="client" {{ ($roleFilter ?? '') === 'client' ? 'selected' : '' }}>Client</option>
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
                            <th class="text-center">Username</th>
                            <th class="text-center">Name</th>
                            <th class="text-center">Email</th>
                            <th class="text-center">Role</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Last Login</th>
                            <th class="text-center">Created At</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td class="text-center">{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                                <td class="text-center">{{ $user->username }}</td>
                                <td class="text-center">{{ $user->name ?? '-' }}</td>
                                <td class="text-center">{{ $user->email }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $user->role === 'admin' ? 'bg-primary' : 'bg-secondary' }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $user->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($user->status ?? 'active') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($user->last_login_at)
                                        {{ $user->last_login_at->diffForHumans() }}
                                    @else
                                        <span class="text-muted">Never</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $user->created_at->format('M d, Y') }}</td>
                                <td class="text-center">
                                    @if($user->id === auth()->id())
                                        <span class="text-muted">-</span>
                                    @else
                                        <div class="d-flex gap-2 justify-content-center">
                                            @if($user->status === 'active')
                                                <button class="btn btn-sm btn-outline-success" title="Edit" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editUserModal"
                                                        data-user-id="{{ $user->id }}"
                                                        data-username="{{ $user->username }}"
                                                        data-name="{{ $user->name }}"
                                                        data-email="{{ $user->email }}"
                                                        data-role="{{ $user->role }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            @endif
                                            <button class="btn btn-sm {{ $user->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }}" 
                                                    title="{{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteUserModal"
                                                    data-user-id="{{ $user->id }}"
                                                    data-username="{{ $user->username }}"
                                                    data-status="{{ $user->status }}">
                                                <i class="bi {{ $user->status === 'active' ? 'bi-trash' : 'bi-arrow-counterclockwise' }}"></i>
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Create User Modal -->
    <div class="modal fade {{ $errors->getBag('create')->any() ? 'show' : '' }}" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true" style="{{ $errors->getBag('create')->any() ? 'display: block;' : '' }}">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createUserModalLabel">Create New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    <div class="modal-body" style="padding: 1rem;">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="create_username" class="form-label" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm {{ $errors->getBag('create')->has('username') ? 'is-invalid' : '' }}" 
                                           id="create_username" name="username" value="{{ old('username') }}" required>
                                    @if($errors->getBag('create')->has('username'))
                                        <div class="invalid-feedback" style="font-size: 0.75rem;">{{ $errors->getBag('create')->first('username') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="create_name" class="form-label" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Name</label>
                                    <input type="text" class="form-control form-control-sm {{ $errors->getBag('create')->has('name') ? 'is-invalid' : '' }}" 
                                           id="create_name" name="name" value="{{ old('name') }}">
                                    @if($errors->getBag('create')->has('name'))
                                        <div class="invalid-feedback" style="font-size: 0.75rem;">{{ $errors->getBag('create')->first('name') }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label for="create_email" class="form-label" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control form-control-sm {{ $errors->getBag('create')->has('email') ? 'is-invalid' : '' }}" 
                                   id="create_email" name="email" value="{{ old('email') }}" required>
                            @if($errors->getBag('create')->has('email'))
                                <div class="invalid-feedback" style="font-size: 0.75rem;">{{ $errors->getBag('create')->first('email') }}</div>
                            @endif
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="create_password" class="form-label" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control form-control-sm {{ $errors->getBag('create')->has('password') ? 'is-invalid' : '' }}" 
                                           id="create_password" name="password" required>
                                    @if($errors->getBag('create')->has('password'))
                                        <div class="invalid-feedback" style="font-size: 0.75rem;">{{ $errors->getBag('create')->first('password') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="create_password_confirmation" class="form-label" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control form-control-sm {{ $errors->getBag('create')->has('password') ? 'is-invalid' : '' }}" 
                                           id="create_password_confirmation" name="password_confirmation" required>
                                    @if($errors->getBag('create')->has('password'))
                                        <div class="invalid-feedback" style="font-size: 0.75rem;">{{ $errors->getBag('create')->first('password') }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted" style="font-size: 0.75rem;">Password must be at least 6 characters and contain at least one letter and one number.</small>
                        </div>
                        <div class="mb-2">
                            <label for="create_role" class="form-label" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Role <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm {{ $errors->getBag('create')->has('role') ? 'is-invalid' : '' }}" 
                                    id="create_role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="client" {{ old('role') === 'client' ? 'selected' : '' }}>Client</option>
                            </select>
                            @if($errors->getBag('create')->has('role'))
                                <div class="invalid-feedback" style="font-size: 0.75rem;">{{ $errors->getBag('create')->first('role') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer" style="padding: 0.75rem 1rem;">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-success">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade {{ $errors->getBag('edit')->any() ? 'show' : '' }}" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true" style="{{ $errors->getBag('edit')->any() ? 'display: block;' : '' }}">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editUserForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body" style="padding: 1rem;">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="edit_username" class="form-label" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm {{ $errors->getBag('edit')->has('username') ? 'is-invalid' : '' }}" 
                                           id="edit_username" name="username" value="{{ old('username') }}" required>
                                    @if($errors->getBag('edit')->has('username'))
                                        <div class="invalid-feedback" style="font-size: 0.75rem;">{{ $errors->getBag('edit')->first('username') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="edit_name" class="form-label" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Name</label>
                                    <input type="text" class="form-control form-control-sm {{ $errors->getBag('edit')->has('name') ? 'is-invalid' : '' }}" 
                                           id="edit_name" name="name" value="{{ old('name') }}">
                                    @if($errors->getBag('edit')->has('name'))
                                        <div class="invalid-feedback" style="font-size: 0.75rem;">{{ $errors->getBag('edit')->first('name') }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label for="edit_email" class="form-label" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control form-control-sm {{ $errors->getBag('edit')->has('email') ? 'is-invalid' : '' }}" 
                                   id="edit_email" name="email" value="{{ old('email') }}" required>
                            @if($errors->getBag('edit')->has('email'))
                                <div class="invalid-feedback" style="font-size: 0.75rem;">{{ $errors->getBag('edit')->first('email') }}</div>
                            @endif
                        </div>
                        <div class="mb-2">
                            <label for="edit_password" class="form-label" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Password <small class="text-muted" style="font-size: 0.75rem;">(Leave blank to keep current password)</small></label>
                            <input type="password" class="form-control form-control-sm {{ $errors->getBag('edit')->has('password') ? 'is-invalid' : '' }}" 
                                   id="edit_password" name="password">
                            @if($errors->getBag('edit')->has('password'))
                                <div class="invalid-feedback" style="font-size: 0.75rem;">{{ $errors->getBag('edit')->first('password') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer" style="padding: 0.75rem 1rem;">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-success">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete User Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel">Update User Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="deleteUserForm" method="POST">
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
                    }, 500); // Wait 500ms after user stops typing
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
                const createModal = new bootstrap.Modal(document.getElementById('createUserModal'));
                createModal.show();
            @endif

            @if($errors->getBag('edit')->any())
                const editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
                editModal.show();
            @endif
        });

        // Handle Edit Modal
        document.addEventListener('DOMContentLoaded', function() {
            const editModal = document.getElementById('editUserModal');
            if (editModal) {
                editModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const userId = button.getAttribute('data-user-id');
                    const username = button.getAttribute('data-username');
                    const name = button.getAttribute('data-name');
                    const email = button.getAttribute('data-email');

                    const form = document.getElementById('editUserForm');
                    form.action = '{{ route("admin.users.update", ":id") }}'.replace(':id', userId);

                    document.getElementById('edit_username').value = username;
                    document.getElementById('edit_name').value = name || '';
                    document.getElementById('edit_email').value = email;
                    document.getElementById('edit_password').value = '';
                });
            }

            // Handle Delete Modal
            const deleteModal = document.getElementById('deleteUserModal');
            if (deleteModal) {
                // Clear modal content when hidden
                deleteModal.addEventListener('hidden.bs.modal', function() {
                    const deleteMessage = document.getElementById('delete_message');
                    if (deleteMessage) deleteMessage.innerHTML = '';
                });

                deleteModal.addEventListener('show.bs.modal', function(event) {
                    // Get the button that triggered the modal
                    const button = event.relatedTarget;
                    if (!button) return;
                    
                    const userId = button.getAttribute('data-user-id');
                    const username = button.getAttribute('data-username');
                    const currentStatus = button.getAttribute('data-status');

                    if (!userId || !username) {
                        console.error('Missing user data:', { userId, username, currentStatus });
                        return;
                    }

                    const form = document.getElementById('deleteUserForm');
                    if (form) {
                        form.action = '{{ route("admin.users.destroy", ":id") }}'.replace(':id', userId);
                    }

                    const modalTitle = document.getElementById('deleteUserModalLabel');
                    const deleteMessage = document.getElementById('delete_message');
                    const submitBtn = document.getElementById('delete_submit_btn');
                    
                    if (!modalTitle || !deleteMessage || !submitBtn) {
                        console.error('Modal elements not found');
                        return;
                    }
                    
                    // Check if user is active (normalize for comparison)
                    const isActive = currentStatus && currentStatus.toLowerCase().trim() === 'active';
                    const action = isActive ? 'deactivate' : 'activate';
                    
                    // Update all modal content with fresh data
                    if (isActive) {
                        modalTitle.textContent = 'Deactivate User';
                        deleteMessage.innerHTML = 'Are you sure you want to <strong>deactivate</strong> user <strong>' + username + '</strong>?';
                        submitBtn.textContent = 'Deactivate User';
                        submitBtn.className = 'btn btn-danger';
                    } else {
                        modalTitle.textContent = 'Activate User';
                        deleteMessage.innerHTML = 'Are you sure you want to <strong>activate</strong> user <strong>' + username + '</strong>?';
                        submitBtn.textContent = 'Activate User';
                        submitBtn.className = 'btn btn-success';
                    }
                });
            }
        });
    </script>
    @endpush
@endsection
