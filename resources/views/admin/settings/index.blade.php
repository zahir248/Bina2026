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
                <input type="hidden" name="countdown_enabled" value="{{ old('countdown_enabled', ($countdownEnabled ?? true) ? '1' : '0') }}">
                <input type="hidden" name="countdown_target_datetime" value="{{ old('countdown_target_datetime', $countdownTargetDatetime ?? '') }}">
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
                <input type="hidden" name="countdown_enabled" value="{{ old('countdown_enabled', ($countdownEnabled ?? true) ? '1' : '0') }}">
                <input type="hidden" name="countdown_target_datetime" value="{{ old('countdown_target_datetime', $countdownTargetDatetime ?? '') }}">
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

    <div class="admin-card mt-4">
        <div class="card-header">
            <h3 class="card-title">Homepage countdown</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.settings.update') }}" class="mb-0">
                @csrf
                @method('PUT')
                <input type="hidden" name="admin_notification_email" value="{{ old('admin_notification_email', $adminNotificationEmail) }}">
                <input type="hidden" name="maintenance_mode" value="{{ old('maintenance_mode', ($maintenanceMode ?? false) ? '1' : '0') }}">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="countdown_enabled" id="countdown_enabled" value="1"
                           {{ old('countdown_enabled', $countdownEnabled ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="countdown_enabled">Show countdown on homepage</label>
                </div>
                <div class="mb-3">
                    <label for="countdown_target_datetime" class="form-label" style="font-size: 0.875rem; font-weight: 500;">Countdown target date & time</label>
                    <input type="datetime-local"
                           name="countdown_target_datetime"
                           id="countdown_target_datetime"
                           class="form-control form-control-sm @error('countdown_target_datetime') is-invalid @enderror"
                           value="{{ old('countdown_target_datetime', $countdownTargetDatetime ?? '') }}">
                    <div class="form-text">This uses the browser/server local time. Example: 2026-10-28 09:00.</div>
                    @error('countdown_target_datetime')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-text mb-3">When disabled, the countdown section will be hidden on the homepage.</div>
                <button type="submit" class="btn-admin btn-admin-primary">
                    <i class="bi bi-check-lg"></i>
                    Save settings
                </button>
            </form>
        </div>
    </div>

    <div class="admin-card mt-4">
        <div class="card-header">
            <h3 class="card-title">Ticket Scanner</h3>
        </div>
        <div class="card-body">
            <p class="mb-2" style="font-size: 0.875rem;">Use this URL on a phone or tablet to scan participant QR codes. No login required.</p>
            <div class="input-group input-group-sm mb-3" style="max-width: 100%;">
                <input type="text"
                       class="form-control font-monospace"
                       value="{{ url(route('admin.scanner')) }}"
                       id="scanner_url"
                       readonly
                       style="font-size: 0.8125rem;">
                <button type="button" class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText(document.getElementById('scanner_url').value); this.textContent='Copied!'; setTimeout(function(){ this.textContent='Copy'; }.bind(this), 1500);" title="Copy URL">
                    Copy
                </button>
            </div>
            <a href="{{ route('admin.scanner') }}" target="_blank" rel="noopener noreferrer" class="btn-admin btn-admin-primary">
                <i class="bi bi-qr-code-scan"></i>
                Open Scanner Page
            </a>
        </div>
    </div>
@endsection
