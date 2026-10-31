document.addEventListener('DOMContentLoaded', () => {

    const navbarToggle =
        document.getElementById('btNavbarToggle');

    const navbarMenu =
        document.getElementById('btNavbarMenu');

    if (!navbarToggle || !navbarMenu) {
        return;
    }


    /* =====================================================
       MOBILE MENU
    ===================================================== */

    navbarToggle.addEventListener('click', () => {

        const isOpen =
            navbarToggle.classList.toggle('is-open');

        navbarMenu.classList.toggle(
            'is-open',
            isOpen
        );

        navbarToggle.setAttribute(
            'aria-expanded',
            isOpen ? 'true' : 'false'
        );
    });


    /* =====================================================
       DROPDOWNS
    ===================================================== */

    const dropdowns =
        document.querySelectorAll(
            '.bt-nav-dropdown'
        );


    dropdowns.forEach(dropdown => {

        const toggle =
            dropdown.querySelector(
                '.bt-nav-dropdown-toggle'
            );

        if (!toggle) {
            return;
        }


        toggle.addEventListener('click', event => {

            event.preventDefault();

            const isOpen =
                dropdown.classList.toggle(
                    'is-open'
                );

            toggle.setAttribute(
                'aria-expanded',
                isOpen ? 'true' : 'false'
            );
        });

    });

});