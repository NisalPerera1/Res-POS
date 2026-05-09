@extends('layouts.app')

@section('content')

@push('styles')
<style>
  /* Only custom styles that Tailwind can't express */
  :root {
    --spice: #D85A30;
    --spice-dim: rgba(216,90,48,.12);
    --spice-b: rgba(216,90,48,.28);
    --ink: #08080A;
    --ink-2: #0F0F13;
    --ink-3: #161620;
    --ink-4: #1E1E2A;
    --b: rgba(255,255,255,.07);
    --t1: #F8F8F6;
    --t2: rgba(248,248,246,.58);
    --t3: rgba(248,248,246,.32);
    --r: 999px;
    --fd: 'Playfair Display', Georgia, serif;
    --fu: 'Montserrat', system-ui, sans-serif;
  }

  /* Ticker animation */
  .ticker-track { animation: ticker 28s linear infinite; }
  .ticker-track:hover { animation-play-state: paused; }

  /* Hero slide */
  .festiva-slide { transition: opacity 2s ease-in-out; }
  .festiva-slide.active { opacity: 0.6 !important; }

  /* Hero slot animations (opacity:0 default then animated in) */
  .hero-badge-anim { opacity: 0; animation: heroBadgeFade 1s 0.3s ease forwards; }
  .hero-title-anim { opacity: 0; animation: heroTitle 2s 0.8s ease forwards; }
  .hero-desc-anim  { opacity: 0; animation: heroDesc 1.2s 2s ease forwards; }
  .hero-btns-anim  { opacity: 0; animation: heroBtns 1s 2.5s ease forwards; }
  .hero-btn-primary-pulse { animation: btnPulse 2s 3s ease-in-out infinite; }
  .hero-btn-ghost-float   { animation: btnFloat 3s 3.2s ease-in-out infinite; }

  /* Image slider */
  .slide { position: absolute; inset: 0; opacity: 0; transition: opacity 1s ease-in-out; }
  .slide.active { opacity: 1; }
  .slide img { width:100%; height:100%; object-fit:cover; }
  .slide.active img { animation: kenBurns 6s ease forwards; }

  /* Slide caption */
  .slide-caption { transform: translateY(8px); opacity: 0; transition: transform .55s ease, opacity .55s ease; }
  .slide.active .slide-caption { transform: translateY(0); opacity: 1; transition-delay: .35s; }

  /* Badge flip */
  .badge-flip-inner { transform-style: preserve-3d; animation: badgeSpin 7s ease-in-out infinite; }
  .badge-face { backface-visibility: hidden; }
  .badge-face-back { transform: rotateY(180deg); }

  /* 3D stat float */
  .stat-float { transform-style: preserve-3d; animation: statFloat 3s ease-in-out infinite; }
  .stat-float:nth-child(2) { animation-delay: .5s; }
  .stat-float:nth-child(3) { animation-delay: 1s; }

  /* Tilt shine */
  .tilt-card { transform-style: preserve-3d; will-change: transform; }
  .tilt-shine {
    position: absolute; inset: 0; border-radius: inherit;
    background: radial-gradient(circle at 50% 50%, rgba(255,255,255,0.10), transparent 65%);
    opacity: 0; pointer-events: none; z-index: 20; transition: opacity .3s;
  }
  .tilt-card:hover .tilt-shine { opacity: 1; }

  /* Category card 3D depth layers */
  .cat-card { transform-style: preserve-3d; }
  .cat-card::before, .cat-card::after {
    content: ''; position: absolute; inset: 0; border-radius: 20px;
    background: var(--ink-4); border: 1px solid var(--b);
    transition: transform .45s cubic-bezier(.4,0,.2,1), opacity .45s ease; z-index: -1;
  }
  .cat-card::before { transform: translateZ(-20px) translateY(8px) scale(.94); opacity: .5; }
  .cat-card::after  { transform: translateZ(-40px) translateY(16px) scale(.88); opacity: .25; }
  .cat-card:hover::before { transform: translateZ(-30px) translateY(14px) scale(.91) rotateX(3deg); opacity: .6; }
  .cat-card:hover::after  { transform: translateZ(-60px) translateY(28px) scale(.84) rotateX(5deg); opacity: .3; }

  /* Dot pill expand */
  .dot-pill { width: 7px; height: 7px; border-radius: var(--r); background: rgba(255,255,255,.28); border: none; padding: 0; cursor: pointer; transition: background .3s, width .3s; }
  .dot-pill.active { background: var(--spice); width: 24px; }
  .slider-dot-menu { width: 10px; height: 10px; border-radius: 50%; background: var(--t3); border: none; cursor: pointer; transition: all .3s; }
  .slider-dot-menu.active { background: var(--spice); box-shadow: 0 0 15px rgba(216,90,48,.5); transform: scale(1.2); }
  .slider-dot-menu:hover { background: rgba(216,90,48,.5); }

  /* Marquee strip */
  .marquee-track { animation: marquee 22s linear infinite; }

  /* Scroll reveal */
  .scroll-reveal { opacity: 0; transform: translateY(30px); transition: opacity .75s ease, transform .75s ease; }
  .scroll-reveal.visible { opacity: 1; transform: translateY(0); }

  /* Menu slider */
  .menu-slider-track { display: flex; transition: transform .5s ease-in-out; }
  .menu-slide { min-width: 100%; display: flex; justify-content: space-around; gap: 30px; flex-wrap: wrap; }

  /* Feature float hover pause */
  .feat-float { animation: float 4s ease-in-out infinite; }
  .feat-float:hover { animation-play-state: paused; }
  .rev-float { animation: float 5s ease-in-out infinite; }

  /* Step shake */
  .step-shake { animation: shake 4s ease-in-out infinite; }
  .step-shake:hover { animation-play-state: paused; }

  /* Plate float */
  .plate-float { animation: float 3s ease-in-out infinite; }

  /* Delivery bike animation */
  @keyframes bikeRide {
    0%   { transform: translateX(-120px); opacity: 0; }
    10%  { opacity: 1; }
    90%  { opacity: 1; }
    100% { transform: translateX(calc(100% + 120px)); opacity: 0; }
  }
  .delivery-bike { animation: bikeRide 6s ease-in-out infinite; }

  /* Ham open state */
  .ham-open span:nth-child(1) { transform: rotate(45deg) translate(5px,5px); }
  .ham-open span:nth-child(2) { opacity: 0; }
  .ham-open span:nth-child(3) { transform: rotate(-45deg) translate(5px,-5px); }

  /* Custom scrollbar for drawer */
  .drawer-scroll::-webkit-scrollbar { width: 4px; }
  .drawer-scroll::-webkit-scrollbar-thumb { background: var(--spice-dim); border-radius: 2px; }

  @keyframes heroBadgeFade {
    0% { opacity:0; transform:translateY(-20px) scale(.9); }
    50% { opacity:.7; transform:translateY(-5px) scale(1.02); }
    100% { opacity:1; transform:translateY(0) scale(1); }
  }
  @keyframes heroTitle {
    0% { opacity:0; transform:translateX(-50px); clip-path:inset(0 100% 0 0); }
    30% { opacity:1; transform:translateX(-10px); clip-path:inset(0 70% 0 0); }
    70% { opacity:1; transform:translateX(0); clip-path:inset(0 0 0 0); }
    100% { opacity:1; transform:translateX(0); clip-path:inset(0 0 0 0); }
  }
  @keyframes heroDesc {
    0% { opacity:0; transform:translateX(-30px); filter:blur(2px); }
    50% { opacity:.8; transform:translateX(-5px); filter:blur(.5px); }
    100% { opacity:1; transform:translateX(0); filter:blur(0); }
  }
  @keyframes heroBtns {
    0% { opacity:0; transform:translateY(20px) scale(.8); }
    60% { opacity:1; transform:translateY(-5px) scale(1.05); }
    80% { opacity:1; transform:translateY(2px) scale(.98); }
    100% { opacity:1; transform:translateY(0) scale(1); }
  }
  @keyframes btnPulse {
    0%,100% { transform:scale(1); box-shadow:0 4px 15px rgba(216,90,48,.3); }
    50% { transform:scale(1.05); box-shadow:0 6px 25px rgba(216,90,48,.5); }
  }
  @keyframes btnFloat {
    0%,100% { transform:translateY(0); }
    50% { transform:translateY(-3px); }
  }
  @keyframes ticker {
    from { transform:translateX(0); }
    to   { transform:translateX(-50%); }
  }
  @keyframes marquee {
    from { transform:translateX(0); }
    to   { transform:translateX(-50%); }
  }
  @keyframes kenBurns {
    from { transform:scale(1); }
    to   { transform:scale(1.08); }
  }
  @keyframes statFloat {
    0%   { transform:rotateX(0deg) rotateY(0deg) translateY(0px); }
    25%  { transform:rotateX(6deg) rotateY(3deg) translateY(-4px); }
    50%  { transform:rotateX(0deg) rotateY(6deg) translateY(-8px); }
    75%  { transform:rotateX(-4deg) rotateY(2deg) translateY(-4px); }
    100% { transform:rotateX(0deg) rotateY(0deg) translateY(0px); }
  }
  @keyframes badgeSpin {
    0%   { transform:rotateY(0deg) rotateX(8deg); }
    45%  { transform:rotateY(170deg) rotateX(-4deg); }
    55%  { transform:rotateY(190deg) rotateX(4deg); }
    100% { transform:rotateY(360deg) rotateX(8deg); }
  }
  @keyframes float {
    0%,100% { transform:translateY(0); }
    50%     { transform:translateY(-10px); }
  }
  @keyframes shake {
    0%,100% { transform:translateX(0); }
    10%,30%,50%,70%,90% { transform:translateX(-2px); }
    20%,40%,60%,80%     { transform:translateX(2px); }
  }
  @keyframes blink {
    0%,100% { opacity:1; }
    50%     { opacity:.3; }
  }
  @keyframes bikeRide {
    0%   { transform:translateX(-120px); opacity:0; }
    10%  { opacity:1; }
    90%  { opacity:1; }
    100% { transform:translateX(calc(100vw + 120px)); opacity:0; }
  }
  @keyframes fadeUp {
    from { opacity:0; transform:translateY(28px); }
    to   { opacity:1; transform:translateY(0); }
  }
</style>
@endpush

<!-- TICKER -->
<div class="h-[30px] bg-spice overflow-hidden flex items-center" aria-hidden="true">
  <div class="ticker-track flex whitespace-nowrap">
    <!-- duplicated 4x for seamless loop -->
    <span class="inline-flex items-center gap-[9px] px-7 font-ui text-[10px] font-semibold uppercase tracking-widest text-white"><span class="w-[3px] h-[3px] rounded-full bg-white/50"></span>Toddy'z Family Restaurant</span>
    <span class="inline-flex items-center gap-[9px] px-7 font-ui text-[10px] font-semibold uppercase tracking-widest text-white"><span class="w-[3px] h-[3px] rounded-full bg-white/50"></span>Authentic Sri Lankan Flavours</span>
    <span class="inline-flex items-center gap-[9px] px-7 font-ui text-[10px] font-semibold uppercase tracking-widest text-white"><span class="w-[3px] h-[3px] rounded-full bg-white/50"></span>Kottu · Biriyani · Nasigurang</span>
    <span class="inline-flex items-center gap-[9px] px-7 font-ui text-[10px] font-semibold uppercase tracking-widest text-white"><span class="w-[3px] h-[3px] rounded-full bg-white/50"></span>Dine In · Takeaway · QR Order</span>
    <span class="inline-flex items-center gap-[9px] px-7 font-ui text-[10px] font-semibold uppercase tracking-widest text-white"><span class="w-[3px] h-[3px] rounded-full bg-white/50"></span>Pambala, Madampe</span>
    <span class="inline-flex items-center gap-[9px] px-7 font-ui text-[10px] font-semibold uppercase tracking-widest text-white"><span class="w-[3px] h-[3px] rounded-full bg-white/50"></span>076 450 4325</span>
    <!-- repeat -->
    <span class="inline-flex items-center gap-[9px] px-7 font-ui text-[10px] font-semibold uppercase tracking-widest text-white"><span class="w-[3px] h-[3px] rounded-full bg-white/50"></span>Toddy'z Family Restaurant</span>
    <span class="inline-flex items-center gap-[9px] px-7 font-ui text-[10px] font-semibold uppercase tracking-widest text-white"><span class="w-[3px] h-[3px] rounded-full bg-white/50"></span>Authentic Sri Lankan Flavours</span>
    <span class="inline-flex items-center gap-[9px] px-7 font-ui text-[10px] font-semibold uppercase tracking-widest text-white"><span class="w-[3px] h-[3px] rounded-full bg-white/50"></span>Kottu · Biriyani · Nasigurang</span>
    <span class="inline-flex items-center gap-[9px] px-7 font-ui text-[10px] font-semibold uppercase tracking-widest text-white"><span class="w-[3px] h-[3px] rounded-full bg-white/50"></span>Dine In · Takeaway · QR Order</span>
    <span class="inline-flex items-center gap-[9px] px-7 font-ui text-[10px] font-semibold uppercase tracking-widest text-white"><span class="w-[3px] h-[3px] rounded-full bg-white/50"></span>Pambala, Madampe</span>
    <span class="inline-flex items-center gap-[9px] px-7 font-ui text-[10px] font-semibold uppercase tracking-widest text-white"><span class="w-[3px] h-[3px] rounded-full bg-white/50"></span>076 450 4325</span>
  </div>
</div>

<!-- NAV -->
<x-navigation />

<!-- HERO -->
<section class="relative min-h-[92vh] flex items-center px-16 overflow-hidden bg-gradient-to-br from-[#0A0C10] to-[#1A1C24] border-b border-spice/20 max-md:px-6">
  <!-- Background slider -->
  <div class="absolute inset-0 z-0 overflow-hidden">
    <div class="absolute inset-0 z-[2] bg-gradient-to-r from-[#0A0C10] via-[rgba(10,12,16,0.85)] to-[rgba(10,12,16,0.25)]"></div>
    <div class="absolute inset-0 z-[3]" style="background:radial-gradient(ellipse 70% 80% at 80% 50%, rgba(216,90,48,0.07) 0%, transparent 70%)"></div>
    <div class="absolute inset-0 z-[1]">
      <div class="festiva-slide absolute inset-0 opacity-0" style="background:url('{{ asset('images/wwwww.jpg') }}') center/cover; opacity:0"></div>
      <div class="festiva-slide active absolute inset-0" style="background:url('{{ asset('images/hq720.jpg') }}') center/cover"></div>
      <div class="festiva-slide absolute inset-0 opacity-0" style="background:url('{{ asset('images/wwwww.jpg') }}') center/cover"></div>
      <div class="festiva-slide absolute inset-0 opacity-0" style="background:url('{{ asset('images/hq720.jpg') }}') center/cover"></div>
    </div>
  </div>

  <!-- Content -->
  <div class="relative z-10 max-w-[580px]">
    <!-- Badge -->
    <div class="hero-badge-anim inline-flex items-center gap-2 bg-spice/10 border border-spice/40 rounded-[40px] px-[14px] py-[5px] text-[12px] font-medium text-spice uppercase tracking-[1px] mb-6">
      <span class="w-1.5 h-1.5 rounded-full bg-spice" style="animation:blink 1.5s infinite"></span>
      Now Open &mdash; Authentic Sri Lankan
    </div>
    <!-- Title -->
    <h1 class="hero-title-anim font-display font-black leading-[1.05] mb-5 text-t1" style="font-size:clamp(40px,6vw,70px)">
      Taste the <em class="text-spice not-italic">Heart</em> of Sri Lanka
    </h1>
    <!-- Sub -->
    <p class="hero-desc-anim font-ui text-[16px] text-t2 leading-[1.75] max-w-[440px] mb-10">
      Experience a culinary journey crafted with traditional spices, inspired by generations of family recipes and served with genuine Sri Lankan hospitality.
    </p>
    <!-- Actions -->
    <div class="hero-btns-anim flex items-center gap-4 flex-wrap max-sm:flex-col">
      <a href="{{ url('/menu/public') }}" class="hero-btn-primary-pulse bg-spice text-[#0d0d0d] no-underline px-8 py-[14px] rounded-[40px] text-[14px] font-semibold tracking-[.5px] inline-block hover:bg-[#bf4e26] hover:-translate-y-0.5 transition-all">Explore Our Menu</a>
      <a href="{{ url('/productions') }}" class="hero-btn-ghost-float bg-transparent text-t1 no-underline border border-t1/30 px-8 py-[14px] rounded-[40px] text-[14px] font-medium tracking-[.5px] inline-block hover:border-spice hover:text-spice transition-all">Our Story</a>
    </div>
  </div>

  <!-- Stats -->
  <div class="absolute right-16 bottom-12 z-10 flex gap-10 max-md:right-6 max-md:bottom-8 max-sm:hidden" style="animation:fadeUp .9s .5s ease both">
    <div class="stat-float text-center">
      <span class="font-display text-[32px] font-bold text-spice block">15+</span>
      <span class="text-[11px] text-t3 uppercase tracking-[1px]">Years</span>
    </div>
    <div class="stat-float text-center">
      <span class="font-display text-[32px] font-bold text-spice block">120</span>
      <span class="text-[11px] text-t3 uppercase tracking-[1px]">Dishes</span>
    </div>
    <div class="stat-float text-center">
      <span class="font-display text-[32px] font-bold text-spice block">4.8&star;</span>
      <span class="text-[11px] text-t3 uppercase tracking-[1px]">Rating</span>
    </div>
  </div>
</section>

<!-- PHILOSOPHY -->
<section class="scroll-reveal py-24 bg-ink-2 px-5 overflow-hidden">
  <div class="max-w-[1120px] mx-auto">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-20 items-center">
      <!-- Image -->
      <div class="relative rounded-[20px] overflow-hidden aspect-[4/5]" style="animation:fadeRight .8s .2s ease both">
        <img src="{{ asset('images/wwwww.jpg') }}" alt="Fresh Sri Lankan Ingredients" class="w-full h-full object-cover rounded-[20px] transition-transform duration-700 hover:scale-[1.03]">
        <!-- 3D Flip Badge -->
        <div class="absolute bottom-8 -left-6 w-[140px] h-[140px]" style="perspective:700px">
          <div class="badge-flip-inner relative w-full h-full" style="transform-style:preserve-3d">
            <!-- Front -->
            <div class="badge-face absolute inset-0 rounded-full flex flex-col items-center justify-center gap-1.5" style="background:radial-gradient(circle at 35% 35%,#E05E34,#D85A30); border:3px solid rgba(255,255,255,.18)">
              <span class="font-display text-[28px] font-black text-white">100%</span>
              <span class="text-[9px] text-white/80 uppercase tracking-[.07em]">Authentic Sri Lankan</span>
            </div>
            <!-- Back -->
            <div class="badge-face badge-face-back absolute inset-0 rounded-full flex flex-col items-center justify-center gap-1.5" style="background:radial-gradient(circle at 65% 35%,#1E1E2A,#0A0C10); border:3px solid rgba(216,90,48,.4)">
              <span class="font-display text-[20px] font-black text-spice text-center leading-tight">Since<br>2009</span>
              <span class="text-[9px] text-white/80 uppercase tracking-[.07em]">Est. Pambala</span>
            </div>
          </div>
        </div>
      </div>
      <!-- Content -->
      <div style="animation:fadeLeft .8s .3s ease both">
        <span class="block text-[11px] font-semibold text-spice uppercase tracking-[2px] mb-3">Our Philosophy</span>
        <h2 class="font-display font-bold leading-[1.15] text-t1 mb-4" style="font-size:clamp(26px,3.5vw,42px)">
          A Passion for <em class="text-spice not-italic">Authentic</em> Sri Lankan Cuisine
        </h2>
        <p class="text-[15px] text-t2 leading-[1.8] my-6 mb-8">
          From local farms to your table, we ensure every ingredient reflects the rich culinary heritage of Sri Lanka. Our chefs bring generations of knowledge to every dish, creating flavors that tell our island's story.
        </p>
        <!-- Features -->
        <div class="flex flex-col gap-3 mb-10">
          <div class="flex items-center gap-3 text-[14px] text-t2">
            <div class="w-[34px] h-[34px] rounded-[9px] bg-spice/10 border border-spice/30 flex items-center justify-center text-[15px] shrink-0">🌶️</div>
            Locally sourced spices &amp; herbs
          </div>
          <div class="flex items-center gap-3 text-[14px] text-t2">
            <div class="w-[34px] h-[34px] rounded-[9px] bg-spice/10 border border-spice/30 flex items-center justify-center text-[15px] shrink-0">📖</div>
            Traditional family recipes
          </div>
          <div class="flex items-center gap-3 text-[14px] text-t2">
            <div class="w-[34px] h-[34px] rounded-[9px] bg-spice/10 border border-spice/30 flex items-center justify-center text-[15px] shrink-0">🥬</div>
            Fresh ingredients daily
          </div>
          <div class="flex items-center gap-3 text-[14px] text-t2">
            <div class="w-[34px] h-[34px] rounded-[9px] bg-spice/10 border border-spice/30 flex items-center justify-center text-[15px] shrink-0">🤝</div>
            Family-style hospitality
          </div>
        </div>
        <a href="{{ url('/productions') }}" class="bg-spice text-[#0d0d0d] border-none px-8 py-[14px] rounded-[40px] text-[14px] font-semibold cursor-pointer tracking-[.5px] font-ui inline-flex items-center gap-2 hover:bg-[#bf4e26] hover:-translate-y-0.5 transition-all no-underline">
          Discover Our Story <span class="transition-transform hover:translate-x-0.5">&raquo;</span>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- CATEGORIES -->
<section class="py-20 bg-ink-2">
  <div class="max-w-[1120px] mx-auto px-12 max-md:px-6 max-sm:px-4">
    <!-- Section Head -->
    <div class="text-center mb-12">
      <span class="inline-flex items-center gap-[7px] bg-spice/10 border border-spice/30 text-spice font-ui text-[10px] font-semibold tracking-[.09em] uppercase py-[5px] px-[13px] rounded-full mb-3">Our Categories</span>
      <h2 class="font-display text-t1 font-extrabold tracking-[-0.02em] leading-[1.15]" style="font-size:clamp(24px,3.5vw,36px)">Try Out Our Variety Of Cuisine</h2>
      <p class="mt-3 text-[14px] text-t2 max-w-[440px] mx-auto leading-[1.72]">Discover dishes made for your mood &mdash; fresh, comforting, or sweet.</p>
    </div>
    <!-- Grid -->
    <div class="grid gap-6 [perspective:1200px]" style="grid-template-columns:repeat(auto-fit,minmax(280px,1fr))">
      <!-- Burger Card -->
      <div class="cat-card tilt-card relative bg-ink-3 border border-white/[.07] rounded-[20px] overflow-hidden transition-all duration-300 hover:border-spice/30 hover:-translate-y-2 hover:shadow-[0_12px_24px_rgba(216,90,48,0.15)] cursor-pointer">
        <div class="tilt-shine rounded-[20px]"></div>
        <div class="relative h-[200px] overflow-hidden">
          <img src="{{ asset('images/wwwww.jpg') }}" alt="Burger" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.08]" loading="lazy">
          <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/40 pointer-events:none"></div>
          <div class="absolute top-4 right-4 w-12 h-12 bg-spice/90 rounded-full flex items-center justify-center text-2xl text-white z-[2] shadow-[0_4px_12px_rgba(216,90,48,0.3)]" style="animation:bounceDot 2s ease-in-out infinite">&nbsp;</div>
        </div>
        <div class="p-6 text-center">
          <h3 class="font-display text-[20px] font-extrabold text-t1 mb-3">Burger</h3>
          <p class="text-[14px] text-t2 leading-[1.6] mb-5">Juicy grilled patties with fresh toppings and special sauces</p>
          <a href="{{ url('/menu/public') }}" class="inline-block bg-spice text-white font-ui text-[12px] font-semibold px-6 py-2.5 rounded-full no-underline hover:bg-[#bf4e26] hover:-translate-y-0.5 transition-all">Explore</a>
        </div>
      </div>

      <!-- Pizza Card -->
      <div class="cat-card tilt-card relative bg-ink-3 border border-white/[.07] rounded-[20px] overflow-hidden transition-all duration-300 hover:border-spice/30 hover:-translate-y-2 hover:shadow-[0_12px_24px_rgba(216,90,48,0.15)] cursor-pointer">
        <div class="tilt-shine rounded-[20px]"></div>
        <div class="relative h-[200px] overflow-hidden">
          <img src="{{ asset('images/hq720.jpg') }}" alt="Pizza" class="w-full h-full object-cover transition-transform duration-500" loading="lazy">
          <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/40 pointer-events:none"></div>
          <div class="absolute top-4 right-4 w-12 h-12 bg-spice/90 rounded-full flex items-center justify-center text-2xl text-white z-[2] shadow-[0_4px_12px_rgba(216,90,48,0.3)]" style="animation:bounceDot 2s .2s ease-in-out infinite">&nbsp;</div>
        </div>
        <div class="p-6 text-center">
          <h3 class="font-display text-[20px] font-extrabold text-t1 mb-3">Pizza</h3>
          <p class="text-[14px] text-t2 leading-[1.6] mb-5">Cheesy Italian classics with fresh toppings and crispy crusts</p>
          <a href="{{ url('/menu/public') }}" class="inline-block bg-spice text-white font-ui text-[12px] font-semibold px-6 py-2.5 rounded-full no-underline hover:bg-[#bf4e26] hover:-translate-y-0.5 transition-all">Explore</a>
        </div>
      </div>

      <!-- Dessert Card -->
      <div class="cat-card tilt-card relative bg-ink-3 border border-white/[.07] rounded-[20px] overflow-hidden transition-all duration-300 hover:border-spice/30 hover:-translate-y-2 hover:shadow-[0_12px_24px_rgba(216,90,48,0.15)] cursor-pointer">
        <div class="tilt-shine rounded-[20px]"></div>
        <div class="relative h-[200px] overflow-hidden">
          <img src="{{ asset('images/wwwww.jpg') }}" alt="Dessert" class="w-full h-full object-cover" loading="lazy">
          <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/40 pointer-events:none"></div>
          <div class="absolute top-4 right-4 w-12 h-12 bg-spice/90 rounded-full flex items-center justify-center text-2xl text-white z-[2] shadow-[0_4px_12px_rgba(216,90,48,0.3)]" style="animation:bounceDot 2s .4s ease-in-out infinite">&nbsp;</div>
        </div>
        <div class="p-6 text-center">
          <h3 class="font-display text-[20px] font-extrabold text-t1 mb-3">Dessert</h3>
          <p class="text-[14px] text-t2 leading-[1.6] mb-5">Sweet treats and indulgent desserts to satisfy your cravings</p>
          <a href="{{ url('/menu/public') }}" class="inline-block bg-spice text-white font-ui text-[12px] font-semibold px-6 py-2.5 rounded-full no-underline hover:bg-[#bf4e26] hover:-translate-y-0.5 transition-all">Explore</a>
        </div>
      </div>

      <!-- Drinks Card -->
      <div class="cat-card tilt-card relative bg-ink-3 border border-white/[.07] rounded-[20px] overflow-hidden transition-all duration-300 hover:border-spice/30 hover:-translate-y-2 hover:shadow-[0_12px_24px_rgba(216,90,48,0.15)] cursor-pointer">
        <div class="tilt-shine rounded-[20px]"></div>
        <div class="relative h-[200px] overflow-hidden">
          <img src="{{ asset('images/hq720.jpg') }}" alt="Drinks" class="w-full h-full object-cover" loading="lazy">
          <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/40 pointer-events:none"></div>
          <div class="absolute top-4 right-4 w-12 h-12 bg-spice/90 rounded-full flex items-center justify-center text-2xl text-white z-[2] shadow-[0_4px_12px_rgba(216,90,48,0.3)]" style="animation:bounceDot 2s .6s ease-in-out infinite">&nbsp;</div>
        </div>
        <div class="p-6 text-center">
          <h3 class="font-display text-[20px] font-extrabold text-t1 mb-3">Drinks</h3>
          <p class="text-[14px] text-t2 leading-[1.6] mb-5">Refreshing beverages and specialty drinks to quench your thirst</p>
          <a href="{{ url('/menu/public') }}" class="inline-block bg-spice text-white font-ui text-[12px] font-semibold px-6 py-2.5 rounded-full no-underline hover:bg-[#bf4e26] hover:-translate-y-0.5 transition-all">Explore</a>
        </div>
      </div>

      <!-- Coffee Card -->
      <div class="cat-card tilt-card relative bg-ink-3 border border-white/[.07] rounded-[20px] overflow-hidden transition-all duration-300 hover:border-spice/30 hover:-translate-y-2 hover:shadow-[0_12px_24px_rgba(216,90,48,0.15)] cursor-pointer">
        <div class="tilt-shine rounded-[20px]"></div>
        <div class="relative h-[200px] overflow-hidden">
          <img src="{{ asset('images/wwwww.jpg') }}" alt="Coffee" class="w-full h-full object-cover" loading="lazy">
          <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/40 pointer-events:none"></div>
          <div class="absolute top-4 right-4 w-12 h-12 bg-spice/90 rounded-full flex items-center justify-center text-2xl text-white z-[2] shadow-[0_4px_12px_rgba(216,90,48,0.3)]" style="animation:bounceDot 2s .8s ease-in-out infinite">&nbsp;</div>
        </div>
        <div class="p-6 text-center">
          <h3 class="font-display text-[20px] font-extrabold text-t1 mb-3">Coffee</h3>
          <p class="text-[14px] text-t2 leading-[1.6] mb-5">Premium brews and specialty coffee drinks to energize your day</p>
          <a href="{{ url('/menu/public') }}" class="inline-block bg-spice text-white font-ui text-[12px] font-semibold px-6 py-2.5 rounded-full no-underline hover:bg-[#bf4e26] hover:-translate-y-0.5 transition-all">Explore</a>
        </div>
      </div>

      <!-- Chicken Card -->
      <div class="cat-card tilt-card relative bg-ink-3 border border-white/[.07] rounded-[20px] overflow-hidden transition-all duration-300 hover:border-spice/30 hover:-translate-y-2 hover:shadow-[0_12px_24px_rgba(216,90,48,0.15)] cursor-pointer">
        <div class="tilt-shine rounded-[20px]"></div>
        <div class="relative h-[200px] overflow-hidden">
          <img src="{{ asset('images/hq720.jpg') }}" alt="Chicken" class="w-full h-full object-cover" loading="lazy">
          <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/40 pointer-events:none"></div>
          <div class="absolute top-4 right-4 w-12 h-12 bg-spice/90 rounded-full flex items-center justify-center text-2xl text-white z-[2] shadow-[0_4px_12px_rgba(216,90,48,0.3)]" style="animation:bounceDot 2s 1s ease-in-out infinite">&nbsp;</div>
        </div>
        <div class="p-6 text-center">
          <h3 class="font-display text-[20px] font-extrabold text-t1 mb-3">Chicken</h3>
          <p class="text-[14px] text-t2 leading-[1.6] mb-5">Grilled and fried chicken specialties with flavorful seasonings</p>
          <a href="{{ url('/menu/public') }}" class="inline-block bg-spice text-white font-ui text-[12px] font-semibold px-6 py-2.5 rounded-full no-underline hover:bg-[#bf4e26] hover:-translate-y-0.5 transition-all">Explore</a>
        </div>
      </div>

      <!-- Fruits Card -->
      <div class="cat-card tilt-card relative bg-ink-3 border border-white/[.07] rounded-[20px] overflow-hidden transition-all duration-300 hover:border-spice/30 hover:-translate-y-2 hover:shadow-[0_12px_24px_rgba(216,90,48,0.15)] cursor-pointer">
        <div class="tilt-shine rounded-[20px]"></div>
        <div class="relative h-[200px] overflow-hidden">
          <img src="{{ asset('images/wwwww.jpg') }}" alt="Fruits" class="w-full h-full object-cover" loading="lazy">
          <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/40 pointer-events:none"></div>
          <div class="absolute top-4 right-4 w-12 h-12 bg-spice/90 rounded-full flex items-center justify-center text-2xl text-white z-[2] shadow-[0_4px_12px_rgba(216,90,48,0.3)]" style="animation:bounceDot 2s 1.2s ease-in-out infinite">&nbsp;</div>
        </div>
        <div class="p-6 text-center">
          <h3 class="font-display text-[20px] font-extrabold text-t1 mb-3">Fruits</h3>
          <p class="text-[14px] text-t2 leading-[1.6] mb-5">Fresh and seasonal fruits packed with vitamins and natural sweetness</p>
          <a href="{{ url('/menu/public') }}" class="inline-block bg-spice text-white font-ui text-[12px] font-semibold px-6 py-2.5 rounded-full no-underline hover:bg-[#bf4e26] hover:-translate-y-0.5 transition-all">Explore</a>
        </div>
      </div>

      <!-- Bakery Card -->
      <div class="cat-card tilt-card relative bg-ink-3 border border-white/[.07] rounded-[20px] overflow-hidden transition-all duration-300 hover:border-spice/30 hover:-translate-y-2 hover:shadow-[0_12px_24px_rgba(216,90,48,0.15)] cursor-pointer">
        <div class="tilt-shine rounded-[20px]"></div>
        <div class="relative h-[200px] overflow-hidden">
          <img src="{{ asset('images/hq720.jpg') }}" alt="Bakery" class="w-full h-full object-cover" loading="lazy">
          <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/40 pointer-events:none"></div>
          <div class="absolute top-4 right-4 w-12 h-12 bg-spice/90 rounded-full flex items-center justify-center text-2xl text-white z-[2] shadow-[0_4px_12px_rgba(216,90,48,0.3)]" style="animation:bounceDot 2s 1.4s ease-in-out infinite">&nbsp;</div>
        </div>
        <div class="p-6 text-center">
          <h3 class="font-display text-[20px] font-extrabold text-t1 mb-3">Bakery</h3>
          <p class="text-[14px] text-t2 leading-[1.6] mb-5">Fresh baked goods, pastries, and artisan breads made daily</p>
          <a href="{{ url('/menu/public') }}" class="inline-block bg-spice text-white font-ui text-[12px] font-semibold px-6 py-2.5 rounded-full no-underline hover:bg-[#bf4e26] hover:-translate-y-0.5 transition-all">Explore</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- WHY CHOOSE US -->
<section class="py-20 bg-ink-2">
  <div class="max-w-[1120px] mx-auto px-12 max-md:px-6 max-sm:px-4">
    <div class="text-center mb-12">
      <span class="inline-flex items-center gap-[7px] bg-spice/10 border border-spice/30 text-spice font-ui text-[10px] font-semibold tracking-[.09em] uppercase py-[5px] px-[13px] rounded-full mb-3">Why Toddy'z</span>
      <h2 class="font-display text-t1 font-extrabold tracking-[-0.02em] leading-[1.15]" style="font-size:clamp(24px,3.5vw,36px)">Crafted for Families,<br>Loved by Everyone</h2>
      <p class="mt-3 text-[14px] text-t2 max-w-[440px] mx-auto">From traditional recipes to fast QR ordering &mdash; every detail considered.</p>
    </div>
    <div class="grid gap-3.5" style="grid-template-columns:repeat(auto-fit,minmax(210px,1fr))">
      <div class="tilt-card feat-float relative bg-ink-3 border border-white/[.07] rounded-[20px] p-8 text-center hover:border-spice/30 hover:-translate-y-2 hover:shadow-[0_12px_24px_rgba(216,90,48,0.15)] transition-all cursor-pointer">
        <div class="tilt-shine rounded-[20px]"></div>
        <div class="w-[42px] h-[42px] rounded-[10px] bg-spice/10 border border-spice/30 flex items-center justify-center text-[19px] mb-4 mx-auto">&nbsp;</div>
        <h3 class="font-display text-[16px] font-extrabold text-t1 mb-2">Authentic Taste</h3>
        <p class="text-[13px] text-t2 leading-[1.65]">Traditional Sri Lankan recipes cooked from scratch. Every kottu and biriyani made fresh daily.</p>
      </div>
      <div class="tilt-card feat-float relative bg-ink-3 border border-white/[.07] rounded-[20px] p-8 text-center hover:border-spice/30 hover:-translate-y-2 hover:shadow-[0_12px_24px_rgba(216,90,48,0.15)] transition-all cursor-pointer" style="animation-delay:.3s">
        <div class="tilt-shine rounded-[20px]"></div>
        <div class="w-[42px] h-[42px] rounded-[10px] bg-spice/10 border border-spice/30 flex items-center justify-center text-[19px] mb-4 mx-auto">&nbsp;</div>
        <h3 class="font-display text-[16px] font-extrabold text-t1 mb-2">Family Friendly</h3>
        <p class="text-[13px] text-t2 leading-[1.65]">Spacious, warm atmosphere with a menu that satisfies everyone from kids to grandparents.</p>
      </div>
      <div class="tilt-card feat-float relative bg-ink-3 border border-white/[.07] rounded-[20px] p-8 text-center hover:border-spice/30 hover:-translate-y-2 hover:shadow-[0_12px_24px_rgba(216,90,48,0.15)] transition-all cursor-pointer" style="animation-delay:.6s">
        <div class="tilt-shine rounded-[20px]"></div>
        <div class="w-[42px] h-[42px] rounded-[10px] bg-spice/10 border border-spice/30 flex items-center justify-center text-[19px] mb-4 mx-auto">&nbsp;</div>
        <h3 class="font-display text-[16px] font-extrabold text-t1 mb-2">QR Table Ordering</h3>
        <p class="text-[13px] text-t2 leading-[1.65]">Scan, browse, and send your order to the kitchen. No app download, no waiting.</p>
      </div>
      <div class="tilt-card feat-float relative bg-ink-3 border border-white/[.07] rounded-[20px] p-8 text-center hover:border-spice/30 hover:-translate-y-2 hover:shadow-[0_12px_24px_rgba(216,90,48,0.15)] transition-all cursor-pointer" style="animation-delay:.9s">
        <div class="tilt-shine rounded-[20px]"></div>
        <div class="w-[42px] h-[42px] rounded-[10px] bg-spice/10 border border-spice/30 flex items-center justify-center text-[19px] mb-4 mx-auto">&nbsp;</div>
        <h3 class="font-display text-[16px] font-extrabold text-t1 mb-2">Takeaway Packs</h3>
        <p class="text-[13px] text-t2 leading-[1.65]">1kg beef packs, family Kottu portions, and Nasigurang boxes ready to go.</p>
      </div>
    </div>
  </div>
</section>

<!-- POPULAR DISHES -->
<section class="py-20 bg-ink" id="tz-featured">
  <div class="max-w-[1120px] mx-auto px-12 max-md:px-6 max-sm:px-4">
    <div class="text-center mb-12">
      <span class="inline-flex items-center gap-[7px] bg-spice/10 border border-spice/30 text-spice font-ui text-[10px] font-semibold tracking-[.09em] uppercase py-[5px] px-[13px] rounded-full mb-3">&nbsp; Popular Dishes</span>
      <h2 class="font-display text-t1 font-extrabold tracking-[-0.02em] leading-[1.15]" style="font-size:clamp(24px,3.5vw,36px)">What Everyone's Ordering</h2>
      <p class="mt-3 text-[14px] text-t2 max-w-[440px] mx-auto">Our most-loved dishes freshly prepared every day.</p>
    </div>
    <div class="grid gap-3.5" style="grid-template-columns:repeat(auto-fill,minmax(240px,1fr))">
      <!-- Dish Card -->
      <article class="tilt-card relative bg-ink-3 border border-white/[.07] rounded-[20px] overflow-hidden flex flex-col hover:border-spice/30 hover:-translate-y-2 hover:shadow-[0_12px_24px_rgba(216,90,48,0.15)] transition-all duration-300 cursor-pointer">
        <div class="tilt-shine rounded-[20px]"></div>
        <div class="h-[220px] overflow-hidden relative bg-ink-4">
          <img src="{{ asset('images/wwwww.jpg') }}" alt="Toddyz Signature Special" class="w-full h-full object-cover transition-transform duration-500 hover:scale-[1.06]" loading="lazy">
          <div class="absolute top-2.5 left-2.5 flex gap-1.5">
            <span class="font-ui text-[9px] font-bold tracking-[.06em] uppercase py-1 px-2.5 rounded-full bg-spice text-white">Chef's Special</span>
            <span class="font-ui text-[9px] font-bold tracking-[.06em] uppercase py-1 px-2.5 rounded-full bg-green-500/10 text-green-400 border border-green-500/20">&star; Popular</span>
          </div>
        </div>
        <div class="p-[16px_18px_20px] flex-1 flex flex-col">
          <div class="font-display text-[15px] font-extrabold text-t1 mb-1.5">Toddyz Signature Special</div>
          <p class="text-[12px] text-t2 leading-[1.58] flex-1 overflow-hidden line-clamp-2">Our exclusive signature dish featuring authentic Sri Lankan flavors with a modern twist.</p>
          <div class="flex items-center justify-between mt-3.5">
            <span class="font-display text-[17px] font-extrabold text-spice">Rs. 850</span>
            <a href="{{ url('/menu?table=1') }}" class="bg-spice/10 text-spice border border-spice/30 font-ui text-[11px] font-semibold px-3.5 py-1.5 rounded-full no-underline hover:bg-spice hover:text-white transition-all">Order &rarr;</a>
          </div>
        </div>
      </article>
      <article class="tilt-card relative bg-ink-3 border border-white/[.07] rounded-[20px] overflow-hidden flex flex-col hover:border-spice/30 hover:-translate-y-2 hover:shadow-[0_12px_24px_rgba(216,90,48,0.15)] transition-all duration-300 cursor-pointer">
        <div class="tilt-shine rounded-[20px]"></div>
        <div class="h-[220px] overflow-hidden relative bg-ink-4">
          <img src="{{ asset('images/hq720.jpg') }}" alt="Kottu" class="w-full h-full object-cover transition-transform duration-500 hover:scale-[1.06]" loading="lazy">
          <div class="absolute top-2.5 left-2.5 flex gap-1.5">
            <span class="font-ui text-[9px] font-bold tracking-[.06em] uppercase py-1 px-2.5 rounded-full bg-spice text-white">Special</span>
            <span class="font-ui text-[9px] font-bold tracking-[.06em] uppercase py-1 px-2.5 rounded-full bg-green-500/10 text-green-400 border border-green-500/20">&nbsp; Instant</span>
          </div>
        </div>
        <div class="p-[16px_18px_20px] flex-1 flex flex-col">
          <div class="font-display text-[15px] font-extrabold text-t1 mb-1.5">Cheese Kottu</div>
          <p class="text-[12px] text-t2 leading-[1.58] flex-1 line-clamp-2">Crispy roti shredded and stir-fried with vegetables, egg, and melted cheese.</p>
          <div class="flex items-center justify-between mt-3.5">
            <span class="font-display text-[17px] font-extrabold text-spice">Rs. 650</span>
            <a href="{{ url('/menu?table=1') }}" class="bg-spice/10 text-spice border border-spice/30 font-ui text-[11px] font-semibold px-3.5 py-1.5 rounded-full no-underline hover:bg-spice hover:text-white transition-all">Order &rarr;</a>
          </div>
        </div>
      </article>
      <article class="tilt-card relative bg-ink-3 border border-white/[.07] rounded-[20px] overflow-hidden flex flex-col hover:border-spice/30 hover:-translate-y-2 hover:shadow-[0_12px_24px_rgba(216,90,48,0.15)] transition-all duration-300 cursor-pointer">
        <div class="tilt-shine rounded-[20px]"></div>
        <div class="h-[220px] overflow-hidden relative bg-ink-4">
          <img src="{{ asset('images/wwwww.jpg') }}" alt="Biriyani" class="w-full h-full object-cover transition-transform duration-500 hover:scale-[1.06]" loading="lazy">
          <div class="absolute top-2.5 left-2.5 flex gap-1.5">
            <span class="font-ui text-[9px] font-bold tracking-[.06em] uppercase py-1 px-2.5 rounded-full bg-spice text-white">Special</span>
          </div>
        </div>
        <div class="p-[16px_18px_20px] flex-1 flex flex-col">
          <div class="font-display text-[15px] font-extrabold text-t1 mb-1.5">Chicken Biriyani</div>
          <p class="text-[12px] text-t2 leading-[1.58] flex-1 line-clamp-2">Fragrant basmati rice layered with tender spiced chicken, slow cooked to perfection.</p>
          <div class="flex items-center justify-between mt-3.5">
            <span class="font-display text-[17px] font-extrabold text-spice">Rs. 750</span>
            <a href="{{ url('/menu?table=1') }}" class="bg-spice/10 text-spice border border-spice/30 font-ui text-[11px] font-semibold px-3.5 py-1.5 rounded-full no-underline hover:bg-spice hover:text-white transition-all">Order &rarr;</a>
          </div>
        </div>
      </article>
      <article class="tilt-card relative bg-ink-3 border border-white/[.07] rounded-[20px] overflow-hidden flex flex-col hover:border-spice/30 hover:-translate-y-2 hover:shadow-[0_12px_24px_rgba(216,90,48,0.15)] transition-all duration-300 cursor-pointer">
        <div class="tilt-shine rounded-[20px]"></div>
        <div class="h-[220px] overflow-hidden relative bg-ink-4">
          <img src="{{ asset('images/hq720.jpg') }}" alt="Nasigurang" class="w-full h-full object-cover transition-transform duration-500 hover:scale-[1.06]" loading="lazy">
          <div class="absolute top-2.5 left-2.5 flex gap-1.5">
            <span class="font-ui text-[9px] font-bold tracking-[.06em] uppercase py-1 px-2.5 rounded-full bg-green-500/10 text-green-400 border border-green-500/20">&nbsp; Instant</span>
          </div>
        </div>
        <div class="p-[16px_18px_20px] flex-1 flex flex-col">
          <div class="font-display text-[15px] font-extrabold text-t1 mb-1.5">Nasigurang Box</div>
          <p class="text-[12px] text-t2 leading-[1.58] flex-1 line-clamp-2">Malaysian-style fried rice with sambal, egg, and your choice of protein.</p>
          <div class="flex items-center justify-between mt-3.5">
            <span class="font-display text-[17px] font-extrabold text-spice">Rs. 580</span>
            <a href="{{ url('/menu?table=1') }}" class="bg-spice/10 text-spice border border-spice/30 font-ui text-[11px] font-semibold px-3.5 py-1.5 rounded-full no-underline hover:bg-spice hover:text-white transition-all">Order &rarr;</a>
          </div>
        </div>
      </article>
    </div>
    <div class="text-center mt-10">
      <a href="{{ url('/menu/public') }}" class="inline-block bg-transparent text-t1 border border-white/20 font-ui text-[13px] font-medium px-8 py-3 rounded-full no-underline hover:bg-white/5 hover:border-white/30 hover:-translate-y-0.5 transition-all">See Full Menu &rarr;</a>
    </div>
  </div>
</section>

<!-- QR STEPS -->
<section class="py-20 bg-ink-3">
  <div class="max-w-[1120px] mx-auto px-12 max-md:px-6 max-sm:px-4">
    <div class="text-center mb-12">
      <span class="inline-flex items-center gap-[7px] bg-spice/10 border border-spice/30 text-spice font-ui text-[10px] font-semibold tracking-[.09em] uppercase py-[5px] px-[13px] rounded-full mb-3">QR Ordering</span>
      <h2 class="font-display text-t1 font-extrabold tracking-[-0.02em] leading-[1.15]" style="font-size:clamp(24px,3.5vw,36px)">Order in Four Simple Steps</h2>
      <p class="mt-3 text-[14px] text-t2 max-w-[440px] mx-auto">No app needed. Scan your table QR and you're ordering in seconds.</p>
    </div>
    <div class="grid gap-3.5" style="grid-template-columns:repeat(auto-fit,minmax(190px,1fr))">
      <div class="step-shake relative bg-ink-3 border border-white/[.07] rounded-[20px] p-[26px_20px] overflow-hidden hover:border-spice/30 transition-colors">
        <div class="font-display text-[64px] font-black absolute top-[-8px] right-2.5 text-white/[.035] leading-none pointer-events-none select-none">1</div>
        <div class="text-2xl mb-3">&nbsp;</div>
        <h3 class="font-display text-[15px] font-extrabold text-t1 mb-1.5">Scan</h3>
        <p class="text-[12px] text-t2 leading-[1.6]">Point your camera at the QR code on your table.</p>
      </div>
      <div class="step-shake relative bg-ink-3 border border-white/[.07] rounded-[20px] p-[26px_20px] overflow-hidden hover:border-spice/30 transition-colors" style="animation-delay:.5s">
        <div class="font-display text-[64px] font-black absolute top-[-8px] right-2.5 text-white/[.035] leading-none pointer-events-none select-none">2</div>
        <div class="text-2xl mb-3">&nbsp;</div>
        <h3 class="font-display text-[15px] font-extrabold text-t1 mb-1.5">Choose</h3>
        <p class="text-[12px] text-t2 leading-[1.6]">Browse the full menu and add items with modifiers.</p>
      </div>
      <div class="step-shake relative bg-ink-3 border border-white/[.07] rounded-[20px] p-[26px_20px] overflow-hidden hover:border-spice/30 transition-colors" style="animation-delay:1s">
        <div class="font-display text-[64px] font-black absolute top-[-8px] right-2.5 text-white/[.035] leading-none pointer-events-none select-none">3</div>
        <div class="text-2xl mb-3">&nbsp;</div>
        <h3 class="font-display text-[15px] font-extrabold text-t1 mb-1.5">Send</h3>
        <p class="text-[12px] text-t2 leading-[1.6]">Confirm &mdash; prints instantly in the kitchen as a KOT.</p>
      </div>
      <div class="step-shake relative bg-ink-3 border border-white/[.07] rounded-[20px] p-[26px_20px] overflow-hidden hover:border-spice/30 transition-colors" style="animation-delay:1.5s">
        <div class="font-display text-[64px] font-black absolute top-[-8px] right-2.5 text-white/[.035] leading-none pointer-events-none select-none">4</div>
        <div class="text-2xl mb-3">&nbsp;</div>
        <h3 class="font-display text-[15px] font-extrabold text-t1 mb-1.5">Enjoy</h3>
        <p class="text-[12px] text-t2 leading-[1.6]">Sit back &mdash; we bring your food straight to the table.</p>
      </div>
    </div>
  </div>
</section>

<!-- REVIEWS -->
<section class="py-20 bg-ink">
  <div class="max-w-[1120px] mx-auto px-12 max-md:px-6 max-sm:px-4">
    <div class="text-center mb-12">
      <span class="inline-flex items-center gap-[7px] bg-spice/10 border border-spice/30 text-spice font-ui text-[10px] font-semibold tracking-[.09em] uppercase py-[5px] px-[13px] rounded-full mb-3">&star; Reviews</span>
      <h2 class="font-display text-t1 font-extrabold tracking-[-0.02em] leading-[1.15]" style="font-size:clamp(24px,3.5vw,36px)">What Our Guests Say</h2>
      <div class="flex justify-center mt-4">
        <span class="inline-flex items-center gap-[7px] bg-green-500/5 border border-green-500/20 py-[5px] px-[13px] rounded-full text-[11px] text-green-400">
          <span class="w-[5px] h-[5px] rounded-full bg-green-400"></span>
          100% recommend on Facebook
        </span>
      </div>
    </div>
    <div class="grid gap-3.5" style="grid-template-columns:repeat(auto-fill,minmax(260px,1fr))">
      <article class="rev-float tilt-card relative bg-ink-3 border border-white/[.07] rounded-[20px] p-[22px] flex flex-col gap-3">
        <div class="tilt-shine rounded-[20px]"></div>
        <div class="flex gap-0.5">
          <span class="text-[#F5A623] text-[13px]">&star;</span><span class="text-[#F5A623] text-[13px]">&star;</span><span class="text-[#F5A623] text-[13px]">&star;</span><span class="text-[#F5A623] text-[13px]">&star;</span><span class="text-[#F5A623] text-[13px]">&star;</span>
        </div>
        <div class="font-display text-[36px] text-spice leading-none -mb-1">"</div>
        <p class="text-[13px] text-t2 leading-[1.68] italic flex-1">Amazing kottu &mdash; best in Chilaw. The cheese roast is absolutely unreal.</p>
        <div class="font-ui text-[10px] text-t3 uppercase tracking-[.05em]">Facebook Review</div>
      </article>
      <article class="rev-float tilt-card relative bg-ink-3 border border-white/[.07] rounded-[20px] p-[22px] flex flex-col gap-3" style="animation-delay:.5s">
        <div class="tilt-shine rounded-[20px]"></div>
        <div class="flex gap-0.5">
          <span class="text-[#F5A623] text-[13px]">&star;</span><span class="text-[#F5A623] text-[13px]">&star;</span><span class="text-[#F5A623] text-[13px]">&star;</span><span class="text-[#F5A623] text-[13px]">&star;</span><span class="text-[#F5A623] text-[13px]">&star;</span>
        </div>
        <div class="font-display text-[36px] text-spice leading-none -mb-1">"</div>
        <p class="text-[13px] text-t2 leading-[1.68] italic flex-1">Came for the Nasigurang, stayed for everything else. Warm atmosphere, fast service.</p>
        <div class="font-ui text-[10px] text-t3 uppercase tracking-[.05em]">Facebook Review</div>
      </article>
      <article class="rev-float tilt-card relative bg-ink-3 border border-white/[.07] rounded-[20px] p-[22px] flex flex-col gap-3" style="animation-delay:1s">
        <div class="tilt-shine rounded-[20px]"></div>
        <div class="flex gap-0.5">
          <span class="text-[#F5A623] text-[13px]">&star;</span><span class="text-[#F5A623] text-[13px]">&star;</span><span class="text-[#F5A623] text-[13px]">&star;</span><span class="text-[#F5A623] text-[13px]">&star;</span><span class="text-[#F5A623] text-[13px]">&star;</span>
        </div>
        <div class="font-display text-[36px] text-spice leading-none -mb-1">"</div>
        <p class="text-[13px] text-t2 leading-[1.68] italic flex-1">The biriyani special is worth the drive. Perfectly spiced and generous portions.</p>
        <div class="font-ui text-[10px] text-t3 uppercase tracking-[.05em]">Facebook Review</div>
      </article>
      <article class="rev-float tilt-card relative bg-ink-3 border border-white/[.07] rounded-[20px] p-[22px] flex flex-col gap-3" style="animation-delay:1.5s">
        <div class="tilt-shine rounded-[20px]"></div>
        <div class="flex gap-0.5">
          <span class="text-[#F5A623] text-[13px]">&star;</span><span class="text-[#F5A623] text-[13px]">&star;</span><span class="text-[#F5A623] text-[13px]">&star;</span><span class="text-[#F5A623] text-[13px]">&star;</span><span class="text-[#F5A623] text-[13px]">&star;</span>
        </div>
        <div class="font-display text-[36px] text-spice leading-none -mb-1">"</div>
        <p class="text-[13px] text-t2 leading-[1.68] italic flex-1">Family pack was perfect for our weekend gathering. Fresh, hot, and absolutely delicious!</p>
        <div class="font-ui text-[10px] text-t3 uppercase tracking-[.05em]">Google Review</div>
      </article>
    </div>
  </div>
</section>

<!-- RESERVE CTA -->
<section id="tz-reserve" class="bg-spice py-[72px] px-5 text-center">
  <div class="max-w-[620px] mx-auto">
    <h2 class="font-display font-black text-white tracking-[-0.02em] mb-3" style="font-size:clamp(26px,4.5vw,44px)">Reserve Your Table Today</h2>
    <p class="font-ui text-[14px] text-white/80 mb-7">Planning a birthday, family gathering, or a night out? Call or WhatsApp &mdash; we'll sort it out.</p>
    <div class="flex gap-3 justify-center flex-wrap">
      <a href="tel:+94764504325" class="bg-white text-spice font-ui text-[13px] font-bold tracking-[.02em] px-8 py-[13px] rounded-full no-underline inline-flex items-center gap-[7px] hover:-translate-y-0.5 transition-transform">&nbsp; Call 076 450 4325</a>
      <a href="https://wa.me/94764504325" class="bg-transparent text-white border border-white/45 font-ui text-[13px] font-medium px-7 py-[13px] rounded-full no-underline inline-flex items-center gap-[7px] hover:border-white hover:bg-white/10 transition-all" target="_blank" rel="noopener">WhatsApp Us</a>
    </div>
  </div>
</section>

<!-- DELIVERY -->
<section class="py-20 bg-ink-2">
  <div class="max-w-[1120px] mx-auto px-12 max-md:px-6 max-sm:px-4">
    <div class="flex items-stretch justify-center gap-[60px] flex-wrap max-md:flex-col max-md:gap-10">
      <!-- Content -->
      <div class="flex-1 min-w-[350px] max-md:min-w-full">
        <h2 class="font-display font-extrabold text-t1 mb-5" style="font-size:clamp(32px,4vw,48px)">Food Delivery</h2>
        <!-- Ornament -->
        <div class="flex items-center gap-3 mb-7 max-md:justify-center">
          <div class="h-[2px] w-10 bg-spice"></div>
          <div class="w-3 h-3 border-2 border-spice rotate-45"></div>
          <div class="w-3 h-3 border-2 border-spice rotate-45"></div>
          <div class="w-3 h-3 border-2 border-spice rotate-45"></div>
          <div class="h-[2px] w-10 bg-spice"></div>
        </div>
        <p class="text-[15px] text-t2 leading-[1.8] mb-10 pb-7 border-b border-white/[.07]">
          Enjoy Toddy'z authentic Sri Lankan flavors delivered right to your doorstep in the Madampe area.
        </p>
        <h4 class="font-ui text-[18px] font-semibold mb-6 tracking-[1px] text-t1">Madampe Delivery Areas</h4>
        <div class="text-[15px] text-t2 py-2 border-b border-white/5 mb-3"><span class="text-spice font-semibold">Madampe Town</span> &nbsp; Min Order - Rs. 600, Fee - Rs. 100</div>
        <div class="text-[15px] text-t2 py-2 border-b border-white/5 mb-3"><span class="text-spice font-semibold">Pambala Area</span> &nbsp; Min Order - Rs. 600, Fee - Rs. 80</div>
        <div class="text-[15px] text-t2 py-2 border-b border-white/5 mb-3"><span class="text-spice font-semibold">Nattandiya</span> &nbsp; Min Order - Rs. 800, Fee - Rs. 120</div>
        <div class="text-[15px] text-t2 py-2 mb-3"><span class="text-spice font-semibold">Wennappuwa</span> &nbsp; Min Order - Rs. 800, Fee - Rs. 150</div>
      </div>
      <!-- Map -->
      <div class="flex-1 min-w-[400px] flex flex-col gap-7 max-md:min-w-full">
        <div class="flex-1 min-h-[450px] border-[10px] border-ink-3 rounded-[20px] shadow-[0_20px_40px_rgba(0,0,0,0.4)] overflow-hidden relative max-md:min-h-[300px]">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7047.956578087915!2d79.82518763168052!3d7.516815451681363!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae2c94130913291%3A0x4d307d644677fe62!2sToddy&#39;s%20restaurant%20Pambala!5e0!3m2!1sen!2slk!4v1775839766225!5m2!1sen!2slk"
            width="100%" height="100%" style="border:0;filter:grayscale(100%) invert(90%) contrast(90%)"
            allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"
            aria-label="Toddyz Restaurant Location Map"></iframe>
          <!-- Delivery bike -->
          <div class="delivery-bike absolute bottom-6 left-0 z-10">
            <svg viewBox="0 0 100 60" width="100" xmlns="http://www.w3.org/2000/svg">
              <path d="M20 35 L35 35 L40 25 L50 25 L55 35 L70 35" stroke="#D85A30" stroke-width="3" fill="none" stroke-linecap="round"/>
              <rect x="32" y="22" width="8" height="3" fill="#D85A30" rx="1"/>
              <path d="M50 25 L50 20 L55 20" stroke="#D85A30" stroke-width="2" fill="none" stroke-linecap="round"/>
              <circle cx="25" cy="40" r="8" stroke="#D85A30" stroke-width="2" fill="none"/>
              <circle cx="65" cy="40" r="8" stroke="#D85A30" stroke-width="2" fill="none"/>
              <line x1="25" y1="32" x2="25" y2="48" stroke="#D85A30" stroke-width="1" opacity=".6"/>
              <line x1="17" y1="40" x2="33" y2="40" stroke="#D85A30" stroke-width="1" opacity=".6"/>
              <line x1="65" y1="32" x2="65" y2="48" stroke="#D85A30" stroke-width="1" opacity=".6"/>
              <line x1="57" y1="40" x2="73" y2="40" stroke="#D85A30" stroke-width="1" opacity=".6"/>
              <rect x="42" y="18" width="12" height="8" fill="#D85A30" rx="1"/>
              <text x="48" y="24" font-size="6" fill="white" text-anchor="middle" font-weight="bold">TZ</text>
            </svg>
          </div>
        </div>
        <!-- CTA Buttons -->
        <div class="flex gap-4 flex-wrap justify-center">
          <a href="tel:+94764504325" class="bg-spice text-white font-ui text-[14px] font-semibold px-8 py-[14px] rounded-full no-underline inline-flex items-center gap-2 hover:bg-[#bf4e26] hover:-translate-y-0.5 transition-all">&nbsp; Call for Delivery</a>
          <a href="https://wa.me/94764504325" class="bg-transparent text-t1 border border-white/20 font-ui text-[14px] font-medium px-8 py-[14px] rounded-full no-underline inline-flex items-center gap-2 hover:bg-white/5 hover:border-white/30 hover:-translate-y-0.5 transition-all" target="_blank" rel="noopener">&nbsp; WhatsApp Order</a>
        </div>
      </div>
    </div>
  </div>
</section>

@push('scripts')
<script>
(function() {
  'use strict';

  // Bottom bar
  var bbar = document.getElementById('tzBBar');
  function checkBBar() {
    if (bbar) bbar.classList.toggle('hidden', window.innerWidth >= 768);
  }
  checkBBar();
  window.addEventListener('resize', checkBBar);

  // Hamburger drawer
  var ham = document.getElementById('tzHam');
  var mask = document.getElementById('tzMask');
  var drawer = document.getElementById('tzDrawer');

  function openDrawer() {
    ham.classList.add('ham-open');
    mask.classList.remove('opacity-0','invisible');
    mask.classList.add('opacity-100','visible');
    mask.style.pointerEvents = 'auto';
    drawer.style.right = '0';
    ham.setAttribute('aria-expanded','true');
    document.body.style.overflow = 'hidden';
  }
  function closeDrawer() {
    ham.classList.remove('ham-open');
    mask.classList.add('opacity-0','invisible');
    mask.classList.remove('opacity-100','visible');
    mask.style.pointerEvents = 'none';
    drawer.style.right = '-100%';
    ham.setAttribute('aria-expanded','false');
    document.body.style.overflow = '';
  }
  if (ham) {
    ham.addEventListener('click', function() {
      if (drawer.style.right === '0px') {
        closeDrawer();
      } else {
        openDrawer();
      }
    });
    mask.addEventListener('click', closeDrawer);
    drawer.querySelectorAll('a').forEach(function(a) { a.addEventListener('click', closeDrawer); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeDrawer(); });
  }

  // Hero festiva slider
  var fSlides = document.querySelectorAll('.festiva-slide');
  var fCur = 0;
  function fNext() {
    fSlides[fCur].classList.remove('active');
    fCur = (fCur + 1) % fSlides.length;
    fSlides[fCur].classList.add('active');
  }
  setInterval(fNext, 5000);

  // Scroll reveal
  var srObserver = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        srObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
  document.querySelectorAll('.scroll-reveal').forEach(function(el) { srObserver.observe(el); });

  // 3D Mouse-tilt
  function attachTilt(selector) {
    document.querySelectorAll(selector).forEach(function(card) {
      var shine = card.querySelector('.tilt-shine');
      card.addEventListener('mousemove', function(e) {
        var r = card.getBoundingClientRect();
        var x = e.clientX - r.left, y = e.clientY - r.top;
        var cx = r.width / 2, cy = r.height / 2;
        var rotY = ((x - cx) / cx) * 14;
        var rotX = -((y - cy) / cy) * 10;
        card.style.transform = 'perspective(700px) rotateX(' + rotX + 'deg) rotateY(' + rotY + 'deg) scale(1.03)';
        card.style.boxShadow = (-rotY * 1.2) + 'px ' + (rotX * 1.2) + 'px 32px rgba(216,90,48,0.20)';
        card.style.borderColor = 'rgba(216,90,48,0.35)';
        if (shine) shine.style.background = 'radial-gradient(circle at ' + x + 'px ' + y + 'px, rgba(255,255,255,0.11), transparent 65%)';
      });
      card.addEventListener('mouseleave', function() {
        card.style.transform = '';
        card.style.boxShadow = '';
        card.style.borderColor = '';
        if (shine) shine.style.background = '';
      });
    });
  }
  attachTilt('.tilt-card');

})();
</script>
@endpush

@endsection