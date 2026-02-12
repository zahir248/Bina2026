<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Check if user is authenticated and is admin
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $currentUserId = auth()->id();
        
        // Get filter parameters
        $search = $request->get('search', '');
        $roleFilter = $request->get('role', '');
        $statusFilter = $request->get('status', '');
        
        // Start building query
        $query = User::query();
        
        // Apply search filter (username, name, email)
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                  ->orWhere('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
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
        // Always prioritize current user first, then other admins, then clients
        // Then sort by created_at descending
        $query->orderByRaw("CASE WHEN id = {$currentUserId} THEN 0 WHEN role = 'admin' THEN 1 ELSE 2 END")
              ->orderBy('created_at', 'desc');
        
        // Paginate with query parameters
        $users = $query->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'roleFilter', 'statusFilter'));
    }

    public function store(Request $request)
    {
        // Check if user is authenticated and is admin
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
                'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'
            ],
            'role' => ['required', 'in:admin,client'],
        ], [
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is already taken.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters.',
            'password.regex' => 'Password must contain at least one letter and one number.',
            'password.confirmed' => 'Password confirmation does not match.',
            'role.required' => 'Role is required.',
            'role.in' => 'Role must be either admin or client.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'create')->withInput();
        }

        User::create([
            'username' => $request->username,
            'name' => $request->name ?? $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('admin.users')->with('success', 'User created successfully!');
    }

    public function update(Request $request, $id)
    {
        // Check if user is authenticated and is admin
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $id],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
            'password' => ['nullable', 'string', 'min:6'],
        ], [
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is already taken.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'password.min' => 'Password must be at least 6 characters.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'edit')->withInput();
        }

        $updateData = [
            'username' => $request->username,
            'name' => $request->name ?? $request->username,
            'email' => $request->email,
            // Role is maintained - not updated
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('admin.users')->with('success', 'User updated successfully!');
    }

    public function destroy($id)
    {
        // Check if user is authenticated and is admin
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $user = User::findOrFail($id);

        // Prevent deactivating yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')->with('error', 'You cannot deactivate your own account.');
        }

        if ($user->status === 'active') {
            // Deactivate the user
            $user->update(['status' => 'inactive']);
            return redirect()->route('admin.users')->with('success', 'User deactivated successfully!');
        } else {
            // Activate the user
            $user->update(['status' => 'active']);
            return redirect()->route('admin.users')->with('success', 'User activated successfully!');
        }
    }

}
