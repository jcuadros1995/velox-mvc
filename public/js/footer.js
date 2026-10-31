(function () {

    'use strict';


    /* =========================================================
       ESTADO
    ========================================================= */

    let locations = [];

    let currentLocation = 0;


    /* =========================================================
       INIT
    ========================================================= */

    document.addEventListener(
        'DOMContentLoaded',
        function () {

            initFaq();

            initBackToTop();

            initNewsletter();

            initFooterLocations();

        }
    );


    /* =========================================================
       HELPERS
    ========================================================= */

    function escapeHtml(value) {

        return String(
            value ?? ''
        )
            .replace(
                /&/g,
                '&amp;'
            )
            .replace(
                /</g,
                '&lt;'
            )
            .replace(
                />/g,
                '&gt;'
            )
            .replace(
                /"/g,
                '&quot;'
            )
            .replace(
                /'/g,
                '&#039;'
            );

    }


    function getImageUrl(branch) {

        if (
            branch &&
            branch.image_url
        ) {

            return branch.image_url;

        }


        if (
            branch &&
            branch.image_path
        ) {

            return (
                window.APP_URL.replace(
                    /\/$/,
                    ''
                ) +
                '/' +
                String(
                    branch.image_path
                ).replace(
                    /^\/+/,
                    ''
                )
            );

        }


        return '';

    }


    /* =========================================================
       SEDES FOOTER
    ========================================================= */

    async function initFooterLocations() {

        const section =
            document.getElementById(
                'sedes-burgertown-footer'
            );


        if (!section) {

            return;

        }


        const track =
            section.querySelector(
                '[data-footer-location-track]'
            );


        const dots =
            section.querySelector(
                '[data-footer-location-dots]'
            );


        const prev =
            section.querySelector(
                '[data-footer-location-prev]'
            );


        const next =
            section.querySelector(
                '[data-footer-location-next]'
            );


        if (
            !track ||
            !dots
        ) {

            console.warn(
                '[FOOTER LOCATIONS] Elementos del slider no encontrados.'
            );

            return;

        }


        try {

            const response =
                await fetch(
                    `${window.APP_URL}/api/public/branches`,
                    {
                        method:
                            'GET',

                        headers: {
                            'Accept':
                                'application/json'
                        },

                        credentials:
                            'same-origin'
                    }
                );


            const result =
                await response.json();


            console.log(
                '[FOOTER LOCATIONS]',
                result
            );


            if (
                !response.ok
            ) {

                throw new Error(
                    `HTTP ${response.status}`
                );

            }


            if (
                !result ||
                result.success !== true
            ) {

                throw new Error(
                    result?.message ||
                    'La API de sedes no devolvió success=true.'
                );

            }


            locations =
                Array.isArray(
                    result.data
                )
                    ? result.data
                    : [];

            renderFooterBranches();

            if (
                !locations.length
            ) {

                renderEmptyLocations(
                    track,
                    dots
                );

                return;

            }


            renderFooterLocations(
                track,
                dots
            );


            if (prev) {

                prev.addEventListener(
                    'click',
                    function () {

                        stopFooterAutoplay();


                        showPreviousLocation(
                            section
                        );


                        startFooterAutoplay(
                            section
                        );

                    }
                );

            }


            if (next) {

                next.addEventListener(
                    'click',
                    function () {

                        stopFooterAutoplay();


                        showNextLocation(
                            section
                        );


                        startFooterAutoplay(
                            section
                        );

                    }
                );

            }


            updateFooterSlider(
                section,
                false
            );

            startFooterAutoplay(
                section
            );



        } catch (error) {

            console.error(
                '[FOOTER LOCATIONS ERROR]',
                error
            );


            /*
             * NO reemplazamos el slider por un fallback
             * visual gigante.
             *
             * Mostramos una tarjeta de error pequeña.
             */

            track.innerHTML = `

                <article
                        class="bt-location-card
                               bt-location-card-empty"
                >

                    <div class="bt-location-image">

                        <div
                                class="bt-location-image-placeholder"
                        >

                            <i
                                    class="
                                        fa-solid
                                        fa-store
                                    "
                            ></i>

                        </div>

                    </div>


                    <div
                            class="bt-location-overlay"
                    ></div>


                    <div
                            class="bt-location-content"
                    >

                        <span
                                class="bt-location-number"
                        >
                            01
                        </span>


                        <h3
                                class="bt-location-name"
                        >

                           

                            <strong>
                                Sedes
                            </strong>

                        </h3>


                        <p
                                class="bt-location-address"
                        >
                            No fue posible cargar las sedes.
                        </p>


                        <a
                                href="${window.APP_URL}/sedes"
                                class="bt-location-button"
                        >

                            <span>
                                VER SEDES
                            </span>

                            <i
                                    class="
                                        fa-solid
                                        fa-arrow-right
                                    "
                            ></i>

                        </a>

                    </div>

                </article>

            `;


            dots.innerHTML =
                '';
        }

    }


    /* =========================================================
       RENDER
    ========================================================= */

    function renderFooterLocations(
        track,
        dots
    ) {

        track.innerHTML =
            locations
                .map(
                    function (
                        branch,
                        index
                    ) {

                        const number =
                            String(
                                index + 1
                            ).padStart(
                                2,
                                '0'
                            );


                        const name =
                            branch.name ||
                            'Burger Town';


                        const address =
                            branch.address ||
                            'Bogotá, Colombia';


                        const image =
                            getImageUrl(
                                branch
                            );


                        return `

                            <article
                                    class="
                                        bt-location-card
                                    "
                                    data-footer-location-index="${index}"
                            >

                                <!-- IMAGEN -->

                                <div
                                        class="bt-location-image"
                                >

                                    ${
                            image

                                ?

                                `
                                                <img
                                                        src="${escapeHtml(
                                    image
                                )}"
                                                        alt="${escapeHtml(
                                    name
                                )}"
                                                        loading="lazy"
                                                        draggable="false"
                                                >
                                            `

                                :

                                `
                                                <div
                                                        class="
                                                            bt-location-image-placeholder
                                                        "
                                                >

                                                    <i
                                                            class="
                                                                fa-solid
                                                                fa-store
                                                            "
                                                    ></i>

                                                </div>
                                            `
                        }

                                </div>


                                <!-- OVERLAY -->

                                <div
                                        class="bt-location-overlay"
                                ></div>


                                <!-- CONTENIDO -->

                                <div
                                        class="bt-location-content"
                                >

                                    <span
                                            class="bt-location-number"
                                    >
                                        ${number}
                                    </span>


                                    <h3
                                            class="bt-location-name"
                                    >


                                        <strong>
                                            ${escapeHtml(
                            name
                        )}
                                        </strong>

                                    </h3>


                                    <p
                                            class="bt-location-address"
                                    >
                                        ${escapeHtml(
                            address
                        )}
                                    </p>


                                    <a
                                            href="${
                            window.APP_URL
                        }/reservas?branch_id=${
                            encodeURIComponent(
                                branch.id
                            )
                        }"
                                            class="bt-location-button"
                                    >

                                        <span>
                                            RESERVAR
                                        </span>

                                        <i
                                                class="
                                                    fa-solid
                                                    fa-arrow-right
                                                "
                                        ></i>

                                    </a>

                                </div>

                            </article>

                        `;

                    }
                )
                .join('');


        dots.innerHTML =
            locations
                .map(
                    function (
                        branch,
                        index
                    ) {

                        return `

                            <button
                                    type="button"
                                    class="
                                        bt-location-dot
                                        ${
                            index ===
                            currentLocation
                                ? 'is-active'
                                : ''
                        }
                                    "
                                    data-footer-location-dot="${index}"
                                    aria-label="Mostrar sede ${
                            index + 1
                        }"
                            ></button>

                        `;

                    }
                )
                .join('');


        dots
            .querySelectorAll(
                '[data-footer-location-dot]'
            )
            .forEach(
                function (
                    dot
                ) {

                    dot.addEventListener(
                        'click',
                        function () {

                            currentLocation =
                                Number(
                                    this.dataset
                                        .footerLocationDot
                                );


                            updateFooterSlider(
                                document.getElementById(
                                    'sedes-burgertown-footer'
                                ),
                                true
                            );

                        }
                    );

                }
            );

    }


    /* =========================================================
   FOOTER LOCATION SLIDER
========================================================= */

    let footerAutoplay = null;


    function updateFooterSlider(
        section,
        smooth = true
    ) {

        if (!section) {
            return;
        }


        const track =
            section.querySelector(
                '[data-footer-location-track]'
            );


        const cards =
            Array.from(
                section.querySelectorAll(
                    '[data-footer-location-index]'
                )
            );


        const dots =
            Array.from(
                section.querySelectorAll(
                    '[data-footer-location-dot]'
                )
            );


        if (
            !track ||
            !cards.length
        ) {

            return;
        }


        /*
         * Estado visual
         */

        cards.forEach(
            (
                card,
                index
            ) => {

                card.classList.toggle(
                    'is-active',
                    index === currentLocation
                );

            }
        );


        /*
         * Dots
         */

        dots.forEach(
            (
                dot,
                index
            ) => {

                dot.classList.toggle(
                    'is-active',
                    index === currentLocation
                );

            }
        );


        const activeCard =
            cards[
                currentLocation
                ];


        if (!activeCard) {
            return;
        }


        /*
         * =====================================================
         * CALCULAR DESPLAZAMIENTO
         * =====================================================
         */

        const viewportWidth =
            section
                .querySelector(
                    '.bt-locations-viewport'
                )
                ?.clientWidth || 0;


        const cardWidth =
            activeCard.offsetWidth;


        const cardLeft =
            activeCard.offsetLeft;


        /*
         * Centrar la tarjeta activa.
         */

        const offset =
            cardLeft -
            (
                viewportWidth -
                cardWidth
            ) / 2;


        /*
         * Importante:
         *
         * NO usamos scrollIntoView()
         * NO usamos track.scrollTo()
         *
         * Movemos únicamente el track
         * horizontalmente con transform.
         */

        track.style.transition =
            smooth
                ? 'transform .55s cubic-bezier(.22,1,.36,1)'
                : 'none';


        track.style.transform =
            `translate3d(${-Math.max(
                0,
                offset
            )}px, 0, 0)`;

    }


    function showNextLocation(
        section
    ) {

        if (
            !locations.length
        ) {

            return;
        }


        currentLocation =
            (
                currentLocation + 1
            ) %
            locations.length;


        updateFooterSlider(
            section,
            true
        );

    }


    function showPreviousLocation(
        section
    ) {

        if (
            !locations.length
        ) {

            return;
        }


        currentLocation =
            (
                currentLocation -
                1 +
                locations.length
            ) %
            locations.length;


        updateFooterSlider(
            section,
            true
        );

    }


    /* =========================================================
       AUTOPLAY
    ========================================================= */

    function startFooterAutoplay(
        section
    ) {

        stopFooterAutoplay();


        if (
            locations.length <= 1
        ) {

            return;

        }


        footerAutoplay =
            setInterval(
                function () {

                    showNextLocation(
                        section
                    );

                },
                4500
            );

    }


    function stopFooterAutoplay() {

        if (
            footerAutoplay
        ) {

            clearInterval(
                footerAutoplay
            );

            footerAutoplay =
                null;

        }

    }


    function renderEmptyLocations(
        track,
        dots
    ) {

        track.innerHTML = `

            <article
                    class="
                        bt-location-card
                        bt-location-card-empty
                "
            >

                <div class="bt-location-image">

                    <div
                            class="
                                bt-location-image-placeholder
                            "
                    >

                        <i
                                class="
                                    fa-solid
                                    fa-store
                                "
                        ></i>

                    </div>

                </div>


                <div
                        class="bt-location-overlay"
                ></div>


                <div
                        class="bt-location-content"
                >

                    <span
                            class="bt-location-number"
                    >
                        01
                    </span>


                    <h3
                            class="bt-location-name"
                    >

                        Burger Town

                        <strong>
                            Próximamente
                        </strong>

                    </h3>


                    <p
                            class="bt-location-address"
                    >

                        Estamos preparando
                        nuevas sedes.

                    </p>

                </div>

            </article>

        `;


        dots.innerHTML =
            '';

    }


    /* =========================================================
       FAQ
    ========================================================= */

    function initFaq() {

        const faq =
            document.querySelector('[data-faq]');

        if (!faq) {
            return;
        }

        const items =
            Array.from(
                faq.querySelectorAll('.bt-faq-item')
            );

        if (!items.length) {
            return;
        }

        items.forEach(function (item) {

            const question =
                item.querySelector('.bt-faq-question');

            const answer =
                item.querySelector('.bt-faq-answer');

            if (!question || !answer) {
                return;
            }

            question.addEventListener('click', function () {

                const isOpen =
                    item.classList.contains('is-open');

                /*
                 * Cerramos todos los FAQ
                 */
                items.forEach(function (otherItem) {

                    otherItem.classList.remove('is-open');

                    const otherQuestion =
                        otherItem.querySelector(
                            '.bt-faq-question'
                        );

                    const otherAnswer =
                        otherItem.querySelector(
                            '.bt-faq-answer'
                        );

                    if (otherQuestion) {

                        otherQuestion.setAttribute(
                            'aria-expanded',
                            'false'
                        );
                    }

                    if (otherAnswer) {

                        otherAnswer.setAttribute(
                            'aria-hidden',
                            'true'
                        );
                    }

                });


                /*
                 * Si estaba cerrado,
                 * lo abrimos.
                 */
                if (!isOpen) {

                    item.classList.add('is-open');

                    question.setAttribute(
                        'aria-expanded',
                        'true'
                    );

                    answer.setAttribute(
                        'aria-hidden',
                        'false'
                    );

                }

            });

        });

    }


    /* =========================================================
       BACK TO TOP
    ========================================================= */

    function initBackToTop() {

        const button =
            document.getElementById(
                'btBackToTop'
            );


        if (!button) {

            return;

        }


        window.addEventListener(
            'scroll',
            function () {

                button.classList.toggle(
                    'is-visible',
                    window.scrollY >
                    500
                );

            },
            {
                passive:
                    true
            }
        );


        button.addEventListener(
            'click',
            function () {

                window.scrollTo({

                    top:
                        0,

                    behavior:
                        'smooth'

                });

            }
        );

    }

    /* =========================================================
   FOOTER INFERIOR - LISTADO DE SEDES
========================================================= */

    function renderFooterBranches() {

        const container =
            document.querySelector(
                '[data-footer-branches]'
            );


        if (!container) {

            return;

        }


        /*
         * Sin sedes
         */

        if (
            !Array.isArray(locations) ||
            !locations.length
        ) {

            container.innerHTML = `
            <span class="bt-footer-loading">
                No hay sedes disponibles.
            </span>
        `;

            return;

        }


        /*
         * Renderizar sedes
         */

        container.innerHTML =
            locations
                .map(
                    function (branch) {

                        const branchId =
                            Number(
                                branch.id
                            );


                        const name =
                            branch.name ||
                            'Burger Town';


                        return `
                        <a
                            href="${window.APP_URL}/sedes"
                            class="bt-footer-branch-link"
                            data-branch-id="${branchId}"
                        >
                            ${escapeHtml(name)}
                        </a>
                    `;

                    }
                )
                .join('');

    }


    /* =========================================================
       NEWSLETTER
    ========================================================= */


    function initNewsletter() {
        const form = document.getElementById('footerNewsletterForm');

        if (!form) {
            return;
        }

        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            const input = form.querySelector('input[type="email"]');
            const button = form.querySelector('button[type="submit"]');

            if (!input || !input.value.trim()) {
                return;
            }

            if (!input.checkValidity()) {
                input.reportValidity();
                return;
            }

            const originalText = button ? button.textContent : '';

            if (button) {
                button.disabled = true;
                button.textContent = 'ENVIANDO...';
            }

            try {
                const response = await fetch(
                    `${window.APP_URL || ''}/api/newsletter/subscribe`,
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            email: input.value.trim()
                        })
                    }
                );

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(
                        result.message || 'No fue posible completar la suscripción.'
                    );
                }

                if (typeof window.showToast === 'function') {
                    window.showToast('success', result.message);
                } else {
                    alert(result.message);
                }

                form.reset();

            } catch (error) {
                if (typeof window.showToast === 'function') {
                    window.showToast(
                        'error',
                        error.message || 'No fue posible suscribirte.'
                    );
                } else {
                    alert(error.message || 'No fue posible suscribirte.');
                }
            } finally {
                if (button) {
                    button.disabled = false;
                    button.textContent = originalText;
                }
            }
        });
    }


})();