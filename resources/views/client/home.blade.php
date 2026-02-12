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
        <div class="content-layout" id="content-layout">
            <!-- Left Sidebar - Event Categories -->
            <aside class="sidebar sidebar-left" id="sidebar-left">
                <div class="sidebar-inner">
                    <h2 class="sidebar-title">Event Category</h2>
                    <ul class="category-list" id="category-list">
                        <!-- Categories will be loaded dynamically -->
                        <li class="category-item">
                            <span class="category-loading">Loading categories...</span>
                        </li>
                    </ul>
                </div>
                <button type="button" class="sidebar-toggle sidebar-toggle-left" aria-label="Collapse categories" title="Collapse categories" id="toggle-left">
                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                </button>
            </aside>

            <!-- Main Content - BINA Intro + Events List -->
            <main class="main-content">
                <!-- BINA 2025 Introduction Panel: images left, intro right -->
                <section class="bina-intro-panel" aria-labelledby="bina-intro-title">
                    <div class="bina-intro-images">
                        <div class="bina-intro-image bina-intro-image-main">
                            <img src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=600&h=400&fit=crop" alt="Construction and building industry">
                        </div>
                        <div class="bina-intro-image bina-intro-image-secondary">
                            <img src="https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=400&h=300&fit=crop" alt="Conference and collaboration">
                        </div>
                        <div class="bina-intro-image bina-intro-image-tertiary">
                            <img src="https://images.unsplash.com/photo-1560179707-f14e90ef3623?w=400&h=300&fit=crop" alt="Modern construction">
                        </div>
                    </div>
                    <div class="bina-intro-content">
                        <div class="bina-intro-header">
                            <p class="bina-intro-subheading">Introduction about</p>
                        <h2 id="bina-intro-title" class="bina-intro-title">BINA 2025</h2>
                        <p class="bina-intro-lead">BINA 2025 will showcase breakthrough solutions, foster high-impact discussions, and shape the next era of construction. Join us in celebrating five years of innovation—here industry meets transformation!</p>
                        </div>
                        <div class="bina-intro-cards">
                        <article class="bina-intro-card">
                            <div class="bina-intro-card-icon" aria-hidden="true">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z" fill="currentColor"/>
                                </svg>
                            </div>
                            <div class="bina-intro-card-content">
                                <h3 class="bina-intro-card-heading">Transforming ASEAN's construction landscape</h3>
                                <p>As part of BINA Conference at ICW 2025, MODULAR ASIA is a premier forum and exhibition dedicated to advancing Modular Technology, Modern Methods of Construction (MMC), and Industrialised Building Systems (IBS).</p>
                            </div>
                        </article>
                        <article class="bina-intro-card">
                            <div class="bina-intro-card-icon" aria-hidden="true">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M11.562 21.56a.38.38 0 0 1-.26-.1l-4.57-3.83a.38.38 0 0 1-.15-.3v-2.1c0-.21.17-.38.38-.38.21 0 .38.17.38.38v1.98l4.2 3.52 5.77-5.77-1.52-1.52-5.25 5.25-2.68-2.25V9.55c0-.21.17-.38.38-.38.21 0 .38.17.38.38v5.82c0 .12-.05.22-.15.3l-2.9 2.43c-.08.06-.17.1-.26.1zm7.38-5.5c-.21 0-.38-.17-.38-.38v-2.9l-2.35 1.96c-.12.1-.27.15-.43.15-.09 0-.18-.02-.26-.06l-5.16-2.58-2.47 1.23c-.18.09-.4.05-.54-.11l-1.52-1.52a.38.38 0 0 1 0-.54l5.77-5.77c.07-.07.16-.12.27-.12.1 0 .2.04.27.12l2.54 2.54 4.89-4.07c.15-.12.35-.15.53-.08l2.4.96c.2.08.33.28.33.5v3.84c0 .21-.17.38-.38.38zm.38-4.05l-1.8-.72-4.13 3.44-2.25-2.25 3.93-3.93 2.4 2.4.85 1.06zM3.5 11.22l2.4.96 4.13-3.44-2.54-2.54-4.89 4.07c-.15.12-.35.15-.53.08l-2.4-.96c-.2-.08-.33-.28-.33-.5V5.5c0-.21.17-.38.38-.38.21 0 .38.17.38.38v4.05l1.8.72 4.13-3.44 2.25 2.25-3.93 3.93-2.4-2.4-.85-1.06zm16.28 6.28l-5.25-5.25 1.07-1.07 5.25 5.25-1.07 1.07zm-12.56 0l-1.07-1.07 5.25-5.25 1.07 1.07-5.25 5.25z" fill="currentColor"/>
                                </svg>
                            </div>
                            <div class="bina-intro-card-content">
                                <h3 class="bina-intro-card-heading">Where expertise meets business growth</h3>
                                <p>As part of BINA 2025 at ICW, Facility Management Engagement Day goes beyond a traditional conference—it's a dynamic platform for industry leaders, innovators, and businesses to exchange expertise, explore best practices, and unlock new opportunities in facility management.</p>
                            </div>
                        </article>
                    </div>
                        <div class="bina-intro-cta">
                            <a href="{{ url('/#about-bina') }}" class="bina-intro-read-more">
                                <span>Read more</span>
                                <i class="bi bi-arrow-right" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                </section>

                <!-- Stats cards: Speaker, Session, Sponsors -->
                <section class="bina-stats" aria-label="Event statistics">
                    <div class="bina-stats-grid">
                        <div class="bina-stat-card">
                            <span class="bina-stat-number">18<span class="bina-stat-plus">+</span></span>
                            <span class="bina-stat-label">Our Speaker</span>
                        </div>
                        <div class="bina-stat-card">
                            <span class="bina-stat-number">3<span class="bina-stat-plus">+</span></span>
                            <span class="bina-stat-label">Our Session</span>
                        </div>
                        <div class="bina-stat-card">
                            <span class="bina-stat-number">20<span class="bina-stat-plus">+</span></span>
                            <span class="bina-stat-label">Our Sponsors</span>
                        </div>
                    </div>
                </section>

                <!-- Events section: header + list -->
                <section class="events-section" id="events-list-section" aria-labelledby="events-section-title">
                    <header class="events-section-header">
                        <h2 id="events-section-title" class="events-section-title">Our Events</h2>
                        <p class="events-section-subtitle">This year we brings you industry leaders, innovators and decision-makers at the premier event of the year!</p>
                    </header>
                    <div class="events-list" id="events-list">
                        <!-- Events will be loaded dynamically -->
                        <div class="event-loading" style="text-align: center; padding: 2rem;">
                            <p>Loading events...</p>
                        </div>
                    </div>
                </section>

                <!-- Experience Our Offering -->
                <section class="offering-section" aria-labelledby="offering-title">
                    <h2 id="offering-title" class="offering-title">Experience our offering</h2>
                    <div class="offering-cards">
                        <div class="offering-card">
                            <div class="offering-card-icon" aria-hidden="true">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm0-14c-3.31 0-6 2.69-6 6s2.69 6 6 6 6-2.69 6-6-2.69-6-6-6zm0 10c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z" fill="currentColor"/>
                                </svg>
                            </div>
                            <p class="offering-card-text">Delivering our insight</p>
                        </div>
                        <div class="offering-card">
                            <div class="offering-card-icon" aria-hidden="true">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H5.17L4 17.17V4h16v12zM7 9h2v2H7V9zm4 0h2v2h-2V9zm4 0h2v2h-2V9z" fill="currentColor"/>
                                </svg>
                            </div>
                            <p class="offering-card-text">Networking potential</p>
                        </div>
                        <div class="offering-card">
                            <div class="offering-card-icon" aria-hidden="true">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" fill="currentColor"/>
                                </svg>
                            </div>
                            <p class="offering-card-text">Shaping the dialogue</p>
                        </div>
                    </div>
                </section>

                <!-- Why should attend -->
                <section class="why-attend-section" aria-labelledby="why-attend-title">
                    <h2 id="why-attend-title" class="why-attend-title">Why should attend?</h2>
                    <p class="why-attend-subtitle">This year we brings you industry leaders, innovators, and decision-makers at the premier event of the year!</p>
                    <div class="why-attend-grid">
                        <article class="why-attend-card">
                            <h3 class="why-attend-card-title">Construction professionals</h3>
                            <p class="why-attend-card-desc">Architects, engineers, contractors, and developers looking to stay ahead with cutting-edge technologies.</p>
                        </article>
                        <article class="why-attend-card">
                            <h3 class="why-attend-card-title">Technology providers</h3>
                            <p class="why-attend-card-desc">Explore innovations like modular, IBS, BIM, 3D printing, facility, maintenance and automation.</p>
                        </article>
                        <article class="why-attend-card">
                            <h3 class="why-attend-card-title">Innovation & opportunities</h3>
                            <p class="why-attend-card-desc">Explore new innovation and opportunities in current construction technology.</p>
                        </article>
                        <article class="why-attend-card">
                            <h3 class="why-attend-card-title">Economic & social impact</h3>
                            <p class="why-attend-card-desc">Learn about the economic and social impacts of advanced building technologies.</p>
                        </article>
                    </div>
                </section>
            </main>

            <!-- Right Sidebar - Calendar -->
            <aside class="sidebar sidebar-right" id="sidebar-right">
                <div class="sidebar-inner">
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
                </div>
                <button type="button" class="sidebar-toggle sidebar-toggle-right" aria-label="Collapse calendar" title="Collapse calendar" id="toggle-right">
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </button>
            </aside>
        </div>
    </section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Collapsible sidebars: restore state and attach toggles
    (function() {
        const layout = document.getElementById('content-layout');
        const sidebarLeft = document.getElementById('sidebar-left');
        const sidebarRight = document.getElementById('sidebar-right');
        const toggleLeft = document.getElementById('toggle-left');
        const toggleRight = document.getElementById('toggle-right');
        const storageKeyLeft = 'bina-sidebar-left-collapsed';
        const storageKeyRight = 'bina-sidebar-right-collapsed';

        function setLeftCollapsed(collapsed) {
            if (collapsed) {
                layout.classList.add('content-layout--left-collapsed');
                layout.classList.remove('content-layout--left-open');
                sidebarLeft.classList.add('sidebar-left--collapsed');
                sidebarLeft.classList.remove('sidebar-left--overlay');
                toggleLeft.setAttribute('aria-label', 'Expand categories');
                toggleLeft.setAttribute('title', 'Expand categories');
                try { localStorage.setItem(storageKeyLeft, '1'); } catch (e) {}
            } else {
                layout.classList.remove('content-layout--left-collapsed');
                layout.classList.add('content-layout--left-open');
                sidebarLeft.classList.remove('sidebar-left--collapsed');
                sidebarLeft.classList.add('sidebar-left--overlay');
                toggleLeft.setAttribute('aria-label', 'Collapse categories');
                toggleLeft.setAttribute('title', 'Collapse categories');
                try { localStorage.setItem(storageKeyLeft, '0'); } catch (e) {}
            }
        }

        function setRightCollapsed(collapsed) {
            if (collapsed) {
                layout.classList.add('content-layout--right-collapsed');
                layout.classList.remove('content-layout--right-open');
                sidebarRight.classList.add('sidebar-right--collapsed');
                sidebarRight.classList.remove('sidebar-right--overlay');
                toggleRight.setAttribute('aria-label', 'Expand calendar');
                toggleRight.setAttribute('title', 'Expand calendar');
                try { localStorage.setItem(storageKeyRight, '1'); } catch (e) {}
            } else {
                layout.classList.remove('content-layout--right-collapsed');
                layout.classList.add('content-layout--right-open');
                sidebarRight.classList.remove('sidebar-right--collapsed');
                sidebarRight.classList.add('sidebar-right--overlay');
                toggleRight.setAttribute('aria-label', 'Collapse calendar');
                toggleRight.setAttribute('title', 'Collapse calendar');
                try { localStorage.setItem(storageKeyRight, '0'); } catch (e) {}
            }
        }

        try {
            setLeftCollapsed(localStorage.getItem(storageKeyLeft) !== '0');
            setRightCollapsed(localStorage.getItem(storageKeyRight) !== '0');
        } catch (e) {
            setLeftCollapsed(true);
            setRightCollapsed(true);
        }

        if (toggleLeft) toggleLeft.addEventListener('click', function() {
            setLeftCollapsed(sidebarLeft.classList.contains('sidebar-left--collapsed') ? false : true);
        });
        if (toggleRight) toggleRight.addEventListener('click', function() {
            setRightCollapsed(sidebarRight.classList.contains('sidebar-right--collapsed') ? false : true);
        });
    })();

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
