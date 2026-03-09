<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>System Under Maintenance - BINA</title>
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
        .maint-icon {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            font-weight: 700;
            line-height: 1;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        .maint-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .maint-message {
            font-size: 1rem;
            color: var(--text-light);
            max-width: 360px;
            line-height: 1.5;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="maint-icon">&#9881;</div>
    <h1 class="maint-title">System under maintenance</h1>
    <p class="maint-message">Our system is currently undergoing scheduled maintenance. We apologise for any inconvenience. Please try again in a few moments.</p>
</body>
</html>
