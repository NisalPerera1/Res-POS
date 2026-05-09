@extends('layouts.app')

@section('content')
{{-- =====================================================================
     TODDYZ FAMILY RESTAURANT - SPECIALS PAGE
     Design: Spice-Black editorial. Playfair Display + Montserrat.
     Accent: #D85A30 (spice orange). Built for mobile-first, dark-first.
     ===================================================================== --}}

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800;900&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* Design tokens - matching homepage */
    :root {
        --spice:       #D85A30;
        --spice-dim:   rgba(216,90,48,.12);
        --spice-b:     rgba(216,90,48,.28);
        --ink:         #08080A;
        --ink-2:       #0F0F13;
        --ink-3:       #161620;
        --ink-4:       #1E1E2A;
        --b:           rgba(255,255,255,.07);
        --t1:          #F8F8F6;
        --t2:          rgba(248,248,246,.58);
        --t3:          rgba(248,248,246,.32);
        --fd:          'Playfair Display', Georgia, serif;
        --fu:          'Montserrat', system-ui, sans-serif;
        --r:           999px;
    }

    .tz-wrap { max-width: 1120px; margin: 0 auto; padding: 0 48px; }
    @media (max-width: 900px)  { .tz-wrap { padding: 0 24px; } }
    @media (max-width: 480px)  { .tz-wrap { padding: 0 16px; } }

    /* Navigation styles */
    .tz-nav {
        position: sticky; top: 0; z-index: 200;
        height: 64px;
        background: rgba(8,8,10,.9);
        border-bottom: 1px solid var(--b);
        backdrop-filter: blur(24px) saturate(1.3);
        -webkit-backdrop-filter: blur(24px) saturate(1.3);
    }
    .tz-nav__inner { height: 100%; display: flex; align-items: center; justify-content: space-between; }
    .container { max-width: 1120px; margin: 0 auto; padding: 0 48px; }
    @media (max-width: 900px) { .container { padding: 0 24px; } }

    .tz-nav__logo { display: flex; align-items: center; gap: 11px; text-decoration: none; }
    .tz-nav__mark {
        width: 36px; height: 36px; border-radius: 50%;
        background: var(--spice); display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .tz-nav__mark svg { width: 18px; height: 18px; }
    .tz-nav__name { font-family: var(--fd); font-size: 18px; color: var(--t1); line-height: 1; }
    .tz-nav__name span { color: var(--spice); }
    .tz-nav__sub { font-size: 9px; color: var(--t3); letter-spacing: .06em; text-transform: uppercase; margin-top: 2px; }

    .tz-nav__links { display: flex; align-items: center; gap: 28px; }
    .tz-nav__link {
        font-family: var(--fu); font-size: 12px; font-weight: 500;
        color: var(--t2); text-decoration: none; letter-spacing: .02em; transition: color .2s;
    }
    .tz-nav__link:hover { color: var(--t1); }
    .tz-nav__link.text-white { color: var(--t1) !important; }
    .tz-nav__cta {
        background: var(--spice); color: #fff;
        font-family: var(--fu); font-size: 12px; font-weight: 600; letter-spacing: .03em;
        padding: 9px 20px; border-radius: var(--r);
        text-decoration: none; transition: background .2s, transform .15s; white-space: nowrap;
    }
    .tz-nav__cta:hover { background: #bf4e26; transform: translateY(-1px); }

    /* Hero Section */
    .tz-specials-hero {
        background: linear-gradient(135deg, var(--ink) 0%, var(--ink-2) 100%);
        padding: 80px 20px 60px;
        text-align: center;
        border-bottom: 1px solid var(--b);
    }
    .tz-specials-hero h1 {
        font-family: var(--fd);
        font-size: clamp(48px, 6vw, 72px);
        font-weight: 900;
        color: var(--t1);
        margin-bottom: 20px;
        line-height: 0.95;
    }
    .tz-specials-hero p {
        font-size: 18px;
        color: var(--t2);
        max-width: 600px;
        margin: 0 auto 30px;
    }
    .tz-specials-hero .tag {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--spice-dim);
        border: 1px solid var(--spice-b);
        color: var(--spice);
        font-family: var(--fu);
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.09em;
        text-transform: uppercase;
        padding: 8px 16px;
        border-radius: var(--r);
    }

    /* Specials Grid */
    .tz-specials-grid {
        padding: 60px 20px;
    }
    .tz-specials-grid .tz-wrap {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 32px;
    }

    .tz-special-card {
        background: var(--ink-2);
        border: 1px solid var(--b);
        border-radius: 20px;
        overflow: hidden;
        transition: all 0.3s ease;
        position: relative;
    }
    .tz-special-card:hover {
        transform: translateY(-4px);
        border-color: var(--spice-b);
        box-shadow: 0 12px 24px rgba(216, 90, 48, 0.15);
    }

    .tz-special-card__image {
        height: 200px;
        background: var(--ink-3);
        position: relative;
        overflow: hidden;
    }

    .tz-special-card__image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .tz-special-card:hover .tz-special-card__image img {
        transform: scale(1.05);
    }

    .tz-special-card__badge {
        position: absolute;
        top: 16px;
        right: 16px;
        background: var(--spice);
        color: #fff;
        font-family: var(--fu);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        padding: 6px 12px;
        border-radius: var(--r);
    }

    .tz-special-card__content {
        padding: 24px;
    }

    .tz-special-card__category {
        font-family: var(--fu);
        font-size: 11px;
        font-weight: 600;
        color: var(--spice);
        text-transform: uppercase;
        letter-spacing: 0.09em;
        margin-bottom: 8px;
    }

    .tz-special-card__name {
        font-family: var(--fd);
        font-size: 22px;
        font-weight: 800;
        color: var(--t1);
        margin-bottom: 12px;
        line-height: 1.2;
    }

    .tz-special-card__description {
        font-size: 14px;
        color: var(--t2);
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .tz-special-card__pricing {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .tz-special-card__original {
        font-family: var(--fd);
        font-size: 16px;
        color: var(--t3);
        text-decoration: line-through;
    }

    .tz-special-card__special {
        font-family: var(--fd);
        font-size: 28px;
        font-weight: 800;
        color: var(--spice);
    }

    .tz-special-card__expiry {
        font-size: 12px;
        color: var(--t3);
        margin-bottom: 20px;
    }

    .tz-special-card__btn {
        background: var(--spice);
        color: #fff;
        font-family: var(--fu);
        font-size: 14px;
        font-weight: 600;
        padding: 12px 24px;
        border-radius: var(--r);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        width: 100%;
        justify-content: center;
    }
    .tz-special-card__btn:hover {
        background: #c14e27;
        transform: translateY(-1px);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .tz-specials-grid {
            grid-template-columns: 1fr;
            gap: 24px;
            padding: 40px 16px;
        }
        .tz-specials-hero {
            padding: 60px 16px 40px;
        }
    }
</style>
@endpush

<!-- Specials Hero -->
<section class="tz-specials-hero">
    <div class="tz-wrap">
        <div class="tag">Limited Time Offers</div>
        <h1>Special Deals</h1>
        <p>Discover our exclusive specials and save big on your favorite Sri Lankan dishes</p>
    </div>
</section>

<!-- Specials Grid -->
<section class="tz-specials-grid">
    <div class="tz-wrap">
        @foreach($specials as $special)
            <div class="tz-special-card">
                <div class="tz-special-card__image">
                    @if($loop->index < 5)
                        <img src="{{ asset('images/special/' . ['627771512_1415888286993297_5124613482969233399_n.jpg', '629520682_1415888240326635_2407032512923494576_n.jpg', '632157616_1418776196704506_4172568519972827635_n.jpg', '633549745_1418767443372048_916874777951600474_n.jpg', '634087313_1418768166705309_5892816474171573961_n.jpg'][$loop->index]) }}" alt="{{ $special['name'] }}" loading="lazy">
                    @else
                        <img src="{{ asset('images/wwwww.jpg') }}" alt="{{ $special['name'] }}" loading="lazy">
                    @endif
                    <div class="tz-special-card__badge">{{ $special['badge'] }}</div>
                </div>
                <div class="tz-special-card__content">
                    <div class="tz-special-card__category">{{ $special['category'] }}</div>
                    <h3 class="tz-special-card__name">{{ $special['name'] }}</h3>
                    <p class="tz-special-card__description">{{ $special['description'] }}</p>
                    <div class="tz-special-card__pricing">
                        <span class="tz-special-card__original">Rs. {{ number_format($special['original_price']) }}</span>
                        <span class="tz-special-card__special">Rs. {{ number_format($special['special_price']) }}</span>
                    </div>
                    <div class="tz-special-card__expiry">Available until: {{ date('M d, Y', strtotime($special['available_until'])) }}</div>
                    <a href="{{ url('/menu/public') }}" class="tz-special-card__btn">
                        Order Now
                        <span>»</span>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</section>

@endsection
