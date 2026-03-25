<?php

namespace App\Support;

use App\Models\Setting;

class StripeConfig
{
    public const SETTING_PAYMENT_TEST_MODE = 'stripe_payment_test_mode';

    public static function adminPaymentTestModeEnabled(): bool
    {
        return Setting::get(self::SETTING_PAYMENT_TEST_MODE, '0') === '1';
    }

    public static function publishableKey(?bool $useTest = null): ?string
    {
        $test = $useTest ?? self::adminPaymentTestModeEnabled();
        $key = $test ? config('services.stripe.test_key') : config('services.stripe.key');

        return $key !== null && $key !== '' ? $key : null;
    }

    public static function secret(?bool $useTest = null): ?string
    {
        $test = $useTest ?? self::adminPaymentTestModeEnabled();
        $secret = $test ? config('services.stripe.test_secret') : config('services.stripe.secret');

        return $secret !== null && $secret !== '' ? $secret : null;
    }
}
