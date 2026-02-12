<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventCategoryController extends Controller
{
    public function index(Request $request)
    {
        // Check if user is authenticated and is admin
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        // Get filter parameters
        $search = $request->get('search', '');
        $statusFilter = $request->get('status', '');
        
        // Start building query
        $query = EventCategory::query();
        
        // Apply search filter (name, description)
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        
        // Apply status filter
        if (!empty($statusFilter)) {
            $query->where('status', $statusFilter);
        }
        
        // Apply sorting
        $query->orderBy('created_at', 'desc');
        
        // Paginate with query parameters
        $categories = $query->paginate(15)->withQueryString();

        return view('admin.events.categories.index', compact('categories', 'search', 'statusFilter'));
    }

    public function store(Request $request)
    {
        // Check if user is authenticated and is admin
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ], [
            'name.required' => 'Category name is required.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'create')->withInput();
        }

        EventCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => 'active',
        ]);

        return redirect()->route('admin.events.categories')->with('success', 'Category created successfully!');
    }

    public function update(Request $request, $id)
    {
        // Check if user is authenticated and is admin
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $category = EventCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ], [
            'name.required' => 'Category name is required.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'edit')->withInput();
        }

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.events.categories')->with('success', 'Category updated successfully!');
    }

    public function destroy($id)
    {
        // Check if user is authenticated and is admin
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $category = EventCategory::findOrFail($id);

        if ($category->status === 'active') {
            // Deactivate the category
            $category->update(['status' => 'inactive']);
            return redirect()->route('admin.events.categories')->with('success', 'Category deactivated successfully!');
        } else {
            // Activate the category
            $category->update(['status' => 'active']);
            return redirect()->route('admin.events.categories')->with('success', 'Category activated successfully!');
        }
    }
}
