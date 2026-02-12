<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
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
        $eventFilter = $request->get('event', '');
        
        // Start building query
        $query = Ticket::with('events');
        
        // Apply search filter (name, description, remarks)
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('remarks', 'like', '%' . $search . '%');
            });
        }
        
        // Apply event filter
        if (!empty($eventFilter)) {
            $query->whereHas('events', function($q) use ($eventFilter) {
                $q->where('events.id', $eventFilter);
            });
        }
        
        // Apply status filter
        if (!empty($statusFilter)) {
            $query->where('status', $statusFilter);
        }
        
        // Apply sorting
        $query->orderBy('created_at', 'desc');
        
        // Paginate with query parameters
        $tickets = $query->paginate(15)->withQueryString();
        
        // Get all events for selection
        $events = Event::where('status', 'active')->orderBy('name')->get();

        return view('admin.events.tickets.index', compact('tickets', 'events', 'search', 'statusFilter', 'eventFilter'));
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
            'price' => ['required', 'numeric', 'min:0'],
            'quantity_discount' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['required', 'exists:events,id'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
        ], [
            'name.required' => 'Name is required.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a number.',
            'price.min' => 'Price must be at least 0.',
            'events.required' => 'At least one event is required.',
            'events.array' => 'Events must be an array.',
            'events.min' => 'At least one event is required.',
            'events.*.exists' => 'Selected event does not exist.',
            'image.image' => 'File must be an image.',
            'image.mimes' => 'Image must be jpeg, jpg, png, gif, or webp format.',
            'image.max' => 'Image must not exceed 5MB.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'create')->withInput();
        }

        // Process quantity_discount - can accept multiple values (one per line)
        // Formats supported:
        // - "2,319" - buy exactly 2, price is RM 319.00
        // - "2-3,319" - buy 2-3, price is RM 319.00
        // - ">4,249" - buy more than 4, price is RM 249.00
        // - "<2,10" - buy less than 2, price is RM 10.00
        $quantityDiscount = null;
        if ($request->has('quantity_discount') && !empty($request->quantity_discount)) {
            // Split by newlines to get multiple entries
            $lines = preg_split('/\r\n|\r|\n/', trim($request->quantity_discount));
            $discounts = [];
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // Parse each line (format: "quantity,price")
                $discountParts = explode(',', $line);
                if (count($discountParts) == 2) {
                    $quantityStr = trim($discountParts[0]);
                    $price = (float)trim($discountParts[1]);
                    
                    if ($price < 0) continue;
                    
                    $discountEntry = ['price' => $price];
                    
                    // Check for "more than" format (e.g., ">4")
                    if (str_starts_with($quantityStr, '>')) {
                        $quantity = (int)trim($quantityStr, '>');
                        if ($quantity > 0) {
                            $discountEntry['type'] = 'more_than';
                            $discountEntry['quantity'] = $quantity;
                            $discounts[] = $discountEntry;
                        }
                    }
                    // Check for "less than" format (e.g., "<2")
                    elseif (str_starts_with($quantityStr, '<')) {
                        $quantity = (int)trim($quantityStr, '<');
                        if ($quantity > 0) {
                            $discountEntry['type'] = 'less_than';
                            $discountEntry['quantity'] = $quantity;
                            $discounts[] = $discountEntry;
                        }
                    }
                    // Check for range format (e.g., "2-3")
                    elseif (strpos($quantityStr, '-') !== false) {
                        $rangeParts = explode('-', $quantityStr);
                        if (count($rangeParts) == 2) {
                            $minQty = (int)trim($rangeParts[0]);
                            $maxQty = (int)trim($rangeParts[1]);
                            if ($minQty > 0 && $maxQty >= $minQty) {
                                $discountEntry['type'] = 'range';
                                $discountEntry['min_quantity'] = $minQty;
                                $discountEntry['max_quantity'] = $maxQty;
                                $discounts[] = $discountEntry;
                            }
                        }
                    }
                    // Exact quantity format (e.g., "2")
                    else {
                        $quantity = (int)$quantityStr;
                        if ($quantity > 0) {
                            $discountEntry['type'] = 'exact';
                            $discountEntry['quantity'] = $quantity;
                            $discounts[] = $discountEntry;
                        }
                    }
                }
            }
            
            if (!empty($discounts)) {
                $quantityDiscount = $discounts;
            }
        }

        // Process image if provided
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('tickets', 'public');
        }

        $ticket = Ticket::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'quantity_discount' => $quantityDiscount,
            'remarks' => $request->remarks,
            'image' => $imagePath,
            'status' => 'active',
        ]);

        // Attach events
        $ticket->events()->attach($request->events);

        return redirect()->route('admin.events.tickets')->with('success', 'Ticket created successfully!');
    }

    public function update(Request $request, $id)
    {
        // Check if user is authenticated and is admin
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $ticket = Ticket::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'quantity_discount' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['required', 'exists:events,id'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
        ], [
            'name.required' => 'Name is required.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a number.',
            'price.min' => 'Price must be at least 0.',
            'events.required' => 'At least one event is required.',
            'events.array' => 'Events must be an array.',
            'events.min' => 'At least one event is required.',
            'events.*.exists' => 'Selected event does not exist.',
            'image.image' => 'File must be an image.',
            'image.mimes' => 'Image must be jpeg, jpg, png, gif, or webp format.',
            'image.max' => 'Image must not exceed 5MB.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'edit')->withInput();
        }

        // Process quantity_discount - can accept multiple values (one per line)
        // Formats supported:
        // - "2,319" - buy exactly 2, price is RM 319.00
        // - "2-3,319" - buy 2-3, price is RM 319.00
        // - ">4,249" - buy more than 4, price is RM 249.00
        // - "<2,10" - buy less than 2, price is RM 10.00
        $quantityDiscount = null;
        if ($request->has('quantity_discount') && !empty($request->quantity_discount)) {
            // Split by newlines to get multiple entries
            $lines = preg_split('/\r\n|\r|\n/', trim($request->quantity_discount));
            $discounts = [];
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // Parse each line (format: "quantity,price")
                $discountParts = explode(',', $line);
                if (count($discountParts) == 2) {
                    $quantityStr = trim($discountParts[0]);
                    $price = (float)trim($discountParts[1]);
                    
                    if ($price < 0) continue;
                    
                    $discountEntry = ['price' => $price];
                    
                    // Check for "more than" format (e.g., ">4")
                    if (str_starts_with($quantityStr, '>')) {
                        $quantity = (int)trim($quantityStr, '>');
                        if ($quantity > 0) {
                            $discountEntry['type'] = 'more_than';
                            $discountEntry['quantity'] = $quantity;
                            $discounts[] = $discountEntry;
                        }
                    }
                    // Check for "less than" format (e.g., "<2")
                    elseif (str_starts_with($quantityStr, '<')) {
                        $quantity = (int)trim($quantityStr, '<');
                        if ($quantity > 0) {
                            $discountEntry['type'] = 'less_than';
                            $discountEntry['quantity'] = $quantity;
                            $discounts[] = $discountEntry;
                        }
                    }
                    // Check for range format (e.g., "2-3")
                    elseif (strpos($quantityStr, '-') !== false) {
                        $rangeParts = explode('-', $quantityStr);
                        if (count($rangeParts) == 2) {
                            $minQty = (int)trim($rangeParts[0]);
                            $maxQty = (int)trim($rangeParts[1]);
                            if ($minQty > 0 && $maxQty >= $minQty) {
                                $discountEntry['type'] = 'range';
                                $discountEntry['min_quantity'] = $minQty;
                                $discountEntry['max_quantity'] = $maxQty;
                                $discounts[] = $discountEntry;
                            }
                        }
                    }
                    // Exact quantity format (e.g., "2")
                    else {
                        $quantity = (int)$quantityStr;
                        if ($quantity > 0) {
                            $discountEntry['type'] = 'exact';
                            $discountEntry['quantity'] = $quantity;
                            $discounts[] = $discountEntry;
                        }
                    }
                }
            }
            
            if (!empty($discounts)) {
                $quantityDiscount = $discounts;
            }
        }

        $updateData = [
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'quantity_discount' => $quantityDiscount,
            'remarks' => $request->remarks,
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
            if ($ticket->image && Storage::disk('public')->exists($ticket->image) && (!$request->has('removed_image') || $request->removed_image !== $ticket->image)) {
                Storage::disk('public')->delete($ticket->image);
            }
            $updateData['image'] = $request->file('image')->store('tickets', 'public');
        } elseif ($request->has('removed_image') && !empty($request->removed_image)) {
            // Image was removed but no new image uploaded, so set to null
            $updateData['image'] = null;
        }

        $ticket->update($updateData);

        // Sync events
        $ticket->events()->sync($request->events);

        return redirect()->route('admin.events.tickets')->with('success', 'Ticket updated successfully!');
    }

    public function destroy($id)
    {
        // Check if user is authenticated and is admin
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $ticket = Ticket::findOrFail($id);

        if ($ticket->status === 'active') {
            // Deactivate the ticket
            $ticket->update(['status' => 'inactive']);
            return redirect()->route('admin.events.tickets')->with('success', 'Ticket deactivated successfully!');
        } else {
            // Activate the ticket
            $ticket->update(['status' => 'active']);
            return redirect()->route('admin.events.tickets')->with('success', 'Ticket activated successfully!');
        }
    }
}
