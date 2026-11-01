/**
 * Shared public navbar behavior.
 * Loaded by public_layout so Home and inner pages use the same scroll state.
 */
(function () {
    'use strict';

    function initPublicNavbar() {
        const navbar = document.getElementById('mainNavbar');
        if (!navbar) return;

        let ticking = false;
        const update = function () {
            navbar.classList.toggle('navbar-scrolled', window.scrollY > 40);
            ticking = false;
        };

        update();
        window.addEventListener('scroll', function () {
            if (ticking) return;
            ticking = true;
            window.requestAnimationFrame(update);
        }, { passive: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPublicNavbar, { once: true });
    } else {
        initPublicNavbar();
    }
})();
