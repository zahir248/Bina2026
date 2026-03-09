<?php

namespace App\Providers;

use App\Http\Middleware\CheckMaintenanceMode;
use App\Listeners\LogEmailSent;
use App\Models\EventCategory;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Authenticate::redirectUsing(function ($request) {
            $path = $request->path();
            $isAdminPath = $path === 'admin' || str_starts_with($path, 'admin/');
            $maintenanceOn = Setting::get(CheckMaintenanceMode::SETTING_KEY, '') === '1';

            if ($isAdminPath && !$maintenanceOn) {
                abort(404);
            }
            if ($isAdminPath && $maintenanceOn) {
                return route('admin.login');
            }
            return route('login');
        });

        Event::listen(MessageSent::class, LogEmailSent::class);

        View::composer('layouts.client.app', function ($view) {
            $categories = EventCategory::where('status', 'active')
                ->whereHas('events', fn ($q) => $q->where('status', 'active'))
                ->orderBy('name', 'asc')
                ->with(['events' => function ($q) {
                    $q->where('status', 'active')->orderBy('start_datetime')->limit(1);
                }])
                ->get();
            $view->with('eventCategories', $categories->map(function ($category) {
                $firstEvent = $category->events->first();
                return (object) [
                    'id' => $category->id,
                    'name' => $category->name,
                    'eventSlug' => $firstEvent ? \App\Models\Event::nameToSlug($firstEvent->name) : null,
                ];
            }));
        });

        View::composer('layouts.admin.app', function ($view) {
            $pendingRefundCount = Order::where('status', 'paid')
                ->where('refund_status', 'pending')
                ->count();
            $view->with('pendingRefundCount', $pendingRefundCount);
        });
    }
}
