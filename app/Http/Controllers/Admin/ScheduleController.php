<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Event;
use App\Models\EventCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
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
        $categoryFilter = $request->get('category', '');
        
        // Start building query for events
        $query = Event::withCount('schedules');
        
        // Apply search filter (name, description)
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        
        // Apply category filter
        if (!empty($categoryFilter)) {
            $query->where('event_category_id', $categoryFilter);
        }
        
        // Apply status filter
        if (!empty($statusFilter)) {
            $query->where('status', $statusFilter);
        }
        
        // Apply sorting
        $query->orderBy('created_at', 'desc');
        
        // Paginate with query parameters
        $events = $query->paginate(15)->withQueryString();
        
        // Get all categories for filter dropdown
        $categories = EventCategory::where('status', 'active')->orderBy('name')->get();

        return view('admin.events.schedules.index', compact('events', 'categories', 'search', 'statusFilter', 'categoryFilter'));
    }

    public function getSchedules($eventId)
    {
        // Check if user is authenticated and is admin
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $event = Event::findOrFail($eventId);
        $schedules = Schedule::where('event_id', $eventId)
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'event' => $event,
            'schedules' => $schedules
        ]);
    }

    public function saveSchedules(Request $request, $eventId)
    {
        // Check if user is authenticated and is admin
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $event = Event::findOrFail($eventId);

        $validator = Validator::make($request->all(), [
            'schedules' => ['required', 'array'],
            'schedules.*.name' => ['required', 'string', 'max:255'],
            'schedules.*.description' => ['nullable', 'string'],
            'schedules.*.start_time' => ['required', 'date_format:H:i'],
            'schedules.*.end_time' => ['nullable', 'date_format:H:i'],
            'schedules.*.session' => ['nullable', 'string', 'max:255'],
            'schedules.*.status' => ['required', 'in:active,inactive'],
        ], [
            'schedules.required' => 'At least one schedule is required.',
            'schedules.*.name.required' => 'Schedule name is required.',
            'schedules.*.start_time.required' => 'Start time is required.',
            'schedules.*.start_time.date_format' => 'Start time must be in valid time format (HH:MM).',
            'schedules.*.end_time.date_format' => 'End time must be in valid time format (HH:MM).',
            'schedules.*.status.required' => 'Status is required.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Delete existing schedules for this event
        Schedule::where('event_id', $eventId)->delete();

        // Create new schedules
        foreach ($request->schedules as $scheduleData) {
            if (!empty($scheduleData['name']) && !empty($scheduleData['start_time'])) {
                Schedule::create([
                    'name' => $scheduleData['name'],
                    'description' => $scheduleData['description'] ?? null,
                    'event_id' => $eventId,
                    'start_time' => $scheduleData['start_time'],
                    'end_time' => $scheduleData['end_time'] ?? null,
                    'session' => $scheduleData['session'] ?? null,
                    'status' => $scheduleData['status'] ?? 'active',
                ]);
            }
        }

        return redirect()->route('admin.events.schedules')->with('success', 'Schedules saved successfully!');
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
            'event_id' => ['required', 'exists:events,id'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'session' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Schedule name is required.',
            'event_id.required' => 'Event is required.',
            'event_id.exists' => 'Selected event does not exist.',
            'start_time.required' => 'Start time is required.',
            'start_time.date_format' => 'Start time must be in valid time format (HH:MM).',
            'end_time.date_format' => 'End time must be in valid time format (HH:MM).',
            'end_time.after' => 'End time must be after start time.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'create')->withInput();
        }

        Schedule::create([
            'name' => $request->name,
            'description' => $request->description,
            'event_id' => $request->event_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'session' => $request->session,
            'status' => 'active',
        ]);

        return redirect()->route('admin.events.schedules')->with('success', 'Schedule created successfully!');
    }

    public function update(Request $request, $id)
    {
        // Check if user is authenticated and is admin
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $schedule = Schedule::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_id' => ['required', 'exists:events,id'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'session' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Schedule name is required.',
            'event_id.required' => 'Event is required.',
            'event_id.exists' => 'Selected event does not exist.',
            'start_time.required' => 'Start time is required.',
            'start_time.date_format' => 'Start time must be in valid time format (HH:MM).',
            'end_time.date_format' => 'End time must be in valid time format (HH:MM).',
            'end_time.after' => 'End time must be after start time.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'edit')->withInput();
        }

        $schedule->update([
            'name' => $request->name,
            'description' => $request->description,
            'event_id' => $request->event_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'session' => $request->session,
        ]);

        return redirect()->route('admin.events.schedules')->with('success', 'Schedule updated successfully!');
    }

    public function destroy($id)
    {
        // Check if user is authenticated and is admin
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $schedule = Schedule::findOrFail($id);

        if ($schedule->status === 'active') {
            // Deactivate the schedule
            $schedule->update(['status' => 'inactive']);
            return redirect()->route('admin.events.schedules')->with('success', 'Schedule deactivated successfully!');
        } else {
            // Activate the schedule
            $schedule->update(['status' => 'active']);
            return redirect()->route('admin.events.schedules')->with('success', 'Schedule activated successfully!');
        }
    }
}
