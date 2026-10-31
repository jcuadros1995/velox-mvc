document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | AOS
    |--------------------------------------------------------------------------
    */

    if (typeof AOS !== 'undefined') {

        AOS.init({
            duration: 900,
            easing: 'ease-out-cubic',
            once: true,
            offset: 80,
            disable: window.matchMedia(
                '(prefers-reduced-motion: reduce)'
            ).matches
        });

    }


    /*
    |--------------------------------------------------------------------------
    | NAVBAR SCROLL
    |--------------------------------------------------------------------------
    */

    const navbar = document.getElementById('mainNavbar');

    function updateNavbar() {

        if (!navbar) {
            return;
        }

        if (window.scrollY > 40) {
            navbar.classList.add('navbar-scrolled');
        } else {
            navbar.classList.remove('navbar-scrolled');
        }

    }

    updateNavbar();

    window.addEventListener(
        'scroll',
        updateNavbar,
        { passive: true }
    );


    /*
    |--------------------------------------------------------------------------
    | CLOSE MOBILE MENU AFTER CLICK
    |--------------------------------------------------------------------------
    */

    const navLinks = document.querySelectorAll(
        '#mainNav .nav-link'
    );

    const navCollapse = document.getElementById(
        'mainNav'
    );

    navLinks.forEach(function (link) {

        link.addEventListener('click', function () {

            if (
                window.innerWidth < 992 &&
                navCollapse &&
                typeof bootstrap !== 'undefined'
            ) {

                const collapse =
                    bootstrap.Collapse.getInstance(navCollapse) ||
                    new bootstrap.Collapse(
                        navCollapse,
                        { toggle: false }
                    );

                collapse.hide();
            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | COUNTERS
    |--------------------------------------------------------------------------
    */

    const counters =
        document.querySelectorAll('[data-counter]');

    if (counters.length) {

        const observer =
            new IntersectionObserver(
                function (entries, observer) {

                    entries.forEach(function (entry) {

                        if (!entry.isIntersecting) {
                            return;
                        }

                        const element = entry.target;

                        const target =
                            parseFloat(
                                element.dataset.counter
                            );

                        const duration = 1800;

                        const startTime = performance.now();

                        function animate(currentTime) {

                            const elapsed =
                                currentTime - startTime;

                            const progress =
                                Math.min(
                                    elapsed / duration,
                                    1
                                );

                            const eased =
                                1 - Math.pow(
                                    1 - progress,
                                    4
                                );

                            const current =
                                target * eased;

                            if (
                                target % 1 !== 0
                            ) {

                                element.textContent =
                                    current.toFixed(1);

                            } else {

                                element.textContent =
                                    Math.floor(current)
                                        .toLocaleString('en-US');

                            }

                            if (progress < 1) {

                                requestAnimationFrame(
                                    animate
                                );

                            } else {

                                element.textContent =
                                    target % 1 !== 0
                                        ? target.toFixed(1)
                                        : target.toLocaleString('en-US');

                            }

                        }

                        requestAnimationFrame(
                            animate
                        );

                        observer.unobserve(element);

                    });

                },
                {
                    threshold: 0.5
                }
            );

        counters.forEach(function (counter) {
            observer.observe(counter);
        });

    }


    /*
    |--------------------------------------------------------------------------
    | SMOOTH SCROLL
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll(
        'a[href^="#"]'
    ).forEach(function (link) {

        link.addEventListener(
            'click',
            function (event) {

                const targetId =
                    this.getAttribute('href');

                if (
                    !targetId ||
                    targetId === '#'
                ) {
                    return;
                }

                const target =
                    document.querySelector(targetId);

                if (!target) {
                    return;
                }

                event.preventDefault();

                const navbarHeight =
                    navbar
                        ? navbar.offsetHeight
                        : 0;

                const position =
                    target.getBoundingClientRect().top +
                    window.scrollY -
                    navbarHeight;

                window.scrollTo({
                    top: position,
                    behavior: 'smooth'
                });

            }
        );

    });


    /*
    |--------------------------------------------------------------------------
    | CONTACT FORM
    |--------------------------------------------------------------------------
    */

    const contactForm =
        document.getElementById('contactForm');

    if (contactForm) {

        contactForm.addEventListener(
            'submit',
            function (event) {

                event.preventDefault();

                const button =
                    contactForm.querySelector(
                        'button[type="submit"]'
                    );

                if (!button) {
                    return;
                }

                const originalHTML =
                    button.innerHTML;

                button.disabled = true;

                button.innerHTML = `
                    <span
                        class="spinner-border spinner-border-sm"
                        role="status"
                    ></span>

                    Enviando...
                `;


                /*
                 * Aquí posteriormente conectaremos
                 * el formulario con ContactController.
                 */

                setTimeout(function () {

                    button.disabled = false;

                    button.innerHTML =
                        originalHTML;

                    if (
                        typeof Swal !== 'undefined'
                    ) {

                        Swal.fire({
                            icon: 'success',
                            title: 'Mensaje enviado',
                            text: 'Gracias por contactarnos. Te responderemos lo antes posible.',
                            confirmButtonText: 'Aceptar',
                            confirmButtonColor: '#111827'
                        });

                    } else {

                        alert(
                            'Mensaje enviado correctamente.'
                        );

                    }

                    contactForm.reset();

                }, 1000);

            }
        );

    }

});