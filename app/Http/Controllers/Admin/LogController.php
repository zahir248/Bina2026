<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
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
}
