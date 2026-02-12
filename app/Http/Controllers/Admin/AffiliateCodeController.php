<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AffiliateCodeController extends Controller
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
        $query = AffiliateCode::query();
        
        // Apply search filter (name, description, code, link)
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%')
                  ->orWhere('link', 'like', '%' . $search . '%');
            });
        }
        
        // Apply status filter
        if (!empty($statusFilter)) {
            $query->where('status', $statusFilter);
        }
        
        // Apply sorting
        $query->orderBy('created_at', 'desc');
        
        // Paginate with query parameters
        $affiliateCodes = $query->paginate(15)->withQueryString();

        return view('admin.affiliate-codes.index', compact('affiliateCodes', 'search', 'statusFilter'));
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
            'code' => ['required', 'string', 'max:255', 'unique:affiliate_codes,code'],
            'link' => ['nullable', 'string', 'max:255', 'url'],
        ], [
            'name.required' => 'Name is required.',
            'code.required' => 'Code is required.',
            'code.unique' => 'This affiliate code already exists.',
            'link.url' => 'Link must be a valid URL.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'create')->withInput();
        }

        AffiliateCode::create([
            'name' => $request->name,
            'description' => $request->description,
            'code' => $request->code,
            'link' => $request->link,
            'total_conversion' => $request->total_conversion ?? 0,
            'status' => 'active',
        ]);

        return redirect()->route('admin.affiliate-codes')->with('success', 'Affiliate code created successfully!');
    }

    public function update(Request $request, $id)
    {
        // Check if user is authenticated and is admin
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $affiliateCode = AffiliateCode::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'code' => ['required', 'string', 'max:255', 'unique:affiliate_codes,code,' . $id],
            'link' => ['nullable', 'string', 'max:255', 'url'],
        ], [
            'name.required' => 'Name is required.',
            'code.required' => 'Code is required.',
            'code.unique' => 'This affiliate code already exists.',
            'link.url' => 'Link must be a valid URL.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'edit')->withInput();
        }

        $affiliateCode->update([
            'name' => $request->name,
            'description' => $request->description,
            'code' => $request->code,
            'link' => $request->link,
        ]);

        return redirect()->route('admin.affiliate-codes')->with('success', 'Affiliate code updated successfully!');
    }

    public function destroy($id)
    {
        // Check if user is authenticated and is admin
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $affiliateCode = AffiliateCode::findOrFail($id);

        if ($affiliateCode->status === 'active') {
            // Deactivate the affiliate code
            $affiliateCode->update(['status' => 'inactive']);
            return redirect()->route('admin.affiliate-codes')->with('success', 'Affiliate code deactivated successfully!');
        } else {
            // Activate the affiliate code
            $affiliateCode->update(['status' => 'active']);
            return redirect()->route('admin.affiliate-codes')->with('success', 'Affiliate code activated successfully!');
        }
    }
}
