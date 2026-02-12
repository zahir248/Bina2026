<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPersonnel;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Get all active events for client side
     */
    public function index(Request $request)
    {
        $query = Event::with(['category', 'tickets'])
            ->where('status', 'active');

        if ($request->filled('category_id')) {
            $query->where('event_category_id', $request->category_id);
        }

        $events = $query->orderBy('start_datetime', 'asc')
            ->get()
            ->map(function ($event) {
                // Get minimum ticket price
                $minPrice = $event->tickets()
                    ->where('status', 'active')
                    ->min('price');
                
                // Get first image from images array and convert to full URL
                $image = null;
                if ($event->images && is_array($event->images) && count($event->images) > 0) {
                    $imagePath = $event->images[0];
                    $image = storage_asset($imagePath);
                }
                
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'description' => $event->description,
                    'category' => $event->category ? $event->category->name : null,
                    'location' => $event->location,
                    'start_datetime' => $event->start_datetime->format('F j, Y'),
                    'start_datetime_raw' => $event->start_datetime->toIso8601String(),
                    'image' => $image,
                    'min_price' => $minPrice ? number_format($minPrice, 2) : null,
                    'slug' => $this->generateSlug($event->name),
                ];
            });

        return response()->json($events);
    }
    
    /**
     * Get upcoming events for calendar widget filtered by month
     */
    public function upcoming(Request $request)
    {
        // Get month and year from request, default to current month
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        
        // Create start and end of the selected month
        $startOfMonth = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();
        
        $events = Event::with(['category', 'schedules'])
            ->where('status', 'active')
            ->whereBetween('start_datetime', [$startOfMonth, $endOfMonth])
            ->orderBy('start_datetime', 'asc')
            ->get()
            ->map(function ($event) {
                // Always use event's start_datetime time to ensure consistency
                // Format time in 12-hour format with AM/PM
                $startTime = $event->start_datetime->format('g:iA');
                
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'location' => $event->location,
                    'start_datetime' => $event->start_datetime->toIso8601String(),
                    'day_abbr' => $event->start_datetime->format('D'),
                    'day_number' => $event->start_datetime->format('j'),
                    'time' => $startTime,
                    'slug' => $this->generateSlug($event->name),
                ];
            });

        return response()->json($events);
    }
    
    /**
     * Show event details page
     */
    public function show($slug)
    {
        // Find event by matching slugified name
        $events = Event::with(['category', 'tickets', 'schedules.personnel'])
            ->where('status', 'active')
            ->get();
        
        $event = null;
        foreach ($events as $e) {
            $eventSlug = $this->generateSlug($e->name);
            if ($eventSlug === $slug) {
                $event = $e;
                break;
            }
        }
        
        if (!$event) {
            abort(404);
        }
        
        // Get minimum ticket price
        $minPrice = $event->tickets()
            ->where('status', 'active')
            ->min('price');
        
        // Get all images from images array and convert to full URLs
        $images = [];
        if ($event->images && is_array($event->images) && count($event->images) > 0) {
            foreach ($event->images as $imagePath) {
                $images[] = storage_asset($imagePath);
            }
        }
        
        // If no images, use default placeholder
        if (empty($images)) {
            $images = ['https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=800&h=1000&fit=crop'];
        }
        
        // Get personnel for this event through schedules
        $personnel = collect();
        if ($event->schedules) {
            foreach ($event->schedules as $schedule) {
                $schedulePersonnel = $schedule->personnel()
                    ->where('status', 'active')
                    ->get();
                $personnel = $personnel->merge($schedulePersonnel);
            }
        }
        
        // Remove duplicates and format personnel data, then sort by role (host, moderator, speaker)
        $roleOrder = ['host' => 1, 'moderator' => 2, 'speaker' => 3];
        
        $personnelFormatted = $personnel->unique('id')->map(function ($person) use ($roleOrder) {
            $imageUrl = null;
            if ($person->image) {
                $imageUrl = storage_asset($person->image);
            }
            
            return [
                'id' => $person->id,
                'name' => $person->name,
                'role' => $person->role,
                'position' => $person->position,
                'company' => $person->company,
                'image' => $imageUrl,
                'role_order' => $roleOrder[$person->role] ?? 99, // Default to end if role not found
            ];
        })->sortBy('role_order')->values();
        
        // Group personnel by role (for backward compatibility if needed)
        $personnelByRole = $personnelFormatted->groupBy('role')->map(function ($group) {
            return $group->values();
        });
        
        // Format schedules data
        $schedulesFormatted = collect();
        if ($event->schedules) {
            $schedules = $event->schedules()
                ->where('status', 'active')
                ->orderBy('start_time')
                ->get();
            
            foreach ($schedules as $schedule) {
                // Format time
                $startTime = \Carbon\Carbon::parse($schedule->start_time)->format('g:i A');
                $endTime = $schedule->end_time ? \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') : null;
                $timeRange = $endTime ? $startTime . ' - ' . $endTime : $startTime;
                
                // Get personnel for this schedule
                $schedulePersonnel = $schedule->personnel()->where('status', 'active')->get();
                $personnelInfo = [];
                foreach ($schedulePersonnel as $person) {
                    $personInfo = $person->name;
                    if ($person->position) {
                        $personInfo .= ', ' . $person->position;
                    }
                    if ($person->company) {
                        $personInfo .= ' of ' . $person->company;
                    }
                    $personnelInfo[] = [
                        'name' => $person->name,
                        'role' => $person->role,
                        'full_info' => $personInfo,
                    ];
                }
                
                $schedulesFormatted->push([
                    'id' => $schedule->id,
                    'name' => $schedule->name,
                    'description' => $schedule->description,
                    'time_range' => $timeRange,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'session' => $schedule->session,
                    'personnel' => $personnelInfo,
                ]);
            }
        }
        
        // Group schedules by session
        $schedulesBySession = $schedulesFormatted->groupBy('session')->map(function ($group) {
            return $group->values();
        });
        
        // Format event data for view
        $eventData = [
            'id' => $event->id,
            'title' => $event->name,
            'category' => $event->category ? $event->category->name : null,
            'date' => $event->start_datetime->format('F j, Y'),
            'date_time_formatted' => $event->start_datetime->format('j M Y, g:iA'),
            'start_datetime' => $event->start_datetime,
            'location' => $event->location,
            'google_maps_address' => $event->google_maps_address,
            'waze_location_address' => $event->waze_location_address,
            'description' => $event->description,
            'price' => $minPrice ? 'RM ' . number_format($minPrice, 2) : 'Price TBA',
            'ticket_stock' => $event->ticket_stock ?? null,
            'images' => $images,
            'image' => $images[0], // Keep for backward compatibility
            'personnel' => $personnelFormatted,
            'personnelByRole' => $personnelByRole,
            'schedules' => $schedulesFormatted,
            'schedulesBySession' => $schedulesBySession,
            'tickets' => $event->tickets
                ->values()
                ->map(function ($ticket) use ($images) {
                    $ticketImage = null;
                    if (!empty($ticket->image)) {
                        $ticketImage = storage_asset($ticket->image);
                    } elseif (!empty($images)) {
                        // fallback to event image/poster if ticket has no image
                        $ticketImage = $images[0];
                    }

                    return [
                        'id' => $ticket->id,
                        'name' => $ticket->name,
                        'description' => $ticket->description,
                        'price' => (float)$ticket->price,
                        'price_formatted' => 'RM ' . number_format((float)$ticket->price, 2),
                        'remarks' => $ticket->remarks,
                        'image' => $ticketImage,
                        'quantity_discount' => $ticket->quantity_discount ?? [],
                        'status' => $ticket->status,
                    ];
                }),
        ];
        
        return view('client.events.show', ['event' => $eventData, 'slug' => $slug]);
    }
    
    /**
     * Generate a URL-friendly slug from event name
     */
    private function generateSlug($name)
    {
        return Event::nameToSlug($name);
    }
}
