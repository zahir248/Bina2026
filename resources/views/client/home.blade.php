@extends('layouts.client.app')

@section('title', 'BINA')

@section('content')
    <!-- Hero Carousel Section -->
    <section class="hero-carousel">
        <button class="carousel-btn carousel-btn-left" aria-label="Previous slide"></button>
        <div class="carousel-container">
            <div class="hero-slide active">
                <img src="https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=1920&h=400&fit=crop" alt="Concert Banner" class="hero-banner-image">
            </div>
            <div class="hero-slide">
                <img src="https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=1920&h=400&fit=crop" alt="Festival Banner" class="hero-banner-image">
            </div>
            <div class="hero-slide">
                <img src="https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=1920&h=400&fit=crop" alt="Music Event Banner" class="hero-banner-image">
            </div>
            <div class="hero-slide">
                <img src="https://images.unsplash.com/photo-1478147427282-58a87a120781?w=1920&h=400&fit=crop" alt="Live Performance Banner" class="hero-banner-image">
            </div>
        </div>
        <button class="carousel-btn carousel-btn-right" aria-label="Next slide"></button>
        <div class="carousel-dots">
            <span class="dot active" data-slide="0"></span>
            <span class="dot" data-slide="1"></span>
            <span class="dot" data-slide="2"></span>
            <span class="dot" data-slide="3"></span>
        </div>
    </section>

    <!-- Countdown Section -->
    <section class="countdown-section">
        <div class="container">
            <div class="countdown-content">
                <div id="countdown" class="countdown-timer"></div>
            </div>
        </div>
    </section>

    <!-- Main Content Section - Event listing by category -->
    <section class="main-content-section" id="events-section">
        <div class="content-layout">
            <!-- Left Sidebar - Event Categories -->
            <aside class="sidebar sidebar-left">
                <h2 class="sidebar-title">Event Category</h2>
                <ul class="category-list" id="category-list">
                    <!-- Categories will be loaded dynamically -->
                    <li class="category-item">
                        <span class="category-loading">Loading categories...</span>
                    </li>
                </ul>
            </aside>

            <!-- Main Content - Events List -->
            <main class="main-content">
                <div class="events-list" id="events-list">
                    <!-- Events will be loaded dynamically -->
                    <div class="event-loading" style="text-align: center; padding: 2rem;">
                        <p>Loading events...</p>
                        </div>
                </div>
            </main>

            <!-- Right Sidebar - Calendar -->
            <aside class="sidebar sidebar-right">
                <div class="calendar-widget">
                    <div class="calendar-header-section">
                        <div class="calendar-header-content">
                            <div class="calendar-month-year" id="calendar-month-year"></div>
                            <div class="calendar-day-name" id="calendar-day-name"></div>
                            <div class="calendar-day-number" id="calendar-day-number"></div>
                        </div>
                        <div class="calendar-nav-arrows">
                            <button class="calendar-nav-btn">◀</button>
                            <button class="calendar-nav-btn">▶</button>
                        </div>
                    </div>
                    <div class="upcoming-events-list" id="upcoming-events-list">
                        <!-- Upcoming events will be loaded dynamically -->
                        <div style="text-align: center; padding: 1rem;">
                            <p>Loading upcoming events...</p>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Base URL for links (required when app is in subdirectory e.g. /Bina2026)
    const baseUrl = '{{ url("/") }}'.replace(/\/$/, '');
    const homeUrl = '{{ route("home") }}';
    const initialCategoryId = {{ request('category') ? (int) request('category') : 'null' }};
    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Fetch event categories
    const categoryList = document.getElementById('category-list');
    
    fetch('{{ route("api.event-categories") }}')
        .then(response => response.json())
        .then(categories => {
            // Clear loading message
            categoryList.innerHTML = '';
            
            if (categories.length === 0) {
                categoryList.innerHTML = '<li class="category-item"><span>No categories available</span></li>';
                return;
            }
            
            // Render categories as links to event page (home) with category filter
            categories.forEach(category => {
                const categoryItem = document.createElement('li');
                categoryItem.className = 'category-item' + (initialCategoryId === category.id ? ' active' : '');
                const categoryLink = document.createElement('a');
                categoryLink.href = homeUrl + (homeUrl.indexOf('?') !== -1 ? '&' : '?') + 'category=' + category.id;
                categoryLink.className = 'category-link';
                categoryLink.innerHTML = `
                    <span class="category-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" fill="currentColor"/>
                        </svg>
                    </span>
                    <span>${escapeHtml(category.name)}</span>
                `;
                categoryItem.appendChild(categoryLink);
                categoryList.appendChild(categoryItem);
            });
        })
        .catch(error => {
            console.error('Error fetching categories:', error);
            categoryList.innerHTML = '<li class="category-item"><span>Error loading categories</span></li>';
        });
    
    // Fetch events (with optional category filter from URL)
    const eventsList = document.getElementById('events-list');
    const eventsUrl = initialCategoryId
        ? '{{ route("api.events") }}?category_id=' + initialCategoryId
        : '{{ route("api.events") }}';
    
    fetch(eventsUrl)
        .then(response => response.json())
        .then(events => {
            // Clear loading message
            eventsList.innerHTML = '';
            
            if (events.length === 0) {
                eventsList.innerHTML = '<div style="text-align: center; padding: 2rem;"><p>No events available</p></div>';
                return;
            }
            
            // Render events
            events.forEach(event => {
                const eventLink = document.createElement('a');
                eventLink.href = `${baseUrl}/event/${event.slug}`;
                eventLink.className = 'event-item-link';
                
                // Default image if no image provided
                const eventImage = event.image || 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=200&h=200&fit=crop';
                
                // Format price
                const priceDisplay = event.min_price ? `RM ${event.min_price}` : 'Price TBA';
                
                eventLink.innerHTML = `
                    <div class="event-item">
                        <div class="event-item-image">
                            <img src="${escapeHtml(eventImage)}" alt="${escapeHtml(event.name)}" class="event-image">
                            <div class="event-image-overlay"></div>
                            </div>
                        <div class="event-item-content">
                            ${event.category ? `<span class="event-category-badge">${escapeHtml(event.category)}</span>` : ''}
                            <h3 style="word-wrap: break-word; overflow-wrap: break-word; width: 100%;">${escapeHtml(event.name)}</h3>
                            <p class="event-meta">
                                <span class="event-meta-icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z" fill="currentColor"/>
                                    </svg>
                                </span>
                                ${escapeHtml(event.start_datetime)}
                                <span class="event-meta-icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="currentColor"/>
                                        </svg>
                                    </span>
                                ${escapeHtml(event.location)}
                            </p>
                            ${event.description ? `<p class="event-description" style="text-align: justify;">${escapeHtml(event.description)}</p>` : ''}
                            <div class="event-item-footer">
                                <div class="event-price-info">
                                    <span class="event-price-label">Starting from</span>
                                    <span class="event-price">${escapeHtml(priceDisplay)}</span>
                                </div>
                                <span class="btn btn-primary btn-small">Buy Now</span>
                            </div>
                        </div>
                    </div>
                `;
                eventsList.appendChild(eventLink);
            });
        })
        .catch(error => {
            console.error('Error fetching events:', error);
            eventsList.innerHTML = '<div style="text-align: center; padding: 2rem;"><p>Error loading events</p></div>';
        });
    
    // Calendar state
    const now = new Date();
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                        'July', 'August', 'September', 'October', 'November', 'December'];
    const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    
    let currentMonth = now.getMonth() + 1; // 1-12
    let currentYear = now.getFullYear();
    
    // Function to update calendar display
    function updateCalendarDisplay() {
        document.getElementById('calendar-month-year').textContent = 
            `${monthNames[currentMonth - 1]} ${currentYear}`;
        document.getElementById('calendar-day-name').textContent = dayNames[now.getDay()];
        document.getElementById('calendar-day-number').textContent = now.getDate();
        
        // Fetch events for the current month
        loadEventsForMonth(currentMonth, currentYear);
    }
    
    // Function to load events for a specific month
    function loadEventsForMonth(month, year) {
        const upcomingEventsList = document.getElementById('upcoming-events-list');
        upcomingEventsList.innerHTML = '<div style="text-align: center; padding: 1rem;"><p>Loading events...</p></div>';
        
        fetch(`{{ route("api.events.upcoming") }}?month=${month}&year=${year}`)
            .then(response => response.json())
            .then(events => {
                // Clear loading message
                upcomingEventsList.innerHTML = '';
                
                if (events.length === 0) {
                    upcomingEventsList.innerHTML = '<div style="text-align: center; padding: 1rem;"><p>No events for this month</p></div>';
                    return;
                }
                
                // Render upcoming events
                events.forEach(event => {
                    const eventItem = document.createElement('div');
                    eventItem.className = 'upcoming-event-item';
                    
                    // Format the date from the datetime
                    const eventDate = new Date(event.start_datetime);
                    const dayAbbr = event.day_abbr || eventDate.toLocaleDateString('en-US', { weekday: 'short' });
                    const dayNumber = event.day_number || eventDate.getDate();
                    
                    eventItem.innerHTML = `
                            <div class="event-date-bar">
                            <div class="event-day-abbr">${escapeHtml(dayAbbr)}</div>
                            <div class="event-day-number">${escapeHtml(dayNumber)}</div>
                            </div>
                            <div class="upcoming-event-info">
                            <div class="upcoming-event-title">${escapeHtml(event.name)}</div>
                                <div class="upcoming-event-details">
                                ${event.location ? `
                                    <div style="display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.25rem;">
                                    <span class="event-location-icon">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="currentColor"/>
                                        </svg>
                                    </span>
                                        <span>${escapeHtml(event.location)}</span>
                                </div>
                                ` : ''}
                                ${event.time ? `
                                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                                    <span class="event-time-icon">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z" fill="currentColor"/>
                                        </svg>
                                    </span>
                                        <span>${escapeHtml(event.time)}</span>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    `;
                    upcomingEventsList.appendChild(eventItem);
                });
            })
            .catch(error => {
                console.error('Error fetching upcoming events:', error);
                upcomingEventsList.innerHTML = '<div style="text-align: center; padding: 1rem;"><p>Error loading upcoming events</p></div>';
            });
    }
    
    // Initialize calendar with current month
    updateCalendarDisplay();
    
    // Add navigation button functionality
    const navButtons = document.querySelectorAll('.calendar-nav-btn');
    navButtons.forEach((button, index) => {
        button.addEventListener('click', function() {
            if (index === 0) {
                // Previous month
                currentMonth--;
                if (currentMonth < 1) {
                    currentMonth = 12;
                    currentYear--;
                }
            } else {
                // Next month
                currentMonth++;
                if (currentMonth > 12) {
                    currentMonth = 1;
                    currentYear++;
                }
            }
            updateCalendarDisplay();
        });
    });
});
</script>
@endpush
