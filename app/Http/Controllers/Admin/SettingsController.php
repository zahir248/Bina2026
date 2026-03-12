<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\CheckMaintenanceMode;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public const KEY_ADMIN_NOTIFICATION_EMAIL = 'admin_notification_email';
    public const KEY_COUNTDOWN_ENABLED = 'countdown_enabled';
    public const KEY_COUNTDOWN_TARGET_DATETIME = 'countdown_target_datetime';

    public function index()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $adminNotificationEmail = Setting::get(self::KEY_ADMIN_NOTIFICATION_EMAIL, '');
        $maintenanceMode = Setting::get(CheckMaintenanceMode::SETTING_KEY, '') === '1';
        $countdownEnabled = Setting::get(self::KEY_COUNTDOWN_ENABLED, '1') === '1';
        $countdownTargetDatetime = Setting::get(self::KEY_COUNTDOWN_TARGET_DATETIME, '2026-06-15T00:00:00');

        return view('admin.settings.index', compact('adminNotificationEmail', 'maintenanceMode', 'countdownEnabled', 'countdownTargetDatetime'));
    }

    public function update(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'admin_notification_email' => 'nullable|email|max:255',
            'maintenance_mode' => 'nullable|in:0,1',
            'countdown_enabled' => 'nullable|in:0,1',
            'countdown_target_datetime' => 'nullable|date_format:Y-m-d\TH:i',
        ], [
            'admin_notification_email.email' => 'Please enter a valid email address.',
        ]);

        Setting::set(self::KEY_ADMIN_NOTIFICATION_EMAIL, $request->input('admin_notification_email') ?: null);
        Setting::set(CheckMaintenanceMode::SETTING_KEY, $request->boolean('maintenance_mode') ? '1' : '0');
        Setting::set(self::KEY_COUNTDOWN_ENABLED, $request->boolean('countdown_enabled') ? '1' : '0');
        Setting::set(self::KEY_COUNTDOWN_TARGET_DATETIME, $request->input('countdown_target_datetime') ?: null);

        return redirect()
            ->route('admin.settings')
            ->with('success', 'Settings saved successfully.');
    }
}
