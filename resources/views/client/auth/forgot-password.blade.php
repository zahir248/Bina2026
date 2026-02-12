@extends('layouts.client.auth')

@section('title', 'BINA')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <a href="{{ route('login') }}" class="auth-back-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Back
        </a>
        <div class="auth-header">
            <h1 class="auth-title">Forgot Password</h1>
        </div>

        <form class="auth-form" method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="form-group">
                <label for="email" class="form-label">Email Address <span class="required-asterisk">*</span></label>
                <input type="email" id="email" name="email" class="form-input" placeholder="Enter your email" value="{{ old('email') }}" required autofocus>
            </div>

            <button type="submit" class="btn-auth-primary">Send Reset Link</button>
        </form>

        <div class="auth-footer">
            <p class="auth-footer-text">
                Remember your password? <a href="{{ route('login') }}" class="auth-link">Log In</a>
            </p>
        </div>
    </div>
</div>
@endsection
