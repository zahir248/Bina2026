<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
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
        
        // Start building query
        $query = Event::with('category');
        
        // Apply search filter (name, description, location)
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('location', 'like', '%' . $search . '%');
            });
        }
        
        // Apply status filter
        if (!empty($statusFilter)) {
            $query->where('status', $statusFilter);
        }
        
        // Apply category filter
        if (!empty($categoryFilter)) {
            $query->where('event_category_id', $categoryFilter);
        }
        
        // Apply sorting
        $query->orderBy('created_at', 'desc');
        
        // Paginate with query parameters
        $events = $query->paginate(15)->withQueryString();
        $categories = EventCategory::where('status', 'active')->orderBy('name')->get();

        return view('admin.events.index', compact('events', 'categories', 'search', 'statusFilter', 'categoryFilter'));
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
            'content_before_tickets' => ['nullable', 'string'],
            'content_cards' => ['nullable', 'array'],
            'content_cards.*.title' => ['nullable', 'string', 'max:255'],
            'content_cards.*.description' => ['nullable', 'string'],
            'content_cards.*.icon_path' => ['nullable', 'string', 'max:500'],
            'content_cards_heading' => ['nullable', 'string', 'max:255'],
            'content_cards_subheading' => ['nullable', 'string', 'max:500'],
            'content_list_heading' => ['nullable', 'string', 'max:255'],
            'content_list_items' => ['nullable', 'array'],
            'content_list_items.*' => ['nullable', 'string', 'max:1000'],
            'event_category_id' => ['required', 'exists:event_categories,id'],
            'location' => ['required', 'string', 'max:255'],
            'google_maps_address' => ['nullable', 'string'],
            'waze_location_address' => ['nullable', 'string'],
            'start_datetime' => ['required', 'date'],
            'end_datetime' => ['required', 'date', 'after:start_datetime'],
            'ticket_stock' => ['nullable', 'integer', 'min:0'],
            'images' => ['nullable', 'array'],
            'images.*' => ['required', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'], // 5MB max per image
        ], [
            'name.required' => 'Event name is required.',
            'event_category_id.required' => 'Event category is required.',
            'event_category_id.exists' => 'Selected category does not exist.',
            'location.required' => 'Location is required.',
            'start_datetime.required' => 'Start date and time is required.',
            'start_datetime.date' => 'Start date and time must be a valid date.',
            'end_datetime.required' => 'End date and time is required.',
            'end_datetime.date' => 'End date and time must be a valid date.',
            'end_datetime.after' => 'End date and time must be after start date and time.',
            'ticket_stock.integer' => 'Ticket stock must be a number.',
            'ticket_stock.min' => 'Ticket stock cannot be negative.',
            'images.array' => 'Images must be an array.',
            'images.*.required' => 'Each image file is required.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Images must be jpeg, jpg, png, gif, or webp format.',
            'images.*.max' => 'Each image must not exceed 5MB.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'create')->withInput();
        }

        // Process images if provided
        $images = null;
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('events', 'public');
                $imagePaths[] = $path;
            }
            $images = !empty($imagePaths) ? $imagePaths : null;
        }

        $contentCards = $this->normalizeContentCards($request->content_cards);

        Event::create([
            'name' => $request->name,
            'description' => $request->description,
            'content_before_tickets' => $request->content_before_tickets,
            'content_cards' => $contentCards,
            'content_cards_heading' => $request->content_cards_heading,
            'content_cards_subheading' => $request->content_cards_subheading,
            'content_list_heading' => $request->content_list_heading,
            'content_list_items' => $this->normalizeContentListItems($request->content_list_items),
            'event_category_id' => $request->event_category_id,
            'location' => $request->location,
            'google_maps_address' => $request->google_maps_address,
            'waze_location_address' => $request->waze_location_address,
            'start_datetime' => $request->start_datetime,
            'end_datetime' => $request->end_datetime,
            'ticket_stock' => $request->ticket_stock,
            'images' => $images,
            'status' => 'active',
        ]);

        return redirect()->route('admin.events.index')->with('success', 'Event created successfully!');
    }

    public function update(Request $request, $id)
    {
        // Check if user is authenticated and is admin
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $event = Event::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'content_before_tickets' => ['nullable', 'string'],
            'content_cards' => ['nullable', 'array'],
            'content_cards.*.title' => ['nullable', 'string', 'max:255'],
            'content_cards.*.description' => ['nullable', 'string'],
            'content_cards.*.icon_path' => ['nullable', 'string', 'max:500'],
            'content_cards_heading' => ['nullable', 'string', 'max:255'],
            'content_cards_subheading' => ['nullable', 'string', 'max:500'],
            'content_list_heading' => ['nullable', 'string', 'max:255'],
            'content_list_items' => ['nullable', 'array'],
            'content_list_items.*' => ['nullable', 'string', 'max:1000'],
            'event_category_id' => ['required', 'exists:event_categories,id'],
            'location' => ['required', 'string', 'max:255'],
            'google_maps_address' => ['nullable', 'string'],
            'waze_location_address' => ['nullable', 'string'],
            'start_datetime' => ['required', 'date'],
            'end_datetime' => ['required', 'date', 'after:start_datetime'],
            'ticket_stock' => ['nullable', 'integer', 'min:0'],
            'images' => ['nullable', 'array'],
            'images.*' => ['required', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'], // 5MB max per image
        ], [
            'name.required' => 'Event name is required.',
            'event_category_id.required' => 'Event category is required.',
            'event_category_id.exists' => 'Selected category does not exist.',
            'location.required' => 'Location is required.',
            'start_datetime.required' => 'Start date and time is required.',
            'start_datetime.date' => 'Start date and time must be a valid date.',
            'end_datetime.required' => 'End date and time is required.',
            'end_datetime.date' => 'End date and time must be a valid date.',
            'end_datetime.after' => 'End date and time must be after start date and time.',
            'ticket_stock.integer' => 'Ticket stock must be a number.',
            'ticket_stock.min' => 'Ticket stock cannot be negative.',
            'images.array' => 'Images must be an array.',
            'images.*.required' => 'Each image file is required.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Images must be jpeg, jpg, png, gif, or webp format.',
            'images.*.max' => 'Each image must not exceed 5MB.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'edit')->withInput();
        }

        // Process images
        $updateData = [
            'name' => $request->name,
            'description' => $request->description,
            'content_before_tickets' => $request->content_before_tickets,
            'content_cards' => $this->normalizeContentCards($request->content_cards),
            'content_cards_heading' => $request->content_cards_heading,
            'content_cards_subheading' => $request->content_cards_subheading,
            'content_list_heading' => $request->content_list_heading,
            'content_list_items' => $this->normalizeContentListItems($request->content_list_items),
            'event_category_id' => $request->event_category_id,
            'location' => $request->location,
            'google_maps_address' => $request->google_maps_address,
            'waze_location_address' => $request->waze_location_address,
            'start_datetime' => $request->start_datetime,
            'end_datetime' => $request->end_datetime,
            'ticket_stock' => $request->ticket_stock,
            // Status is not updated, it maintains the existing value
        ];

        // Handle removed images
        $currentImages = $event->images ?? [];
        $removedImages = [];
        
        if ($request->has('removed_images') && !empty($request->removed_images)) {
            try {
                $removedImages = json_decode($request->removed_images, true) ?? [];
            } catch (\Exception $e) {
                $removedImages = [];
            }
        }
        
        // Delete removed images from storage
        if (!empty($removedImages)) {
            foreach ($removedImages as $removedImage) {
                if (Storage::disk('public')->exists($removedImage)) {
                    Storage::disk('public')->delete($removedImage);
                }
            }
        }
        
        // Filter out removed images from current images
        $remainingImages = array_filter($currentImages, function($image) use ($removedImages) {
            return !in_array($image, $removedImages);
        });
        $remainingImages = array_values($remainingImages); // Re-index array
        
        // Handle new image uploads
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('events', 'public');
                $imagePaths[] = $path;
            }
            // Merge remaining images with new images
            $updateData['images'] = array_merge($remainingImages, $imagePaths);
        } else {
            // No new images, just keep remaining images
            $updateData['images'] = !empty($remainingImages) ? $remainingImages : null;
        }

        $event->update($updateData);

        return redirect()->route('admin.events.index')->with('success', 'Event updated successfully!');
    }

    /**
     * Build content_cards array for storage. Keeps only cards with at least title or description.
     */
    private function normalizeContentCards(?array $cards): ?array
    {
        if (empty($cards) || !is_array($cards)) {
            return null;
        }
        $out = [];
        foreach ($cards as $card) {
            if (!is_array($card)) {
                continue;
            }
            $title = isset($card['title']) ? trim((string) $card['title']) : '';
            $description = isset($card['description']) ? trim((string) $card['description']) : '';
            $icon = isset($card['icon_path']) ? trim((string) $card['icon_path']) : '';
            if ($title === '' && $description === '') {
                continue;
            }
            $out[] = [
                'icon' => $icon !== '' ? $icon : null,
                'title' => $title !== '' ? $title : null,
                'description' => $description !== '' ? $description : null,
            ];
        }
        return empty($out) ? null : $out;
    }

    /**
     * Build content_list_items array for storage (non-empty strings only).
     */
    private function normalizeContentListItems(?array $items): ?array
    {
        if (empty($items) || !is_array($items)) {
            return null;
        }
        $out = [];
        foreach ($items as $item) {
            $trimmed = trim((string) $item);
            if ($trimmed !== '') {
                $out[] = $trimmed;
            }
        }
        return empty($out) ? null : $out;
    }

    /**
     * Upload an image for the "content before tickets" rich content area.
     * Returns JSON with the URL to use in <img src="...">.
     */
    public function uploadContentImage(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
        ], [
            'image.required' => 'Please select an image.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'Image must be jpeg, jpg, png, gif, or webp.',
            'image.max' => 'Image must not exceed 5MB.',
        ]);

        $path = $request->file('image')->store('events/content-images', 'public');
        $url = storage_asset($path);

        return response()->json(['url' => $url, 'path' => $path]);
    }

    public function destroy($id)
    {
        // Check if user is authenticated and is admin
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $event = Event::findOrFail($id);

        if ($event->status === 'active') {
            // Deactivate the event
            $event->update(['status' => 'inactive']);
            return redirect()->route('admin.events.index')->with('success', 'Event deactivated successfully!');
        } else {
            // Activate the event
            $event->update(['status' => 'active']);
            return redirect()->route('admin.events.index')->with('success', 'Event activated successfully!');
        }
    }
}
