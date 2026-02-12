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

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Toast Notifications -->
    <x-toast />

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
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
