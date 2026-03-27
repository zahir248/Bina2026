// Navbar: hide on scroll down, show on scroll up
(function() {
    var lastScrollY = 0;
    var scrollThreshold = 60;
    var ticking = false;

    function updateNavbar() {
        var navbar = document.querySelector('.navbar');
        if (!navbar) return;
        var currentScrollY = window.scrollY || window.pageYOffset;
        if (currentScrollY <= scrollThreshold) {
            navbar.classList.remove('navbar--hidden');
        } else if (currentScrollY > lastScrollY) {
            navbar.classList.add('navbar--hidden');
        } else {
            navbar.classList.remove('navbar--hidden');
        }
        lastScrollY = currentScrollY;
        ticking = false;
    }

    function onScroll() {
        if (!ticking) {
            requestAnimationFrame(updateNavbar);
            ticking = true;
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            lastScrollY = window.scrollY || window.pageYOffset;
            window.addEventListener('scroll', onScroll, { passive: true });
        });
    } else {
        lastScrollY = window.scrollY || window.pageYOffset;
        window.addEventListener('scroll', onScroll, { passive: true });
    }
})();

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href !== '#' && href.length > 1) {
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    });
});

// Event Show Page Tabs
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.event-tab');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Get the tab name
            const tabName = this.getAttribute('data-tab');
            
            // Hide all tab contents
            tabContents.forEach(content => {
                content.classList.remove('active');
            });
            
            // Show the selected tab content
            const selectedContent = document.querySelector(`.tab-content[data-tab-content="${tabName}"]`);
            if (selectedContent) {
                selectedContent.classList.add('active');
            }
        });
    });
});

// Carousel functionality
document.addEventListener('DOMContentLoaded', function() {
    let currentSlide = 0;
    const slides = document.querySelectorAll('.hero-slide');
    const totalSlides = slides.length;

    if (totalSlides === 0) return;

    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.classList.remove('active');
            if (i === index) {
                slide.classList.add('active');
            }
        });
        
        // Update dots
        const dots = document.querySelectorAll('.dot');
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === index);
        });
    }

    function nextSlide() {
        currentSlide = (currentSlide + 1) % totalSlides;
        showSlide(currentSlide);
    }

    function prevSlide() {
        currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
        showSlide(currentSlide);
    }

    // Initialize carousel
    showSlide(0);
    
    // Auto-play carousel
    setInterval(nextSlide, 5000);
    
    // Carousel navigation buttons
    const prevBtn = document.querySelector('.carousel-btn-left');
    const nextBtn = document.querySelector('.carousel-btn-right');
    
    if (prevBtn) {
        prevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            prevSlide();
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            nextSlide();
        });
    }
    
    // Dot navigation
    const dots = document.querySelectorAll('.dot');
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            currentSlide = index;
            showSlide(currentSlide);
        });
    });
});

// Countdown timer (supports multiple events: when one ends, continues to the next)
(function() {
    const countdownElement = document.getElementById('countdown');
    if (!countdownElement) return;

    let events = [];
    try {
        const dataEl = document.getElementById('countdown-events-data');
        if (dataEl && dataEl.textContent) events = JSON.parse(dataEl.textContent);
    } catch (e) {}
    const fallback = countdownElement.getAttribute('data-countdown-fallback') || '2026-06-15T00:00:00';

    let currentIndex = 0;
    let currentTarget = null;
    if (events.length > 0) {
        currentTarget = events[0].datetime;
    } else {
        currentTarget = fallback;
    }

    function updateCountdown() {
        if (!countdownElement) return;
        const targetDate = new Date(currentTarget).getTime();
        if (Number.isNaN(targetDate)) {
            countdownElement.innerHTML = 'Invalid countdown date.';
            return;
        }
        const now = new Date().getTime();
        const distance = targetDate - now;

        if (distance < 0) {
            // Using admin fallback (no events): show message and collapse
            if (events.length === 0) {
                countdownElement.innerHTML = 'Event has started!';
                var sectionFallback = countdownElement.closest('.countdown-section');
                if (sectionFallback) sectionFallback.style.display = 'none';
                return;
            }
            // Start time reached for an event: show "Event has started!" then check end time
            var currentEvent = events[currentIndex];
            var endTime = currentEvent && currentEvent.end ? new Date(currentEvent.end).getTime() : null;
            if (endTime !== null && now >= endTime) {
                // Event has ended: move to next event or collapse
                if (currentIndex + 1 < events.length) {
                    currentIndex++;
                    currentTarget = events[currentIndex].datetime;
                    return updateCountdown();
                }
                var section = countdownElement.closest('.countdown-section');
                if (section) section.style.display = 'none';
                return;
            }
            countdownElement.innerHTML = 'Event has started!';
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        const formatNumber = (num) => String(num).padStart(2, '0');

        countdownElement.innerHTML = `
            <div class="countdown-item">
                <div class="countdown-box">
                    <div class="countdown-flip">
                        <span class="countdown-number">${formatNumber(days)}</span>
                    </div>
                </div>
                <span class="countdown-label">DAYS</span>
            </div>
            <div class="countdown-item">
                <div class="countdown-box">
                    <div class="countdown-flip">
                        <span class="countdown-number">${formatNumber(hours)}</span>
                    </div>
                </div>
                <span class="countdown-label">HOURS</span>
            </div>
            <div class="countdown-item">
                <div class="countdown-box">
                    <div class="countdown-flip">
                        <span class="countdown-number">${formatNumber(minutes)}</span>
                    </div>
                </div>
                <span class="countdown-label">MINUTES</span>
            </div>
            <div class="countdown-item">
                <div class="countdown-box">
                    <div class="countdown-flip">
                        <span class="countdown-number">${formatNumber(seconds)}</span>
                    </div>
                </div>
                <span class="countdown-label">SECONDS</span>
            </div>
        `;
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);
})();

// Dropdown menu toggle on click + mobile navbar toggle
document.addEventListener('DOMContentLoaded', function() {
    const dropdowns = document.querySelectorAll('.dropdown');
    const navbar = document.querySelector('.navbar');
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    dropdowns.forEach(dropdown => {
        const dropdownLink = dropdown.querySelector('a');
        
        if (dropdownLink) {
            dropdownLink.addEventListener('click', function(e) {
                // On desktop, keep existing click-to-open behaviour.
                // On mobile, also keep behaviour but within the slide-down menu.
                e.preventDefault();
                
                // Close all other dropdowns
                dropdowns.forEach(otherDropdown => {
                    if (otherDropdown !== dropdown) {
                        otherDropdown.classList.remove('active');
                    }
                });
                
                // Toggle current dropdown
                dropdown.classList.toggle('active');
            });
        }
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            dropdowns.forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
    });

    // Mobile navbar toggle
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function () {
            const isOpen = navMenu.classList.toggle('is-open');
            navToggle.classList.toggle('is-open', isOpen);
            navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            if (navbar) {
                navbar.classList.toggle('navbar--menu-open', isOpen);
            }
        });

        // Close menu when clicking a real nav link or a dropdown submenu item (not the dropdown trigger).
        navMenu.addEventListener('click', function (e) {
            const link = e.target.closest('a');
            if (!link) return;
            const isDropdownTrigger = link.closest('.dropdown') && !link.closest('.dropdown-menu');
            if (isDropdownTrigger) return; /* keep menu open so dropdown can show */
            navMenu.classList.remove('is-open');
            navToggle.classList.remove('is-open');
            navToggle.setAttribute('aria-expanded', 'false');
            if (navbar) {
                navbar.classList.remove('navbar--menu-open');
            }
        });
    }
});
