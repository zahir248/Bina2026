<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:255'],
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        NewsletterSubscriber::firstOrCreate([
            'email' => strtolower(trim($request->input('email'))),
        ]);

        return redirect()->back()->with('success', 'Thank you for subscribing to our newsletter.');
    }
}
