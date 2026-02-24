@extends('layouts.client.app')

@section('title', 'Profile - BINA')

@section('content')
<div class="profile-page-container">
    <div class="container">
        <div class="profile-page-content-wrapper">
            <div class="profile-page-header">
                <h1 class="profile-page-title">My Profile</h1>
                <p class="profile-page-subtitle">Update your personal and address details.</p>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="profile-form">
                @csrf
                @method('PUT')
                <div class="profile-section-card">
                    <div class="profile-section-header">
                        <h2 class="profile-section-title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" fill="currentColor"/>
                            </svg>
                            <span>Personal &amp; Address Details</span>
                        </h2>
                    </div>
                    <div class="profile-section-body">
                        <div class="profile-form-row profile-form-row-full">
                            <div class="profile-form-group">
                                <label for="name" class="profile-form-label">Full Name <span class="profile-label-required">*</span></label>
                                <input type="text" id="name" name="name" class="profile-form-input" value="{{ old('name', $user->name ?? '') }}">
                                @error('name')
                                    <span class="profile-form-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="profile-form-row">
                            <div class="profile-form-group">
                                <label for="username" class="profile-form-label">Username <span class="profile-label-optional">(optional)</span></label>
                                <input type="text" id="username" name="username" class="profile-form-input" value="{{ old('username', $user->username ?? '') }}" autocomplete="username">
                                @error('username')
                                    <span class="profile-form-error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="profile-form-group">
                                <label for="gender" class="profile-form-label">Gender <span class="profile-label-optional">(optional)</span></label>
                                <select id="gender" name="gender" class="profile-form-input">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender', $user->gender ?? '') == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender', $user->gender ?? '') == 'female' ? 'selected' : '' }}>Female</option>
                                </select>
                                @error('gender')
                                    <span class="profile-form-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="profile-form-row">
                            <div class="profile-form-group">
                                <label for="nric_passport" class="profile-form-label">NRIC/Passport Number <span class="profile-label-optional">(optional)</span></label>
                                <input type="text" id="nric_passport" name="nric_passport" class="profile-form-input" value="{{ old('nric_passport', $user->nric_passport ?? '') }}">
                                @error('nric_passport')
                                    <span class="profile-form-error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="profile-form-group">
                                <label for="contact_number" class="profile-form-label">Contact Number <span class="profile-label-optional">(optional)</span></label>
                                <input type="tel" id="contact_number" name="contact_number" class="profile-form-input" value="{{ old('contact_number', $user->contact_number ?? '') }}">
                                @error('contact_number')
                                    <span class="profile-form-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="profile-form-group profile-country-dropdown-wrap">
                            <label for="country_region_trigger" class="profile-form-label">Country/Region <span class="profile-label-optional">(optional)</span></label>
                            <input type="hidden" name="country_region" id="country_region" value="{{ old('country_region', $user->country_region ?? '') }}">
                            <div class="profile-country-trigger profile-form-input" id="country_region_trigger" tabindex="0" role="combobox" aria-expanded="false" aria-haspopup="listbox" aria-label="Country/Region">
                                @php
                                    $countryValue = old('country_region', $user->country_region ?? '');
                                    $countryDisplay = $countryValue && isset($countriesRegions[$countryValue]) ? $countriesRegions[$countryValue] : ($countryValue ?: 'Select Country/Region');
                                @endphp
                                <span class="profile-country-trigger-text" id="country_region_display">{{ $countryDisplay }}</span>
                                <span class="profile-country-trigger-arrow">▼</span>
                            </div>
                            <div class="profile-country-panel" id="country_region_panel" role="listbox" aria-hidden="true">
                                <div class="profile-country-search-wrap">
                                    <input type="text" class="profile-country-search" id="country_region_search" placeholder="Search country..." autocomplete="off">
                                </div>
                                <div class="profile-country-options" id="country_region_options">
                                    @foreach($countriesRegions ?? [] as $value => $label)
                                        <div class="profile-country-option{{ ($countryValue == $value ? ' profile-country-option-selected' : '') }}" data-value="{{ $value }}" data-label="{{ $label }}" role="option">{{ $label }}</div>
                                    @endforeach
                                </div>
                            </div>
                            @error('country_region')
                                <span class="profile-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="profile-form-group">
                            <label for="street_address" class="profile-form-label">Street Address <span class="profile-label-optional">(optional)</span></label>
                            <input type="text" id="street_address" name="street_address" class="profile-form-input" value="{{ old('street_address', $user->street_address ?? '') }}">
                            @error('street_address')
                                <span class="profile-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="profile-form-row profile-form-row-three">
                            <div class="profile-form-group">
                                <label for="town_city" class="profile-form-label">Town/City <span class="profile-label-optional">(optional)</span></label>
                                <input type="text" id="town_city" name="town_city" class="profile-form-input" value="{{ old('town_city', $user->town_city ?? '') }}">
                                @error('town_city')
                                    <span class="profile-form-error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="profile-form-group">
                                <label for="state" class="profile-form-label">State <span class="profile-label-optional">(optional)</span></label>
                                <input type="text" id="state" name="state" class="profile-form-input" value="{{ old('state', $user->state ?? '') }}">
                                @error('state')
                                    <span class="profile-form-error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="profile-form-group">
                                <label for="postcode_zip" class="profile-form-label">Postcode/Zip <span class="profile-label-optional">(optional)</span></label>
                                <input type="text" id="postcode_zip" name="postcode_zip" class="profile-form-input" value="{{ old('postcode_zip', $user->postcode_zip ?? '') }}">
                                @error('postcode_zip')
                                    <span class="profile-form-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="profile-actions">
                    <a href="{{ route('home') }}" class="btn-profile-cancel">Cancel</a>
                    <button type="submit" class="btn-profile-save">Save Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.profile-page,
.profile-page main {
    background: #F9FAFB;
}

.profile-page-container {
    padding: 4rem 0 3rem;
    min-height: calc(100vh - 60px);
    background: #F9FAFB;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.profile-page-content-wrapper {
    padding: 0 2.5rem 2.5rem;
    max-width: 720px;
    margin: 0 auto;
}

.profile-page-header {
    text-align: center;
    margin-bottom: 1.5rem;
    padding-top: 1rem;
}

.profile-page-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 0.5rem;
    font-family: 'Playfair Display', serif;
    line-height: 1.2;
}

.profile-page-subtitle {
    font-size: 0.9375rem;
    color: #6B7280;
    font-family: 'Inter', sans-serif;
    line-height: 1.4;
}

.profile-section-card {
    background: #fff;
    border-radius: 0.5rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #E5E7EB;
    font-family: 'Inter', sans-serif;
}

.profile-section-header {
    margin-bottom: 1.25rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #E5E7EB;
}

.profile-section-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    font-size: 1rem;
    color: var(--text-dark);
    font-family: 'Inter', sans-serif;
    margin: 0;
}

.profile-section-title svg {
    width: 20px;
    height: 20px;
    color: var(--primary-color);
}

.profile-section-body {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.profile-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.profile-form-row-full {
    grid-template-columns: 1fr;
}

.profile-form-row-three {
    grid-template-columns: 1fr 1fr 1fr;
}

.profile-form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.profile-country-dropdown-wrap {
    position: relative;
}

.profile-country-trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    user-select: none;
}

.profile-country-trigger-text {
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.profile-country-trigger-arrow {
    flex-shrink: 0;
    margin-left: 0.5rem;
    font-size: 0.65rem;
    color: #6B7280;
    transition: transform 0.2s;
}

.profile-country-dropdown-wrap.is-open .profile-country-trigger-arrow {
    transform: rotate(180deg);
}

.profile-country-panel {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    margin-top: 2px;
    max-height: 320px;
    background: #fff;
    border: 1px solid #D1D5DB;
    border-radius: 0.375rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 50;
    display: none;
    flex-direction: column;
}

.profile-country-dropdown-wrap.is-open .profile-country-panel {
    display: flex;
}

.profile-country-search-wrap {
    flex-shrink: 0;
    padding: 0.5rem;
    border-bottom: 1px solid #E5E7EB;
}

.profile-country-search {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #D1D5DB;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-family: 'Inter', sans-serif;
    color: var(--text-dark);
    background: #fff;
}

.profile-country-search:focus {
    outline: none;
    border-color: var(--primary-color);
}

.profile-country-search::placeholder {
    color: #9CA3AF;
}

.profile-country-options {
    overflow-y: auto;
    max-height: 260px;
}

.profile-country-option {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    font-family: 'Inter', sans-serif;
    color: var(--text-dark);
    cursor: pointer;
    transition: background 0.15s;
}

.profile-country-option:hover {
    background: #F3F4F6;
}

.profile-country-option-selected,
.profile-country-option.profile-country-option-selected:hover {
    background: rgba(255, 152, 0, 0.12);
    color: var(--primary-dark);
    font-weight: 500;
}

.profile-form-label {
    font-size: 0.875rem;
    color: var(--text-dark);
    font-weight: 500;
    font-family: 'Inter', sans-serif;
}

.profile-label-required {
    color: #DC2626;
    font-weight: 600;
}

.profile-label-optional {
    color: #6B7280;
    font-weight: 400;
    font-size: 0.8125rem;
}

.profile-form-input {
    width: 100%;
    padding: 0.625rem 0.75rem;
    border: 1px solid #D1D5DB;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-family: 'Inter', sans-serif;
    color: var(--text-dark);
    background: #fff;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.profile-form-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(255, 152, 0, 0.1);
}

.profile-form-input-readonly {
    background: #F3F4F6;
    color: #6B7280;
    cursor: not-allowed;
}

.profile-form-hint {
    font-size: 0.75rem;
    color: #6B7280;
    margin: 0;
    font-family: 'Inter', sans-serif;
}

.profile-form-error {
    font-size: 0.8125rem;
    color: #DC2626;
    font-family: 'Inter', sans-serif;
}

.profile-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 1rem;
    margin-top: 1.5rem;
}

.btn-profile-cancel {
    color: #6B7280;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.875rem;
    padding: 0.625rem 1rem;
    font-family: 'Inter', sans-serif;
    transition: color 0.2s;
}

.btn-profile-cancel:hover {
    color: var(--text-dark);
}

.btn-profile-save {
    padding: 0.625rem 1.75rem;
    background: var(--primary-color);
    color: #fff;
    border: none;
    border-radius: 0.375rem;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: background 0.2s, transform 0.2s;
    font-family: 'Inter', sans-serif;
    box-shadow: 0 2px 8px rgba(255, 152, 0, 0.25);
}

.btn-profile-save:hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 152, 0, 0.35);
}

@media (max-width: 768px) {
    .profile-page-container {
        padding: 2rem 0;
    }

    .profile-page-content-wrapper {
        padding: 0 1rem 1rem;
    }

    .profile-page-title {
        font-size: 1.5rem;
    }

    .profile-form-row,
    .profile-form-row-three {
        grid-template-columns: 1fr;
    }

    .profile-actions {
        flex-direction: column;
    }

    .btn-profile-save {
        width: 100%;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var trigger = document.getElementById('country_region_trigger');
    var panel = document.getElementById('country_region_panel');
    var input = document.getElementById('country_region');
    var display = document.getElementById('country_region_display');
    var wrap = trigger && trigger.closest('.profile-country-dropdown-wrap');

    function open() {
        if (wrap) wrap.classList.add('is-open');
        if (panel) panel.setAttribute('aria-hidden', 'false');
        if (trigger) trigger.setAttribute('aria-expanded', 'true');
    }
    function close() {
        if (wrap) wrap.classList.remove('is-open');
        if (panel) panel.setAttribute('aria-hidden', 'true');
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
    }

    var searchInput = document.getElementById('country_region_search');
    var optionsContainer = document.getElementById('country_region_options');

    function filterCountries() {
        var q = (searchInput && searchInput.value) ? searchInput.value.trim().toLowerCase() : '';
        var options = optionsContainer ? optionsContainer.querySelectorAll('.profile-country-option') : [];
        options.forEach(function(opt) {
            var label = (opt.getAttribute('data-label') || opt.textContent || '').toLowerCase();
            var show = !q || label.indexOf(q) !== -1;
            opt.style.display = show ? '' : 'none';
        });
    }

    if (trigger) {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            var isOpen = wrap.classList.toggle('is-open');
            panel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
            trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            if (isOpen) {
                if (searchInput) {
                    searchInput.value = '';
                    searchInput.focus();
                    filterCountries();
                }
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterCountries);
        searchInput.addEventListener('keydown', function(e) {
            e.stopPropagation();
        });
    }

    if (panel) {
        panel.querySelectorAll('.profile-country-option').forEach(function(opt) {
            opt.addEventListener('click', function(e) {
                e.preventDefault();
                var val = this.getAttribute('data-value');
                var text = this.getAttribute('data-label') || this.textContent;
                if (input) input.value = val;
                if (display) display.textContent = text || 'Select Country/Region';
                panel.querySelectorAll('.profile-country-option').forEach(function(o) { o.classList.remove('profile-country-option-selected'); });
                this.classList.add('profile-country-option-selected');
                if (searchInput) searchInput.value = '';
                close();
            });
        });
    }

    document.addEventListener('click', function(e) {
        if (wrap && !wrap.contains(e.target)) close();
    });
});
</script>
@endpush
@endsection
