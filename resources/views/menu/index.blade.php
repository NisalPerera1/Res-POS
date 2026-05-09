@extends('layouts.app')

@section('content')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400;1,700&family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">

<style>
:root {
    --spice:        #D85A30;
    --spice-soft:   rgba(216,90,48,.10);
    --spice-border: rgba(216,90,48,.22);
    --ink:          #080809;
    --ink-2:        #0E0E11;
    --ink-3:        #151518;
    --ink-4:        #1C1C21;
    --ink-5:        #242429;
    --glass:        rgba(255,255,255,.04);
    --glass-b:      rgba(255,255,255,.08);
    --glass-h:      rgba(255,255,255,.06);
    --t1:           #F5F4F0;
    --t2:           rgba(245,244,240,.60);
    --t3:           rgba(245,244,240,.32);
    --t4:           rgba(245,244,240,.16);
    --fd:           'Playfair Display', Georgia, serif;
    --fds:          'DM Serif Display', Georgia, serif;
    --fu:           'DM Sans', system-ui, sans-serif;
    --ease:         cubic-bezier(.25,.46,.45,.94);
    --ease-spring:  cubic-bezier(.34,1.56,.64,1);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    background: var(--ink);
    color: var(--t1);
    font-family: var(--fu);
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    overflow-x: hidden;
}

/* ── SCROLLBAR ── */
::-webkit-scrollbar { width: 4px; }
::-webkit-scrollbar-track { background: var(--ink-2); }
::-webkit-scrollbar-thumb { background: var(--ink-5); border-radius: 2px; }

/* ── LAYOUT ── */
.container { max-width: 1160px; margin: 0 auto; padding: 0 24px; }
.container--wide { max-width: 1360px; margin: 0 auto; padding: 0 24px; }

/* ── NAV ── */
.tz-nav {
    position: sticky; top: 0; z-index: 200;
    height: 64px;
    background: rgba(8,8,9,.82);
    backdrop-filter: blur(24px) saturate(1.6);
    -webkit-backdrop-filter: blur(24px) saturate(1.6);
    border-bottom: 1px solid var(--glass-b);
}
.tz-nav__inner {
    height: 100%;
    display: flex; align-items: center; justify-content: space-between;
}
.tz-nav__logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
.tz-nav__mark {
    width: 36px; height: 36px; border-radius: 50%;
    background: var(--spice);
    display: grid; place-items: center; flex-shrink: 0;
}
.tz-nav__mark svg { width: 18px; height: 18px; }
.tz-nav__wordmark { display: flex; flex-direction: column; }
.tz-nav__name {
    font-family: var(--fd); font-size: 16px; font-weight: 700;
    color: var(--t1); line-height: 1.1;
    letter-spacing: .01em;
}
.tz-nav__name span { color: var(--spice); }
.tz-nav__sub { font-size: 9px; color: var(--t3); letter-spacing: .12em; text-transform: uppercase; margin-top: 1px; }

.tz-nav__links { display: flex; align-items: center; gap: 8px; }
.tz-nav__link {
    font-size: 13px; font-weight: 400; color: var(--t2);
    text-decoration: none; padding: 6px 14px; border-radius: 8px;
    transition: color .2s var(--ease), background .2s var(--ease);
}
.tz-nav__link:hover { color: var(--t1); background: var(--glass); }
.tz-nav__link--active { color: var(--t1); background: var(--glass); }
.tz-nav__cta {
    background: var(--spice); color: #fff;
    font-size: 13px; font-weight: 500;
    padding: 9px 20px; border-radius: 40px;
    text-decoration: none; margin-left: 8px;
    transition: background .2s, transform .15s var(--ease-spring);
    white-space: nowrap;
}
.tz-nav__cta:hover { background: #c14e27; transform: translateY(-1px); }

@media (max-width: 680px) {
    .tz-nav__links .tz-nav__link { display: none; }
}

/* ── HERO ── */
.tz-hero {
    position: relative; overflow: hidden;
    padding: 100px 24px 80px;
    border-bottom: 1px solid var(--glass-b);
}
.tz-hero__bg {
    position: absolute; inset: 0; pointer-events: none;
    background:
        radial-gradient(ellipse 60% 60% at 80% 50%, rgba(216,90,48,.08) 0%, transparent 70%),
        radial-gradient(ellipse 40% 40% at 20% 80%, rgba(216,90,48,.05) 0%, transparent 60%);
}
.tz-hero__grid {
    position: absolute; inset: 0; pointer-events: none;
    background-image:
        linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
    background-size: 60px 60px;
    mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 20%, transparent 80%);
    -webkit-mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 20%, transparent 80%);
}
.tz-hero__inner { position: relative; text-align: center; }
.tz-hero__eyebrow {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: 11px; font-weight: 500; letter-spacing: .10em; text-transform: uppercase;
    color: var(--spice);
    background: var(--spice-soft);
    border: 1px solid var(--spice-border);
    padding: 6px 16px; border-radius: 40px;
    margin-bottom: 28px;
}
.tz-hero__eyebrow::before {
    content: '';
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--spice);
    animation: pulse 2s ease-in-out infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: .5; transform: scale(.7); }
}
.tz-hero__title {
    font-family: var(--fd);
    font-size: clamp(44px, 7vw, 80px);
    font-weight: 900; line-height: .95;
    color: var(--t1); margin-bottom: 24px;
    letter-spacing: -.02em;
}
.tz-hero__title em {
    font-style: italic; color: var(--spice);
    font-weight: 400;
}
.tz-hero__desc {
    font-size: 17px; color: var(--t2); line-height: 1.7;
    max-width: 520px; margin: 0 auto 40px;
}

/* STICKY CATEGORY NAV */
.tz-cat-nav {
    position: sticky; top: 64px; z-index: 100;
    background: rgba(8,8,9,.90);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid var(--glass-b);
    overflow: hidden;
    height: 60px;
}
.tz-cat-nav__scroll {
    display: flex; align-items: center; gap: 4px;
    overflow-x: auto; padding: 12px 24px;
    scrollbar-width: none;
}
.tz-cat-nav__scroll::-webkit-scrollbar { display: none; }
.tz-cat-nav__btn {
    flex-shrink: 0;
    display: flex; align-items: center; gap: 8px;
    font-size: 13px; font-weight: 500;
    color: var(--t3); background: transparent;
    border: 1px solid transparent;
    padding: 7px 16px; border-radius: 40px;
    cursor: pointer; text-decoration: none;
    transition: color .2s, background .2s, border-color .2s;
    font-family: var(--fu);
    white-space: nowrap;
}
.tz-cat-nav__btn:hover { color: var(--t1); background: var(--glass); border-color: var(--glass-b); }
.tz-cat-nav__btn.active { color: var(--spice); background: var(--spice-soft); border-color: var(--spice-border); }
.tz-cat-nav__icon { font-size: 14px; }

/* ── FEATURED STRIP ── */
.tz-featured {
    padding: 64px 0 48px;
    border-bottom: 1px solid var(--glass-b);
    background: var(--ink);
}
.tz-section-label {
    display: flex; align-items: center; gap: 12px;
    margin-bottom: 36px;
}
.tz-section-label__tag {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 10px; font-weight: 600; letter-spacing: .10em; text-transform: uppercase;
    color: var(--spice);
    background: var(--spice-soft); border: 1px solid var(--spice-border);
    padding: 5px 14px; border-radius: 40px;
}
.tz-section-label__line {
    flex: 1; height: 1px; background: var(--glass-b);
}

.tz-featured-scroll {
    display: flex; gap: 16px; overflow-x: auto; padding-bottom: 8px;
    scrollbar-width: none;
}
.tz-featured-scroll::-webkit-scrollbar { display: none; }

.tz-feat-card {
    flex-shrink: 0; width: 200px;
    background: var(--ink-3); border: 1px solid var(--glass-b);
    border-radius: 20px; padding: 24px 20px;
    text-align: center;
    text-decoration: none;
    transition: border-color .25s, transform .25s var(--ease-spring), background .2s;
    display: flex; flex-direction: column; align-items: center; gap: 10px;
}
.tz-feat-card:hover {
    border-color: var(--spice-border);
    background: var(--ink-4);
    transform: translateY(-4px);
}
.tz-feat-card__emoji {
    width: 56px; height: 56px; border-radius: 16px;
    background: var(--ink-4); border: 1px solid var(--glass-b);
    display: grid; place-items: center; font-size: 26px;
}
.tz-feat-card__name {
    font-family: var(--fd); font-size: 15px; font-weight: 700;
    color: var(--t1); line-height: 1.3;
}
.tz-feat-card__price {
    font-family: var(--fd); font-size: 17px; font-weight: 700;
    color: var(--spice);
}
.tz-feat-card__cta {
    font-size: 11px; font-weight: 500; letter-spacing: .04em;
    color: var(--spice);
    background: var(--spice-soft); border: 1px solid var(--spice-border);
    padding: 6px 14px; border-radius: 40px;
    transition: background .2s, color .2s;
    margin-top: auto;
}
.tz-feat-card:hover .tz-feat-card__cta { background: var(--spice); color: #fff; }

/* ── FULL MENU ── */
.tz-menu { padding: 0 0 80px; }

.tz-category {
    padding: 72px 0 0;
    scroll-margin-top: 124px;
}
.tz-cat-header {
    display: flex; align-items: flex-end; justify-content: space-between;
    margin-bottom: 32px; padding-bottom: 20px;
    border-bottom: 1px solid var(--glass-b);
    gap: 16px;
}
.tz-cat-header__left { display: flex; align-items: center; gap: 16px; }
.tz-cat-header__icon {
    width: 52px; height: 52px; border-radius: 16px;
    background: var(--spice-soft); border: 1px solid var(--spice-border);
    display: grid; place-items: center; font-size: 26px; flex-shrink: 0;
}
.tz-cat-header__title {
    font-family: var(--fd);
    font-size: clamp(26px, 3.5vw, 36px);
    font-weight: 900; color: var(--t1); letter-spacing: -.01em;
}
.tz-cat-header__count {
    font-size: 13px; color: var(--t3); margin-top: 2px;
    font-weight: 400;
}
.tz-cat-header__divider {
    flex: 1; height: 1px; background: var(--glass-b); margin-bottom: 10px;
    display: none;
}
@media (min-width: 600px) { .tz-cat-header__divider { display: block; } }

/* ── MENU GRID ── */
.tz-menu-grid {
    display: grid; gap: 16px;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
}

.tz-item {
    display: flex; gap: 0;
    background: var(--ink-3); border: 1px solid var(--glass-b);
    border-radius: 20px; overflow: hidden;
    text-decoration: none;
    transition: border-color .25s, transform .25s var(--ease), background .2s;
    position: relative;
}
.tz-item:hover {
    border-color: var(--spice-border);
    transform: translateY(-3px);
    background: var(--ink-4);
}

.tz-item__thumb {
    width: 130px; flex-shrink: 0;
    background: var(--ink-4);
    position: relative; overflow: hidden;
}
.tz-item__thumb img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform .4s var(--ease);
}
.tz-item:hover .tz-item__thumb img { transform: scale(1.06); }
.tz-item__thumb-placeholder {
    width: 100%; height: 100%; min-height: 140px;
    display: grid; place-items: center;
    font-size: 40px;
}

.tz-item__body {
    flex: 1; padding: 20px; display: flex; flex-direction: column; gap: 8px;
    min-width: 0;
}
.tz-item__badges {
    display: flex; gap: 6px; flex-wrap: wrap;
}
.tz-badge {
    font-size: 9px; font-weight: 600; letter-spacing: .08em;
    text-transform: uppercase; padding: 3px 9px; border-radius: 40px;
    font-family: var(--fu);
}
.tz-badge--hot   { background: var(--spice); color: #fff; }
.tz-badge--instant { background: rgba(52,211,153,.14); color: #34d399; border: 1px solid rgba(52,211,153,.22); }
.tz-badge--veg   { background: rgba(74,222,128,.12); color: #4ade80; border: 1px solid rgba(74,222,128,.20); }

.tz-item__name {
    font-family: var(--fd);
    font-size: 18px; font-weight: 700; color: var(--t1);
    line-height: 1.25; letter-spacing: .00em;
}
.tz-item__desc {
    font-size: 13px; color: var(--t3); line-height: 1.6;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
    overflow: hidden; flex: 1;
}
.tz-item__footer {
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
    margin-top: 4px;
}
.tz-item__price {
    font-family: var(--fd);
    font-size: 22px; font-weight: 900; color: var(--spice);
    letter-spacing: -.01em;
}
.tz-item__price span {
    font-size: 12px; font-weight: 400; color: var(--t3);
    font-family: var(--fu); letter-spacing: 0;
}
.tz-item__action {
    width: 36px; height: 36px; border-radius: 50%;
    background: var(--spice-soft); border: 1px solid var(--spice-border);
    display: grid; place-items: center; flex-shrink: 0;
    transition: background .2s, transform .2s var(--ease-spring);
}
.tz-item__action svg { width: 14px; height: 14px; }
.tz-item:hover .tz-item__action { background: var(--spice); transform: scale(1.1); }
.tz-item:hover .tz-item__action svg path { stroke: #fff; }

/* MOBILE CARD LAYOUT */
@media (max-width: 640px) {
    .tz-menu-grid { grid-template-columns: 1fr; }
    .tz-item { flex-direction: row; }
    .tz-item__thumb { width: 110px; }
    .tz-item__thumb-placeholder { min-height: 120px; font-size: 32px; }
    .tz-item__body { padding: 16px; }
    .tz-item__name { font-size: 16px; }
}

/* ── EMPTY STATE ── */
.tz-empty {
    text-align: center; padding: 100px 24px;
    color: var(--t3);
}
.tz-empty__icon { font-size: 56px; margin-bottom: 20px; }
.tz-empty h3 {
    font-family: var(--fd); font-size: 28px; font-weight: 700;
    color: var(--t2); margin-bottom: 8px;
}
.tz-empty p { font-size: 15px; }

/* ── FOOTER CTA BAND ── */
.tz-cta-band {
    background: var(--ink-2); border-top: 1px solid var(--glass-b);
    padding: 64px 24px;
    text-align: center;
}
.tz-cta-band h2 {
    font-family: var(--fd); font-size: clamp(28px, 4vw, 40px);
    font-weight: 900; color: var(--t1); margin-bottom: 12px;
    letter-spacing: -.01em;
}
.tz-cta-band p { font-size: 16px; color: var(--t2); margin-bottom: 28px; }
.tz-cta-band__actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
.tz-btn {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: 14px; font-weight: 500; font-family: var(--fu);
    padding: 12px 26px; border-radius: 40px; text-decoration: none;
    transition: background .2s, transform .15s var(--ease-spring);
}
.tz-btn--primary { background: var(--spice); color: #fff; }
.tz-btn--primary:hover { background: #c14e27; transform: translateY(-2px); }
.tz-btn--ghost {
    background: transparent; color: var(--t1);
    border: 1px solid var(--glass-b);
}
.tz-btn--ghost:hover { background: var(--glass); border-color: var(--glass-h); }

/* ── SCROLL ANIMATION ── */
.tz-category { opacity: 0; transform: translateY(24px); transition: opacity .5s var(--ease), transform .5s var(--ease); }
.tz-category.visible { opacity: 1; transform: translateY(0); }
</style>
@endpush


{{-- HERO --}}
<section class="tz-hero">
    <div class="tz-hero__bg"></div>
    <div class="tz-hero__grid"></div>
    <div class="tz-hero__inner">
        <div class="tz-hero__eyebrow">Sri Lankan Cuisine</div>
        <h1 class="tz-hero__title">
            Our Full<br>
            <em>Menu</em>
        </h1>
        <p class="tz-hero__desc">
            From fiery kottu to fragrant biriyani — every dish tells a story of authentic Sri Lankan heritage.
        </p>
    </div>
</section>

{{-- STICKY CATEGORY NAV --}}
@if($categories->count() > 0)
<nav class="tz-cat-nav">
    <div class="tz-cat-nav__scroll">
        @foreach($categories as $category)
            @if($category->menuItems->count() > 0)
                <a href="#cat-{{ $category->id }}" class="tz-cat-nav__btn" data-cat="{{ $category->id }}">
                    <span class="tz-cat-nav__icon">{{ $category->icon ?? '🍽' }}</span>
                    {{ $category->name }}
                </a>
            @endif
        @endforeach
    </div>
</nav>
@endif

{{-- FEATURED --}}
@if(isset($featuredItems) && $featuredItems->count() > 0)
<section class="tz-featured">
    <div class="container">
        <div class="tz-section-label">
            <div class="tz-section-label__tag">⭐ Customer Favorites</div>
            <div class="tz-section-label__line"></div>
        </div>
        <div class="tz-featured-scroll">
            @foreach($featuredItems as $item)
                <a href="{{ url('/menu/'.$item->id) }}" class="tz-feat-card">
                    <div class="tz-feat-card__emoji">
                        {{ $item->icon ?? '🍽' }}
                    </div>
                    <div class="tz-feat-card__name">{{ $item->name }}</div>
                    <div class="tz-feat-card__price">Rs.&nbsp;{{ number_format((float)$item->price, 0) }}</div>
                    <div class="tz-feat-card__cta">View Details</div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- FULL MENU --}}
<section class="tz-menu">
    <div class="container">
        @forelse($categories as $category)
            @if($category->menuItems->count() > 0)
                <div class="tz-category" id="cat-{{ $category->id }}">

                    <div class="tz-cat-header">
                        <div class="tz-cat-header__left">
                            <div class="tz-cat-header__icon">{{ $category->icon ?? '🍽' }}</div>
                            <div>
                                <div class="tz-cat-header__title">{{ $category->name }}</div>
                                <div class="tz-cat-header__count">{{ $category->menuItems->count() }} {{ Str::plural('dish', $category->menuItems->count()) }} available</div>
                            </div>
                        </div>
                        <div class="tz-cat-header__divider"></div>
                    </div>

                    <div class="tz-menu-grid">
                        @foreach($category->menuItems as $item)
                            <a href="{{ url('/menu/'.$item->id) }}" class="tz-item">
                                <div class="tz-item__thumb">
                                    @if(!empty($item->image))
                                        <img src="{{ '/storage/menu_items/'.$item->image }}" alt="{{ $item->name }}" loading="lazy">
                                    @else
                                        <div class="tz-item__thumb-placeholder">{{ $item->icon ?? '🍽' }}</div>
                                    @endif
                                </div>
                                <div class="tz-item__body">
                                    @if($item->is_popular || $item->is_instant)
                                        <div class="tz-item__badges">
                                            @if($item->is_popular)
                                                <span class="tz-badge tz-badge--hot">Popular</span>
                                            @endif
                                            @if($item->is_instant)
                                                <span class="tz-badge tz-badge--instant">Instant</span>
                                            @endif
                                        </div>
                                    @endif
                                    <div class="tz-item__name">{{ $item->name }}</div>
                                    @if(!empty($item->description))
                                        <p class="tz-item__desc">{{ $item->description }}</p>
                                    @endif
                                    <div class="tz-item__footer">
                                        <div class="tz-item__price">
                                            Rs.&nbsp;{{ number_format((float)$item->price, 0) }}
                                            <span>LKR</span>
                                        </div>
                                        <div class="tz-item__action">
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M5 12h14M13 6l6 6-6 6" stroke="#D85A30" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                </div>
            @endif
        @empty
            <div class="tz-empty">
                <div class="tz-empty__icon">🍽</div>
                <h3>Menu Coming Soon</h3>
                <p>We're preparing something delicious. Please check back shortly!</p>
            </div>
        @endforelse
    </div>
</section>

{{-- FOOTER CTA --}}
<section class="tz-cta-band">
    <h2>Ready to Dine?</h2>
    <p>Visit us in Negombo or give us a call to reserve your table today.</p>
    <div class="tz-cta-band__actions">
        <a href="tel:+94XXXXXXXXX" class="tz-btn tz-btn--primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.95 11a19.79 19.79 0 01-3.07-8.67A2 2 0 012.86 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 9.91a16 16 0 006.16 6.16l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Call to Reserve
        </a>
        <a href="{{ url('/contact') }}" class="tz-btn tz-btn--ghost">Get Directions</a>
    </div>
</section>

@push('scripts')
<script>
(function () {
    // Scroll-reveal for category sections
    const cats = document.querySelectorAll('.tz-category');
    const io = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) { e.target.classList.add('visible'); io.unobserve(e.target); }
        });
    }, { threshold: 0.06 });
    cats.forEach(c => io.observe(c));

    // Sticky category nav active state
    const navBtns = document.querySelectorAll('.tz-cat-nav__btn');
    const catSections = document.querySelectorAll('.tz-category[id]');
    const catIo = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                navBtns.forEach(b => b.classList.remove('active'));
                const active = document.querySelector(`.tz-cat-nav__btn[data-cat="${e.target.id.replace('cat-', '')}"]`);
                if (active) {
                    active.classList.add('active');
                    // Only scroll into view if not already visible
                    const rect = active.getBoundingClientRect();
                    if (rect.left < 0 || rect.right > window.innerWidth) {
                        active.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                    }
                }
            }
        });
    }, { 
        rootMargin: '-100px 0px -300px 0px',
        threshold: 0.1
    });
    catSections.forEach(s => catIo.observe(s));

    // Smooth scroll for nav links
    navBtns.forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            const target = document.querySelector(btn.getAttribute('href'));
            if (target) {
                const offset = 124; // Account for sticky nav height
                const targetPosition = target.offsetTop - offset;
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
})();
</script>
@endpush

@endsection