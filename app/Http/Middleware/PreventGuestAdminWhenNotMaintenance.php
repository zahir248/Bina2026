<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventGuestAdminWhenNotMaintenance
{
    /**
     * When maintenance mode is off, guests visiting /admin get 404 instead of being redirected to login.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $maintenanceOn = Setting::get(CheckMaintenanceMode::SETTING_KEY, '') === '1';

        if (!$maintenanceOn && !$request->user()) {
            abort(404);
        }

        return $next($request);
    }
}
