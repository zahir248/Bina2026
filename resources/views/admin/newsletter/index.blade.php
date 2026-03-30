@extends('layouts.admin.app')

@section('title', 'Newsletter')

@section('content')
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">Send newsletter</h3>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                Enter a subject and message. The message is sent as HTML to all subscribers ({{ number_format($totalSubscribers) }} total).
                You can use basic HTML tags such as <code>&lt;p&gt;</code>, <code>&lt;br&gt;</code>, <code>&lt;strong&gt;</code>, and links.
            </p>
            <form method="POST" action="{{ route('admin.newsletter.send') }}">
                @csrf
                <div class="mb-3">
                    <label for="newsletter_subject" class="form-label">Subject</label>
                    <input type="text"
                           name="subject"
                           id="newsletter_subject"
                           class="form-control @error('subject') is-invalid @enderror"
                           value="{{ old('subject') }}"
                           maxlength="255"
                           required>
                    @error('subject')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="newsletter_body" class="form-label">Email content (HTML)</label>
                    <textarea name="body"
                              id="newsletter_body"
                              class="form-control font-monospace @error('body') is-invalid @enderror"
                              rows="14"
                              required>{{ old('body') }}</textarea>
                    @error('body')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn-admin btn-admin-primary" {{ $totalSubscribers === 0 ? 'disabled' : '' }}>
                    <i class="bi bi-send"></i>
                    Send to all subscribers
                </button>
            </form>
        </div>
    </div>

    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">Subscribers</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.newsletter') }}" class="mb-4">
                <div class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label for="search" class="form-label small text-muted mb-1">Search email</label>
                        <input type="text" name="search" id="search" class="form-control" value="{{ $search }}" placeholder="Filter by email">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn-admin btn-admin-primary">Filter</button>
                        @if($search !== '')
                            <a href="{{ route('admin.newsletter') }}" class="btn-admin btn-admin-secondary ms-1">Clear</a>
                        @endif
                    </div>
                </div>
            </form>

            @if($subscribers->isEmpty())
                <p class="text-muted mb-0">No subscribers match your filter.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Subscribed</th>
                                <th style="width: 100px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subscribers as $subscriber)
                                <tr>
                                    <td>{{ $subscriber->email }}</td>
                                    <td class="text-muted small">{{ $subscriber->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.newsletter.subscribers.destroy', $subscriber) }}" onsubmit="return confirm('Remove this subscriber?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="admin-pagination mt-3">
                    {{ $subscribers->links('pagination.admin') }}
                </div>
            @endif
        </div>
    </div>
@endsection
