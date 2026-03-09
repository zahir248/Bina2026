@extends('layouts.admin.app')

@section('title', 'Settings')
@section('page-title', 'Settings')

@section('content')
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">Notification settings</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.settings.update') }}" class="mb-0">
                @csrf
                @method('PUT')
                <input type="hidden" name="maintenance_mode" value="{{ old('maintenance_mode', ($maintenanceMode ?? false) ? '1' : '0') }}">
                <div class="mb-4">
                    <label for="admin_notification_email" class="form-label" style="font-size: 0.875rem; font-weight: 500;">Email for admin notifications</label>
                    <input type="email"
                           name="admin_notification_email"
                           id="admin_notification_email"
                           class="form-control form-control-sm @error('admin_notification_email') is-invalid @enderror"
                           value="{{ old('admin_notification_email', $adminNotificationEmail) }}"
                           placeholder="e.g. admin@example.com"
                           maxlength="255"
                           autocomplete="email">
                    <div class="form-text">Refund requests and other alerts will be sent to this email. Leave empty to disable.</div>
                    @error('admin_notification_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn-admin btn-admin-primary">
                    <i class="bi bi-check-lg"></i>
                    Save settings
                </button>
            </form>
        </div>
    </div>

    <div class="admin-card mt-4">
        <div class="card-header">
            <h3 class="card-title">Maintenance mode</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.settings.update') }}" class="mb-0">
                @csrf
                @method('PUT')
                <input type="hidden" name="admin_notification_email" value="{{ old('admin_notification_email', $adminNotificationEmail) }}">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="maintenance_mode" id="maintenance_mode" value="1"
                           {{ old('maintenance_mode', $maintenanceMode ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="maintenance_mode">Enable maintenance mode</label>
                </div>
                <div class="form-text mb-3">When enabled, only admins can access the site. Other visitors will see an “Under maintenance” page. You can still log in and turn this off from Settings.</div>
                <button type="submit" class="btn-admin btn-admin-primary">
                    <i class="bi bi-check-lg"></i>
                    Save settings
                </button>
            </form>
        </div>
    </div>
@endsection
