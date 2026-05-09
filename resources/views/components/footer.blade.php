{{-- Footer Component - Visible on all pages --}}
<footer class="tz-footer" role="contentinfo">
    <div class="tz-wrap">
        <div class="tz-footer__inner">
            <div class="tz-footer__brand">
                <div class="tz-footer__logo">Toddy<span>'z</span></div>
                <p class="tz-footer__tag">
                    &ldquo;Delight Your Tongue&rdquo; &mdash; Authentic Sri Lankan family restaurant in Pambala Kakkapalliya, Madampe.
                </p>
            </div>
            <div class="tz-footer__col">
                <h4>Quick Links</h4>
                <a href="{{ url('/menu/public') }}">Full Menu</a>
                <a href="{{ url('/specials') }}">Special Deals</a>
                <a href="#tz-featured">Popular Dishes</a>
                <a href="#tz-reserve">Reserve a Table</a>
            </div>
            <div class="tz-footer__col">
                <h4>Contact</h4>
                <a href="tel:+94764504325">076 450 4325</a>
                <a href="https://wa.me/94764504325" target="_blank" rel="noopener">WhatsApp</a>
                <a href="mailto:toddyz.res2sl@gmail.com">toddyz.res2sl@gmail.com</a>
                <p>Pambala Kakkapalliya, Madampe</p>
            </div>
            <div class="tz-footer__col">
                <h4>Follow Us</h4>
                <a href="https://www.facebook.com/share/1GAuMG5NF3/" target="_blank" rel="noopener">Facebook</a>
                <a href="https://youtube.com/@malithpeiris2026" target="_blank" rel="noopener">YouTube</a>
                <a href="{{ url('/admin') }}">Staff Login</a>
            </div>
        </div>
        <div class="tz-footer__btm">
            <span class="tz-footer__copy">&copy; {{ date('Y') }} Toddy&rsquo;z Family Restaurant. All rights reserved.</span>
            <div class="tz-footer__mobile">
                <a href="{{ url('/admin') }}" style="font-size:11px;color:var(--t3);text-decoration:none">Admin</a>
                <button class="tz-footer__ham" id="tzFooterHam" aria-label="Open footer menu" aria-expanded="false">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </div>
</footer>

{{-- Footer slide drawer --}}
<div class="tz-footer-drawer__mask" id="tzFooterMask" aria-hidden="true"></div>
<div class="tz-footer-drawer" id="tzFooterDrawer" role="dialog" aria-modal="true" aria-label="Footer menu">
    <a href="{{ url('/menu/public') }}"      class="tz-footer-drawer__link">Full Menu</a>
    <a href="{{ url('/specials') }}"         class="tz-footer-drawer__link">Special Deals</a>
    <a href="#tz-featured"             class="tz-footer-drawer__link">Popular Dishes</a>
    <a href="#tz-reserve"              class="tz-footer-drawer__link">Reserve a Table</a>
    <a href="{{ url('/productions') }}"      class="tz-footer-drawer__link">Productions</a>
    <a href="{{ url('/contact') }}"          class="tz-footer-drawer__link">Contact</a>
    <a href="tel:+94764504325"          class="tz-footer-drawer__link">Call 076 450 4325</a>
    <a href="https://wa.me/94764504325" class="tz-footer-drawer__link">WhatsApp Us</a>
    <a href="mailto:toddyz.res2sl@gmail.com" class="tz-footer-drawer__link">Email Us</a>
</div>
