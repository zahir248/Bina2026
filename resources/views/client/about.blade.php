@extends('layouts.client.app')

@section('title', 'About BINA — BINA')

@section('content')
    <!-- Hero Section (plain background image, same as gallery) -->
    <section class="hero-plain" aria-label="About BINA banner">
        <h1 class="hero-plain-title">About BINA</h1>
    </section>

    <!-- About BINA Content - two columns: image left, text right -->
    <section class="about-bina-content" aria-label="About BINA content">
        <div class="container">
            <div class="about-bina-layout">
                <div class="about-bina-image-wrap">
                    <img src="{{ file_exists(public_path('images/about-bina.png')) ? asset('images/about-bina.png') : 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=500&h=650&fit=crop' }}" alt="BINA exhibition and industry players at ICW" class="about-bina-image" loading="lazy">
                </div>
                <div class="about-bina-text">
                    <div class="about-bina-logo-block">
                        <img src="{{ asset('images/about-bina-logo.png') }}" alt="BINA" class="about-bina-logo-img">
                    </div>
                    <h2 class="about-bina-heading">ABOUT BINA</h2>
                    <p class="about-bina-paragraph">Formerly known as CR4.0 Conference, BINA 2025 is a platform to introduce building technologies into the construction industry, including infrastructure, real estate and other built assets that are designed, constructed, operated and maintained. In-line with the vision of the International Construction Week (ICW) 2025, this premier event will be held on 28 – 30th October 2025 with two overarching platforms.</p>
                    <p class="about-bina-paragraph">As a premier platform for showcasing transformative building technologies, we aims to drive any innovation and efficiency within the IBS sector. By aligning with the government's vision, BINA 2025 aims to propel the IBS industry forward, delivering substantial economic and social impacts and establishing Malaysia as a leader in modern construction practices.</p>
                    <h3 class="about-bina-subheading">In conjunction with International Construction Week</h3>
                    <p class="about-bina-paragraph">BINA 2025 is one of the exclusive event of the ICW 2025. While ICW focuses on the overall aspect of construction industry in Malaysia, BINA 2025 will be the platform for the construction industry players especially in Industrialised Building System (IBS) to explore in person, the latest trends, developments and technologies in the construction industry.</p>
                </div>
            </div>

            <!-- Summary of BINA 2025 - two platforms -->
            <div class="about-bina-summary">
                <h2 class="about-bina-summary-title">SUMMARY OF BINA 2025</h2>
                <p class="about-bina-summary-subtitle">CONSTRUCTING THE FUTURE OF ASEAN</p>
                <div class="about-bina-summary-grid">
                    <div class="about-bina-summary-card">
                        <img src="{{ asset('images/modular-logo.png') }}" alt="MODULAR ASIA Forum &amp; Exhibition 2025" class="about-bina-summary-card-logo" loading="lazy">
                        <p class="about-bina-summary-card-text">MODULAR ASIA will serve as the premier platform advancing Modular Technology, Modern Methods of Construction (MMC), and Industrialised Building Systems (IBS), gathering global leaders to exchange best practices and showcase breakthroughs driving construction efficiency, sustainability, and scalability across ASEAN and beyond.</p>
                    </div>
                    <div class="about-bina-summary-card">
                        <img src="{{ asset('images/facility-logo.png') }}" alt="Facility Management Engagement Day 2025" class="about-bina-summary-card-logo" loading="lazy">
                        <p class="about-bina-summary-card-text">Facility Management Engagement Day will foster dynamic exchanges among facility managers, technology providers, and industry experts, unlocking business opportunities while exploring the latest trends and challenges in facility management.</p>
                    </div>
                </div>
            </div>

            <!-- Three Key Showcase -->
            <div class="about-bina-showcase">
                <h2 class="about-bina-showcase-title">THREE KEY SHOWCASE</h2>
                <div class="about-bina-showcase-grid">
                    <div class="about-bina-showcase-card">
                        <div class="about-bina-showcase-logo-box">
                            <img src="{{ asset('images/bina-nextgen-tvet-modular.png') }}" alt="NEXTGEN TVET MODULAR THINKER" class="about-bina-showcase-logo-img" loading="lazy">
                        </div>
                        <p class="about-bina-showcase-card-text">Modular Thinkers invites TVET students to design sustainable, affordable township developments, promoting the next generation of smart modular living.</p>
                    </div>
                    <div class="about-bina-showcase-card">
                        <div class="about-bina-showcase-logo-box">
                            <img src="{{ asset('images/bina-career-spotlight.png') }}" alt="CONSTRUCTION CAREER SPOTLIGHT featuring MYFutureJobs" class="about-bina-showcase-logo-img" loading="lazy">
                        </div>
                        <p class="about-bina-showcase-card-text">BINA: Career Spotlight returns for its second year, empowering talents and professionals by connecting them with top employers in the construction industry, with strong collaboration support from PERKESO.</p>
                    </div>
                    <div class="about-bina-showcase-card">
                        <div class="about-bina-showcase-logo-box">
                            <img src="{{ asset('images/bina-nextgen-tvet-modular.png') }}" alt="IBS Homes powered by modular technology" class="about-bina-showcase-logo-img" loading="lazy">
                        </div>
                        <p class="about-bina-showcase-card-text">CIDB IBS presents a bold evolution of housing solutions that are faster, smarter, and more sustainable, offering the public an immersive experience into the future of urban living.</p>
                    </div>
                </div>
            </div>

            <!-- MODULAR ASIA - Transforming ASEAN's Construction Landscape -->
            <div class="about-modular-asia">
                <div class="about-modular-asia-panel">
                    <div class="about-modular-asia-panel-left">
                        <img src="{{ asset('images/modular-logo.png') }}" alt="MODULAR ASIA Forum &amp; Exhibition 2025" class="about-modular-asia-logo" loading="lazy">
                        <h2 class="about-modular-asia-title">TRANSFORMING ASEAN'S CONSTRUCTION LANDSCAPE</h2>
                    </div>
                    <div class="about-modular-asia-panel-right">
                        <p class="about-modular-asia-paragraph">As part of BINA Conference at ICW 2025, MODULAR ASIA is a premier forum and exhibition dedicated to advancing Modular Technology, Modern Methods of Construction (MMC), and Industrialised Building Systems (IBS).</p>
                        <p class="about-modular-asia-paragraph">This exclusive platform will bring together global modular leaders, innovators, and industry pioneers to share best practices, insights, and breakthroughs that are revolutionizing construction efficiency, sustainability, and scalability across ASEAN and global markets.</p>
                    </div>
                </div>
                <div class="about-modular-asia-showcase">
                    <div class="about-modular-asia-showcase-item">
                        <img src="https://picsum.photos/seed/modular1/640/400" alt="MODULAR ASIA exhibition and industry leaders" class="about-modular-asia-showcase-img" loading="lazy">
                    </div>
                    <div class="about-modular-asia-showcase-item">
                        <img src="https://picsum.photos/seed/modular2/640/400" alt="MODULAR ASIA event and networking" class="about-modular-asia-showcase-img" loading="lazy">
                    </div>
                </div>
            </div>

            <!-- Facility Management - paragraphs left, logo + title right, video previews -->
            <div class="about-facility-section">
                <div class="about-facility-panel">
                    <div class="about-facility-panel-left">
                        <p class="about-facility-paragraph">Facility Management Engagement Day will foster dynamic exchanges among facility managers, technology providers, and industry experts, unlocking business opportunities while exploring the latest trends and challenges in facility management.</p>
                        <p class="about-facility-paragraph">This exclusive platform will bring together facility management leaders and innovators to share best practices, insights, and breakthroughs that are transforming operational efficiency, sustainability, and scalability across ASEAN and global markets.</p>
                    </div>
                    <div class="about-facility-panel-right">
                        <img src="{{ asset('images/facility-logo.png') }}" alt="Facility Management Engagement Day 2025" class="about-facility-logo" loading="lazy">
                        <h2 class="about-facility-title">TRANSFORMING ASEAN'S CONSTRUCTION LANDSCAPE</h2>
                    </div>
                </div>
                <div class="about-facility-showcase">
                    <div class="about-facility-showcase-item">
                        <img src="https://picsum.photos/seed/fm1/640/400" alt="Facility Management event" class="about-facility-showcase-img" loading="lazy">
                    </div>
                    <div class="about-facility-showcase-item">
                        <img src="https://picsum.photos/seed/fm2/640/400" alt="BINA IBS HOMES Gallery" class="about-facility-showcase-img" loading="lazy">
                    </div>
                </div>
            </div>

            <!-- Our Audiences -->
            <div class="about-audiences">
                <h2 class="about-audiences-title">OUR AUDIENCES</h2>
                <div class="about-audiences-grid">
                    <div class="about-audiences-item">
                        <h3 class="about-audiences-item-title">CONSTRUCTION PROFESSIONALS</h3>
                        <p class="about-audiences-item-text">Architects, engineers, contractors, and developers looking to stay ahead with cutting-edge technologies.</p>
                    </div>
                    <div class="about-audiences-item">
                        <h3 class="about-audiences-item-title">INVESTORS &amp; BUSINESS LEADERS</h3>
                        <p class="about-audiences-item-text">Explore new opportunities in current construction technology</p>
                    </div>
                    <div class="about-audiences-item">
                        <h3 class="about-audiences-item-title">REAL ESTATE DEVELOPERS</h3>
                        <p class="about-audiences-item-text">Learn about the economic and social impacts of advanced building technologies</p>
                    </div>
                    <div class="about-audiences-item">
                        <h3 class="about-audiences-item-title">ACADEMICIAN</h3>
                        <p class="about-audiences-item-text">Researchers, professors and students specializing in construction, engineering and related fields can gain insights into the latest technologies and connect with industry professionals</p>
                    </div>
                    <div class="about-audiences-item">
                        <h3 class="about-audiences-item-title">TECHNOLOGY PROVIDERS</h3>
                        <p class="about-audiences-item-text">Showcase and explore innovations like IBS, BIM, 3D printing, and automation</p>
                    </div>
                </div>
            </div>

            <!-- Unveil the Extraordinary - three cards -->
            <div class="about-unveil">
                <h2 class="about-unveil-title">UNVEIL THE EXTRAORDINARY AT BINA 2025</h2>
                <div class="about-unveil-cards">
                    <div class="about-unveil-card">
                        <div class="about-unveil-icon" aria-hidden="true">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="16" cy="16" r="10" stroke="currentColor" stroke-width="2" fill="none"/><circle cx="16" cy="16" r="4" fill="currentColor"/></svg>
                        </div>
                        <span class="about-unveil-label">DELIVERING OUR INSIGHT</span>
                    </div>
                    <div class="about-unveil-card">
                        <div class="about-unveil-icon" aria-hidden="true">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="8" cy="10" r="4" stroke="currentColor" stroke-width="2" fill="none"/><circle cx="24" cy="10" r="4" stroke="currentColor" stroke-width="2" fill="none"/><circle cx="16" cy="22" r="4" stroke="currentColor" stroke-width="2" fill="none"/><path d="M12 12l4 6 4-6 4 6" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>
                        </div>
                        <span class="about-unveil-label">NETWORKING POTENTIAL</span>
                    </div>
                    <div class="about-unveil-card">
                        <div class="about-unveil-icon" aria-hidden="true">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 16v-2a4 4 0 018 0v2" stroke="currentColor" stroke-width="2" fill="none"/><path d="M10 16h2a2 2 0 004 0h2" stroke="currentColor" stroke-width="2" fill="none"/><ellipse cx="10" cy="18" rx="3" ry="4" stroke="currentColor" stroke-width="2" fill="none"/><ellipse cx="22" cy="18" rx="3" ry="4" stroke="currentColor" stroke-width="2" fill="none"/></svg>
                        </div>
                        <span class="about-unveil-label">SHAPING THE DIALOGUE</span>
                    </div>
                </div>
            </div>

            <!-- CCD and CPD Points Applied -->
            <div class="about-ccd-cpd">
                <h2 class="about-ccd-cpd-title">CCD AND CPD POINTS APPLIED</h2>
                <div class="about-ccd-cpd-grid">
                    <div class="about-ccd-cpd-card">
                        <div class="about-ccd-cpd-icon" aria-hidden="true">
                            <svg width="28" height="28" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="16" cy="16" r="12" stroke="currentColor" stroke-width="2" fill="none"/><circle cx="16" cy="16" r="7" stroke="currentColor" stroke-width="2" fill="none"/><circle cx="16" cy="16" r="2.5" fill="currentColor"/></svg>
                        </div>
                        <span class="about-ccd-cpd-label">LEMBAGA ARKITEK MALAYSIA</span>
                    </div>
                    <div class="about-ccd-cpd-card">
                        <div class="about-ccd-cpd-icon" aria-hidden="true">
                            <svg width="28" height="28" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="16" cy="16" r="12" stroke="currentColor" stroke-width="2" fill="none"/><circle cx="16" cy="16" r="7" stroke="currentColor" stroke-width="2" fill="none"/><circle cx="16" cy="16" r="2.5" fill="currentColor"/></svg>
                        </div>
                        <span class="about-ccd-cpd-label">ROYAL INSTITUTION OF SURVEYORS MALAYSIA</span>
                    </div>
                    <div class="about-ccd-cpd-card">
                        <div class="about-ccd-cpd-icon" aria-hidden="true">
                            <svg width="28" height="28" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="6" y="4" width="20" height="24" rx="1" stroke="currentColor" stroke-width="2" fill="none"/><path d="M10 10h12M10 14h12M10 18h8" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>
                        </div>
                        <span class="about-ccd-cpd-label">BOARD OF QUANTITY SURVEYORS</span>
                    </div>
                    <div class="about-ccd-cpd-card">
                        <div class="about-ccd-cpd-icon" aria-hidden="true">
                            <svg width="28" height="28" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="6" y="4" width="20" height="24" rx="1" stroke="currentColor" stroke-width="2" fill="none"/><path d="M10 10h12M10 14h12M10 18h8" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>
                        </div>
                        <span class="about-ccd-cpd-label">MALAYSIA BOARD OF TECHNOLOGIES</span>
                    </div>
                    <div class="about-ccd-cpd-card">
                        <div class="about-ccd-cpd-icon" aria-hidden="true">
                            <svg width="28" height="28" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="16" cy="16" r="12" stroke="currentColor" stroke-width="2" fill="none"/><circle cx="16" cy="16" r="7" stroke="currentColor" stroke-width="2" fill="none"/><circle cx="16" cy="16" r="2.5" fill="currentColor"/></svg>
                        </div>
                        <span class="about-ccd-cpd-label">BOARD OF ENGINEERS MALAYSIA</span>
                    </div>
                    <div class="about-ccd-cpd-card">
                        <div class="about-ccd-cpd-icon" aria-hidden="true">
                            <svg width="28" height="28" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="4" y="4" width="24" height="24" rx="2" stroke="currentColor" stroke-width="2" fill="none"/><rect x="10" y="10" width="12" height="12" rx="1" fill="currentColor"/></svg>
                        </div>
                        <span class="about-ccd-cpd-label">CONSTRUCTION INDUSTRY DEVELOPMENT BOARD</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
