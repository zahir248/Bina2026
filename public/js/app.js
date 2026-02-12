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

// Countdown timer
function updateCountdown() {
    const countdownElement = document.getElementById('countdown');
    if (!countdownElement) return;
    
    const targetDate = new Date('2026-06-15T00:00:00').getTime();
    const now = new Date().getTime();
    const distance = targetDate - now;
    
    if (distance < 0) {
        countdownElement.innerHTML = 'Event has started!';
        return;
    }
    
    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
    
    // Format with leading zeros
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

if (document.getElementById('countdown')) {
    updateCountdown();
    setInterval(updateCountdown, 1000);
}

// Dropdown menu toggle on click
document.addEventListener('DOMContentLoaded', function() {
    const dropdowns = document.querySelectorAll('.dropdown');
    
    dropdowns.forEach(dropdown => {
        const dropdownLink = dropdown.querySelector('a');
        
        if (dropdownLink) {
            dropdownLink.addEventListener('click', function(e) {
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
});
