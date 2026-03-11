@extends('layouts.client.app')

@section('title', 'Career Spotlight @ Bina — BINA')

@section('content')
    <!-- Hero Section -->
    <section class="hero-plain" aria-label="Career Spotlight banner">
        <h1 class="hero-plain-title">Career Spotlight @ Bina</h1>
    </section>

    <!-- Career Spotlight Content: left = text, right = two photos (diagram will be an image) -->
    <section class="career-spotlight-content" aria-label="Career Spotlight content">
        <div class="container">
            <div class="career-spotlight-layout">
                <div class="career-spotlight-left">
                    <div class="career-spotlight-logo-block">
                        <img src="{{ asset('images/bina-career-spotlight.png') }}" alt="BINA Construction Career Spotlight" class="career-spotlight-logo-img" loading="lazy">
                    </div>
                    <h2 class="career-spotlight-title">CAREER SPOTLIGHT @ BINA</h2>
                    <p class="career-spotlight-paragraph">
                        The BINA Construction Career Spotlight is an integral part of International Construction Week (ICW 2025), designed to bridge a gap between job seekers, academic &amp; TVET students, and industry professionals with leading employers in the construction sector.
                    </p>
                    <div class="career-spotlight-diagram-img">
                        <img src="{{ asset('images/career-1.png') }}" alt="International ICW Construction Week – BINA Conference 2025, BUILDXPO Malaysia, Construction Career Spotlight" class="career-spotlight-img" loading="lazy">
                    </div>
                </div>
                <div class="career-spotlight-right">
                    <div class="career-spotlight-photo career-spotlight-photo-top">
                        <img src="{{ asset('images/career-spotlight-fair.jpg') }}" alt="Career fair at BINA with exhibitors and job seekers" class="career-spotlight-img" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1521737711867-e3b97375f902?w=640&h=400&fit=crop'">
                    </div>
                    <div class="career-spotlight-photo career-spotlight-photo-bottom">
                        <img src="{{ asset('images/career-spotlight-perkeso.jpg') }}" alt="PERKESO MYFutureJobs booth at Career Spotlight" class="career-spotlight-img" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?w=640&h=400&fit=crop'">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Objectives & Target Audiences - two columns -->
    <section class="career-spotlight-objectives-section" aria-label="Objectives and target audiences">
        <div class="container">
            <div class="career-spotlight-objectives-grid">
                <div class="career-spotlight-objectives-col">
                    <h2 class="career-spotlight-objectives-title">THE OBJECTIVES</h2>
                    <div class="career-spotlight-objectives-list">
                        <div class="career-spotlight-objective-item">
                            <div class="career-spotlight-objective-icon" aria-hidden="true">
                                <i class="bi bi-person"></i>
                            </div>
                            <div class="career-spotlight-objective-content">
                                <h3 class="career-spotlight-objective-heading">CONNECTING CAREER SEEKERS WITH EMPLOYERS</h3>
                                <p class="career-spotlight-objective-text">Serve as a bridge between job seekers and employers, attendees to explore various career paths in the construction industry.</p>
                            </div>
                        </div>
                        <div class="career-spotlight-objective-item">
                            <div class="career-spotlight-objective-icon" aria-hidden="true">
                                <i class="bi bi-mortarboard"></i>
                            </div>
                            <div class="career-spotlight-objective-content">
                                <h3 class="career-spotlight-objective-heading">HIGHLIGHTING TVET AND PROFESSIONAL AREAS</h3>
                                <p class="career-spotlight-objective-text">Emphasize opportunities in both TVET and professional areas within the construction sector.</p>
                            </div>
                        </div>
                        <div class="career-spotlight-objective-item">
                            <div class="career-spotlight-objective-icon" aria-hidden="true">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div class="career-spotlight-objective-content">
                                <h3 class="career-spotlight-objective-heading">EMPOWER CONSTRUCTION BEST PRACTICES</h3>
                                <p class="career-spotlight-objective-text">Elevate the construction professions while promoting and empowering best practices within the construction industry.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="career-spotlight-audiences-col">
                    <h2 class="career-spotlight-objectives-title">TARGET AUDIENCES</h2>
                    <div class="career-spotlight-audiences-content">
                        <h3 class="career-spotlight-audiences-subtitle">CAREER PROVIDER :</h3>
                        <ul class="career-spotlight-audiences-list">
                            <li>Built Environment</li>
                            <li>Manufacturing</li>
                            <li>Logistics</li>
                            <li>Academians / Vocationals</li>
                            <li>Financial Institutions</li>
                            <li>Solution / Services Providers</li>
                        </ul>
                        <h3 class="career-spotlight-audiences-subtitle">JOB SEEKER :</h3>
                        <ul class="career-spotlight-audiences-list">
                            <li>Construction Professionals &amp; Practitioners</li>
                            <li>Technical Experts</li>
                            <li>Environmental &amp; sustainability experts</li>
                            <li>IT and digital technology enthusiast</li>
                            <li>Students &amp; TVET Graduates</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- MITEC venue & stats - left: details + stat cards, right: photo -->
    <section class="career-spotlight-mitec-section" aria-label="Venue and event statistics">
        <div class="container">
            <div class="career-spotlight-mitec-layout">
                <div class="career-spotlight-mitec-left">
                    <h2 class="career-spotlight-mitec-title">MITEC</h2>
                    <p class="career-spotlight-mitec-detail">Level 1, Hall 2 - Hall 3</p>
                    <p class="career-spotlight-mitec-detail">28 - 30 OCTOBER 2025</p>
                    <div class="career-spotlight-mitec-stats">
                        <div class="career-spotlight-mitec-stat">
                            <div class="career-spotlight-mitec-stat-icon" aria-hidden="true">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <div class="career-spotlight-mitec-stat-content">
                                <span class="career-spotlight-mitec-stat-number">17,000++</span>
                                <span class="career-spotlight-mitec-stat-label">VISITORS</span>
                            </div>
                        </div>
                        <div class="career-spotlight-mitec-stat">
                            <div class="career-spotlight-mitec-stat-icon" aria-hidden="true">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <div class="career-spotlight-mitec-stat-content">
                                <span class="career-spotlight-mitec-stat-number">500++</span>
                                <span class="career-spotlight-mitec-stat-label">BOOTHS</span>
                            </div>
                        </div>
                        <div class="career-spotlight-mitec-stat">
                            <div class="career-spotlight-mitec-stat-icon" aria-hidden="true">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <div class="career-spotlight-mitec-stat-content">
                                <span class="career-spotlight-mitec-stat-number">30++</span>
                                <span class="career-spotlight-mitec-stat-label">CAREER EXHIBITOR</span>
                            </div>
                        </div>
                        <div class="career-spotlight-mitec-stat">
                            <div class="career-spotlight-mitec-stat-icon" aria-hidden="true">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <div class="career-spotlight-mitec-stat-content">
                                <span class="career-spotlight-mitec-stat-number">200++</span>
                                <span class="career-spotlight-mitec-stat-label">CAREER OPENINGS</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="career-spotlight-mitec-right">
                    <div class="career-spotlight-mitec-photo">
                        <img src="{{ asset('images/career-spotlight-mitec.jpg') }}" alt="Exhibition hall at MITEC - career fair and booths" class="career-spotlight-mitec-img" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=600&fit=crop'">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features grid -->
    <section class="career-spotlight-features-section" aria-label="Career Spotlight features">
        <div class="container">
            <h2 class="career-spotlight-features-title">FEATURES</h2>
            <div class="career-spotlight-features-grid">
                <div class="career-spotlight-feature-card">
                    <div class="career-spotlight-feature-icon" aria-hidden="true">
                        <i class="bi bi-house"></i>
                    </div>
                    <p class="career-spotlight-feature-label">EXCLUSIVE CAREER PAVILION</p>
                </div>
                <div class="career-spotlight-feature-card">
                    <div class="career-spotlight-feature-icon" aria-hidden="true">
                        <i class="bi bi-palette"></i>
                    </div>
                    <p class="career-spotlight-feature-label">INDUSTRY-INSIGHT SESSIONS</p>
                </div>
                <div class="career-spotlight-feature-card">
                    <div class="career-spotlight-feature-icon" aria-hidden="true">
                        <i class="bi bi-chat-square-text"></i>
                    </div>
                    <p class="career-spotlight-feature-label">ON-THE-SPOT INTERVIEWS</p>
                </div>
                <div class="career-spotlight-feature-card">
                    <div class="career-spotlight-feature-icon" aria-hidden="true">
                        <i class="bi bi-people"></i>
                    </div>
                    <p class="career-spotlight-feature-label">NETWORKING OPPORTUNITIES</p>
                </div>
                <div class="career-spotlight-feature-card">
                    <div class="career-spotlight-feature-icon" aria-hidden="true">
                        <i class="bi bi-file-earmark-arrow-down"></i>
                    </div>
                    <p class="career-spotlight-feature-label">RESUME DROP-OFF POINTS</p>
                </div>
                <div class="career-spotlight-feature-card">
                    <div class="career-spotlight-feature-icon" aria-hidden="true">
                        <i class="bi bi-mortarboard"></i>
                    </div>
                    <p class="career-spotlight-feature-label">TVET SHOWCASE ZONE</p>
                </div>
                <div class="career-spotlight-feature-card">
                    <div class="career-spotlight-feature-icon" aria-hidden="true">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <p class="career-spotlight-feature-label">CAREER TALK</p>
                </div>
                <div class="career-spotlight-feature-card">
                    <div class="career-spotlight-feature-icon" aria-hidden="true">
                        <i class="bi bi-star"></i>
                    </div>
                    <p class="career-spotlight-feature-label">INTERACTIVE ZONE</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Setting-up Pavilion -->
    <section class="career-spotlight-pavilion-section" aria-label="Setting-up Pavilion">
        <div class="container">
            <div class="career-spotlight-pavilion-layout">
                <div class="career-spotlight-pavilion-left">
                    <h2 class="career-spotlight-pavilion-title">SETTING-UP PAVILLION</h2>
                    <p class="career-spotlight-pavilion-text">The provided layout showcases the planned arrangement of booths and spaces for the Career Spotlight at Buildexpo 2024. The design emphasizes efficient space utilization and an engaging flow for attendees.</p>
                    <p class="career-spotlight-pavilion-text">The layout illustrates the planned arrangement for the Career Spotlight zone at Buildexpo 2024. It is designed to balance exhibitor engagement, attendee navigation, and interactive activities, ensuring an engaging experience for all participants.</p>
                </div>
                <div class="career-spotlight-pavilion-right">
                    <div class="career-spotlight-pavilion-img-wrap">
                        <img src="{{ asset('images/career-5.png') }}" alt="Career Spotlight pavilion layout" class="career-spotlight-pavilion-img" loading="lazy">
                    </div>
                    <div class="career-spotlight-pavilion-img-wrap">
                        <img src="{{ asset('images/career-6.png') }}" alt="Career Spotlight pavilion plan view" class="career-spotlight-pavilion-img" loading="lazy">
                    </div>
                    <div class="career-spotlight-pavilion-img-wrap">
                        <img src="{{ asset('images/career-7.png') }}" alt="Career Spotlight pavilion components" class="career-spotlight-pavilion-img" loading="lazy">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pocket Talk - image left, text right -->
    <section class="career-spotlight-pocket-section" aria-label="Pocket Talk">
        <div class="container">
            <div class="career-spotlight-pocket-layout">
                <div class="career-spotlight-pocket-left">
                    <div class="career-spotlight-pocket-img-wrap">
                        <img src="{{ asset('images/pocket-talk-open-forum.jpg') }}" alt="OPEN FORUM event at Career Spotlight with speaker and audience" class="career-spotlight-pocket-img" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=600&fit=crop'">
                    </div>
                </div>
                <div class="career-spotlight-pocket-right">
                    <h2 class="career-spotlight-pocket-title">POCKET TALK</h2>
                    <ul class="career-spotlight-pocket-list">
                        <li>Conduct talk sharing initiatives, subsidies, and discussing topics on career development</li>
                        <li>An interactive forum for attendees to engage in short, dynamic discussions with industry experts.</li>
                        <li>This session provides a platform to share ideas, ask questions, and gain insights into topics ranging from emerging trends and technologies to best practices in construction.</li>
                        <li>Pocket Talks are designed to be informal yet impactful, fostering open dialogue and knowledge exchange.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Last Event Visual Highlights - 2x3 grid -->
    <section class="career-spotlight-highlights-section" aria-label="Last event visual highlights">
        <div class="container">
            <h2 class="career-spotlight-highlights-title">LAST EVENT VISUAL HIGHLIGHTS</h2>
            <div class="career-spotlight-highlights-grid">
                <div class="career-spotlight-highlight-item">
                    <img src="{{ asset('images/last-event-1.jpg') }}" alt="Career Spotlight – exhibition booth and attendees" class="career-spotlight-highlight-img" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=400&h=300&fit=crop'">
                </div>
                <div class="career-spotlight-highlight-item">
                    <img src="{{ asset('images/last-event-2.jpg') }}" alt="Career Spotlight – consultation at booth" class="career-spotlight-highlight-img" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1521737711867-e3b97375f902?w=400&h=300&fit=crop'">
                </div>
                <div class="career-spotlight-highlight-item">
                    <img src="{{ asset('images/last-event-3.jpg') }}" alt="Career Spotlight – interactive prize wheel and PERKESO MyFutureJobs" class="career-spotlight-highlight-img" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?w=400&h=300&fit=crop'">
                </div>
                <div class="career-spotlight-highlight-item">
                    <img src="{{ asset('images/last-event-4.jpg') }}" alt="Career Spotlight 2024 – exhibition area and booths" class="career-spotlight-highlight-img" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=400&h=300&fit=crop'">
                </div>
                <div class="career-spotlight-highlight-item">
                    <img src="{{ asset('images/last-event-5.jpg') }}" alt="Career Spotlight – exhibition hall and Construction Career Spotlight" class="career-spotlight-highlight-img" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1521737711867-e3b97375f902?w=400&h=300&fit=crop'">
                </div>
                <div class="career-spotlight-highlight-item">
                    <img src="{{ asset('images/last-event-6.jpg') }}" alt="Career Spotlight – interactive mini-golf and PERKESO MyFutureJobs" class="career-spotlight-highlight-img" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?w=400&h=300&fit=crop'">
                </div>
            </div>
        </div>
    </section>

    <!-- Collaboration Contribution & Benefits -->
    <section class="career-spotlight-benefits-section" aria-label="Collaboration contribution and benefits">
        <div class="container">
            <h2 class="career-spotlight-benefits-main-title">Career Spotlight</h2>
            <p class="career-spotlight-benefits-subtitle">Collaboration Contribution &amp; Benefits</p>
            <div class="career-spotlight-benefits-card">
                <div class="career-spotlight-benefits-header-row">
                    <h3 class="career-spotlight-benefits-col-header">Benefits</h3>
                    <h3 class="career-spotlight-benefits-col-header career-spotlight-benefits-desc-header">Description</h3>
                </div>
                <div class="career-spotlight-benefits-row">
                    <span class="career-spotlight-benefit-tag">LOGO PLACING</span>
                    <p class="career-spotlight-benefit-desc">Perkeso and/or MyFuture Jobs logo shall be featured with "BINA Construction Career Spotlight" logo to upscale impact and benefit from strategic branding opportunities.</p>
                </div>
                <div class="career-spotlight-benefits-row">
                    <span class="career-spotlight-benefit-tag">LOGO APPEARANCES</span>
                    <p class="career-spotlight-benefit-desc">Logo appearance in the career pavilion, marketing collaterals, social media posting, email newsletter and websites</p>
                </div>
                <div class="career-spotlight-benefits-row">
                    <span class="career-spotlight-benefit-tag">CAREER PAVILION</span>
                    <p class="career-spotlight-benefit-desc">216 sqm shared pavilion</p>
                </div>
                <div class="career-spotlight-benefits-row">
                    <span class="career-spotlight-benefit-tag">CAREER PROVIDERS</span>
                    <p class="career-spotlight-benefit-desc">25++ career providers shall be invited for job offering (1 career provider shall provide &gt; 8 openings)</p>
                </div>
                <div class="career-spotlight-benefits-row">
                    <span class="career-spotlight-benefit-tag">COLLABORATIVE COUNTER</span>
                    <p class="career-spotlight-benefit-desc">1-shared counter</p>
                </div>
                <div class="career-spotlight-benefits-row">
                    <span class="career-spotlight-benefit-tag">HONOURABLE MENTION</span>
                    <p class="career-spotlight-benefit-desc">Exclusive mention of the collaboration in the opening ceremony by Prime Minister during Opening Ceremony</p>
                </div>
                <div class="career-spotlight-benefits-row">
                    <span class="career-spotlight-benefit-tag">CAREER TALK</span>
                    <p class="career-spotlight-benefit-desc">Conduct talk sharing initiatives, subsidies, and discussing topics on career development</p>
                </div>
                <div class="career-spotlight-benefits-row">
                    <span class="career-spotlight-benefit-tag">EVENT REPORT</span>
                    <p class="career-spotlight-benefit-desc">An in-depth analysis of the event, featuring details in attendees, statistics and feedback through a detailed survey</p>
                </div>
                <div class="career-spotlight-benefits-row">
                    <span class="career-spotlight-benefit-tag">CONTRIBUTION</span>
                    <p class="career-spotlight-benefit-desc">RM50,000</p>
                </div>
            </div>
        </div>
    </section>
@endsection
