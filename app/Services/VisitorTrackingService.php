<?php

namespace App\Services;

use App\Models\VisitorCount;
use Carbon\Carbon;

class VisitorTrackingService
{
    public static function track()
    {
        $today = Carbon::today('Asia/Kuala_Lumpur');
        
        $visitorCount = VisitorCount::firstOrCreate(
            ['visit_date' => $today],
            ['daily_count' => 0]
        );
        
        $visitorCount->increment('daily_count');
        
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
}
