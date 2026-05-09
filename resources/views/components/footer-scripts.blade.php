<script>
(function () {
    'use strict';

    /* Footer drawer functionality - Available on all pages */
    var footerHam = document.getElementById('tzFooterHam');
    var footerMask = document.getElementById('tzFooterMask');
    var footerDrawer = document.getElementById('tzFooterDrawer');

    function openFooterDrawer() {
        footerHam.classList.add('open');
        footerMask.classList.add('open');
        footerDrawer.classList.add('open');
        footerHam.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }

    function closeFooterDrawer() {
        footerHam.classList.remove('open');
        footerMask.classList.remove('open');
        footerDrawer.classList.remove('open');
        footerHam.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    if (footerHam) {
        footerHam.addEventListener('click', function () {
            footerDrawer.classList.contains('open') ? closeFooterDrawer() : openFooterDrawer();
        });
        footerMask.addEventListener('click', closeFooterDrawer);
        footerDrawer.querySelectorAll('a').forEach(function (a) {
            a.addEventListener('click', closeFooterDrawer);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeFooterDrawer();
        });
    }
})();
</script>
