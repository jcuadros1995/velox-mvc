(function () {

    'use strict';


    /* =========================================================
       ESTADO
    ========================================================== */

    let items = [];

    let categories = [];

    let filteredItems = [];


    /* =========================================================
       HELPERS DOM
    ========================================================== */

    const $ = (id) =>
        document.getElementById(id);


    /* =========================================================
       INIT
    ========================================================== */

    document.addEventListener(
        'DOMContentLoaded',
        init
    );


    async function init() {

        const page =
            $('adminItemsPage');

        if (!page) {
            return;
        }


        bindEvents();


        try {

            setLoading(true);

            await Promise.all([
                loadCategories(),
                loadItems()
            ]);

            renderAll();

        } catch (error) {

            console.error(
                '[ADMIN ITEMS]',
                error
            );

            showError(
                error.message ||
                'No fue posible cargar el módulo de productos.'
            );

        } finally {

            setLoading(false);

        }
    }


    /* =========================================================
       EVENTOS
    ========================================================== */

    function bindEvents() {

        $('btnRefreshItems')
            ?.addEventListener(
                'click',
                async function () {

                    try {

                        setLoading(true);

                        await loadItems();

                        renderAll();

                        showSuccess(
                            'Catálogo actualizado correctamente.'
                        );

                    } catch (error) {

                        console.error(error);

                        showError(
                            error.message ||
                            'No fue posible actualizar el catálogo.'
                        );

                    } finally {

                        setLoading(false);

                    }
                }
            );


        $('itemsSearch')
            ?.addEventListener(
                'input',
                debounce(
                    applyFilters,
                    250
                )
            );


        $('itemsCategory')
            ?.addEventListener(
                'change',
                applyFilters
            );


        $('itemsStatus')
            ?.addEventListener(
                'change',
                applyFilters
            );


        $('itemsAvailability')
            ?.addEventListener(
                'change',
                applyFilters
            );


        $('itemsFeatured')
            ?.addEventListener(
                'change',
                applyFilters
            );


        $('itemsTableBody')
            ?.addEventListener(
                'click',
                handleTableAction
            );
    }


    /* =========================================================
       API
    ========================================================== */

    async function loadItems() {

        const response =
            await fetch(
                `${window.APP_URL}/api/admin/items`,
                {
                    method: 'GET',

                    credentials:
                        'same-origin',

                    headers: {
                        'Accept':
                            'application/json'
                    }
                }
            );


        const result =
            await parseResponse(
                response
            );


        if (
            !response.ok ||
            !isSuccessful(result)
        ) {

            throw new Error(
                result.message ||
                'No fue posible obtener los productos.'
            );
        }


        items =
            Array.isArray(result.data)
                ? result.data
                : [];

        filteredItems =
            [...items];
    }


    async function loadCategories() {

        const response =
            await fetch(
                `${window.APP_URL}/api/admin/items/categories`,
                {
                    method: 'GET',

                    credentials:
                        'same-origin',

                    headers: {
                        'Accept':
                            'application/json'
                    }
                }
            );


        const result =
            await parseResponse(
                response
            );


        if (
            !response.ok ||
            !isSuccessful(result)
        ) {

            throw new Error(
                result.message ||
                'No fue posible cargar las categorías.'
            );
        }


        categories =
            Array.isArray(result.data)
                ? result.data
                : [];


        renderCategoryFilter();
    }


    async function deleteItem(id, name) {

        if (!id) {
            return;
        }


        const confirmed =
            await askConfirmation(
                'Desactivar producto',
                `¿Deseas desactivar "${name}"?`,
                'Sí, desactivar'
            );


        if (!confirmed) {
            return;
        }


        try {

            const response =
                await fetch(
                    `${window.APP_URL}/api/admin/items/delete`,
                    {
                        method: 'POST',

                        credentials:
                            'same-origin',

                        headers: {
                            'Content-Type':
                                'application/json',

                            'Accept':
                                'application/json'
                        },

                        body:
                            JSON.stringify({
                                id: Number(id)
                            })
                    }
                );


            const result =
                await parseResponse(
                    response
                );


            if (
                !response.ok ||
                !isSuccessful(result)
            ) {

                throw new Error(
                    result.message ||
                    'No fue posible desactivar el producto.'
                );
            }


            showSuccess(
                result.message ||
                'Producto desactivado correctamente.'
            );


            await loadItems();

            renderAll();

        } catch (error) {

            console.error(
                '[DELETE ITEM]',
                error
            );

            showError(
                error.message ||
                'No fue posible desactivar el producto.'
            );
        }
    }


    async function activateItem(id, name) {

        if (!id) {
            return;
        }


        const confirmed =
            await askConfirmation(
                'Activar producto',
                `¿Deseas activar nuevamente "${name}"?`,
                'Sí, activar'
            );


        if (!confirmed) {
            return;
        }


        try {

            const response =
                await fetch(
                    `${window.APP_URL}/api/admin/items/activate`,
                    {
                        method: 'POST',

                        credentials:
                            'same-origin',

                        headers: {
                            'Content-Type':
                                'application/json',

                            'Accept':
                                'application/json'
                        },

                        body:
                            JSON.stringify({
                                id: Number(id)
                            })
                    }
                );


            const result =
                await parseResponse(
                    response
                );


            if (
                !response.ok ||
                !isSuccessful(result)
            ) {

                throw new Error(
                    result.message ||
                    'No fue posible activar el producto.'
                );
            }


            showSuccess(
                result.message ||
                'Producto activado correctamente.'
            );


            await loadItems();

            renderAll();

        } catch (error) {

            console.error(
                '[ACTIVATE ITEM]',
                error
            );

            showError(
                error.message ||
                'No fue posible activar el producto.'
            );
        }
    }


    /* =========================================================
       RENDER
    ========================================================== */

    function renderAll() {

        applyFilters();

        updateStats();

        renderTable();
    }


    function renderCategoryFilter() {

        const select =
            $('itemsCategory');

        if (!select) {
            return;
        }


        const currentValue =
            select.value;


        select.innerHTML = `
            <option value="">
                Todas las categorías
            </option>
        `;


        const parents =
            categories.filter(
                category =>
                    !category.parent_id
            );


        const children =
            categories.filter(
                category =>
                    category.parent_id
            );


        parents.forEach(
            parent => {

                const parentOption =
                    document.createElement(
                        'option'
                    );

                parentOption.value =
                    String(parent.id);

                parentOption.textContent =
                    parent.name;

                select.appendChild(
                    parentOption
                );


                children
                    .filter(
                        child =>
                            Number(child.parent_id) ===
                            Number(parent.id)
                    )
                    .forEach(
                        child => {

                            const childOption =
                                document.createElement(
                                    'option'
                                );

                            childOption.value =
                                String(child.id);

                            childOption.textContent =
                                `↳ ${child.name}`;

                            select.appendChild(
                                childOption
                            );
                        }
                    );
            }
        );


        if (
            [...select.options].some(
                option =>
                    option.value ===
                    currentValue
            )
        ) {

            select.value =
                currentValue;
        }
    }


    function renderTable() {

        const tbody =
            $('itemsTableBody');

        const emptyState =
            $('itemsEmptyState');

        const table =
            $('itemsTable');

        if (!tbody) {
            return;
        }


        updateResultCount();


        if (!filteredItems.length) {

            tbody.innerHTML = '';

            table?.classList.add(
                'd-none'
            );

            emptyState?.classList.remove(
                'd-none'
            );

            return;
        }


        table?.classList.remove(
            'd-none'
        );

        emptyState?.classList.add(
            'd-none'
        );


        tbody.innerHTML =
            filteredItems
                .map(
                    item =>
                        renderRow(item)
                )
                .join('');
    }


    function renderRow(item) {

        const id =
            Number(item.id || 0);

        const name =
            escapeHtml(
                item.name ||
                'Sin nombre'
            );

        const sku =
            escapeHtml(
                item.sku ||
                'Sin SKU'
            );

        const category =
            escapeHtml(
                item.category_name ||
                'Sin categoría'
            );

        const description =
            escapeHtml(
                item.description ||
                ''
            );

        const price =
            formatMoney(
                item.price
            );

        const favoriteImage =
            getImageUrl(
                item.favorite_image
            );


        const isActive =
            Number(
                item.is_active
            ) === 1;


        const isAvailable =
            Number(
                item.is_available
            ) === 1;


        const isFeatured =
            Number(
                item.is_featured
            ) === 1;


        const imageCount =
            Number(
                item.image_count || 0
            );


        const imageHtml =
            favoriteImage
                ? `
                    <div class="items-product-image-wrapper">

                        <img
                            src="${escapeHtml(favoriteImage)}"
                            alt="${name}"
                            class="items-product-image"
                            loading="lazy"
                            onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');"
                        >

                        <div
                            class="items-product-image-placeholder d-none"
                        >
                            <i class="fa-solid fa-burger"></i>
                        </div>

                        ${
                    isFeatured
                        ? `
                                    <span
                                        class="items-featured-badge"
                                        title="Producto destacado"
                                    >
                                        <i class="fa-solid fa-star"></i>
                                    </span>
                                `
                        : ''
                }

                    </div>
                `
                : `
                    <div class="items-product-image-placeholder">

                        <i class="fa-solid fa-burger"></i>

                    </div>
                `;


        return `
            <tr
                data-item-id="${id}"
            >

                <!-- PRODUCTO -->

                <td>

                    <div class="items-product-cell">

                        ${imageHtml}

                        <div class="items-product-info">

                            <strong
                                title="${name}"
                            >
                                ${name}
                            </strong>

                            <span>
                                SKU: ${sku}
                            </span>

                            ${
            description
                ? `
                                        <small
                                            title="${description}"
                                        >
                                            ${description}
                                        </small>
                                    `
                : ''
        }

                        </div>

                    </div>

                </td>


                <!-- CATEGORÍA -->

                <td>

                    <span
                        class="items-category-badge"
                    >
                        ${category}
                    </span>

                </td>


                <!-- PRECIO -->

                <td>

                    <strong
                        class="items-price"
                    >
                        ${price}
                    </strong>

                    ${
            item.combo_price !== null &&
            item.combo_price !== '' &&
            Number(item.combo_price) > 0
                ? `
                                <small
                                    class="items-combo-price"
                                >
                                    Combo:
                                    ${formatMoney(item.combo_price)}
                                </small>
                            `
                : ''
        }

                </td>


                <!-- ESTADO -->

                <td>

                    ${
            isActive
                ? `
                                <span
                                    class="items-status-badge is-active"
                                >
                                    <i class="fa-solid fa-circle-check"></i>
                                    Activo
                                </span>
                            `
                : `
                                <span
                                    class="items-status-badge is-inactive"
                                >
                                    <i class="fa-solid fa-circle-xmark"></i>
                                    Inactivo
                                </span>
                            `
        }

                </td>


                <!-- DISPONIBILIDAD -->

                <td>

                    ${
            isAvailable
                ? `
                                <span
                                    class="items-availability-badge is-available"
                                >
                                    Disponible
                                </span>
                            `
                : `
                                <span
                                    class="items-availability-badge is-unavailable"
                                >
                                    No disponible
                                </span>
                            `
        }

                </td>


                <!-- DESTACADO -->

                <td>

                    ${
            isFeatured
                ? `
                                <span
                                    class="items-featured-status is-featured"
                                >
                                    <i class="fa-solid fa-star"></i>
                                    Sí
                                </span>
                            `
                : `
                                <span
                                    class="items-featured-status"
                                >
                                    <i class="fa-regular fa-star"></i>
                                    No
                                </span>
                            `
        }

                </td>


                <!-- IMÁGENES -->

                <td>

                    <span
                        class="items-image-count"
                    >

                        <i
                            class="fa-regular fa-images"
                        ></i>

                        ${imageCount}

                    </span>

                </td>


                <!-- ACCIONES -->

                <td>

                    <div
                        class="items-actions"
                    >

                        <a
                            href="${window.APP_URL}/admin/items/edit?id=${encodeURIComponent(id)}"
                            class="items-action-btn edit"
                            title="Editar producto"
                        >

                            <i class="fa-solid fa-pen"></i>

                        </a>


                        ${
            isActive
                ? `
                                    <button
                                        type="button"
                                        class="items-action-btn delete"
                                        data-action="delete"
                                        data-id="${id}"
                                        data-name="${escapeHtmlAttribute(item.name || '')}"
                                        title="Desactivar producto"
                                    >

                                        <i class="fa-solid fa-power-off"></i>

                                    </button>
                                `
                : `
                                    <button
                                        type="button"
                                        class="items-action-btn activate"
                                        data-action="activate"
                                        data-id="${id}"
                                        data-name="${escapeHtmlAttribute(item.name || '')}"
                                        title="Activar producto"
                                    >

                                        <i class="fa-solid fa-power-off"></i>

                                    </button>
                                `
        }

                    </div>

                </td>

            </tr>
        `;
    }


    /* =========================================================
       FILTROS
    ========================================================== */

    function applyFilters() {

        const search =
            (
                $('itemsSearch')?.value ||
                ''
            )
                .trim()
                .toLowerCase();


        const category =
            String(
                $('itemsCategory')?.value ||
                ''
            );


        const status =
            String(
                $('itemsStatus')?.value ||
                ''
            );


        const availability =
            String(
                $('itemsAvailability')?.value ||
                ''
            );


        const featured =
            String(
                $('itemsFeatured')?.value ||
                ''
            );


        filteredItems =
            items.filter(
                item => {

                    const searchableText =
                        [
                            item.name,
                            item.sku,
                            item.description,
                            item.ingredients
                        ]
                            .filter(Boolean)
                            .join(' ')
                            .toLowerCase();


                    if (
                        search &&
                        !searchableText.includes(
                            search
                        )
                    ) {

                        return false;
                    }


                    if (
                        category &&
                        Number(item.category_id) !==
                        Number(category)
                    ) {

                        return false;
                    }


                    if (
                        status !== '' &&
                        Number(item.is_active) !==
                        Number(status)
                    ) {

                        return false;
                    }


                    if (
                        availability !== '' &&
                        Number(item.is_available) !==
                        Number(availability)
                    ) {

                        return false;
                    }


                    if (
                        featured !== '' &&
                        Number(item.is_featured) !==
                        Number(featured)
                    ) {

                        return false;
                    }


                    return true;
                }
            );


        renderTable();

        updateStats();
    }


    /* =========================================================
       ESTADÍSTICAS
    ========================================================== */

    function updateStats() {

        const total =
            items.length;


        const active =
            items.filter(
                item =>
                    Number(item.is_active) === 1
            ).length;


        const available =
            items.filter(
                item =>
                    Number(item.is_available) === 1
            ).length;


        const featured =
            items.filter(
                item =>
                    Number(item.is_featured) === 1
            ).length;


        setText(
            'itemsStatTotal',
            total
        );


        setText(
            'itemsStatActive',
            active
        );


        setText(
            'itemsStatAvailable',
            available
        );


        setText(
            'itemsStatFeatured',
            featured
        );
    }


    function updateResultCount() {

        setText(
            'itemsResultCount',
            filteredItems.length
        );
    }


    /* =========================================================
       ACCIONES TABLA
    ========================================================== */

    async function handleTableAction(event) {

        const button =
            event.target.closest(
                '[data-action]'
            );


        if (!button) {
            return;
        }


        const action =
            button.dataset.action;


        const id =
            Number(
                button.dataset.id || 0
            );


        const name =
            button.dataset.name ||
            'este producto';


        if (!id) {
            return;
        }


        if (action === 'delete') {

            await deleteItem(
                id,
                name
            );

            return;
        }


        if (action === 'activate') {

            await activateItem(
                id,
                name
            );
        }
    }


    /* =========================================================
       LOADING
    ========================================================== */

    function setLoading(
        loading
    ) {

        const indicator =
            $('itemsLoadingIndicator');


        if (!indicator) {
            return;
        }


        indicator.classList.toggle(
            'd-none',
            !loading
        );
    }


    /* =========================================================
       RESPONSE
    ========================================================== */

    async function parseResponse(
        response
    ) {

        const text =
            await response.text();


        if (!text) {

            return {
                success: response.ok,
                data: null
            };
        }


        try {

            return JSON.parse(
                text
            );

        } catch (error) {

            console.error(
                '[ITEMS API RESPONSE]',
                text
            );

            throw new Error(
                'El servidor devolvió una respuesta no válida.'
            );
        }
    }


    function isSuccessful(
        result
    ) {

        return (
            result?.success === true ||
            result?.status === 'success'
        );
    }


    /* =========================================================
       CONFIRMACIONES
    ========================================================== */

    async function askConfirmation(
        title,
        text,
        confirmButtonText
    ) {

        if (
            typeof Swal !==
            'undefined'
        ) {

            const result =
                await Swal.fire({

                    icon: 'warning',

                    title,

                    text,

                    showCancelButton: true,

                    confirmButtonText,

                    cancelButtonText:
                        'Cancelar',

                    reverseButtons: true
                });


            return result.isConfirmed;
        }


        return window.confirm(
            `${title}\n\n${text}`
        );
    }


    /* =========================================================
       TOAST
    ========================================================== */

    function showSuccess(
        message
    ) {

        if (
            typeof window.showToast ===
            'function'
        ) {

            window.showToast(
                'success',
                message
            );

            return;
        }


        if (
            typeof Swal !==
            'undefined'
        ) {

            Swal.fire({
                icon: 'success',
                title: 'Correcto',
                text: message,
                timer: 1800,
                showConfirmButton: false
            });

            return;
        }


        alert(message);
    }


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


        if (
            typeof Swal !==
            'undefined'
        ) {

            Swal.fire({
                icon: 'error',
                title: 'Atención',
                text: message
            });

            return;
        }


        alert(message);
    }


    /* =========================================================
       UTILIDADES
    ========================================================== */

    function setText(
        id,
        value
    ) {

        const element =
            $(id);


        if (element) {

            element.textContent =
                String(value);
        }
    }


    function formatMoney(
        value
    ) {

        const amount =
            Number(value || 0);


        return new Intl.NumberFormat(
            'es-CO',
            {
                style: 'currency',
                currency: 'COP',
                maximumFractionDigits: 0
            }
        )
            .format(amount);
    }


    function getImageUrl(
        path
    ) {

        if (!path) {
            return '';
        }


        const normalized =
            String(path)
                .replace(
                    /^\/+/,
                    ''
                );


        if (
            normalized.startsWith(
                'http://'
            ) ||
            normalized.startsWith(
                'https://'
            )
        ) {

            return normalized;
        }


        return `${window.APP_URL}/${normalized}`;
    }


    function escapeHtml(
        value
    ) {

        return String(value ?? '')
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


    function escapeHtmlAttribute(
        value
    ) {

        return escapeHtml(
            value
        );
    }


    function debounce(
        callback,
        delay
    ) {

        let timeout = null;


        return function () {

            clearTimeout(
                timeout
            );


            timeout =
                setTimeout(
                    () => callback(),
                    delay
                );
        };
    }

})();