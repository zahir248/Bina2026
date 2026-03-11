@extends('layouts.client.app')

@section('title', 'NextGen @ Bina — BINA')

@section('content')
    <!-- Hero Section -->
    <section class="hero-plain" aria-label="NextGen @ Bina banner">
        <h1 class="hero-plain-title">NextGen @ Bina</h1>
    </section>

    <!-- Content Section -->
    <section class="about-bina-content nextgen-bina-content" aria-label="NextGen @ Bina content">
        <div class="container">
            <div class="nextgen-bina-page-layout">
                <div class="nextgen-bina-main">
            <div class="about-bina-layout nextgen-bina-layout">
                <div class="about-bina-text">
                    <div class="about-bina-logo-block nextgen-bina-logo-block">
                        <img src="{{ asset('images/bina-nextgen-tvet-modular.png') }}" alt="BINA NextGen TVET Modular Thinkers" class="about-bina-logo-img nextgen-bina-logo-img" loading="lazy">
                    </div>
                    <h2 class="about-bina-heading">NEXTGEN TVET: MODULAR THINKERS</h2>
                    <p class="about-bina-paragraph">BINA 2025 proudly introduces NextGen TVET: Modular Thinkers, an exciting competition designed to spark innovation and real-world problem-solving among TVET students through the lens of modular construction.</p>
                    <p class="about-bina-paragraph">The competition challenges participants to design innovative, functional, and cost-effective modular building solutions that address critical global needs such as affordable housing, disaster relief shelters, and eco-friendly structures. Beyond design, students will also be tested on their ability to assemble modular structures quickly and accurately under time constraints, simulating the fast-paced demands of real-world construction scenarios.</p>
                    <div class="nextgen-bina-banner">
                        <img src="{{ asset('images/nextgen-cover.jpg') }}" alt="NextGen TVET Modular Thinker – A BINA CIDB-IBS City" class="nextgen-bina-banner-img" loading="lazy">
                    </div>
                    <p class="about-bina-paragraph">NextGen TVET: Modular Thinkers is more than a competition—it's a platform to showcase the future talents who will drive innovation, sustainability, and smart urban development through modular technology.</p>
                </div>
            </div>

            <!-- Competition posters: English & Malay -->
            <div class="nextgen-bina-competition">
                <div class="nextgen-bina-competition-grid">
                    <div class="nextgen-bina-poster-wrap">
                        <img src="{{ asset('images/poster-bi.jpg') }}" alt="NextGen TVET Modular Thinker – Competition poster (English)" class="nextgen-bina-poster-img" loading="lazy">
                    </div>
                    <div class="nextgen-bina-poster-wrap">
                        <img src="{{ asset('images/poster-bm.jpg') }}" alt="NextGen TVET Modular Thinker – Poster pertandingan (Bahasa Melayu)" class="nextgen-bina-poster-img" loading="lazy">
                    </div>
                </div>
            </div>

            <!-- Ready to Register section -->
            <div class="nextgen-bina-register-section">
                <h3 class="nextgen-bina-register-title">Ready to Register? / Sedia untuk Mendaftar?</h3>
                <p class="nextgen-bina-register-btn-text">Click the button below to register / Klik butang di bawah untuk mendaftar</p>
                <a href="http://bina-cidbibs.my/nextgen" target="_blank" rel="noopener noreferrer" class="nextgen-bina-register-btn">
                    <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                    CLICK HERE TO REGISTER / KLIK UNTUK MENDAFTAR
                </a>
                <p class="nextgen-bina-register-note">Opens registration form in a new tab / Membuka borang pendaftaran dalam tab baru</p>
            </div>
                </div>
                <!-- Key objectives sidebar: same pattern as checkout — wrapper gets fixed on scroll, card stays inside (stops before footer via maxHeight) -->
                <aside class="nextgen-bina-sidebar" id="nextgen-right-panel" aria-label="Key objectives of the competition">
                    <div class="nextgen-bina-sticky-wrap" id="nextgen-objectives-card">
                        <div class="ma-card nextgen-bina-objectives-card animate-on-scroll">
                            <div class="ma-card-title">Key objectives of the competition</div>
                            <ul class="objectives-list">
                                <li>
                                    <div class="objectives-list-heading">Encouraging Creativity</div>
                                    <div class="objectives-list-desc">Fostering practicality in designing modular solutions that solve real-world challenges.</div>
                                </li>
                                <li>
                                    <div class="objectives-list-heading">Testing Hands-on Skills</div>
                                    <div class="objectives-list-desc">Mirroring industry expectations for speed and precision in modular assembly.</div>
                                </li>
                                <li>
                                    <div class="objectives-list-heading">Promoting Sustainability</div>
                                    <div class="objectives-list-desc">Inspiring students to build modular structures that prioritize environmental responsibility.</div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var animateElements = document.querySelectorAll('.animate-on-scroll');
    var observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };
    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    animateElements.forEach(function(el) { observer.observe(el); });

    (function() {
        var panel = document.getElementById('nextgen-right-panel');
        var card = document.getElementById('nextgen-objectives-card');
        var contentSection = document.querySelector('.nextgen-bina-content');
        var footer = document.querySelector('.footer');
        if (!panel || !card) return;
        var stickyTop = 80;
        var ticking = false;

        function updateSticky() {
            if (window.innerWidth <= 992) {
                card.classList.remove('is-sticky-fixed');
                card.style.left = '';
                card.style.width = '';
                card.style.maxHeight = '';
                panel.style.minHeight = '';
                return;
            }
            var rect = panel.getBoundingClientRect();
            var contentRect = contentSection ? contentSection.getBoundingClientRect() : { top: -Infinity };
            var footerRect = footer ? footer.getBoundingClientRect() : { top: Infinity };
            var aboveHero = contentRect.top > stickyTop;
            var footerInView = footerRect.top <= window.innerHeight;
            var shouldStick = rect.top <= stickyTop && !aboveHero && !footerInView;
            if (shouldStick) {
                card.classList.add('is-sticky-fixed');
                card.style.left = rect.left + 'px';
                card.style.width = rect.width + 'px';
                card.style.maxHeight = '';
                panel.style.minHeight = rect.height + 'px';
            } else {
                card.classList.remove('is-sticky-fixed');
                card.style.left = '';
                card.style.width = '';
                card.style.maxHeight = '';
                panel.style.minHeight = '';
            }
        }

        function onTick() {
            ticking = false;
            updateSticky();
        }

        function requestTick() {
            if (!ticking) {
                ticking = true;
                requestAnimationFrame(onTick);
            }
        }

        window.addEventListener('scroll', requestTick, { passive: true });
        window.addEventListener('resize', requestTick);
        updateSticky();
        setTimeout(updateSticky, 100);
    })();
});
</script>
@endpush
@endsection
