<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterMail;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    public function index(Request $request)
    {
        if (! auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $search = $request->get('search', '');
        $query = NewsletterSubscriber::query()->orderBy('created_at', 'desc');
        if ($search !== '') {
            $query->where('email', 'like', '%'.$search.'%');
        }
        $subscribers = $query->paginate(20)->withQueryString();
        $totalSubscribers = NewsletterSubscriber::count();

        return view('admin.newsletter.index', compact('subscribers', 'totalSubscribers', 'search'));
    }

    public function send(Request $request)
    {
        if (! auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $validator = Validator::make($request->all(), [
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:50000'],
        ], [
            'subject.required' => 'Subject is required.',
            'body.required' => 'Email content is required.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.newsletter')->withErrors($validator)->withInput();
        }

        $emails = NewsletterSubscriber::query()->orderBy('id')->pluck('email');
        if ($emails->isEmpty()) {
            return redirect()->route('admin.newsletter')->with('error', 'There are no subscribers to email yet.');
        }

        $subject = $request->input('subject');
        $body = $request->input('body');
        $sent = 0;
        $failed = 0;

        foreach ($emails as $email) {
            try {
                Mail::to($email)->send(new NewsletterMail($subject, $body));
                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                report($e);
            }
        }

        $message = "Newsletter sent to {$sent} address".($sent === 1 ? '' : 'es').'.';
        if ($failed > 0) {
            $message .= " {$failed} failed (check the application log).";
        }

        return redirect()->route('admin.newsletter')->with('success', $message);
    }

    public function destroy(int $id)
    {
        if (! auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $subscriber = NewsletterSubscriber::find($id);
        if ($subscriber) {
            $subscriber->delete();
        }

        return redirect()->route('admin.newsletter')->with('success', 'Subscriber removed.');
    }
}
