@extends('layouts.client.app')

@section('title', 'BINA')

@section('content')
    <!-- Hero Section with Overlay Content -->
    <section class="hero-carousel event-hero">
        <div class="carousel-container">
            <div class="hero-slide active">
                <img src="{{ $event['image'] }}" alt="{{ $event['title'] }}" class="hero-banner-image">
            </div>
        </div>
        
        <!-- Top Action Buttons Overlay -->
        <div class="event-action-buttons-overlay">
            <div class="container">
                <div class="action-buttons-group">
                    <a href="#" class="btn-buy-ticket" id="hero-buy-ticket-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-2 14H6V6h12v12z" fill="currentColor"/>
                            <path d="M8 8h8v1H8V8zm0 3h8v1H8v-1zm0 3h5v1H8v-1z" fill="currentColor"/>
                            <circle cx="18" cy="9" r="1" fill="currentColor"/>
                            <circle cx="18" cy="15" r="1" fill="currentColor"/>
                        </svg>
                        Buy Ticket Now
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Event Content -->
    <div class="event-show-container">
        <div class="container">
            <div class="event-show-content">
                <!-- Event Title -->
                <h1 class="event-show-title">{{ $event['title'] }}</h1>

                <!-- Navigation Tabs -->
                <div class="event-tabs">
                    <button class="event-tab active" data-tab="ticket">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-2 14H6V6h12v12z" fill="currentColor"/>
                            <path d="M8 8h8v1H8V8zm0 3h8v1H8v-1zm0 3h5v1H8v-1z" fill="currentColor"/>
                            <circle cx="18" cy="9" r="1" fill="currentColor"/>
                            <circle cx="18" cy="15" r="1" fill="currentColor"/>
                        </svg>
                        Ticket
                    </button>
                    <button class="event-tab" data-tab="details">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" fill="currentColor"/>
                        </svg>
                        Tentative
                    </button>
                    <button class="event-tab" data-tab="organiser">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" fill="currentColor"/>
                        </svg>
                        Personnel
                    </button>
                </div>

                <!-- Tab Content Areas -->
                <!-- Ticket Tab Content -->
                <div class="tab-content active" data-tab-content="ticket">
                    <div class="event-main-layout">
                        <!-- Left Section - Event Poster -->
                        <div class="event-poster-section">
                            <div class="event-poster-container">
                                @if(count($event['images']) > 1)
                                    <!-- Carousel Navigation Arrows -->
                                    <button class="poster-carousel-nav poster-carousel-nav-prev" aria-label="Previous image">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                    <button class="poster-carousel-nav poster-carousel-nav-next" aria-label="Next image">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                @endif
                                <div class="poster-slides-wrapper">
                                    @foreach($event['images'] as $index => $image)
                                        <div class="poster-slide {{ $index === 0 ? 'active' : '' }}" data-slide-index="{{ $index }}">
                                            <img src="{{ $image }}" alt="{{ $event['title'] }}" class="event-poster-image">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Right Sidebar - Event Information -->
                        <div class="event-info-sidebar">
                            <!-- Scan and Share -->
                            <div class="scan-share-card">
                                <div class="qr-code-container">
                                    <div class="qr-code-wrapper">
                                        <img id="qr-code-image" alt="QR Code" style="display: none;">
                                        <canvas id="qr-code-canvas" width="120" height="120" style="display: block;"></canvas>
                                    </div>
                                </div>
                                <div class="scan-share-content">
                                    <h3 class="scan-share-title">Scan and Share</h3>
                                    <p class="scan-share-text">
                                        Scan the qr to open and share in mobile, or 
                                        <a href="#" class="scan-share-link">
                                            Click Here
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: inline-block; vertical-align: middle; margin-left: 4px;">
                                                <path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z" fill="currentColor"/>
                                            </svg>
                                        </a>
                                        to copy the shareable link
                                    </p>
                                    <p class="scan-share-url">{{ url()->current() }}</p>
                                </div>
                            </div>
                            
                            <!-- Location Card -->
                            @if($event['location'])
                            <div class="location-card">
                                <div class="location-icon">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="#DC2626"/>
                                        <circle cx="12" cy="9" r="2.5" fill="white"/>
                                    </svg>
                                </div>
                                <div class="location-content">
                                    <h3 class="location-title">{{ $event['location'] }}</h3>
                                </div>
                                @if($event['google_maps_address'] || $event['waze_location_address'])
                                    <button type="button" 
                                            class="location-map-btn"
                                            onclick="openShareLocationModal('{{ addslashes($event['google_maps_address'] ?? '') }}', '{{ addslashes($event['waze_location_address'] ?? '') }}')">
                                        Map
                                    </button>
                                @endif
                            </div>
                            
                            <!-- Location Info Items -->
                            <div class="location-info-section">
                                <div class="location-info-item">
                                    <div class="location-info-icon">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z" fill="currentColor"/>
                                        </svg>
                                    </div>
                                    <span class="location-info-text">{{ $event['date_time_formatted'] }}</span>
                                </div>
                                @if($event['category'])
                                <div class="location-info-item">
                                    <div class="location-info-icon">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M17.63 5.84C17.27 5.33 16.67 5 16 5L5 5.01C3.9 5.01 3 5.9 3 7v10c0 1.1.9 1.99 2 1.99L16 19c.67 0 1.27-.33 1.63-.84L22 12l-4.37-6.16z" fill="currentColor"/>
                                        </svg>
                                    </div>
                                    <span class="location-info-text location-info-tag">#{{ $event['category'] }}</span>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Tickets List (Cards) -->
                    <div class="event-tickets-section" id="ticket-information-section">
                        <div class="event-tickets-inner">
                            @if(!empty($event['tickets']) && count($event['tickets']) > 0)
                                <div class="ticket-information-title">
                                    <svg class="ticket-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-2 14H6V6h12v12z" fill="currentColor"/>
                                        <path d="M8 8h8v1H8V8zm0 3h8v1H8v-1zm0 3h5v1H8v-1z" fill="currentColor"/>
                                        <circle cx="18" cy="9" r="1" fill="currentColor"/>
                                        <circle cx="18" cy="15" r="1" fill="currentColor"/>
                                    </svg>
                                    <span class="ticket-information-text">Ticket Information</span>
                                </div>
                                <div class="ticket-info-header">
                                    <span class="ticket-info-label">Event Date :</span>
                                    <span class="ticket-info-value">{{ $event['date_time_formatted'] }}</span>
                                </div>
                                <form id="ticket-selection-form" method="POST" action="{{ route('cart.store') }}">
                                    @csrf
                                    <input type="hidden" name="event_id" value="{{ $event['id'] }}">
                                <div class="event-tickets-list">
                                    @foreach($event['tickets'] as $ticket)
                                        <div class="ticket-card ticket-card-v2">
                                            <div class="ticket-card-top">
                                                <div class="ticket-top-left">
                                                    <h3 class="ticket-top-title">{{ $ticket['name'] }}</h3>

                                                    @if(!empty($ticket['quantity_discount']) && is_array($ticket['quantity_discount']) && count($ticket['quantity_discount']) > 0)
                                                        <div class="ticket-quantity-discount-table">
                                                            <table class="quantity-discount-table">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Quantity</th>
                                                                        <th>Price (RM)</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($ticket['quantity_discount'] as $discount)
                                                                        <tr>
                                                                            <td>
                                                                                @if(isset($discount['type']))
                                                                                    @if($discount['type'] === 'range')
                                                                                        {{ $discount['min_quantity'] ?? 0 }} - {{ $discount['max_quantity'] ?? 0 }}
                                                                                    @elseif($discount['type'] === 'more_than')
                                                                                        More than {{ $discount['quantity'] ?? 0 }}
                                                                                    @elseif($discount['type'] === 'less_than')
                                                                                        Less than {{ $discount['quantity'] ?? 0 }}
                                                                                    @else
                                                                                        Exactly {{ $discount['quantity'] ?? 0 }}
                                                                                    @endif
                                                                                @else
                                                                                    Exactly {{ $discount['quantity'] ?? 0 }}
                                                                                @endif
                                                                            </td>
                                                                            <td>{{ number_format((float)($discount['price'] ?? 0), 2) }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif

                                                    @php
                                                        $descLines = [];
                                                        if (!empty($ticket['description'])) {
                                                            $descLines = preg_split('/\r\n|\r|\n/', trim($ticket['description']));
                                                            $descLines = array_values(array_filter(array_map('trim', $descLines)));
                                                        }
                                                    @endphp

                                                    @if(!empty($descLines))
                                                        <ul class="ticket-top-bullets">
                                                            @foreach($descLines as $line)
                                                                <li>{{ ltrim($line, "- \t") }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </div>

                                                <div class="ticket-top-right">
                                                    @if(!empty($ticket['image']))
                                                        <div class="ticket-card-image">
                                                            <img src="{{ $ticket['image'] }}" alt="{{ $ticket['name'] }}">
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="ticket-card-divider"></div>

                                            <div class="ticket-card-bottom">
                                                <div class="ticket-bottom-price">
                                                    RM {{ number_format((float)($ticket['price'] ?? 0), 2) }}
                                                    @if(!empty($ticket['remarks']))
                                                        <span class="ticket-bottom-remark">{{ $ticket['remarks'] }}</span>
                                                    @endif
                                                </div>
                                                <div class="ticket-bottom-qty">
                                                    @php
                                                        $isInactive = isset($ticket['status']) && $ticket['status'] === 'inactive';
                                                        $isOutOfStock = isset($event['ticket_stock']) && $event['ticket_stock'] == 0;
                                                        $isNotLoggedIn = !auth()->check();
                                                        $showStatusText = $isInactive || $isOutOfStock;
                                                        
                                                        if ($showStatusText) {
                                                            $statusText = $isInactive ? 'Unavailable' : 'Out of Stock';
                                                        } else {
                                                            $maxQuantity = 10;
                                                            if (isset($event['ticket_stock']) && $event['ticket_stock'] !== null) {
                                                                $maxQuantity = max(0, min(10, (int)$event['ticket_stock']));
                                                            }
                                                        }
                                                    @endphp
                                                    @if($showStatusText)
                                                        <div class="ticket-qty-status">{{ $statusText }}</div>
                                                    @else
                                                        <select class="ticket-qty-select" name="ticket_qty[{{ $ticket['id'] }}]" @if($isNotLoggedIn) disabled @endif>
                                                            @for($i = 0; $i <= $maxQuantity; $i++)
                                                                <option value="{{ $i }}">{{ $i }}</option>
                                                            @endfor
                                                        </select>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @php
                                    $hasActiveTickets = !empty($event['tickets']) && collect($event['tickets'])->contains(function($ticket) {
                                        return isset($ticket['status']) && $ticket['status'] === 'active';
                                    });
                                    $hasStock = !isset($event['ticket_stock']) || $event['ticket_stock'] > 0;
                                    $isNotLoggedIn = !auth()->check();
                                @endphp
                                @if($hasActiveTickets && $hasStock)
                                    <div class="buy-ticket-section">
                                        <button type="submit" id="buy-ticket-now-btn" class="btn-buy-ticket @if($isNotLoggedIn) disabled @endif" @if($isNotLoggedIn) disabled @endif>
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M7 18c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.15.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12L8.1 13h7.45c.75 0 1.41-.41 1.75-1.03L21.7 4H5.21l-.94-2H1zm16 16c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" fill="currentColor"/>
                                            </svg>
                                            Add to Cart
                                        </button>
                                        <p class="buy-ticket-disclaimer @if($isNotLoggedIn) buy-ticket-login-message @endif">
                                            @if($isNotLoggedIn)
                                                Please log in to start your purchase.
                                            @else
                                                Price excluding Processing Fee and Surcharge, if any.
                                            @endif
                                        </p>
                                    </div>
                                @endif
                                </form>
                            @else
                                <div class="tab-content-placeholder">
                                    <p>No tickets available for this event.</p>
                                </div>
                            @endif

                            <!-- More Info Section -->
                            <div class="more-info-section">
                                <h3 class="more-info-title">More info on this event</h3>
                                <div class="more-info-buttons">
                                    <button class="more-info-btn" data-tab="details">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/>
                                            <path d="M12 16v-4M12 8h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                        <span>Event Tentative</span>
                                    </button>
                                    <button class="more-info-btn" data-tab="organiser">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" fill="currentColor"/>
                                        </svg>
                                        <span>Event Personnel</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Details Tab Content -->
                <div class="tab-content" data-tab-content="details">
                    <div class="schedule-content">
                        @if(count($event['schedules']) > 0)
                            @php
                                $groupedSchedules = $event['schedules']->groupBy(function($schedule) {
                                    return $schedule['session'] ?: 'other';
                                });
                            @endphp
                            
                            @foreach($groupedSchedules as $sessionName => $schedules)
                                @if($sessionName !== 'other')
                                    <div class="schedule-session-header">
                                        SESSION {{ $sessionName }}
                                    </div>
                                @endif
                                
                                @foreach($schedules as $schedule)
                                    <div class="schedule-item">
                                        <div class="schedule-time">
                                            {{ $schedule['time_range'] }}
                                        </div>
                                        <div class="schedule-line"></div>
                                        <div class="schedule-details">
                                            <h3 class="schedule-name">{{ $schedule['name'] }}</h3>
                                            @if($schedule['description'])
                                                <p class="schedule-description">{!! nl2br(e($schedule['description'])) !!}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach
                        @else
                            <div class="tab-content-placeholder">
                                <p>No schedule information available for this event.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Personnel Tab Content -->
                <div class="tab-content" data-tab-content="organiser">
                    <div class="personnel-content">
                        @if(count($event['personnel']) > 0)
                            <div class="personnel-grid">
                                @foreach($event['personnel'] as $person)
                                    <div class="personnel-card">
                                        @if($person['image'])
                                            <a href="{{ $person['image'] }}" target="_blank" rel="noopener noreferrer" class="personnel-image-link">
                                                <div class="personnel-image">
                                                    <img src="{{ $person['image'] }}" alt="{{ $person['name'] }}">
                                                </div>
                                            </a>
                                        @else
                                            <div class="personnel-image personnel-image-placeholder">
                                                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" fill="currentColor"/>
                                                </svg>
                                            </div>
                                        @endif
                                        <div class="personnel-info">
                                            <h3 class="personnel-name">{{ $person['name'] }}</h3>
                                            @if($person['role'])
                                                <span class="personnel-role-badge personnel-role-badge-{{ $person['role'] }}">{{ ucfirst($person['role']) }}</span>
                                            @endif
                                            @if($person['position'])
                                                <p class="personnel-position">{!! nl2br(e($person['position'])) !!}</p>
                                            @endif
                                            @if($person['company'])
                                                <p class="personnel-company">{!! nl2br(e($person['company'])) !!}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="tab-content-placeholder">
                                <p>No personnel information available for this event.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Action Buttons -->
    <div class="floating-actions">
        <button type="button" class="floating-btn" aria-label="Ticket" data-floating-tab="ticket">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-2 14H6V6h12v12z" fill="currentColor"/>
                <path d="M8 8h8v1H8V8zm0 3h8v1H8v-1zm0 3h5v1H8v-1z" fill="currentColor"/>
                <circle cx="18" cy="9" r="1" fill="currentColor"/>
                <circle cx="18" cy="15" r="1" fill="currentColor"/>
            </svg>
        </button>
        <button type="button" class="floating-btn" aria-label="Tentative" data-floating-tab="details">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" fill="currentColor"/>
            </svg>
        </button>
        <button type="button" class="floating-btn" aria-label="Personnel" data-floating-tab="organiser">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" fill="currentColor"/>
            </svg>
        </button>
    </div>

    <!-- Share Location Modal -->
    <div class="modal-overlay" id="shareLocationModal" style="display: none;">
        <div class="modal-container share-location-modal">
            <div class="modal-header share-location-header">
                <div class="share-location-title-wrapper">
                    <svg class="share-location-pin-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="#EC4899"/>
                        <circle cx="12" cy="9" r="2.5" fill="white"/>
                    </svg>
                    <h5 class="modal-title share-location-title">Share This Location</h5>
                </div>
                <button type="button" class="modal-close-btn" onclick="closeShareLocationModal()" aria-label="Close">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" fill="currentColor"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body share-location-body">
                <div class="share-location-buttons">
                    <a href="#" id="waze-link" target="_blank" rel="noopener noreferrer" class="share-location-btn share-location-btn-waze">
                        <img src="https://img.icons8.com/color/48/waze.png" alt="Waze" class="share-location-btn-icon" style="width: 24px; height: 24px; filter: brightness(0) invert(1);">
                        <span>Waze</span>
                    </a>
                    <a href="#" id="google-maps-link" target="_blank" rel="noopener noreferrer" class="share-location-btn share-location-btn-google">
                        <svg class="share-location-btn-icon" width="24" height="24" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M24 4c-7.732 0-14 6.268-14 14 0 10.5 14 26 14 26s14-15.5 14-26c0-7.732-6.268-14-14-14zm0 18.5c-2.485 0-4.5-2.015-4.5-4.5s2.015-4.5 4.5-4.5 4.5 2.015 4.5 4.5-2.015 4.5-4.5 4.5z" fill="white"/>
                        </svg>
                        <span>Google Maps</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(function() {
    const eventUrl = '{{ url()->current() }}';
    
    function generateQRCode() {
        const qrWrapper = document.querySelector('.qr-code-wrapper');
        const qrCanvas = document.getElementById('qr-code-canvas');
        const qrImage = document.getElementById('qr-code-image');
        
        if (!qrWrapper) {
            console.error('QR code wrapper not found');
            return;
        }
        
        // Clear existing content
        if (qrCanvas) qrCanvas.style.display = 'none';
        if (qrImage) qrImage.style.display = 'none';
        
        // Check if QRCode library is available
        if (typeof QRCode !== 'undefined') {
            // Use QRCode.js library
            try {
                const qr = new QRCode(qrWrapper, {
                    text: eventUrl,
                    width: 120,
                    height: 120,
                    colorDark: '#1F2937',
                    colorLight: '#FFFFFF',
                    correctLevel: QRCode.CorrectLevel.M
                });
                
                // Hide canvas and image, show the generated QR code
                if (qrCanvas) qrCanvas.style.display = 'none';
                if (qrImage) qrImage.style.display = 'none';
            } catch (error) {
                console.error('Error generating QR code:', error);
                qrWrapper.innerHTML = '<div style="text-align: center; padding: 20px; color: #6B7280; font-size: 0.8rem;">QR Code Error</div>';
            }
        } else {
            // Try alternative: use qrcode library (different package)
            if (typeof qrcode !== 'undefined') {
                qrcode.toDataURL(eventUrl, {
                    width: 120,
                    margin: 1,
                    color: {
                        dark: '#1F2937',
                        light: '#FFFFFF'
                    }
                }, function (error, url) {
                    if (error) {
                        console.error('Error generating QR code:', error);
                        qrWrapper.innerHTML = '<div style="text-align: center; padding: 20px; color: #6B7280; font-size: 0.8rem;">QR Code Error</div>';
                        return;
                    }
                    if (qrImage) {
                        qrImage.src = url;
                        qrImage.style.display = 'block';
                    }
                });
            } else {
                // Last resort: use online QR code API
                const qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' + encodeURIComponent(eventUrl);
                if (qrImage) {
                    qrImage.src = qrApiUrl;
                    qrImage.style.display = 'block';
                    if (qrCanvas) qrCanvas.style.display = 'none';
                } else {
                    qrWrapper.innerHTML = '<img src="' + qrApiUrl + '" alt="QR Code" style="width: 120px; height: 120px;">';
                }
            }
        }
    }
    
    function init() {
        // Wait a bit for library to load
        setTimeout(function() {
            if (typeof QRCode !== 'undefined' || typeof qrcode !== 'undefined') {
                generateQRCode();
            } else {
                // Use online API as fallback
                generateQRCode();
            }
        }, 500);
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

// Function to show toast notification
function showToast(message, type) {
    type = type || 'success';
    
    // Remove existing toast if any
    const existingToast = document.getElementById('dynamic-toast');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.id = 'dynamic-toast';
    toast.className = 'toast toast-' + type;
    
    const iconSvg = type === 'success' 
        ? '<svg class="toast-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        : '<svg class="toast-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
    
    toast.innerHTML = `
        <div class="toast-content">
            ${iconSvg}
            <span class="toast-message">${message}</span>
        </div>
    `;
    
    // Append to body
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(function() {
        toast.classList.add('show');
    }, 100);
    
    // Hide toast after 3 seconds
    setTimeout(function() {
        toast.classList.remove('show');
        setTimeout(function() {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 3000);
}

// Share Location Modal functions
function openShareLocationModal(googleMapsUrl, wazeUrl) {
    const modal = document.getElementById('shareLocationModal');
    const wazeLink = document.getElementById('waze-link');
    const googleMapsLink = document.getElementById('google-maps-link');
    
    if (modal && wazeLink && googleMapsLink) {
        // Set links
        if (wazeUrl && wazeUrl.trim() !== '') {
            wazeLink.href = wazeUrl;
            wazeLink.target = '_blank';
            wazeLink.rel = 'noopener noreferrer';
            wazeLink.style.display = 'flex';
            wazeLink.onclick = function(e) {
                e.stopPropagation();
                window.open(wazeUrl, '_blank', 'noopener,noreferrer');
                closeShareLocationModal();
                return false;
            };
        } else {
            wazeLink.style.display = 'none';
        }
        
        if (googleMapsUrl && googleMapsUrl.trim() !== '') {
            googleMapsLink.href = googleMapsUrl;
            googleMapsLink.target = '_blank';
            googleMapsLink.rel = 'noopener noreferrer';
            googleMapsLink.style.display = 'flex';
            googleMapsLink.onclick = function(e) {
                e.stopPropagation();
                window.open(googleMapsUrl, '_blank', 'noopener,noreferrer');
                closeShareLocationModal();
                return false;
            };
        } else {
            googleMapsLink.style.display = 'none';
        }
        
        // Show modal
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeShareLocationModal() {
    const modal = document.getElementById('shareLocationModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Carousel functionality for poster section
document.addEventListener('DOMContentLoaded', function() {
    // Close modal when clicking outside
    const modal = document.getElementById('shareLocationModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeShareLocationModal();
            }
        });
    }
    const posterSlides = document.querySelectorAll('.poster-slide');
    const posterPrevBtn = document.querySelector('.poster-carousel-nav-prev');
    const posterNextBtn = document.querySelector('.poster-carousel-nav-next');
    let currentPosterSlide = 0;
    
    if (posterSlides.length > 1) {
        function showPosterSlide(index) {
            posterSlides.forEach((slide, i) => {
                slide.classList.toggle('active', i === index);
            });
        }
        
        function nextPosterSlide() {
            currentPosterSlide = (currentPosterSlide + 1) % posterSlides.length;
            showPosterSlide(currentPosterSlide);
        }
        
        function prevPosterSlide() {
            currentPosterSlide = (currentPosterSlide - 1 + posterSlides.length) % posterSlides.length;
            showPosterSlide(currentPosterSlide);
        }
        
        if (posterNextBtn) {
            posterNextBtn.addEventListener('click', nextPosterSlide);
        }
        
        if (posterPrevBtn) {
            posterPrevBtn.addEventListener('click', prevPosterSlide);
        }
    }
    
    // Copy link functionality
    const shareLink = document.querySelector('.scan-share-link');
    const eventUrl = '{{ url()->current() }}';
    
    if (shareLink) {
        shareLink.addEventListener('click', function(e) {
            e.preventDefault();
            navigator.clipboard.writeText(eventUrl).then(function() {
                // Show success toast
                showToast('Content copied to clipboard', 'success');
            }).catch(function(err) {
                console.error('Failed to copy:', err);
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = eventUrl;
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    // Show success toast
                    showToast('Content copied to clipboard', 'success');
                } catch (err) {
                    console.error('Fallback copy failed:', err);
                    showToast('Failed to copy link', 'error');
                }
                document.body.removeChild(textArea);
            });
        });
    }

    // Handle hero "Buy Ticket Now" button click
    const heroBuyTicketBtn = document.getElementById('hero-buy-ticket-btn');
    const ticketSection = document.getElementById('ticket-information-section');
    
    if (heroBuyTicketBtn) {
        heroBuyTicketBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (ticketSection) {
                // Switch to ticket tab
                const ticketTab = document.querySelector('.event-tab[data-tab="ticket"]');
                const tabContents = document.querySelectorAll('.tab-content');
                const ticketTabContent = document.querySelector('.tab-content[data-tab-content="ticket"]');
                
                if (ticketTab && ticketTabContent) {
                    // Remove active class from all tabs
                    document.querySelectorAll('.event-tab').forEach(t => t.classList.remove('active'));
                    
                    // Add active class to ticket tab
                    ticketTab.classList.add('active');
                    
                    // Hide all tab contents
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Show ticket tab content
                    ticketTabContent.classList.add('active');
                    
                    // Scroll to ticket information section
                    setTimeout(function() {
                        ticketSection.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }, 100);
                }
            }
        });
    }

    // Function to switch tabs
    function switchToTab(targetTab) {
        if (!targetTab) return;
        
        const tabs = document.querySelectorAll('.event-tab');
        const tabContents = document.querySelectorAll('.tab-content');
        const eventTabs = document.querySelector('.event-tabs');
        
        // Remove active class from all tabs
        tabs.forEach(t => t.classList.remove('active'));
        
        // Add active class to target tab
        const targetTabBtn = document.querySelector(`.event-tab[data-tab="${targetTab}"]`);
        if (targetTabBtn) {
            targetTabBtn.classList.add('active');
        }
        
        // Hide all tab contents
        tabContents.forEach(content => content.classList.remove('active'));
        
        // Show the selected tab content
        const selectedContent = document.querySelector(`.tab-content[data-tab-content="${targetTab}"]`);
        if (selectedContent) {
            selectedContent.classList.add('active');
            
            // Scroll to the top of the tab section
            setTimeout(function() {
                if (eventTabs) {
                    eventTabs.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }, 100);
        }
    }

    // Handle "More Info" buttons click
    const moreInfoButtons = document.querySelectorAll('.more-info-btn');
    moreInfoButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            switchToTab(targetTab);
        });
    });

    // Handle floating action buttons click using event delegation
    const floatingActions = document.querySelector('.floating-actions');
    
    if (floatingActions) {
        floatingActions.addEventListener('click', function(e) {
            const btn = e.target.closest('.floating-btn[data-floating-tab]');
            if (!btn) return;
            
            e.preventDefault();
            e.stopPropagation();
            
            const targetTab = btn.getAttribute('data-floating-tab');
            
            if (!targetTab) return;
            
            // Switch to the tab
            switchToTab(targetTab);
            
            // For Ticket tab, scroll to ticket information section if it exists
            if (targetTab === 'ticket') {
                const ticketSection = document.getElementById('ticket-information-section');
                if (ticketSection) {
                    setTimeout(function() {
                        ticketSection.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }, 150);
                }
            }
        });
    }

    // Handle ticket selection form submission with validation
    const ticketForm = document.getElementById('ticket-selection-form');
    if (ticketForm) {
        ticketForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get all quantity selectors
            const quantitySelects = document.querySelectorAll('.ticket-qty-select');
            let hasSelectedTicket = false;
            
            // Check if at least one ticket has quantity > 0
            quantitySelects.forEach(select => {
                const quantity = parseInt(select.value) || 0;
                if (quantity > 0) {
                    hasSelectedTicket = true;
                }
            });
            
            // Show error if no tickets selected
            if (!hasSelectedTicket) {
                showToast('Please select at least one ticket', 'error');
                return false;
            }
            
            // Submit the form if tickets are selected
            this.submit();
        });
    }
});
</script>
@endpush

@endsection
