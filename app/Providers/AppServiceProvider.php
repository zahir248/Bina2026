<?php

namespace App\Providers;

use App\Models\EventCategory;
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
        View::composer('layouts.client.app', function ($view) {
            $categories = EventCategory::where('status', 'active')
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
    }
}
