(function () {

    'use strict';


    /* =========================================================
       ESTADO
    ========================================================== */

    let branches = [];

    let selectedBranch = null;

    let galleryImages = [];

    let galleryIndex = 0;


    /* =========================================================
       HELPERS
    ========================================================== */

    const $ = (id) =>
        document.getElementById(id);


    function escapeHtml(value) {

        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }


    function imageUrl(image) {

        if (!image) {
            return '';
        }


        if (image.url) {
            return image.url;
        }


        return (
            `${window.APP_URL}/` +
            String(
                image.file_path || ''
            ).replace(/^\/+/, '')
        );
    }


    /* =========================================================
       INIT
    ========================================================== */

    document.addEventListener(
        'DOMContentLoaded',
        init
    );


    async function init() {

        bindEvents();

        await loadBranches();

    }


    /* =========================================================
       EVENTS
    ========================================================== */

    function bindEvents() {

        $('branch')
            ?.addEventListener(
                'change',
                async function () {

                    const branchId =
                        Number(
                            this.value
                        );

                    selectedBranch =
                        branches.find(
                            branch =>
                                Number(branch.id) ===
                                branchId
                        ) || null;


                    renderBranch();

                    await loadBusinessHours(
                        branchId
                    );

                }
            );


        $('branchesGalleryPrev')
            ?.addEventListener(
                'click',
                () => {

                    changeGallery(
                        -1
                    );

                }
            );


        $('branchesGalleryNext')
            ?.addEventListener(
                'click',
                () => {

                    changeGallery(
                        1
                    );

                }
            );

    }


    /* =========================================================
       LOAD BRANCHES
    ========================================================== */

    async function loadBranches() {

        const select =
            $('branch');


        if (!select) {
            return;
        }


        try {

            select.innerHTML = `
                <option value="">
                    Cargando sedes...
                </option>
            `;


            const response =
                await fetch(
                    `${window.APP_URL}/api/public/branches`,
                    {
                        headers: {
                            Accept:
                                'application/json'
                        }
                    }
                );


            const result =
                await response.json();


            if (
                !response.ok ||
                !result.success
            ) {

                throw new Error(
                    result.message ||
                    'No fue posible cargar las sedes.'
                );
            }


            branches =
                Array.isArray(
                    result.data
                )
                    ? result.data
                    : [];


            if (!branches.length) {

                select.innerHTML = `
                    <option value="">
                        No hay sedes disponibles
                    </option>
                `;

                return;
            }


            /* =================================================
               SELECT
            ================================================== */

            select.innerHTML = '';

            branches.forEach(
                branch => {

                    const option =
                        document.createElement(
                            'option'
                        );


                    option.value =
                        String(
                            branch.id
                        );


                    option.textContent =
                        branch.name;


                    select.appendChild(
                        option
                    );

                }
            );


            /* =================================================
               SEDE INICIAL
            ================================================== */

            const params =
                new URLSearchParams(
                    window.location.search
                );


            const requestedId =
                Number(
                    params.get(
                        'branch_id'
                    ) || 0
                );


            selectedBranch =
                requestedId > 0
                    ? branches.find(
                    branch =>
                        Number(branch.id) ===
                        requestedId
                ) || branches[0]
                    : branches[0];


            if (!selectedBranch) {
                return;
            }


            select.value =
                String(
                    selectedBranch.id
                );


            renderBranch();


            await loadBusinessHours(
                Number(
                    selectedBranch.id
                )
            );


        } catch (error) {

            console.error(
                '[BRANCHES PAGE]',
                error
            );


            select.innerHTML = `
                <option value="">
                    No fue posible cargar las sedes
                </option>
            `;

            showError(
                error.message
            );
        }

    }


    /* =========================================================
       RENDER SEDE
    ========================================================== */

    function renderBranch() {

        if (!selectedBranch) {
            return;
        }


        const branch =
            selectedBranch;


        /* =====================================================
           HERO
        ====================================================== */

        const title =
            $('branchesHeroTitle');


        const description =
            $('branchesHeroDescription');


        const heroImage =
            $('branchesHeroImage');


        const placeholder =
            $('branchesHeroPlaceholder');


        const contact =
            $('branchesHeroContact');


        const address =
            $('branchesHeroAddress');


        const phone =
            $('branchesHeroPhone');


        if (title) {

            title.textContent =
                branch.name ||
                'Burger Town';
        }


        if (description) {

            description.textContent =
                'Conoce nuestra sede, ubicación, horarios y encuentra el mejor lugar para disfrutar Burger Town.';
        }


        if (address) {

            address.textContent =
                branch.address ||
                'Dirección no disponible';
        }


        if (phone) {

            phone.textContent =
                branch.phone ||
                'Teléfono no disponible';
        }


        if (contact) {

            contact.hidden =
                false;
        }


        /* =====================================================
           INFORMACIÓN DE LA SEDE
        ====================================================== */

        const informationAddress =
            $('branchInformationAddress');


        const informationPhone =
            $('branchInformationPhone');


        const informationEmail =
            $('branchInformationEmail');


        const informationCode =
            $('branchInformationCode');


        if (informationAddress) {

            informationAddress.textContent =
                branch.address ||
                'Dirección no disponible';
        }


        if (informationPhone) {

            informationPhone.textContent =
                branch.phone ||
                'Teléfono no disponible';
        }


        if (informationEmail) {

            informationEmail.textContent =
                branch.email ||
                'Correo no disponible';
        }


        if (informationCode) {

            informationCode.textContent =
                branch.code ||
                'Sin código';
        }


        /* =====================================================
           TÍTULO INFORMACIÓN
        ====================================================== */

        const informationTitle =
            $('branchesInformationTitle');


        if (informationTitle) {

            informationTitle.textContent =
                branch.name ||
                'Información de la sede';
        }


        /* =====================================================
           IMÁGENES
        ====================================================== */

        galleryImages =
            Array.isArray(
                branch.images
            )
                ? [...branch.images]
                : [];


        galleryImages.sort(
            (a, b) => {

                const favoriteA =
                    Boolean(
                        a.is_favorite
                    );


                const favoriteB =
                    Boolean(
                        b.is_favorite
                    );


                if (
                    favoriteA !==
                    favoriteB
                ) {

                    return favoriteB
                        ? 1
                        : -1;
                }


                return (
                    Number(
                        a.sort_order || 0
                    ) -
                    Number(
                        b.sort_order || 0
                    )
                );
            }
        );


        galleryIndex = 0;


        renderGallery();


        /* =====================================================
           MAPA
        ====================================================== */

        renderMap(
            branch
        );


        /* =====================================================
           CTA
        ====================================================== */

        const reserveButton =
            $('branchReserveButton');


        if (reserveButton) {

            reserveButton.href =
                `${window.APP_URL}/reservas?branch_id=${encodeURIComponent(
                    branch.id
                )}`;
        }


        const ctaTitle =
            $('branchCtaTitle');


        if (ctaTitle) {

            ctaTitle.innerHTML = `
            Reserva en
            <span>
                ${escapeHtml(
                branch.name ||
                'Burger Town'
            )}
            </span>
        `;
        }


        const ctaDescription =
            $('branchCtaDescription');


        if (ctaDescription) {

            ctaDescription.textContent =
                `Reserva tu mesa en ${
                    branch.name ||
                    'esta sede'
                } y disfruta la experiencia Burger Town.`;
        }

    }


    /* =========================================================
       GALLERY
    ========================================================== */

    function renderGallery() {

        const gallery =
            $('branchesGallery');


        const thumbnails =
            $('branchesThumbnails');


        const heroImage =
            $('branchesHeroImage');


        const placeholder =
            $('branchesHeroPlaceholder');


        if (!gallery || !thumbnails) {
            return;
        }


        if (!galleryImages.length) {

            gallery.hidden =
                true;


            if (heroImage) {

                heroImage.hidden =
                    true;

                heroImage.removeAttribute(
                    'src'
                );

            }


            placeholder?.removeAttribute(
                'hidden'
            );


            thumbnails.innerHTML =
                '';

            return;
        }


        gallery.hidden =
            galleryImages.length <= 1;


        renderActiveGalleryImage();


        thumbnails.innerHTML =
            '';


        galleryImages.forEach(
            (image, index) => {

                const button =
                    document.createElement(
                        'button'
                    );


                button.type =
                    'button';


                button.className =
                    'bt-branches-thumbnail';


                button.classList.toggle(
                    'is-active',
                    index === galleryIndex
                );


                button.classList.toggle(
                    'is-favorite',
                    Boolean(
                        image.is_favorite
                    )
                );


                button.innerHTML = `

                    <img
                        src="${escapeHtml(
                    imageUrl(image)
                )}"
                        alt=""
                        loading="lazy"
                    >

                    ${
                    image.is_favorite
                        ? `
                                <span>
                                    <i class="fa-solid fa-star"></i>
                                </span>
                            `
                        : ''
                }

                `;


                button.addEventListener(
                    'click',
                    () => {

                        galleryIndex =
                            index;

                        renderGallery();

                    }
                );


                thumbnails.appendChild(
                    button
                );

            }
        );

    }


    function renderActiveGalleryImage() {

        const heroImage =
            $('branchesHeroImage');


        const placeholder =
            $('branchesHeroPlaceholder');


        const image =
            galleryImages[
                galleryIndex
                ];


        if (!image || !heroImage) {
            return;
        }


        const url =
            imageUrl(image);


        if (!url) {

            heroImage.hidden =
                true;

            placeholder?.removeAttribute(
                'hidden'
            );

            return;
        }


        heroImage.src =
            url;


        heroImage.alt =
            selectedBranch?.name ||
            'Burger Town';


        heroImage.hidden =
            false;


        placeholder?.setAttribute(
            'hidden',
            'hidden'
        );

    }


    function changeGallery(
        direction
    ) {

        const total =
            galleryImages.length;


        if (total <= 1) {
            return;
        }


        galleryIndex =
            (
                galleryIndex +
                Number(direction) +
                total
            ) % total;


        renderGallery();

    }


    /* =========================================================
       HORARIOS
    ========================================================== */

    async function loadBusinessHours(
        branchId
    ) {

        const container =
            $('branchBusinessHoursList');


        if (!container) {
            return;
        }


        container.innerHTML = `
            <div class="bt-branches-loading">
                <i class="fa-solid fa-spinner fa-spin"></i>
                Consultando horarios...
            </div>
        `;


        try {

            const response =
                await fetch(
                    `${window.APP_URL}/api/public/branches/hours` +
                    `?branch_id=${encodeURIComponent(branchId)}`,
                    {
                        headers: {
                            Accept:
                                'application/json'
                        }
                    }
                );


            const result =
                await response.json();


            if (
                !response.ok ||
                !result.success
            ) {

                throw new Error(
                    result.message ||
                    'No fue posible consultar los horarios.'
                );
            }


            renderBusinessHours(
                result.data || []
            );


        } catch (error) {

            console.error(
                '[BRANCH HOURS]',
                error
            );


            container.innerHTML = `
                <div class="bt-branches-hours-error">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    No fue posible consultar los horarios.

                </div>
            `;

        }

    }


    function renderBusinessHours(
        hours
    ) {

        const container =
            $('branchBusinessHoursList');


        if (!container) {
            return;
        }


        const dayNames = {

            1: 'Lunes',
            2: 'Martes',
            3: 'Miércoles',
            4: 'Jueves',
            5: 'Viernes',
            6: 'Sábado',
            7: 'Domingo'

        };


        const rows =
            [...hours]
                .sort(
                    (a, b) =>
                        Number(
                            a.day_of_week
                        ) -
                        Number(
                            b.day_of_week
                        )
                )
                .map(
                    hour => {

                        const closed =
                            Number(
                                hour.is_closed
                            ) === 1 ||
                            hour.is_closed === true;


                        const open =
                            hour.open_time
                                ? String(
                                    hour.open_time
                                ).substring(
                                    0,
                                    5
                                )
                                : '--:--';


                        const close =
                            hour.close_time
                                ? String(
                                    hour.close_time
                                ).substring(
                                    0,
                                    5
                                )
                                : '--:--';


                        return `
                            <div class="bt-branches-hours-row">

                                <span>

                                    ${escapeHtml(
                            dayNames[
                                Number(
                                    hour.day_of_week
                                )
                                ] || ''
                        )}

                                </span>


                                <strong>

                                    ${
                            closed
                                ? 'Cerrado'
                                : `${open} - ${close}`
                        }

                                </strong>

                            </div>
                        `;

                    }
                )
                .join('');


        container.innerHTML =
            rows ||
            `
                <div class="bt-branches-hours-error">

                    No hay horarios configurados.

                </div>
            `;

    }


    /* =========================================================
       MAP
    ========================================================== */

    function renderMap(
        branch
    ) {

        const iframe =
            $('branchMap');


        const loading =
            $('branchMapLoading');


        const mapName =
            $('branchMapName');


        const mapAddress =
            $('branchMapAddress');


        const mapsLink =
            $('branchGoogleMapsLink');


        if (
            !iframe ||
            !branch
        ) {
            return;
        }


        const address =
            (
                branch.address ||
                branch.name ||
                ''
            ).trim();


        if (!address) {

            iframe.src =
                'about:blank';


            if (loading) {

                loading.innerHTML = `
                    <i class="fa-solid fa-location-dot"></i>
                    No hay una dirección configurada para esta sede.
                `;

                loading.hidden =
                    false;
            }


            return;
        }


        const query =
            `${address}, Bogotá, Colombia`;


        const encoded =
            encodeURIComponent(
                query
            );


        iframe.src =
            `https://www.google.com/maps?q=${encoded}&output=embed`;


        iframe.onload =
            () => {

                if (loading) {
                    loading.hidden =
                        true;
                }

            };


        if (loading) {

            loading.hidden =
                false;

        }


        if (mapName) {

            mapName.textContent =
                branch.name ||
                'Burger Town';

        }


        if (mapAddress) {

            mapAddress.textContent =
                branch.address ||
                'Bogotá, Colombia';

        }


        if (mapsLink) {

            mapsLink.href =
                `https://www.google.com/maps/search/?api=1&query=${encoded}`;

        }

    }


    /* =========================================================
       ERROR
    ========================================================== */

    function showError(
        message
    ) {

        if (
            typeof window.showToast ===
            'function'
        ) {

            window.showToast(
                'error',
                message
            );

            return;
        }


        console.error(
            message
        );

    }

})();