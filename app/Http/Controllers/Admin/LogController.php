<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\CheckoutActivityLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function emailLog(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $search = $request->get('search', '');
        $statusFilter = $request->get('status', '');
        $query = EmailLog::query()->orderBy('created_at', 'desc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', '%' . $search . '%')
                    ->orWhere('mailable_class', 'like', '%' . $search . '%')
                    ->orWhere('to', 'like', '%' . $search . '%'); // to is JSON array of addresses
            });
        }

        if (in_array($statusFilter, ['sent', 'failed'], true)) {
            $query->where('status', $statusFilter);
        }

        $logs = $query->paginate(10)->withQueryString();

        return view('admin.logs.email', compact('logs', 'search', 'statusFilter'));
    }

    public function activityLog(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $search = $request->get('search', '');
        $statusFilter = $request->get('status', '');
        $flowFilter = $request->get('flow', '');
        $selectedUserId = $request->get('user_id', '');

        $query = CheckoutActivityLog::query()->orderBy('created_at', 'desc');

        // When a user is selected, filter strictly by that user_id; otherwise show all logs
        if ($selectedUserId !== '' && $selectedUserId !== null) {
            $query->where('user_id', $selectedUserId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('stripe_payment_intent_id', 'like', '%' . $search . '%')
                    ->orWhere('action', 'like', '%' . $search . '%')
                    ->orWhere('message', 'like', '%' . $search . '%');
            });
        }

        if ($statusFilter !== '') {
            $query->where('status', $statusFilter);
        }

        if ($flowFilter !== '') {
            $query->where('flow', $flowFilter);
        }

        $logs = $query->get();

        // Exclude admin users from the dropdown; only show client users
        $users = \App\Models\User::where('role', '!=', 'admin')
            ->orderBy('email')
            ->get(['id', 'email']);

        return view('admin.logs.activity', [
            'logs' => $logs,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'flowFilter' => $flowFilter,
            'users' => $users,
            'selectedUserId' => $selectedUserId,
        ]);
    }
}
