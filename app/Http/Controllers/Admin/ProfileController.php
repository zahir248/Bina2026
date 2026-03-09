<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Client\ProfileController as ClientProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Show the admin user's profile page.
     */
    public function index()
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $user = Auth::user();

        return view('admin.profile.index', [
            'user' => $user,
            'countriesRegions' => ClientProfileController::getCountriesRegions(),
        ]);
    }

    /**
     * Update the admin user's profile.
     */
    public function update(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $allowedCountries = array_values(array_filter(
            array_keys(ClientProfileController::getCountriesRegions()),
            fn (string $v): bool => $v !== ''
        ));

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore(Auth::id())],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', 'string', 'in:male,female'],
            'nric_passport' => ['nullable', 'string', 'max:50'],
            'country_region' => ['nullable', 'string', Rule::in($allowedCountries)],
            'street_address' => ['nullable', 'string', 'max:255'],
            'town_city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postcode_zip' => ['nullable', 'string', 'max:20'],
        ], [
            'name.required' => 'Full name is required.',
            'username.unique' => 'This username is already taken.',
        ]);

        Auth::user()->update($request->only([
            'name',
            'username',
            'contact_number',
            'gender',
            'nric_passport',
            'country_region',
            'street_address',
            'town_city',
            'state',
            'postcode_zip',
        ]));

        return redirect()->route('admin.profile')->with('success', 'Profile updated successfully.');
    }
}
