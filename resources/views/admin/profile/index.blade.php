@extends('layouts.admin.app')

@section('title', 'Profile')
@section('page-title', 'Profile')

@section('content')
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">My Profile</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.profile.update') }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" class="form-control form-control-sm" value="{{ $user->email ?? '' }}" disabled readonly>
                    <div class="form-text">Email cannot be changed from this page.</div>
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-control form-control-sm @error('name') is-invalid @enderror"
                           value="{{ old('name', $user->name ?? '') }}" required maxlength="255">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Username <span class="text-muted">(optional)</span></label>
                    <input type="text" id="username" name="username" class="form-control form-control-sm @error('username') is-invalid @enderror"
                           value="{{ old('username', $user->username ?? '') }}" autocomplete="username" maxlength="255">
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="gender" class="form-label">Gender</label>
                        <select id="gender" name="gender" class="form-select form-select-sm @error('gender') is-invalid @enderror">
                            <option value="">Select</option>
                            <option value="male" {{ old('gender', $user->gender ?? '') == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $user->gender ?? '') == 'female' ? 'selected' : '' }}>Female</option>
                        </select>
                        @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="contact_number" class="form-label">Contact Number</label>
                        <input type="text" id="contact_number" name="contact_number" class="form-control form-control-sm @error('contact_number') is-invalid @enderror"
                               value="{{ old('contact_number', $user->contact_number ?? '') }}" maxlength="50">
                        @error('contact_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3 mt-3">
                    <label for="nric_passport" class="form-label">NRIC / Passport Number</label>
                    <input type="text" id="nric_passport" name="nric_passport" class="form-control form-control-sm @error('nric_passport') is-invalid @enderror"
                           value="{{ old('nric_passport', $user->nric_passport ?? '') }}" maxlength="50">
                    @error('nric_passport')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="country_region" class="form-label">Country / Region</label>
                    <select id="country_region" name="country_region" class="form-select form-select-sm @error('country_region') is-invalid @enderror">
                        @foreach($countriesRegions ?? [] as $value => $label)
                            <option value="{{ $value }}" {{ old('country_region', $user->country_region ?? '') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('country_region')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="street_address" class="form-label">Street Address</label>
                    <input type="text" id="street_address" name="street_address" class="form-control form-control-sm @error('street_address') is-invalid @enderror"
                           value="{{ old('street_address', $user->street_address ?? '') }}" maxlength="255">
                    @error('street_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="town_city" class="form-label">Town / City</label>
                        <input type="text" id="town_city" name="town_city" class="form-control form-control-sm @error('town_city') is-invalid @enderror"
                               value="{{ old('town_city', $user->town_city ?? '') }}" maxlength="100">
                        @error('town_city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="state" class="form-label">State</label>
                        <input type="text" id="state" name="state" class="form-control form-control-sm @error('state') is-invalid @enderror"
                               value="{{ old('state', $user->state ?? '') }}" maxlength="100">
                        @error('state')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="postcode_zip" class="form-label">Postcode / Zip</label>
                        <input type="text" id="postcode_zip" name="postcode_zip" class="form-control form-control-sm @error('postcode_zip') is-invalid @enderror"
                               value="{{ old('postcode_zip', $user->postcode_zip ?? '') }}" maxlength="20">
                        @error('postcode_zip')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn-admin btn-admin-primary">
                        <i class="bi bi-check-lg"></i>
                        Save profile
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="btn-admin btn-admin-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
