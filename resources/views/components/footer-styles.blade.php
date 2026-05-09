<style>
/* Footer Styles - Included on all pages */

/* CSS Variables for Footer - Define globally */
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
    --fd: 'Playfair Display', Georgia, serif;
    --fu: 'Montserrat', system-ui, sans-serif;
    --r: 999px;
}

/* Container wrapper for consistent padding */
.tz-wrap {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

@media (min-width: 768px) {
    .tz-wrap {
        padding: 0 40px;
    }
}

.tz-footer { background: var(--ink); border-top: 1px solid var(--b); padding: 56px 0 40px; }
.tz-footer__inner { display: flex; flex-wrap: wrap; gap: 40px; justify-content: space-between; }
.tz-footer__brand { max-width: 240px; }
.tz-footer__logo { font-family: var(--fd); font-size: 20px; font-weight: 900; color: var(--t1); margin-bottom: 8px; }
.tz-footer__logo span { color: var(--spice); }
.tz-footer__tag { font-size: 12px; color: var(--t3); line-height: 1.62; }
.tz-footer__col h4 { font-family: var(--fu); font-size: 10px; font-weight: 600; letter-spacing: .09em; text-transform: uppercase; color: var(--t3); margin-bottom: 14px; }
.tz-footer__col a  { display: block; font-size: 13px; color: var(--t2); text-decoration: none; margin-bottom: 8px; transition: color .2s; }
.tz-footer__col a:hover { color: var(--spice); }
.tz-footer__col p  { font-size: 11px; color: var(--t3); margin-top: 4px; }
.tz-footer__btm {
    margin-top: 44px; padding-top: 20px; border-top: 1px solid var(--b);
    display: flex; flex-wrap: wrap; gap: 10px; justify-content: space-between; align-items: center;
}
.tz-footer__copy { font-size: 11px; color: var(--t3); }
.tz-footer__mobile { display: none; align-items: center; gap: 12px; }

/* Footer hamburger */
.tz-footer__ham {
    display: none; background: transparent; border: none; cursor: pointer; padding: 4px;
}
.tz-footer__ham span {
    display: block; width: 18px; height: 2px; background: var(--t3);
    margin: 3px 0; border-radius: 1px; transition: all .28s ease;
}
.tz-footer__ham.open span:nth-child(1) { transform: rotate(45deg) translate(4px,4px); }
.tz-footer__ham.open span:nth-child(2) { opacity: 0; }
.tz-footer__ham.open span:nth-child(3) { transform: rotate(-45deg) translate(4px,-4px); }

/* Footer slide drawer */
.tz-footer-drawer {
    position: fixed; bottom: -100%; left: 0; right: 0; width: 100%;
    background: var(--ink-2); border-top: 1px solid var(--b);
    z-index: 500; transition: bottom .3s cubic-bezier(.4,0,.2,1);
    padding: 24px 20px; overflow-y: auto;
    display: flex; flex-direction: column; gap: 12px;
    max-height: 60vh;
}
.tz-footer-drawer.open { bottom: 0; }
.tz-footer-drawer__link {
    display: block; padding: 12px 16px;
    background: var(--ink-3); border: 1px solid var(--b); border-radius: 14px;
    color: var(--t1); font-family: var(--fu); font-size: 14px; font-weight: 500;
    text-decoration: none; transition: all .2s ease;
}
.tz-footer-drawer__link:hover {
    background: var(--spice-dim); border-color: var(--spice-b); color: var(--spice);
    transform: translateX(4px);
}
.tz-footer-drawer__mask {
    position: fixed; inset: 0; background: rgba(0,0,0,.55);
    z-index: 400; opacity: 0; visibility: hidden;
    transition: opacity .28s, visibility .28s;
}
.tz-footer-drawer__mask.open { opacity: 1; visibility: visible; }

@media (max-width: 768px) {
    .tz-footer__mobile { display: flex; }
    .tz-footer__col { display: none; }
}
</style>
