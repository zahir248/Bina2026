<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page Not Found - BINA</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ff9800;
            --primary-dark: #e68900;
            --primary-light: #ffb84d;
            --text-dark: #1F2937;
            --text-light: #6B7280;
            --bg-light-grey: #F3F4F6;
            --bg-lavender: #FFF4E6;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(180deg, var(--bg-light-grey) 0%, var(--bg-lavender) 100%);
            color: var(--text-dark);
            padding: 2rem;
            text-align: center;
        }
        .error-code {
            font-family: 'Playfair Display', serif;
            font-size: clamp(6rem, 18vw, 10rem);
            font-weight: 700;
            line-height: 1;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .error-message {
            font-size: 1rem;
            color: var(--text-light);
            max-width: 360px;
            margin-bottom: 2rem;
            line-height: 1.5;
        }
        .error-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: center;
        }
        .error-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-size: 0.9375rem;
            font-weight: 500;
            font-family: inherit;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: background-color 0.2s, color 0.2s;
        }
        .error-btn-primary {
            background: var(--primary-color);
            color: #fff;
        }
        .error-btn-primary:hover {
            background: var(--primary-dark);
            color: #fff;
        }
        .error-btn-secondary {
            background: #e2e8f0;
            color: var(--text-dark);
        }
        .error-btn-secondary:hover {
            background: #e5e7eb;
            color: var(--text-dark);
        }
    </style>
</head>
<body>
    <div class="error-code">404</div>
    <h1 class="error-title">Page not found</h1>
    <p class="error-message">The page you're looking for doesn't exist or has been moved.</p>
    <div class="error-actions">
        <a href="{{ url('/') }}" class="error-btn error-btn-primary">
            <span>Go to Home</span>
        </a>
        <a href="javascript:history.back()" class="error-btn error-btn-secondary">Go back</a>
    </div>
</body>
</html>
