@extends('layouts.app')

@section('content')
{{-- =====================================================================
     TODDYZ FAMILY RESTAURANT - MENU ITEM DETAIL PAGE
     Design: Spice-Black editorial. Playfair Display + Cormorant Garamond.
     Accent: #D85A30 (spice orange). Built for mobile-first, dark-first.
     ===================================================================== --}}

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800;900&family=Cormorant+Garamond:wght@300;400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* Reuse same styles as menu page */
    :root {
        --spice:        #D85A30;
        --spice-dim:    rgba(216,90,48,0.15);
        --spice-glow:   rgba(216,90,48,0.08);
        --ink:          #09090B;
        --ink-2:        #111116;
        --ink-3:        #18181F;
        --ink-4:        #1F1F28;
        --border:       rgba(255,255,255,0.07);
        --border-warm:  rgba(216,90,48,0.25);
        --text-1:       #FAFAF9;
        --text-2:       rgba(250,250,249,0.60);
        --text-3:       rgba(250,250,249,0.35);
        --font-display: 'Playfair Display', serif;
        --font-body:    'Cormorant Garamond', serif;
        --font-ui:      'Montserrat', sans-serif;
        --r-md:         10px;
        --r-lg:         16px;
        --r-xl:         24px;
        --r-full:       999px;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        background: var(--ink);
        color: var(--text-1);
        font-family: var(--font-body);
        -webkit-font-smoothing: antialiased;
        font-size: 18px;
        line-height: 1.6;
    }

    .container  { max-width: 1120px; margin: 0 auto; padding: 0 20px; }
    .tag        { display: inline-flex; align-items: center; gap: 6px;
                  font-size: 11px; font-weight: 500; letter-spacing: .08em;
                  text-transform: uppercase; color: var(--spice);
                  background: var(--spice-dim); border: 1px solid var(--border-warm);
                  padding: 5px 12px; border-radius: var(--r-full); font-family: var(--font-ui); }

    /* Navigation styles (reused from homepage) */
    .tz-nav {
        position: sticky; top: 0; z-index: 100;
        background: rgba(9,9,11,.88);
        backdrop-filter: blur(20px) saturate(1.4);
        -webkit-backdrop-filter: blur(20px) saturate(1.4);
        border-bottom: 1px solid var(--border);
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
        width: 34px; height: 34px; border-radius: 50%;
        background: var(--spice);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .tz-nav__mark svg { width: 18px; height: 18px; }
    .tz-nav__name {
        font-family: var(--font-display);
        font-size: 17px; color: var(--text-1); line-height: 1;
    }
    .tz-nav__name span { color: var(--spice); }
    .tz-nav__sub { font-size: 10px; color: var(--text-3); margin-top: 2px; letter-spacing: .04em; }
    .tz-nav__links { display: flex; align-items: center; gap: 24px; }
    .tz-nav__link {
        font-size: 13px; font-weight: 400; color: var(--text-2);
        text-decoration: none; transition: color .2s; letter-spacing: .01em;
        font-family: var(--font-ui);
    }
    .tz-nav__link:hover { color: var(--text-1); }
    .tz-nav__cta {
        background: var(--spice); color: #fff; font-size: 13px; font-weight: 500;
        padding: 8px 18px; border-radius: var(--r-full); text-decoration: none;
        transition: background .2s, transform .15s; white-space: nowrap;
        font-family: var(--font-ui);
    }
    .tz-nav__cta:hover { background: #c14e27; transform: translateY(-1px); }
    @media (max-width: 600px) {
        .tz-nav__links .tz-nav__link { display: none; }
    }

    /* Menu item specific styles */
    .tz-item-hero {
        background: linear-gradient(135deg, var(--ink) 0%, var(--ink-2) 100%);
        padding: 60px 20px;
        border-bottom: 1px solid var(--border);
    }
    .tz-item-hero__content {
        display: grid; gap: 40px;
        grid-template-columns: 1fr 1fr;
        align-items: center;
        max-width: 1120px; margin: 0 auto;
    }
    .tz-item-hero__img {
        height: 400px; border-radius: var(--r-xl);
        overflow: hidden; background: var(--ink-3);
    }
    .tz-item-hero__img img {
        width: 100%; height: 100%; object-fit: cover;
    }
    .tz-item-hero__placeholder {
        width: 100%; height: 100%;
        display: flex; align-items: center; justify-content: center;
        font-size: 64px; color: var(--text-3);
    }
    .tz-item-hero__info h1 {
        font-family: var(--font-display);
        font-size: clamp(32px, 5vw, 48px);
        font-weight: 800; color: var(--text-1);
        margin-bottom: 16px;
    }
    .tz-item-hero__category {
        font-size: 16px; color: var(--text-3);
        margin-bottom: 20px;
        font-family: var(--font-ui);
        text-transform: uppercase; letter-spacing: .05em;
    }
    .tz-item-hero__desc {
        font-size: 18px; color: var(--text-2);
        line-height: 1.7; margin-bottom: 32px;
    }
    .tz-item-hero__price {
        font-family: var(--font-display);
        font-size: 32px; font-weight: 700; color: var(--spice);
        margin-bottom: 32px;
    }
    .tz-item-hero__actions {
        display: flex; gap: 16px; flex-wrap: wrap;
    }
    .btn-spice {
        background: var(--spice); color: #fff;
        font-size: 16px; font-weight: 500;
        padding: 14px 28px; border-radius: var(--r-full);
        text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
        transition: background .2s, transform .15s;
        font-family: var(--font-ui);
    }
    .btn-spice:hover { background: #c14e27; transform: translateY(-2px); }
    .btn-ghost {
        background: transparent; color: var(--text-1);
        border: 1px solid var(--border);
        font-size: 16px; font-weight: 400;
        padding: 14px 28px; border-radius: var(--r-full);
        text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
        transition: border-color .2s, background .2s, transform .15s;
        font-family: var(--font-ui);
    }
    .btn-ghost:hover { border-color: rgba(255,255,255,.3); background: rgba(255,255,255,.05); transform: translateY(-2px); }
    .tz-badge {
        font-size: 12px; font-weight: 600; letter-spacing: .05em;
        padding: 6px 14px; border-radius: var(--r-full); text-transform: uppercase;
        font-family: var(--font-ui); margin-right: 12px;
    }
    .tz-badge--popular { background: var(--spice); color: #fff; }
    .tz-badge--instant { background: rgba(74,222,128,.15); color: #4ade80; border: 1px solid rgba(74,222,128,.25); }

    .tz-item-section {
        padding: 80px 20px;
    }
    .tz-item-section--dark { background: var(--ink); }
    .tz-item-section--light { background: var(--ink-2); }

    .tz-section-head { text-align: center; margin-bottom: 60px; }
    .tz-section-head h2 {
        font-family: var(--font-display);
        font-size: clamp(28px, 4vw, 40px);
        font-weight: 800; color: var(--text-1);
        margin-bottom: 16px;
    }
    .tz-section-head p {
        font-size: 18px; color: var(--text-2);
        max-width: 600px; margin: 0 auto;
        line-height: 1.7;
    }

    .tz-modifiers-grid {
        display: grid; gap: 24px;
        max-width: 800px; margin: 0 auto;
    }
    .tz-modifier-group {
        background: var(--ink-3); border: 1px solid var(--border);
        border-radius: var(--r-xl); padding: 32px;
    }
    .tz-modifier-group__header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border);
    }
    .tz-modifier-group__name {
        font-family: var(--font-display);
        font-size: 20px; font-weight: 700; color: var(--text-1);
    }
    .tz-modifier-group__required {
        font-size: 12px; color: var(--spice);
        font-family: var(--font-ui);
        text-transform: uppercase; letter-spacing: .05em;
    }
    .tz-modifier-list {
        display: grid; gap: 12px;
    }
    .tz-modifier-item {
        display: flex; align-items: center; justify-content: space-between;
        padding: 12px 16px;
        background: var(--ink-2); border: 1px solid var(--border);
        border-radius: var(--r-lg);
        transition: border-color .2s;
    }
    .tz-modifier-item:hover {
        border-color: var(--border-warm);
    }
    .tz-modifier-name {
        font-size: 16px; color: var(--text-1);
    }
    .tz-modifier-price {
        font-family: var(--font-display);
        font-size: 16px; font-weight: 600; color: var(--spice);
    }

    .tz-related-section {
        background: var(--ink-3);
        padding: 80px 20px;
    }
    .tz-related-grid {
        display: grid; gap: 24px;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    }
    .tz-related-item {
        background: var(--ink-2); border: 1px solid var(--border);
        border-radius: var(--r-xl); padding: 24px;
        text-align: center;
        transition: border-color .25s;
    }
    .tz-related-item:hover {
        border-color: var(--border-warm);
    }
    .tz-related-item__icon {
        font-size: 32px; margin-bottom: 12px;
    }
    .tz-related-item__name {
        font-family: var(--font-display);
        font-size: 18px; font-weight: 700; color: var(--text-1);
        margin-bottom: 8px;
    }
    .tz-related-item__price {
        font-family: var(--font-display);
        font-size: 18px; font-weight: 700; color: var(--spice);
        margin-bottom: 16px;
    }
    .tz-related-item__btn {
        background: var(--spice-dim); color: var(--spice);
        border: 1px solid var(--border-warm);
        font-size: 12px; font-weight: 500;
        padding: 8px 20px; border-radius: var(--r-full);
        text-decoration: none; transition: background .2s;
        font-family: var(--font-ui);
    }
    .tz-related-item__btn:hover {
        background: var(--spice); color: #fff;
    }

    @media (max-width: 768px) {
        .tz-item-hero__content {
            grid-template-columns: 1fr;
        }
        .tz-item-hero__img {
            height: 300px;
        }
        .tz-related-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

<!-- Menu Item Hero -->
<section class="tz-item-hero">
    <div class="tz-item-hero__content">
        <div class="tz-item-hero__img">
            @if(!empty($item->image))
                <img src="{{ '/storage/menu_items/' . $item->image }}" alt="{{ $item->name }}" loading="lazy">
            @else
                <div class="tz-item-hero__placeholder">&#127859;</div>
            @endif
        </div>
        <div class="tz-item-hero__info">
            <div class="tz-item-hero__category">{{ $item->category->name ?? 'Uncategorized' }}</div>
            <h1>{{ $item->name }}</h1>
            @if(!empty($item->description))
                <p class="tz-item-hero__desc">{{ $item->description }}</p>
            @endif
            <div class="tz-item-hero__price">Rs. {{ number_format((float) $item->price, 0) }}</div>
            <div class="tz-item-hero__actions">
                <div style="display: flex; align-items: center; margin-bottom: 16px;">
                    @if($item->is_popular)
                        <span class="tz-badge tz-badge--popular">Popular</span>
                    @endif
                    @if($item->is_instant)
                        <span class="tz-badge tz-badge--instant">Instant</span>
                    @endif
                </div>
                <a href="{{ url('/menu?table=1&item=' . $item->id) }}" class="btn-spice">Order Now &rarr;</a>
                <a href="{{ url('/menu/public') }}" class="btn-ghost">Back to Menu</a>
            </div>
        </div>
    </div>
</section>

<!-- Modifiers Section -->
@if($item->modifierGroups && $item->modifierGroups->count() > 0)
<section class="tz-item-section tz-item-section--dark">
    <div class="container">
        <div class="tz-section-head">
            <span class="tag">Customize Your Order</span>
            <h2>Available Modifiers</h2>
            <p>Enhance your dish with these delicious additions and modifications.</p>
        </div>
        
        <div class="tz-modifiers-grid">
            @foreach($item->modifierGroups as $group)
                @if($group->modifiers && $group->modifiers->count() > 0)
                    <div class="tz-modifier-group">
                        <div class="tz-modifier-group__header">
                            <div class="tz-modifier-group__name">{{ $group->name }}</div>
                            @if($group->is_required)
                                <div class="tz-modifier-group__required">Required</div>
                            @endif
                        </div>
                        <div class="tz-modifier-list">
                            @foreach($group->modifiers as $modifier)
                                <div class="tz-modifier-item">
                                    <div class="tz-modifier-name">{{ $modifier->name }}</div>
                                    <div class="tz-modifier-price">
                                        @if($modifier->price > 0)
                                            +Rs. {{ number_format((float) $modifier->price, 0) }}
                                        @else
                                            No charge
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Related Items Section -->
@if($relatedItems && $relatedItems->count() > 0)
<section class="tz-related-section">
    <div class="container">
        <div class="tz-section-head">
            <span class="tag">You Might Also Like</span>
            <h2>Related Dishes</h2>
            <p>More delicious options from the same category.</p>
        </div>
        
        <div class="tz-related-grid">
            @foreach($relatedItems as $relatedItem)
                <div class="tz-related-item">
                    <div class="tz-related-item__icon">
                        @if(!empty($relatedItem->icon))
                            {{ $relatedItem->icon }}
                        @else
                            &#127859;
                        @endif
                    </div>
                    <div class="tz-related-item__name">{{ $relatedItem->name }}</div>
                    <div class="tz-related-item__price">Rs. {{ number_format((float) $relatedItem->price, 0) }}</div>
                    <a href="{{ url('/menu/'.$relatedItem->id) }}" class="tz-related-item__btn">View Details</a>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection
