@extends('layouts.client.app')

@section('title', 'Modular Asia Exhibition — BINA')

@section('content')
    <!-- Hero -->
    <section class="modular-asia-hero" aria-label="Modular Asia Exhibition">
        <div class="modular-asia-hero-bg" aria-hidden="true"></div>
        <div class="modular-asia-hero-inner">
            <p class="modular-asia-hero-badge">
                <span class="modular-asia-hero-badge-dot" aria-hidden="true"></span>
                <span class="modular-asia-hero-badge-label">Held during International Construction Week (ICW)</span>
            </p>
            <h1 class="modular-asia-hero-title">Modular Asia Exhibition</h1>
            <p class="modular-asia-hero-lead">
                Malaysia’s flagship platform for modular construction, Industrialised Building Systems (IBS), and the future of built environments—presented alongside ICW at MITEC.
            </p>
            <div class="modular-asia-hero-meta">
                <span class="modular-asia-meta-chip">
                    <i class="bi bi-calendar3-event" aria-hidden="true"></i>
                    November 10 – 12, 2026
                </span>
                <span class="modular-asia-meta-chip">
                    <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
                    MITEC, Kuala Lumpur
                </span>
            </div>
        </div>
    </section>

    <!-- At a glance -->
    <section class="modular-asia-glance" aria-label="Event summary">
        <div class="container">
            <div class="modular-asia-glance-grid">
                <div class="modular-asia-glance-card">
                    <span class="modular-asia-glance-label">Date</span>
                    <strong class="modular-asia-glance-value">November 10 – 12, 2026</strong>
                    <span class="modular-asia-glance-note">Three full days of innovation and industry networking</span>
                </div>
                <div class="modular-asia-glance-card">
                    <span class="modular-asia-glance-label">Venue</span>
                    <strong class="modular-asia-glance-value">MITEC</strong>
                    <span class="modular-asia-glance-note">Kuala Lumpur’s flagship exhibition centre</span>
                </div>
                <div class="modular-asia-glance-card modular-asia-glance-card--accent">
                    <span class="modular-asia-glance-label">Context</span>
                    <strong class="modular-asia-glance-value">ICW</strong>
                    <span class="modular-asia-glance-note">Part of the international construction week</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Intro -->
    <section class="modular-asia-intro" aria-label="About the exhibition">
        <div class="container">
            <div class="modular-asia-intro-inner">
                <h2 class="modular-asia-section-title">What to expect</h2>
                <p class="modular-asia-intro-text">
                    <strong>Modular Asia Exhibition</strong> brings together policymakers, developers, contractors, manufacturers, and future talent under one roof. Discover how modular and IBS approaches are accelerating quality, safety, and productivity—while supporting Malaysia’s construction transformation agenda through to 2030 and beyond.
                </p>
                <p class="modular-asia-intro-text">
                    Whether you are sourcing systems, exploring partnerships, or planning workforce development, this exhibition is your concise entry point to the modular ecosystem in Asia—timed with <strong>ICW</strong> for maximum industry presence and cross-sector collaboration.
                </p>
            </div>
        </div>
    </section>

    <!-- Program components -->
    <section class="modular-asia-programs" aria-label="Program components">
        <div class="container">
            <h2 class="modular-asia-section-title modular-asia-section-title--center">Programme components</h2>
            <p class="modular-asia-section-sub">Highlights across the three-day run</p>
            <ul class="modular-asia-program-grid">
                <li class="modular-asia-program-card">
                    <span class="modular-asia-program-num" aria-hidden="true">01</span>
                    <h3 class="modular-asia-program-title">Launching the National Modular Roadmap to 2030</h3>
                    <p class="modular-asia-program-desc">A strategic unveiling of national direction for modular adoption—aligning industry, standards, and investment with medium-term targets.</p>
                </li>
                <li class="modular-asia-program-card">
                    <span class="modular-asia-program-num" aria-hidden="true">02</span>
                    <h3 class="modular-asia-program-title">Exhibition &amp; Buildxpo</h3>
                    <p class="modular-asia-program-desc">Walk the show floor for systems, materials, digital tools, and turnkey modular solutions from leading exhibitors.</p>
                </li>
                <li class="modular-asia-program-card">
                    <span class="modular-asia-program-num" aria-hidden="true">03</span>
                    <h3 class="modular-asia-program-title">Technical visit: Modular show unit / IBS Homes &amp; Gallery</h3>
                    <p class="modular-asia-program-desc">See full-scale modular demonstrations and IBS homes—translating policy and design into buildable, inspectable reality.</p>
                </li>
                <li class="modular-asia-program-card">
                    <span class="modular-asia-program-num" aria-hidden="true">04</span>
                    <h3 class="modular-asia-program-title">Construction Career Spotlight</h3>
                    <p class="modular-asia-program-desc">Connect employers with job seekers and TVET talent—in collaboration with <strong>PERKESO</strong> (programme details on our Career Spotlight page).</p>
                    <a href="{{ route('career-spotlight') }}" class="modular-asia-inline-link">Career Spotlight @ Bina <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                </li>
                <li class="modular-asia-program-card modular-asia-program-card--wide">
                    <span class="modular-asia-program-num" aria-hidden="true">05</span>
                    <h3 class="modular-asia-program-title">NextGen TVET: Modular Thinker</h3>
                    <p class="modular-asia-program-desc">Youth and TVET engagement on modular thinking and skills—in collaboration with the <strong>Ministry of Youth and Sports (KBS)</strong>, the <strong>Ministry of Education Malaysia (KPM)</strong>, <strong>IEM</strong>, and <strong>CIOB</strong>.</p>
                    <a href="{{ route('nextgen-bina') }}" class="modular-asia-inline-link">NextGen @ Bina <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                </li>
            </ul>
        </div>
    </section>

    <!-- Technical visit highlight -->
    <section class="modular-asia-visit" aria-label="Technical visit schedule">
        <div class="container">
            <div class="modular-asia-visit-panel">
                <div class="modular-asia-visit-text">
                    <p class="modular-asia-visit-kicker">Modular / IBS component</p>
                    <h2 class="modular-asia-visit-title">Technical visit — Modular show unit &amp; IBS Homes</h2>
                    <ul class="modular-asia-visit-list">
                        <li><strong>November 11, 2026</strong> (second day of the programme)</li>
                        <li><strong>2 visit sessions</strong> — morning and afternoon</li>
                        <li><strong>1 bus</strong> per session (limited capacity)</li>
                        <li><strong>Early booking</strong> through the website for limited slots</li>
                    </ul>
                    <p class="modular-asia-visit-note">
                        Technical-visit slots are limited. Register early when booking opens.
                    </p>
                    <div class="modular-asia-register-block">
                        <p class="modular-asia-register-hint">Click the button below to register.</p>
                        <a href="{{ route('home') }}#events-section" class="nextgen-bina-register-btn modular-asia-register-btn">
                            <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                            CLICK HERE TO REGISTER
                        </a>
                        <p class="modular-asia-register-footnote">Takes you to our events area to sign up and complete registration.</p>
                    </div>
                </div>
                <div class="modular-asia-visit-aside" aria-hidden="true">
                    <div class="modular-asia-visit-stat">
                        <span class="modular-asia-visit-stat-label">Key date</span>
                        <span class="modular-asia-visit-stat-value">Nov 11, 2026</span>
                    </div>
                    <div class="modular-asia-visit-stat">
                        <span class="modular-asia-visit-stat-label">Sessions</span>
                        <span class="modular-asia-visit-stat-value">Morning &amp; afternoon</span>
                    </div>
                    <div class="modular-asia-visit-stat">
                        <span class="modular-asia-visit-stat-label">Transport</span>
                        <span class="modular-asia-visit-stat-value">1 bus / session</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Partner strip -->
    <section class="modular-asia-partners" aria-label="Collaborations">
        <div class="container">
            <h2 class="modular-asia-section-title modular-asia-section-title--center modular-asia-section-title--light">Strategic partnerships</h2>
            <p class="modular-asia-partners-lead">This programme is supported through partnerships with key agencies and professional bodies.</p>
            <div class="modular-asia-partners-chips">
                <span class="modular-asia-partner-chip">PERKESO — Career Spotlight</span>
                <span class="modular-asia-partner-chip">KBS</span>
                <span class="modular-asia-partner-chip">KPM</span>
                <span class="modular-asia-partner-chip">IEM</span>
                <span class="modular-asia-partner-chip">CIOB</span>
            </div>
        </div>
    </section>
@endsection
