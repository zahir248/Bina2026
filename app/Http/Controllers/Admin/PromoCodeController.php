<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PromoCodeController extends Controller
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
        $query = PromoCode::with('events');
        
        // Apply search filter (name, description, code)
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%');
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
        $promoCodes = $query->paginate(15)->withQueryString();
        
        // Get all events for selection
        $events = Event::where('status', 'active')->orderBy('name')->get();

        return view('admin.promo-codes.index', compact('promoCodes', 'events', 'search', 'statusFilter', 'eventFilter'));
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
            'code' => ['required', 'string', 'max:255', 'unique:promo_codes,code'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'events' => ['nullable', 'array'],
            'events.*' => ['required', 'exists:events,id'],
        ], [
            'name.required' => 'Name is required.',
            'code.required' => 'Code is required.',
            'code.unique' => 'This promo code already exists.',
            'discount.numeric' => 'Discount must be a number.',
            'discount.min' => 'Discount must be at least 0.',
            'events.array' => 'Events must be an array.',
            'events.*.exists' => 'Selected event does not exist.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'create')->withInput();
        }

        $promoCode = PromoCode::create([
            'name' => $request->name,
            'description' => $request->description,
            'code' => $request->code,
            'discount' => $request->discount,
            'status' => 'active',
        ]);

        // Attach events if provided
        if ($request->has('events') && !empty($request->events)) {
            $promoCode->events()->attach($request->events);
        }

        return redirect()->route('admin.promo-codes')->with('success', 'Promo code created successfully!');
    }

    public function update(Request $request, $id)
    {
        // Check if user is authenticated and is admin
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $promoCode = PromoCode::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'code' => ['required', 'string', 'max:255', 'unique:promo_codes,code,' . $id],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'events' => ['nullable', 'array'],
            'events.*' => ['required', 'exists:events,id'],
        ], [
            'name.required' => 'Name is required.',
            'code.required' => 'Code is required.',
            'code.unique' => 'This promo code already exists.',
            'discount.numeric' => 'Discount must be a number.',
            'discount.min' => 'Discount must be at least 0.',
            'events.array' => 'Events must be an array.',
            'events.*.exists' => 'Selected event does not exist.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'edit')->withInput();
        }

        $promoCode->update([
            'name' => $request->name,
            'description' => $request->description,
            'code' => $request->code,
            'discount' => $request->discount,
        ]);

        // Sync events
        if ($request->has('events')) {
            $promoCode->events()->sync($request->events ?? []);
        } else {
            $promoCode->events()->detach();
        }

        return redirect()->route('admin.promo-codes')->with('success', 'Promo code updated successfully!');
    }

    public function destroy($id)
    {
        // Check if user is authenticated and is admin
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $promoCode = PromoCode::findOrFail($id);

        if ($promoCode->status === 'active') {
            // Deactivate the promo code
            $promoCode->update(['status' => 'inactive']);
            return redirect()->route('admin.promo-codes')->with('success', 'Promo code deactivated successfully!');
        } else {
            // Activate the promo code
            $promoCode->update(['status' => 'active']);
            return redirect()->route('admin.promo-codes')->with('success', 'Promo code activated successfully!');
        }
    }
}
