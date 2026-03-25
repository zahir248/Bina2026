<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL').'/auth/google/callback'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'test_key' => env('STRIPE_TEST_KEY'),
        'test_secret' => env('STRIPE_TEST_SECRET'),
        // Pass Stripe processing fee to customer. See: https://stripe.com/en-my/pricing
        // Domestic (FPX / Malaysia card): fee_percentage + fee_fixed_cents. International card: fee_percentage_international + fee_fixed_cents.
        'fee_percentage' => (float) env('STRIPE_FEE_PERCENTAGE', 0),
        'fee_percentage_international' => (float) env('STRIPE_FEE_PERCENTAGE_INTERNATIONAL', 0),
        'fee_fixed_cents' => (int) env('STRIPE_FEE_FIXED_CENTS', 0),
    ],

];
