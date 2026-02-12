<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\EventCategory;
use Illuminate\Http\Request;

class EventCategoryController extends Controller
{
    /**
     * Get all active event categories for client side
     */
    public function index()
    {
        $categories = EventCategory::where('status', 'active')
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'description']);

        return response()->json($categories);
    }
}
