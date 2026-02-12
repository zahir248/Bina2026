<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventPersonnel;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class EventPersonnelController extends Controller
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
        $roleFilter = $request->get('role', '');
        
        // Start building query
        $query = EventPersonnel::with('schedules');
        
        // Apply search filter (name, position, company)
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('position', 'like', '%' . $search . '%')
                  ->orWhere('company', 'like', '%' . $search . '%');
            });
        }
        
        // Apply role filter
        if (!empty($roleFilter)) {
            $query->where('role', $roleFilter);
        }
        
        // Apply status filter
        if (!empty($statusFilter)) {
            $query->where('status', $statusFilter);
        }
        
        // Apply sorting
        $query->orderBy('created_at', 'desc');
        
        // Paginate with query parameters
        $personnel = $query->paginate(15)->withQueryString();
        
        // Get all schedules for selection with their events
        $schedules = Schedule::with('event')->where('status', 'active')->orderBy('start_time')->get();

        return view('admin.events.personnel.index', compact('personnel', 'schedules', 'search', 'statusFilter', 'roleFilter'));
    }

    public function store(Request $request)
    {
        // Check if user is authenticated and is admin
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'in:host,moderator,speaker'],
            'position' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'schedules' => ['required', 'array', 'min:1'],
            'schedules.*' => ['required', 'exists:schedules,id'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
        ], [
            'name.required' => 'Name is required.',
            'role.required' => 'Role is required.',
            'role.in' => 'Role must be host, moderator, or speaker.',
            'schedules.required' => 'At least one schedule is required.',
            'schedules.array' => 'Schedules must be an array.',
            'schedules.min' => 'At least one schedule is required.',
            'schedules.*.exists' => 'Selected schedule does not exist.',
            'image.image' => 'File must be an image.',
            'image.mimes' => 'Image must be jpeg, jpg, png, gif, or webp format.',
            'image.max' => 'Image must not exceed 5MB.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'create')->withInput();
        }

        // Process image if provided
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('personnel', 'public');
        }

        $personnel = EventPersonnel::create([
            'name' => $request->name,
            'role' => $request->role,
            'position' => $request->position,
            'company' => $request->company,
            'image' => $imagePath,
            'status' => 'active',
        ]);

        // Attach schedules
        $personnel->schedules()->attach($request->schedules);

        return redirect()->route('admin.events.personnel')->with('success', 'Event personnel created successfully!');
    }

    public function update(Request $request, $id)
    {
        // Check if user is authenticated and is admin
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $personnel = EventPersonnel::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'in:host,moderator,speaker'],
            'position' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'schedules' => ['required', 'array', 'min:1'],
            'schedules.*' => ['required', 'exists:schedules,id'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
        ], [
            'name.required' => 'Name is required.',
            'role.required' => 'Role is required.',
            'role.in' => 'Role must be host, moderator, or speaker.',
            'schedules.required' => 'At least one schedule is required.',
            'schedules.array' => 'Schedules must be an array.',
            'schedules.min' => 'At least one schedule is required.',
            'schedules.*.exists' => 'Selected schedule does not exist.',
            'image.image' => 'File must be an image.',
            'image.mimes' => 'Image must be jpeg, jpg, png, gif, or webp format.',
            'image.max' => 'Image must not exceed 5MB.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'edit')->withInput();
        }

        $updateData = [
            'name' => $request->name,
            'role' => $request->role,
            'position' => $request->position,
            'company' => $request->company,
        ];

        // Handle removed image
        if ($request->has('removed_image') && !empty($request->removed_image)) {
            // Delete the removed image from storage
            if (Storage::disk('public')->exists($request->removed_image)) {
                Storage::disk('public')->delete($request->removed_image);
            }
            $updateData['image'] = null;
        }

        // Process new image if provided
        if ($request->hasFile('image')) {
            // Delete old image if it exists and wasn't already removed
            if ($personnel->image && Storage::disk('public')->exists($personnel->image) && (!$request->has('removed_image') || $request->removed_image !== $personnel->image)) {
                Storage::disk('public')->delete($personnel->image);
            }
            $updateData['image'] = $request->file('image')->store('personnel', 'public');
        } elseif ($request->has('removed_image') && !empty($request->removed_image)) {
            // Image was removed but no new image uploaded, so set to null
            $updateData['image'] = null;
        }

        $personnel->update($updateData);

        // Sync schedules
        $personnel->schedules()->sync($request->schedules);

        return redirect()->route('admin.events.personnel')->with('success', 'Event personnel updated successfully!');
    }

    public function destroy($id)
    {
        // Check if user is authenticated and is admin
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $personnel = EventPersonnel::findOrFail($id);

        if ($personnel->status === 'active') {
            // Deactivate the personnel
            $personnel->update(['status' => 'inactive']);
            return redirect()->route('admin.events.personnel')->with('success', 'Event personnel deactivated successfully!');
        } else {
            // Activate the personnel
            $personnel->update(['status' => 'active']);
            return redirect()->route('admin.events.personnel')->with('success', 'Event personnel activated successfully!');
        }
    }
}
