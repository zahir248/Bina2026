@if ($errors->any())
    <div class="toast toast-error" id="error-toast">
        <div class="toast-content">
            <svg class="toast-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                <line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <div class="toast-messages">
                @foreach ($errors->all() as $error)
                    <span class="toast-message">{{ $error }}</span>
                @endforeach
            </div>
        </div>
    </div>
@elseif (session('success') || session('error'))
    @php
        $toastType = session('error') ? 'error' : 'success';
        $toastMessage = session('error') ?? session('success');
    @endphp
    <div class="toast toast-{{ $toastType }}" id="message-toast">
        <div class="toast-content">
            @if ($toastType === 'success')
                <svg class="toast-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            @else
                <svg class="toast-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                    <line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            @endif
            <span class="toast-message">{{ $toastMessage }}</span>
        </div>
    </div>
@endif
