@php
    use App\Services\VisitorTrackingService;
    VisitorTrackingService::track();
    $todayCount = VisitorTrackingService::getTodayCount();
    $totalCount = VisitorTrackingService::getTotalCount();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'BINA')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Styles (version query busts cache when CSS is updated on server) -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : time() }}">
    @stack('styles')
</head>
<body class="@if(request()->routeIs('cart.index')) cart-page @endif">
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('images/bina-logo.png') }}" alt="BINA Logo" class="nav-logo">
                </a>
            </div>
            <ul class="nav-menu">
                <li class="nav-text-item"><a class="nav-text-link" href="{{ route('home') }}">Home</a></li>
                <li class="nav-text-item"><a class="nav-text-link" href="{{ route('about-bina') }}">About<br>BINA</a></li>
                <li class="nav-text-item"><a class="nav-text-link" href="{{ route('gallery') }}">Gallery</a></li>
                <li class="nav-category-group">
                    <ul class="nav-category-list">
                        @foreach($eventCategories ?? [] as $category)
                        <li><a class="nav-category-link" href="{{ $category->eventSlug ? route('events.show', $category->eventSlug) : route('home') }}">{{ $category->name }}</a></li>
                        @endforeach
                    </ul>
                </li>
                @auth
                <li class="nav-icon">
                    <a href="{{ route('cart.index') }}" aria-label="Cart" class="cart-toggle">
                        <i class="bi bi-cart"></i>
                    </a>
                    @php
                        $cartTotal = \App\Models\Cart::where('user_id', auth()->id())->sum('quantity');
                    @endphp
                    @if($cartTotal > 0)
                        <span class="cart-badge">{{ $cartTotal }}</span>
                    @endif
                </li>
                @endauth
                <li class="nav-auth">
                    @auth
                        <div class="auth-buttons-group">
                            <div class="dropdown">
                                <a href="#" class="auth-btn auth-btn-account">Account <span class="dropdown-arrow">▼</span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="#" class="dropdown-menu-item">Profile</a></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}" style="display: block; margin: 0; padding: 0; width: 100%;">
                                            @csrf
                                            <button type="submit" class="dropdown-menu-item dropdown-logout-btn">Log Out</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @else
                        <div class="auth-buttons-group">
                            <a href="{{ route('login') }}" class="auth-btn auth-btn-login">Log In</a>
                            <a href="{{ route('signup') }}" class="auth-btn auth-btn-signup">Sign Up</a>
                        </div>
                    @endauth
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    @unless(request()->routeIs('cart.index') || request()->routeIs('events.show'))
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <!-- Left Column: Logo, Slogan, Company Info, Social Media -->
                <div class="footer-section footer-left">
                    <div class="footer-logo">
                        <img src="{{ asset('images/bina-logo.png') }}" alt="BINA Logo" class="footer-logo-img">
                    </div>
                    <p class="footer-slogan">Beyond Limit, Build Tomorrow</p>
                    <p class="footer-company">CIDB IBS SDN. BHD.</p>
                    <p class="footer-address">Lot 8, Jalan Chan Sow, Cheras Batu 2 1/2, 55200 Kuala Lumpur, Federal Territory of Kuala Lumpur</p>
                    <div class="footer-social">
                        <a href="#" class="social-icon" aria-label="Facebook">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" class="social-icon" aria-label="LinkedIn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Middle Column: Contact Us -->
                <div class="footer-section footer-middle">
                    <h4 class="footer-title">CONTACT US</h4>
                    <div class="footer-contact-item">
                        <div class="contact-icon-wrapper">
                            <svg class="contact-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="contact-info">
                            <span class="contact-label">Our Email</span>
                            <a href="mailto:bina@cidbibs.com.my" class="contact-value">bina@cidbibs.com.my</a>
                        </div>
                    </div>
                    <div class="footer-contact-item">
                        <div class="contact-icon-wrapper">
                            <svg class="contact-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="contact-info">
                            <span class="contact-label">Our Phone</span>
                            <div class="contact-value">
                                <a href="tel:+60392242280">+603-92242280</a><br>
                                <a href="tel:+60126909457">+6012-6909457</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Newsletter & Quick Links -->
                <div class="footer-section footer-right">
                    <div class="footer-newsletter">
                        <h4 class="footer-title">SUBSCRIBE OUR NEWSLETTER</h4>
                        <form class="newsletter-form" method="POST" action="#">
                            @csrf
                            <div class="newsletter-input-group">
                                <input type="email" name="email" class="newsletter-input" placeholder="Your Email Address" required>
                                <button type="submit" class="newsletter-btn">SIGN UP</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="footer-divider"></div>
            <div class="footer-visitor-stats">
                <div class="visitor-stats-content">
                    <h4 class="visitor-stats-title">Total Visitors</h4>
                    <div class="visitor-stats-info">
                        <div class="visitor-stat-item">
                            <span class="visitor-stat-label">Today:</span>
                            <span class="visitor-stat-value">{{ number_format($todayCount) }}</span>
                        </div>
                        <div class="visitor-stat-item">
                            <span class="visitor-stat-label">Total:</span>
                            <span class="visitor-stat-value">{{ number_format($totalCount) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} BINA CIDB IBS All rights Reserved</p>
            </div>
        </div>
    </footer>
    @endunless

    <!-- Toast Notifications -->
    <x-toast />

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}?v={{ file_exists(public_path('js/app.js')) ? filemtime(public_path('js/app.js')) : time() }}"></script>
    <script>
        // Toast notification handler - available on all pages
        document.addEventListener('DOMContentLoaded', function() {
            const errorToast = document.getElementById('error-toast');
            const messageToast = document.getElementById('message-toast');
            
            // Handle error toast
            if (errorToast) {
                setTimeout(() => {
                    errorToast.classList.add('show');
                }, 100);
                
                setTimeout(() => {
                    errorToast.classList.remove('show');
                }, 5000);
            }
            
            // Handle success/error message toast
            if (messageToast) {
                setTimeout(() => {
                    messageToast.classList.add('show');
                }, 100);
                
                setTimeout(() => {
                    messageToast.classList.remove('show');
                }, 3000);
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
