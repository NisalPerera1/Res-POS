@extends('layouts.app')
@section('content')

@push('styles')
<style>
:root {
    --spice:#D85A30; --spice-dim:rgba(216,90,48,.13); --spice-b:rgba(216,90,48,.28);
    --ink:#09090B; --ink-2:#111116; --ink-3:#18181F; --ink-4:#1F1F28;
    --b:rgba(255,255,255,.07); --t1:#FAFAF9; --t2:rgba(250,250,249,.60); --t3:rgba(250,250,249,.32);
    --fd:'Playfair Display',serif; --fu:'Montserrat',sans-serif; --r:999px;
}
*{ box-sizing:border-box; margin:0; padding:0; }
body{ background:var(--ink); color:var(--t1); font-family:var(--fu); font-size:15px; line-height:1.6; -webkit-font-smoothing:antialiased; }


/* ── HERO ── */
.tz-hero {
    min-height:55vh; display:flex; align-items:center; justify-content:center;
    background:linear-gradient(135deg,#0A0C10 0%,#14161E 100%);
    position:relative; overflow:hidden; text-align:center; padding:80px 24px 60px;
    border-bottom:1px solid rgba(216,90,48,.18);
}
.tz-hero-orb {
    position:absolute; border-radius:50%; pointer-events:none;
    animation:tz-orb 12s ease-in-out infinite alternate;
}
.tz-hero-orb:nth-child(1){ width:500px; height:500px; left:-180px; top:-180px; background:radial-gradient(circle,rgba(216,90,48,.08) 0%,transparent 70%); }
.tz-hero-orb:nth-child(2){ width:400px; height:400px; right:-120px; bottom:-120px; background:radial-gradient(circle,rgba(216,90,48,.06) 0%,transparent 70%); animation-delay:-5s; }
@keyframes tz-orb{ from{transform:translate(0,0) scale(1);} to{transform:translate(30px,20px) scale(1.1);} }

.tz-hero__content{ position:relative; z-index:2; max-width:680px; }
.tz-hero__tag {
    display:inline-flex; align-items:center; gap:8px;
    background:var(--spice-dim); border:1px solid var(--spice-b);
    color:var(--spice); font-size:10px; font-weight:600; letter-spacing:.1em; text-transform:uppercase;
    padding:6px 16px; border-radius:var(--r); margin-bottom:24px;
    animation:tz-fadeup .7s ease both;
}
.tz-hero__dot{ width:6px; height:6px; border-radius:50%; background:var(--spice); animation:tz-blink 2s infinite; }
@keyframes tz-blink{ 0%,100%{opacity:1;} 50%{opacity:.3;} }
@keyframes tz-fadeup{ from{opacity:0;transform:translateY(20px);} to{opacity:1;transform:translateY(0);} }

.tz-hero h1 {
    font-family:var(--fd); font-size:clamp(40px,7vw,72px); font-weight:900;
    line-height:1.05; letter-spacing:-.02em; color:var(--t1); margin-bottom:20px;
    animation:tz-fadeup .8s .15s ease both;
}
.tz-hero h1 em{ color:var(--spice); font-style:normal; }
.tz-hero__sub{ font-size:16px; color:var(--t2); max-width:500px; margin:0 auto 36px; line-height:1.75; animation:tz-fadeup .8s .3s ease both; }
.tz-hero__acts{ display:flex; gap:14px; justify-content:center; flex-wrap:wrap; animation:tz-fadeup .8s .45s ease both; }

.tz-btn-pri{ background:var(--spice); color:#fff; font-family:var(--fu); font-size:13px; font-weight:600; padding:13px 28px; border-radius:var(--r); text-decoration:none; transition:background .2s,transform .15s; display:inline-flex; align-items:center; gap:8px; }
.tz-btn-pri:hover{ background:#bf4e26; transform:translateY(-2px); }
.tz-btn-ghost{ background:transparent; color:var(--t1); border:1px solid rgba(255,255,255,.2); font-family:var(--fu); font-size:13px; font-weight:500; padding:13px 28px; border-radius:var(--r); text-decoration:none; transition:all .2s; display:inline-flex; align-items:center; gap:8px; }
.tz-btn-ghost:hover{ border-color:var(--spice); color:var(--spice); }

/* ── STATS ── */
.tz-stats-row{ display:flex; justify-content:center; background:var(--ink-3); border-bottom:1px solid var(--b); perspective:1000px; flex-wrap:wrap; }
.tz-stat-tile {
    flex:1; max-width:240px; padding:28px 24px; text-align:center;
    border-right:1px solid var(--b); transform-style:preserve-3d;
    animation:tz-stat-rock 4s ease-in-out infinite; cursor:default; transition:background .25s;
}
.tz-stat-tile:last-child{ border-right:none; }
.tz-stat-tile:hover{ background:var(--ink-4); animation-play-state:paused; transform:perspective(600px) rotateX(8deg) rotateY(5deg) translateY(-6px) scale(1.04); box-shadow:0 12px 28px rgba(216,90,48,.18); }
.tz-stat-tile:nth-child(2){ animation-delay:.6s; }
.tz-stat-tile:nth-child(3){ animation-delay:1.2s; }
.tz-stat-tile:nth-child(4){ animation-delay:1.8s; }
@keyframes tz-stat-rock{
    0%,100%{ transform:rotateX(0) rotateY(0) translateY(0); }
    30%{ transform:rotateX(4deg) rotateY(3deg) translateY(-3px); }
    60%{ transform:rotateX(-3deg) rotateY(-2deg) translateY(-5px); }
}
.tz-stat-num{ font-family:var(--fd); font-size:28px; font-weight:900; color:var(--spice); display:block; }
.tz-stat-lbl{ font-size:11px; color:var(--t3); letter-spacing:.07em; text-transform:uppercase; margin-top:4px; display:block; }

/* ── SECTION SHELL ── */
.tz-sec{ padding:80px 0; }
.tz-sec--dark{ background:var(--ink); }
.tz-sec--mid{ background:var(--ink-2); }
.tz-sec--card{ background:var(--ink-3); }
.tz-wrap{ max-width:1100px; margin:0 auto; padding:0 48px; }
@media(max-width:900px){ .tz-wrap{ padding:0 24px; } }
@media(max-width:480px){ .tz-wrap{ padding:0 16px; } }

.tz-sec-head{ text-align:center; margin-bottom:56px; }
.tz-sec-head .tz-tag{ display:inline-flex; align-items:center; gap:6px; background:var(--spice-dim); border:1px solid var(--spice-b); color:var(--spice); font-size:10px; font-weight:600; letter-spacing:.1em; text-transform:uppercase; padding:5px 14px; border-radius:var(--r); margin-bottom:14px; }
.tz-sec-head h2{ font-family:var(--fd); font-size:clamp(26px,4vw,40px); font-weight:900; color:var(--t1); letter-spacing:-.02em; line-height:1.15; margin-bottom:12px; }
.tz-sec-head p{ font-size:14px; color:var(--t2); max-width:460px; margin:0 auto; line-height:1.75; }

/* ── CONTACT CARDS (3D tilt + depth layers) ── */
.tz-cards-grid{ display:grid; gap:16px; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); perspective:1200px; }
.tz-ccard {
    background:var(--ink-3); border:1px solid var(--b); border-radius:20px;
    padding:32px 24px; text-align:center;
    transform-style:preserve-3d; will-change:transform;
    transition:border-color .3s, box-shadow .3s;
    position:relative; overflow:hidden; cursor:default;
}
.tz-ccard-shine{ position:absolute; inset:0; border-radius:20px; opacity:0; pointer-events:none; z-index:10; transition:opacity .3s; }
.tz-ccard:hover .tz-ccard-shine{ opacity:1; }
.tz-ccard::before,.tz-ccard::after{ content:''; position:absolute; inset:0; border-radius:20px; background:var(--ink-4); border:1px solid var(--b); z-index:-1; transition:transform .45s cubic-bezier(.4,0,.2,1),opacity .45s; }
.tz-ccard::before{ transform:translateZ(-20px) translateY(8px) scale(.94); opacity:.45; }
.tz-ccard::after{ transform:translateZ(-40px) translateY(16px) scale(.88); opacity:.22; }
.tz-ccard:hover::before{ transform:translateZ(-30px) translateY(14px) scale(.91) rotateX(3deg); opacity:.55; }
.tz-ccard:hover::after{ transform:translateZ(-55px) translateY(26px) scale(.84) rotateX(5deg); }

.tz-ccard__icon{ width:56px; height:56px; border-radius:50%; margin:0 auto 20px; background:var(--spice-dim); border:1px solid var(--spice-b); display:flex; align-items:center; justify-content:center; font-size:24px; transform:translateZ(18px); transition:transform .3s; }
.tz-ccard:hover .tz-ccard__icon{ transform:translateZ(28px) scale(1.08); }
.tz-ccard h3{ font-family:var(--fd); font-size:18px; font-weight:800; color:var(--t1); margin-bottom:8px; transform:translateZ(12px); }
.tz-ccard p{ font-size:13px; color:var(--t2); line-height:1.65; margin-bottom:20px; transform:translateZ(8px); }
.tz-ccard a{ display:inline-flex; align-items:center; gap:6px; background:var(--spice); color:#fff; font-size:12px; font-weight:600; padding:9px 20px; border-radius:var(--r); text-decoration:none; transition:background .2s; transform:translateZ(14px); }
.tz-ccard a:hover{ background:#bf4e26; }

/* ── FORM SECTION ── */
.tz-form-layout{ display:grid; grid-template-columns:1fr 1.2fr; gap:60px; align-items:center; }
@media(max-width:768px){ .tz-form-layout{ grid-template-columns:1fr; } .tz-img-side{ display:none; } }

/* Image 3D */
.tz-img-side{ position:relative; perspective:800px; }
.tz-img-3d {
    border-radius:24px; overflow:hidden; transform-style:preserve-3d;
    animation:tz-img-float 6s ease-in-out infinite;
    box-shadow:0 40px 80px rgba(0,0,0,.5), 0 0 0 1px rgba(216,90,48,.15);
    border:1px solid rgba(216,90,48,.18);
}
@keyframes tz-img-float{
    0%,100%{ transform:rotateY(-4deg) rotateX(3deg) translateY(0); }
    33%{ transform:rotateY(3deg) rotateX(-2deg) translateY(-12px); }
    66%{ transform:rotateY(-2deg) rotateX(4deg) translateY(-6px); }
}
.tz-img-3d img{ width:100%; height:auto; display:block; border-radius:24px; }

.tz-img-badge {
    position:absolute; bottom:-16px; left:-16px;
    background:var(--spice); color:#fff; border-radius:16px; padding:16px 20px;
    font-family:var(--fd); font-weight:700;
    box-shadow:0 8px 24px rgba(216,90,48,.35);
    animation:tz-badge-float 3s ease-in-out infinite; z-index:5;
}
@keyframes tz-badge-float{
    0%,100%{ transform:translateY(0) rotate(-1deg); }
    50%{ transform:translateY(-8px) rotate(1deg); }
}
.tz-img-badge .big{ font-size:22px; display:block; }
.tz-img-badge .sml{ font-size:10px; opacity:.85; letter-spacing:.06em; text-transform:uppercase; }

/* Form box */
.tz-form-box{ background:var(--ink-2); border:1px solid var(--b); border-radius:24px; padding:44px; }
.tz-form-box h2{ font-family:var(--fd); font-size:28px; font-weight:900; color:var(--t1); margin-bottom:8px; }
.tz-form-sub{ font-size:13px; color:var(--t2); margin-bottom:32px; line-height:1.7; }
.tz-fg{ margin-bottom:18px; }
.tz-fg label{ display:block; font-size:12px; font-weight:600; letter-spacing:.06em; text-transform:uppercase; color:var(--t3); margin-bottom:7px; }
.tz-fg input,.tz-fg textarea,.tz-fg select {
    width:100%; background:var(--ink-3); border:1px solid var(--b);
    border-radius:10px; padding:13px 16px; font-size:14px; color:var(--t1); font-family:var(--fu);
    transition:border-color .2s, background .2s, transform .2s, box-shadow .2s;
}
.tz-fg input:focus,.tz-fg textarea:focus,.tz-fg select:focus {
    outline:none; border-color:var(--spice); background:var(--ink-4);
    transform:translateY(-2px) scale(1.005);
    box-shadow:0 6px 20px rgba(216,90,48,.14);
}
.tz-fg textarea{ min-height:110px; resize:vertical; }
.tz-form-row{ display:grid; grid-template-columns:1fr 1fr; gap:16px; }
@media(max-width:480px){ .tz-form-row{ grid-template-columns:1fr; } }
.tz-submit-btn {
    width:100%; background:var(--spice); color:#fff; font-family:var(--fu);
    font-size:14px; font-weight:700; padding:15px 28px; border:none; border-radius:var(--r);
    cursor:pointer; display:flex; align-items:center; justify-content:center; gap:10px;
    transition:background .2s, transform .15s; margin-top:8px;
}
.tz-submit-btn:hover{ background:#bf4e26; transform:translateY(-2px); }

/* ── INFO CARDS ── */
.tz-info-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:24px; }
.tz-info-card {
    background:var(--ink-3); border:1px solid var(--b); border-radius:20px; padding:32px 28px;
    text-align:center; transform-style:preserve-3d;
    animation:tz-info-float 5s ease-in-out infinite; transition:border-color .3s;
}
.tz-info-card:hover{ border-color:var(--spice-b); animation-play-state:paused; transform:translateY(-8px) rotateX(3deg); box-shadow:0 16px 32px rgba(216,90,48,.12); }
.tz-info-card:nth-child(2){ animation-delay:1s; }
.tz-info-card:nth-child(3){ animation-delay:2s; }
@keyframes tz-info-float{ 0%,100%{transform:translateY(0) rotateX(0);} 50%{transform:translateY(-6px) rotateX(2deg);} }
.tz-info-card .ico{ font-size:28px; margin-bottom:14px; display:block; animation:tz-ico-pulse 2.5s ease-in-out infinite; }
.tz-info-card:nth-child(2) .ico{ animation-delay:.8s; }
.tz-info-card:nth-child(3) .ico{ animation-delay:1.6s; }
@keyframes tz-ico-pulse{ 0%,100%{transform:scale(1);} 50%{transform:scale(1.15);} }
.tz-info-card h3{ font-family:var(--fd); font-size:18px; font-weight:800; color:var(--t1); margin-bottom:12px; }
.tz-info-card p{ font-size:13px; color:var(--t2); line-height:1.75; }
.tz-info-card a{ color:var(--spice); text-decoration:none; }
.tz-info-card a:hover{ color:#bf4e26; }

/* ── MAP SECTION ── */
.tz-map-layout{ display:grid; grid-template-columns:1fr 1.5fr; gap:48px; align-items:center; }
@media(max-width:768px){ .tz-map-layout{ grid-template-columns:1fr; } }
.tz-map-text h2{ font-family:var(--fd); font-size:clamp(26px,4vw,38px); font-weight:900; color:var(--t1); margin-bottom:16px; letter-spacing:-.02em; }
.tz-map-text p{ font-size:14px; color:var(--t2); line-height:1.8; margin-bottom:28px; }
.tz-zones{ display:flex; flex-direction:column; gap:10px; }
.tz-zone{ display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:var(--ink-3); border:1px solid var(--b); border-radius:10px; font-size:13px; transition:border-color .2s,transform .2s; }
.tz-zone:hover{ border-color:var(--spice-b); transform:translateX(4px); }
.tz-zone__name{ color:var(--spice); font-weight:600; }
.tz-zone__info{ color:var(--t2); }
.tz-map-frame {
    border-radius:20px; overflow:hidden;
    border:8px solid var(--ink-3);
    box-shadow:0 24px 48px rgba(0,0,0,.45), 0 0 0 1px rgba(216,90,48,.15);
    animation:tz-map-tilt 8s ease-in-out infinite;
}
@keyframes tz-map-tilt{
    0%,100%{ transform:perspective(800px) rotateY(-2deg) rotateX(1deg); }
    50%{ transform:perspective(800px) rotateY(2deg) rotateX(-1deg); }
}
.tz-map-frame iframe{ width:100%; height:340px; border:none; display:block; filter:grayscale(80%) invert(85%) contrast(90%); }

/* ── CTA BAND ── */
.tz-cta{ background:var(--spice); padding:72px 24px; text-align:center; position:relative; overflow:hidden; }
.tz-cta::before{ content:''; position:absolute; inset:0; background:radial-gradient(ellipse 60% 80% at 80% 50%,rgba(255,255,255,.07) 0%,transparent 70%); }
.tz-cta h2{ font-family:var(--fd); font-size:clamp(28px,5vw,46px); font-weight:900; color:#fff; letter-spacing:-.02em; margin-bottom:12px; position:relative; }
.tz-cta p{ font-size:14px; color:rgba(255,255,255,.82); margin-bottom:28px; position:relative; }
.tz-cta__acts{ display:flex; gap:12px; justify-content:center; flex-wrap:wrap; position:relative; }
.tz-btn-white{ background:#fff; color:var(--spice); font-family:var(--fu); font-size:13px; font-weight:700; padding:13px 28px; border-radius:var(--r); text-decoration:none; display:inline-flex; align-items:center; gap:7px; transition:transform .15s; }
.tz-btn-white:hover{ transform:translateY(-2px); }
.tz-btn-white-o{ background:transparent; color:#fff; border:1px solid rgba(255,255,255,.4); font-family:var(--fu); font-size:13px; font-weight:500; padding:13px 26px; border-radius:var(--r); text-decoration:none; display:inline-flex; align-items:center; gap:7px; transition:all .2s; }
.tz-btn-white-o:hover{ border-color:#fff; background:rgba(255,255,255,.1); }
</style>
@endpush

{{-- ── HERO ── --}}
<section class="tz-hero">
    <div class="tz-hero-orb"></div>
    <div class="tz-hero-orb"></div>
    <div class="tz-hero__content">
        <div class="tz-hero__tag"><span class="tz-hero__dot"></span>Toddy&rsquo;z Restaurant</div>
        <h1>Let&rsquo;s <em>Connect</em></h1>
        <p class="tz-hero__sub">Reservations, catering, or just a question — our team is always ready with genuine Sri Lankan hospitality.</p>
        <div class="tz-hero__acts">
            <a href="tel:+94764504325" class="tz-btn-pri">&#128222; Call Now</a>
            <a href="https://wa.me/94764504325" class="tz-btn-ghost" target="_blank">WhatsApp Us</a>
        </div>
    </div>
</section>

{{-- ── STATS ROW ── --}}
<div class="tz-stats-row">
    <div class="tz-stat-tile"><span class="tz-stat-num">15+</span><span class="tz-stat-lbl">Years Open</span></div>
    <div class="tz-stat-tile"><span class="tz-stat-num">120</span><span class="tz-stat-lbl">Menu Items</span></div>
    <div class="tz-stat-tile"><span class="tz-stat-num">4.8&#9733;</span><span class="tz-stat-lbl">Rating</span></div>
    <div class="tz-stat-tile"><span class="tz-stat-num">24h</span><span class="tz-stat-lbl">Reply Time</span></div>
</div>

{{-- ── CONTACT METHOD CARDS ── --}}
<section class="tz-sec tz-sec--dark">
    <div class="tz-wrap">
        <div class="tz-sec-head">
            <span class="tz-tag">Contact Options</span>
            <h2>Reach Out Anytime</h2>
            <p>Choose the most convenient way to connect. We&rsquo;re always ready to serve.</p>
        </div>
        <div class="tz-cards-grid" id="tzCCards">
            <div class="tz-ccard">
                <div class="tz-ccard-shine"></div>
                <div class="tz-ccard__icon">&#128222;</div>
                <h3>Call Us</h3>
                <p>Speak directly for instant assistance, reservations, or catering inquiries.</p>
                <a href="tel:+94764504325">076 450 4325</a>
            </div>
            <div class="tz-ccard">
                <div class="tz-ccard-shine"></div>
                <div class="tz-ccard__icon">&#128172;</div>
                <h3>WhatsApp</h3>
                <p>Quick messages, menu questions, or photos for event planning.</p>
                <a href="https://wa.me/94764504325" target="_blank">Start Chat</a>
            </div>
            <div class="tz-ccard">
                <div class="tz-ccard-shine"></div>
                <div class="tz-ccard__icon">&#9993;</div>
                <h3>Email Us</h3>
                <p>Detailed inquiries, catering proposals, or feedback. Reply in 24h.</p>
                <a href="mailto:toddyz.res2sl@gmail.com">Send Email</a>
            </div>
            <div class="tz-ccard">
                <div class="tz-ccard-shine"></div>
                <div class="tz-ccard__icon">&#127968;</div>
                <h3>Visit Us</h3>
                <p>Experience our flavors in person at Pambala, Madampe.</p>
                <a href="https://maps.google.com/?q=Pambala+Kakkapalliya+Madampe" target="_blank">Get Directions</a>
            </div>
        </div>
    </div>
</section>

{{-- ── CONTACT FORM ── --}}
<section class="tz-sec tz-sec--card">
    <div class="tz-wrap">
        <div class="tz-sec-head">
            <span class="tz-tag">Send Message</span>
            <h2>Tell Us How We Can Help</h2>
            <p>Fill in the form and we&rsquo;ll get back to you within 24 hours.</p>
        </div>
        <div class="tz-form-layout">
            <div class="tz-img-side">
                <div class="tz-img-3d">
                    <img src="{{ asset('images/cheff2.png') }}" alt="Toddy'z Chef">
                </div>
                <div class="tz-img-badge">
                    <span class="big">100%</span>
                    <span class="sml">Authentic Sri Lankan</span>
                </div>
            </div>
            <div class="tz-form-box">
                <h2>Get in Touch</h2>
                <p class="tz-form-sub">Reservations &middot; Catering &middot; Feedback &middot; General Enquiries</p>
                <form action="{{ url('/contact/send') }}" method="POST">
                    @csrf
                    <div class="tz-fg">
                        <label>Full Name</label>
                        <input type="text" name="name" placeholder="Your name" required>
                    </div>
                    <div class="tz-form-row">
                        <div class="tz-fg">
                            <label>Email</label>
                            <input type="email" name="email" placeholder="you@email.com" required>
                        </div>
                        <div class="tz-fg">
                            <label>Phone</label>
                            <input type="tel" name="phone" placeholder="+94 7X XXX XXXX">
                        </div>
                    </div>
                    <div class="tz-fg">
                        <label>Enquiry Type</label>
                        <select name="type">
                            <option>Table Reservation</option>
                            <option>Catering / Event</option>
                            <option>Delivery Order</option>
                            <option>General Feedback</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div class="tz-fg">
                        <label>Message</label>
                        <textarea name="message" placeholder="Tell us more..."></textarea>
                    </div>
                    <button type="submit" class="tz-submit-btn">
                        &#9993;&nbsp; Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

{{-- ── INFO CARDS ── --}}
<section class="tz-sec tz-sec--mid">
    <div class="tz-wrap">
        <div class="tz-info-grid">
            <div class="tz-info-card">
                <span class="ico">&#128336;</span>
                <h3>Opening Hours</h3>
                <p>Mon&ndash;Fri: 11:00 AM &ndash; 10:00 PM<br>Sat&ndash;Sun: 10:00 AM &ndash; 11:00 PM<br>Holidays: Open as usual</p>
            </div>
            <div class="tz-info-card">
                <span class="ico">&#128205;</span>
                <h3>Our Location</h3>
                <p>Pambala Kakkapalliya<br>Madampe, Sri Lanka<br><a href="https://maps.google.com/?q=Pambala+Kakkapalliya+Madampe" target="_blank">View on Google Maps &rarr;</a></p>
            </div>
            <div class="tz-info-card">
                <span class="ico">&#128279;</span>
                <h3>Quick Links</h3>
                <p><a href="{{ url('/menu') }}">View Full Menu &rarr;</a><br><a href="{{ url('/productions') }}">Catering Services &rarr;</a><br><a href="{{ url('/menu?table=1') }}">Order Online &rarr;</a></p>
            </div>
        </div>
    </div>
</section>

{{-- ── MAP ── --}}
<section class="tz-sec tz-sec--dark">
    <div class="tz-wrap">
        <div class="tz-map-layout">
            <div class="tz-map-text">
                <h2>Find Us in Madampe</h2>
                <p>We deliver across Madampe and surrounding areas. Walk in, call ahead, or place your order online.</p>
                <div class="tz-zones">
                    <div class="tz-zone"><span class="tz-zone__name">Madampe Town</span><span class="tz-zone__info">Min Rs.600 &middot; Fee Rs.100</span></div>
                    <div class="tz-zone"><span class="tz-zone__name">Pambala Area</span><span class="tz-zone__info">Min Rs.600 &middot; Fee Rs.80</span></div>
                    <div class="tz-zone"><span class="tz-zone__name">Nattandiya</span><span class="tz-zone__info">Min Rs.800 &middot; Fee Rs.120</span></div>
                    <div class="tz-zone"><span class="tz-zone__name">Wennappuwa</span><span class="tz-zone__info">Min Rs.800 &middot; Fee Rs.150</span></div>
                </div>
            </div>
            <div class="tz-map-frame">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7047.956578087915!2d79.82518763168052!3d7.516815451681363!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae2c94130913291%3A0x4d307d644677fe62!2sToddy&#39;s%20restaurant%20Pambala!5e0!3m2!1sen!2slk!4v1775839766225!5m2!1sen!2slk" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" aria-label="Toddyz Location"></iframe>
            </div>
        </div>
    </div>
</section>

{{-- ── CTA ── --}}
<section class="tz-cta">
    <h2>Ready to Taste Sri Lanka?</h2>
    <p>Call ahead and we&rsquo;ll have your table ready &mdash; or just walk in.</p>
    <div class="tz-cta__acts">
        <a href="tel:+94764504325" class="tz-btn-white">&#128222; 076 450 4325</a>
        <a href="https://wa.me/94764504325" class="tz-btn-white-o" target="_blank">WhatsApp Us</a>
    </div>
</section>

@push('scripts')
<script>
(function(){
    /* 3D mouse-tilt on contact cards */
    document.querySelectorAll('.tz-ccard').forEach(function(card) {
        card.addEventListener('mousemove', function(e) {
            var r = card.getBoundingClientRect();
            var x = e.clientX - r.left, y = e.clientY - r.top;
            var rY = ((x - r.width/2)  / (r.width/2))  * 16;
            var rX = -((y - r.height/2) / (r.height/2)) * 12;
            card.style.transform = 'perspective(700px) rotateX('+rX+'deg) rotateY('+rY+'deg) scale(1.04) translateZ(0)';
            card.style.boxShadow = (-rY*1.2)+'px '+(rX*1.2)+'px 32px rgba(216,90,48,.22)';
            card.style.borderColor = 'rgba(216,90,48,.38)';
            card.querySelector('.tz-ccard-shine').style.background =
                'radial-gradient(circle at '+x+'px '+y+'px,rgba(255,255,255,.11),transparent 65%)';
        });
        card.addEventListener('mouseleave', function() {
            card.style.transform = '';
            card.style.boxShadow = '';
            card.style.borderColor = '';
            card.querySelector('.tz-ccard-shine').style.background = '';
        });
    });

    /* Stat tile manual hover override */
    document.querySelectorAll('.tz-stat-tile').forEach(function(tile) {
        tile.addEventListener('mouseenter', function() {
            tile.style.animationPlayState = 'paused';
            tile.style.transform = 'perspective(600px) rotateX(8deg) rotateY(5deg) translateY(-6px) scale(1.04)';
            tile.style.boxShadow = '0 12px 28px rgba(216,90,48,.18)';
        });
        tile.addEventListener('mouseleave', function() {
            tile.style.transform = '';
            tile.style.boxShadow = '';
            tile.style.animationPlayState = 'running';
        });
    });
})();
</script>
@endpush

@endsection