<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public const SETTING_KEY = 'maintenance_mode';

    /**
     * If maintenance mode is enabled, show maintenance view unless the user is an admin
     * or is accessing login, logout, or admin routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $enabled = Setting::get(self::SETTING_KEY, '') === '1';

        if (!$enabled) {
            $path = $request->path();
            $isAdminPath = $path === 'admin' || str_starts_with($path, 'admin/');
            if ($isAdminPath && !$request->user()) {
                abort(404);
            }
            return $next($request);
        }

        if (auth()->check() && auth()->user()->role === 'admin') {
            return $next($request);
        }

        if ($request->is('logout')) {
            return $next($request);
        }

        if ($request->is('admin', 'admin/*')) {
            return $next($request);
        }

        if ($request->isMethod('POST') && $request->is('login')) {
            return $next($request);
        }

        return response()->view('errors.maintenance', [], 503);
    }
}
