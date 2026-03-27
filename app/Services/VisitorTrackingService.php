<?php

namespace App\Services;

use App\Models\VisitorCount;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class VisitorTrackingService
{
    public static function track(?Request $request = null)
    {
        $timezone = 'Asia/Kuala_Lumpur';
        $today = Carbon::today($timezone);
        $request = $request ?? request();

        // Only track page-view traffic (not AJAX/API-like requests).
        if (!$request->isMethod('GET') || $request->expectsJson() || $request->ajax()) {
            return VisitorCount::firstOrCreate(
                ['visit_date' => $today],
                ['daily_count' => 0]
            );
        }

        $userAgent = (string) $request->userAgent();
        if (self::isBotUserAgent($userAgent)) {
            return VisitorCount::firstOrCreate(
                ['visit_date' => $today],
                ['daily_count' => 0]
            );
        }

        // Count unique visitors by day using IP + User-Agent fingerprint.
        $ip = (string) ($request->ip() ?? 'unknown');
        $fingerprint = sha1($ip . '|' . strtolower(trim($userAgent)));
        $cacheKey = 'visitor_tracked:' . $today->toDateString() . ':' . $fingerprint;
        if (Cache::has($cacheKey)) {
            return VisitorCount::firstOrCreate(
                ['visit_date' => $today],
                ['daily_count' => 0]
            );
        }

        $visitorCount = VisitorCount::firstOrCreate(
            ['visit_date' => $today],
            ['daily_count' => 0]
        );

        $visitorCount->increment('daily_count');

        $now = Carbon::now($timezone);
        $secondsUntilEndOfDay = max(60, $now->diffInSeconds($now->copy()->endOfDay()));
        Cache::put($cacheKey, true, $secondsUntilEndOfDay);

        return $visitorCount;
    }

    public static function getTodayCount()
    {
        $today = Carbon::today('Asia/Kuala_Lumpur');

        $visitorCount = VisitorCount::where('visit_date', $today)->first();

        return $visitorCount ? $visitorCount->daily_count : 0;
    }

    public static function getTotalCount()
    {
        return VisitorCount::sum('daily_count');
    }

    private static function isBotUserAgent(string $userAgent): bool
    {
        if ($userAgent === '') {
            return true;
        }

        return preg_match('/bot|crawl|spider|slurp|curl|wget|python|postman|insomnia|headless|facebookexternalhit|whatsapp/i', $userAgent) === 1;
    }
}
