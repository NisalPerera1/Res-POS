@extends('layouts.app')

@section('content')
{{-- =====================================================================
     TODDYZ FAMILY RESTAURANT - PRODUCTIONS PAGE
     Design: Spice-Black editorial. Playfair Display + Cormorant Garamond.
     Accent: #D85A30 (spice orange). Built for mobile-first, dark-first.
     ===================================================================== --}}

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800;900&family=Cormorant+Garamond:wght@300;400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* Reuse same styles as homepage */
    :root {
        --spice:        #D85A30;
        --spice-dim:    rgba(216,90,48,0.15);
        --spice-glow:   rgba(216,90,48,0.08);
        --ink:          #09090B;
        --ink-2:        #111116;
        --ink-3:        #18181F;
        --ink-4:        #1F1F28;
        --b:       rgba(255,255,255,0.07);
        --spice-b:  rgba(216,90,48,0.25);
        --t1:       #FAFAF9;
        --t2:       rgba(250,250,249,0.60);
        --t3:       rgba(250,250,249,0.35);
        --fd: 'Playfair Display', serif;
        --fu:    'Cormorant Garamond', serif;
        --fu:      'Montserrat', sans-serif;
        --r-md:         10px;
        --r-lg:         16px;
        --r-xl:         24px;
        --r-full:       999px;
    }

    * { box-sizing: b-box; margin: 0; padding: 0; }

    body {
        background: var(--ink);
        color: var(--t1);
        font-family: var(--fu);
        -webkit-font-smoothing: antialiased;
        font-size: 18px;
        line-height: 1.6;
    }

    .container  { max-width: 1120px; margin: 0 auto; padding: 0 20px; }
    .tag        { display: inline-flex; align-items: center; gap: 6px;
                  font-size: 11px; font-weight: 500; letter-spacing: .08em;
                  text-transform: uppercase; color: var(--spice);
                  background: var(--spice-dim); b: 1px solid var(--spice-b);
                  padding: 5px 12px; b-radius: var(--r-full); font-family: var(--fu); }

    /* Navigation styles (reused from homepage) */
    .tz-nav {
        position: sticky; top: 0; z-index: 100;
        background: rgba(9,9,11,.88);
        backdrop-filter: blur(20px) saturate(1.4);
        -webkit-backdrop-filter: blur(20px) saturate(1.4);
        b-bottom: 1px solid var(--b);
        height: 60px;
    }
    .tz-nav__inner {
        height: 100%;
        display: flex; align-items: center; justify-content: space-between;
    }
    .tz-nav__logo {
        display: flex; align-items: center; gap: 10px;
        text-decoration: none;
    }
    .tz-nav__mark {
        width: 34px; height: 34px; b-radius: 50%;
        background: var(--spice);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .tz-nav__mark svg { width: 18px; height: 18px; }
    .tz-nav__name {
        font-family: var(--fd);
        font-size: 17px; color: var(--t1); line-height: 1;
    }
    .tz-nav__name span { color: var(--spice); }
    .tz-nav__sub { font-size: 10px; color: var(--t3); margin-top: 2px; letter-spacing: .04em; }
    .tz-nav__links { display: flex; align-items: center; gap: 24px; }
    .tz-nav__link {
        font-size: 13px; font-weight: 400; color: var(--t2);
        text-decoration: none; transition: color .2s; letter-spacing: .01em;
        font-family: var(--fu);
    }
    .tz-nav__link:hover { color: var(--t1); }
    .tz-nav__cta {
        background: var(--spice); color: #fff; font-size: 13px; font-weight: 500;
        padding: 8px 18px; b-radius: var(--r-full); text-decoration: none;
        transition: background .2s, transform .15s; white-space: nowrap;
        font-family: var(--fu);
    }
    .tz-nav__cta:hover { background: #c14e27; transform: translateY(-1px); }
    @media (max-width: 600px) {
        .tz-nav__links .tz-nav__link { display: none; }
    }

    /* Productions specific styles */
    .tz-productions-hero {
        background: linear-gradient(135deg, var(--ink) 0%, var(--ink-2) 100%);
        padding: 80px 20px 60px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .tz-productions-hero::before {
        content: '';
        position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        background: url('{{ asset('images/tttt.jpg') }}') center/cover;
        opacity: 0.1;
        z-index: 0;
    }
    .tz-productions-hero__content {
        position: relative; z-index: 1;
    }
    .tz-productions-hero h1 {
        font-family: var(--fd);
        font-size: clamp(40px, 6vw, 64px);
        font-weight: 800; color: var(--t1);
        margin-bottom: 20px;
    }
    .tz-productions-hero p {
        font-size: 20px; color: var(--t2);
        max-width: 700px; margin: 0 auto 40px;
        line-height: 1.7;
    }

    .tz-productions-section {
        padding: 80px 20px;
    }
    .tz-productions-section--dark { background: var(--ink); }
    .tz-productions-section--light { background: var(--ink-2); }

    .tz-section-head { text-align: center; margin-bottom: 60px; }
    .tz-section-head h2 {
        font-family: var(--fd);
        font-size: clamp(32px, 4vw, 48px);
        font-weight: 800; color: var(--t1);
        margin-bottom: 16px;
    }
    .tz-section-head p {
        font-size: 18px; color: var(--t2);
        max-width: 600px; margin: 0 auto;
        line-height: 1.7;
    }

    .tz-production-grid {
        display: grid; gap: 40px;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    }
    .tz-production-card {
        background: var(--ink-3); b: 1px solid var(--b);
        b-radius: var(--r-xl); overflow: hidden;
        transition: b-color .25s, transform .2s;
    }
    .tz-production-card:hover {
        b-color: var(--spice-b); 
        transform: translateY(-4px);
    }
    .tz-production-card__img {
        height: 240px; overflow: hidden;
        background: var(--ink-4); position: relative;
    }
    .tz-production-card__img img {
        width: 100%; height: 100%; object-fit: cover;
        transition: transform .4s ease;
    }
    .tz-production-card:hover .tz-production-card__img img {
        transform: scale(1.05);
    }
    .tz-production-card__placeholder {
        width: 100%; height: 100%;
        display: flex; align-items: center; justify-content: center;
        font-size: 48px; color: var(--t3);
    }
    .tz-production-card__badge {
        position: absolute; top: 16px; right: 16px;
        background: var(--spice); color: #fff;
        font-size: 12px; font-weight: 600;
        padding: 6px 14px; b-radius: var(--r-full);
        text-transform: uppercase; letter-spacing: .05em;
        font-family: var(--fu);
    }
    .tz-production-card__body {
        padding: 32px;
    }
    .tz-production-card__title {
        font-family: var(--fd);
        font-size: 24px; font-weight: 700; color: var(--t1);
        margin-bottom: 12px;
    }
    .tz-production-card__desc {
        font-size: 16px; color: var(--t2);
        line-height: 1.7; margin-bottom: 20px;
    }
    .tz-production-card__features {
        margin-bottom: 24px;
    }
    .tz-production-card__feature {
        display: flex; align-items: center; gap: 12px;
        margin-bottom: 8px;
        font-size: 14px; color: var(--t2);
    }
    .tz-production-card__feature-icon {
        width: 20px; height: 20px; b-radius: 50%;
        background: var(--spice-dim); b: 1px solid var(--spice-b);
        display: flex; align-items: center; justify-content: center;
        font-size: 10px; color: var(--spice);
        flex-shrink: 0;
    }
    .tz-production-card__price {
        font-family: var(--fd);
        font-size: 20px; font-weight: 700; color: var(--spice);
        margin-bottom: 20px;
    }
    .tz-production-card__btn {
        background: var(--spice); color: #fff;
        font-size: 14px; font-weight: 500;
        padding: 12px 24px; b-radius: var(--r-full);
        text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
        transition: background .2s, transform .15s;
        font-family: var(--fu);
    }
    .tz-production-card__btn:hover {
        background: #c14e27; transform: translateY(-2px);
    }

    /* Video Grid Styles */
    .tz-video-grid {
        display: grid;
        gap: 32px;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    }
    
    .tz-video-card {
        background: var(--ink-3);
        border: 1px solid var(--b);
        border-radius: var(--r-xl);
        overflow: hidden;
        transition: border-color 0.25s, transform 0.2s;
    }
    
    .tz-video-card:hover {
        border-color: var(--spice-b);
        transform: translateY(-4px);
    }
    
    .tz-video-card__embed {
        position: relative;
        padding-bottom: 56.25%; /* 16:9 aspect ratio */
        height: 0;
        overflow: hidden;
        background: var(--ink-4);
    }
    
    .tz-video-card__embed iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: none;
    }
    
    .tz-video-card__body {
        padding: 24px;
    }
    
    .tz-video-card__title {
        font-family: var(--fd);
        font-size: 20px;
        font-weight: 700;
        color: var(--t1);
        margin-bottom: 12px;
    }
    
    .tz-video-card__desc {
        font-size: 14px;
        color: var(--t2);
        line-height: 1.6;
        margin-bottom: 16px;
    }
    
    .tz-video-card__meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
    
    .tz-video-card__badge {
        background: var(--spice);
        color: #fff;
        font-size: 11px;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: var(--r-full);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-family: var(--fu);
    }
    
    .tz-video-card__duration {
        font-size: 13px;
        color: var(--t3);
        font-family: var(--fu);
    }

    .tz-process-section {
        background: var(--ink-3);
        padding: 80px 20px;
    }
    .tz-process-timeline {
        display: grid; gap: 40px;
        max-width: 800px; margin: 0 auto;
    }
    .tz-process-step {
        display: flex; gap: 24px;
        align-items: flex-start;
    }
    .tz-process-step__num {
        width: 48px; height: 48px; b-radius: 50%;
        background: var(--spice); color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-family: var(--fd);
        font-size: 20px; font-weight: 700;
        flex-shrink: 0;
    }
    .tz-process-step__content {
        flex: 1;
    }
    .tz-process-step__title {
        font-family: var(--fd);
        font-size: 20px; font-weight: 700; color: var(--t1);
        margin-bottom: 8px;
    }
    .tz-process-step__desc {
        font-size: 16px; color: var(--t2);
        line-height: 1.7;
    }

    .tz-cta-section {
        background: var(--spice);
        padding: 80px 20px;
        text-align: center;
    }
    .tz-cta-section h2 {
        font-family: var(--fd);
        font-size: clamp(32px, 4vw, 48px);
        font-weight: 800; color: #fff;
        margin-bottom: 20px;
    }
    .tz-cta-section p {
        font-size: 18px; color: rgba(255,255,255,.9);
        max-width: 600px; margin: 0 auto 40px;
        line-height: 1.7;
    }
    .tz-cta-section__actions {
        display: flex; flex-wrap: wrap; gap: 16px; justify-content: center;
    }
    .btn-white {
        background: #fff; color: var(--spice);
        font-size: 16px; font-weight: 600;
        padding: 14px 32px; b-radius: var(--r-full);
        text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
        transition: transform .15s;
        font-family: var(--fu);
    }
    .btn-white:hover { transform: translateY(-2px); }
    .btn-outline-white {
        background: transparent; color: #fff;
        b: 1px solid rgba(255,255,255,.5);
        font-size: 16px; font-weight: 400;
        padding: 14px 28px; b-radius: var(--r-full);
        text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
        transition: b-color .2s, background .2s;
        font-family: var(--fu);
    }
    .btn-outline-white:hover { b-color: #fff; background: rgba(255,255,255,.1); }

    @media (max-width: 768px) {
        .tz-production-grid {
            grid-template-columns: 1fr;
        }
        .tz-video-grid {
            grid-template-columns: 1fr;
            gap: 24px;
        }
        .tz-process-step {
            flex-direction: column;
            text-align: center;
        }
        .tz-process-step__num {
            margin: 0 auto 16px;
        }
    }
</style>
@endpush

{{-- Productions Hero Section --}}
<section class="tz-productions-hero">
    <div class="tz-productions-hero__content">
        <h1>Toddyz Productions</h1>
        <p>Welcome to our video showcase! Experience the authentic flavors and vibrant atmosphere of Toddy'z Family Restaurant through our promotional videos. From signature dishes to behind-the-scenes kitchen action, see what makes us special.</p>
    </div>
</section>

{{-- Video Showcase --}}
<section class="tz-productions-section tz-productions-section--dark">
    <div class="container">
        <div class="tz-section-head">
            <span class="tag">Video Gallery</span>
            <h2>Toddyz Promotional Videos</h2>
            <p>Watch our featured videos showcasing the best of Toddy'z Family Restaurant - from kitchen preparations to satisfied customers enjoying our signature dishes.</p>
        </div>
        
        <div class="tz-video-grid">
            <!-- Featured Video 1 -->
            <div class="tz-video-card">
                <div class="tz-video-card__embed">
                    <iframe 
                        width="100%" 
                        height="315" 
                        src="https://www.youtube.com/embed/X_GP-ZDc3C4" 
                        title="Toddyz Family Restaurant - Signature Kottu Preparation"
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
                </div>
                <div class="tz-video-card__body">
                    <h3 class="tz-video-card__title">Signature Kottu Preparation</h3>
                    <p class="tz-video-card__desc">Watch our chefs prepare the famous Toddy'z kottu with fresh ingredients and traditional techniques.</p>
                    <div class="tz-video-card__meta">
                        <span class="tz-video-card__badge">Featured</span>
                        <span class="tz-video-card__duration">5:42</span>
                    </div>
                </div>
            </div>

            <!-- Video 2 -->
            <div class="tz-video-card">
                <div class="tz-video-card__embed">
                    <iframe 
                        width="100%" 
                        height="315" 
                        src="https://www.youtube.com/embed/X_GP-ZDc3C4" 
                        title="Toddyz Family Restaurant - Kitchen Tour"
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
                </div>
                <div class="tz-video-card__body">
                    <h3 class="tz-video-card__title">Kitchen Tour</h3>
                    <p class="tz-video-card__desc">Take a behind-the-scenes tour of our kitchen and see how we maintain quality and hygiene standards.</p>
                    <div class="tz-video-card__meta">
                        <span class="tz-video-card__badge">Behind the Scenes</span>
                        <span class="tz-video-card__duration">3:28</span>
                    </div>
                </div>
            </div>

            <!-- Video 3 -->
            <div class="tz-video-card">
                <div class="tz-video-card__embed">
                    <iframe 
                        width="100%" 
                        height="315" 
                        src="https://www.youtube.com/embed/X_GP-ZDc3C4" 
                        title="Toddyz Family Restaurant - Customer Stories"
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
                </div>
                <div class="tz-video-card__body">
                    <h3 class="tz-video-card__title">Customer Stories</h3>
                    <p class="tz-video-card__desc">Hear what our customers have to say about their dining experience at Toddy'z Family Restaurant.</p>
                    <div class="tz-video-card__meta">
                        <span class="tz-video-card__badge">Testimonials</span>
                        <span class="tz-video-card__duration">4:15</span>
                    </div>
                </div>
            </div>

            <!-- Video 4 -->
            <div class="tz-video-card">
                <div class="tz-video-card__embed">
                    <iframe 
                        width="100%" 
                        height="315" 
                        src="https://www.youtube.com/embed/X_GP-ZDc3C4" 
                        title="Toddyz Family Restaurant - Special Dishes"
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
                </div>
                <div class="tz-video-card__body">
                    <h3 class="tz-video-card__title">Special Dishes</h3>
                    <p class="tz-video-card__desc">Discover our special menu items including Nasigurang, Biriyani, and family packs.</p>
                    <div class="tz-video-card__meta">
                        <span class="tz-video-card__badge">Menu Special</span>
                        <span class="tz-video-card__duration">6:10</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Our Process -->
<section class="tz-process-section">
    <div class="container">
        <div class="tz-section-head">
            <span class="tag">Our Process</span>
            <h2>How We Work</h2>
            <p>From consultation to execution, we ensure every detail is perfect for your special occasion.</p>
        </div>
        
        <div class="tz-process-timeline">
            <div class="tz-process-step">
                <div class="tz-process-step__num">1</div>
                <div class="tz-process-step__content">
                    <h3 class="tz-process-step__title">Consultation</h3>
                    <p class="tz-process-step__desc">We discuss your requirements, preferences, and budget to create the perfect menu and service plan.</p>
                </div>
            </div>
            
            <div class="tz-process-step">
                <div class="tz-process-step__num">2</div>
                <div class="tz-process-step__content">
                    <h3 class="tz-process-step__title">Menu Planning</h3>
                    <p class="tz-process-step__desc">Our chefs design a customized menu featuring your favorite dishes and seasonal specialties.</p>
                </div>
            </div>
            
            <div class="tz-process-step">
                <div class="tz-process-step__num">3</div>
                <div class="tz-process-step__content">
                    <h3 class="tz-process-step__title">Preparation</h3>
                    <p class="tz-process-step__desc">We source fresh ingredients and prepare everything with attention to detail and quality.</p>
                </div>
            </div>
            
            <div class="tz-process-step">
                <div class="tz-process-step__num">4</div>
                <div class="tz-process-step__content">
                    <h3 class="tz-process-step__title">Execution</h3>
                    <p class="tz-process-step__desc">Our team delivers and sets up everything, ensuring a seamless dining experience for your guests.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="tz-cta-section">
    <div class="container" style="max-width: 800px;">
        <h2>Ready to Create Something Special?</h2>
        <p>Let us bring the authentic flavors of Toddy'z to your next event. Contact us today to discuss your requirements and get a personalized quote.</p>
        <div class="tz-cta-section__actions">
            <a href="{{ url('/contact') }}" class="btn-white">&#128222; Contact Us</a>
            <a href="tel:+94764504325" class="btn-outline-white">Call 076 450 4325</a>
        </div>
    </div>
</section>

@endsection

