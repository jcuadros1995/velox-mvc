(function () {

    'use strict';


    let categories = [];


    document.addEventListener(
        'DOMContentLoaded',
        init
    );


    /* =========================================================
       INICIALIZACIÓN
    ========================================================== */

    async function init() {

        const page =
            document.getElementById(
                'adminItemFormPage'
            );

        if (!page) {
            return;
        }


        bindEvents();

        initializeItemGallery();


        try {

            /*
             * =====================================================
             * EDICIÓN
             * =====================================================
             *
             * Cuando estamos en:
             *
             * /admin/items/edit?id=3
             *
             * obtenemos el ID desde:
             *
             * 1. data-item-id
             * 2. ?id=
             *
             * y consultamos la API para traer el producto.
             */

            const mode =
                page.dataset.mode ||
                'create';


            if (mode === 'edit') {

                const queryId =
                    new URLSearchParams(
                        window.location.search
                    ).get('id');


                const itemId =
                    Number(
                        page.dataset.itemId ||
                        queryId ||
                        0
                    );


                if (
                    page.dataset.itemId === '0' &&
                    itemId > 0
                ) {

                    page.dataset.itemId =
                        String(itemId);
                }


                if (itemId <= 0) {

                    throw new Error(
                        'No se encontró un ID de producto válido para editar.'
                    );
                }


                await loadItemForEdit(
                    itemId
                );
            }


            /*
             * =====================================================
             * CATEGORÍAS
             * =====================================================
             */

            await loadCategories();


            /*
             * Aplicamos las reglas después de
             * haber cargado el producto y las categorías.
             */

            applyCurrentCategoryRules();


            /*
             * Picante
             */

            setupExistingSpicyLevel();


        } catch (error) {

            console.error(
                '[ITEM FORM]',
                error
            );


            showError(
                error.message ||
                'No fue posible cargar el formulario.'
            );
        }
    }


    /* =========================================================
       EVENTOS
    ========================================================== */

    function bindEvents() {

        document
            .getElementById('itemCategory')
            ?.addEventListener(
                'change',
                applyCurrentCategoryRules
            );


        document
            .getElementById('itemForm')
            ?.addEventListener(
                'submit',
                submitForm
            );


        document
            .getElementById('itemImagesInput')
            ?.addEventListener(
                'change',
                previewImages
            );


        document
            .getElementById('btnAddPriceOption')
            ?.addEventListener(
                'click',
                function () {

                    addPriceOption();

                }
            );


        document
            .getElementById('itemPriceOptions')
            ?.addEventListener(
                'click',
                function (event) {

                    const button =
                        event.target.closest(
                            '.item-price-option-remove'
                        );

                    if (!button) {
                        return;
                    }

                    const row =
                        button.closest(
                            '[data-price-option]'
                        );

                    if (!row) {
                        return;
                    }

                    row.remove();

                    updatePriceOptionsEmptyState();

                }
            );
    }


    /* =========================================================
       CARGAR PRODUCTO EN EDICIÓN
    ========================================================== */

    async function loadItemForEdit(
        itemId
    ) {

        const url =
            `${window.APP_URL}/api/admin/items/show?id=${encodeURIComponent(itemId)}`;


        console.log(
            '[ITEM FORM] Cargando producto:',
            url
        );


        const response =
            await fetch(
                url,
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


        console.log(
            '[ITEM FORM] Respuesta producto:',
            result
        );


        if (
            !response.ok ||
            !isSuccess(result)
        ) {

            throw new Error(
                result?.message ||
                'No fue posible cargar la información del producto.'
            );
        }


        /*
         * Algunas APIs devuelven:
         *
         * {
         *   success: true,
         *   data: {...}
         * }
         *
         * y otras:
         *
         * {
         *   success: true,
         *   data: {
         *      item: {...}
         *   }
         * }
         *
         * Admitimos ambas.
         */

        let item =
            result.data;


        if (
            item &&
            item.item &&
            typeof item.item === 'object'
        ) {

            item =
                item.item;
        }


        if (
            !item ||
            typeof item !== 'object'
        ) {

            throw new Error(
                'La API no devolvió la información válida del producto.'
            );
        }


        populateItemForm(
            item
        );
    }


    /* =========================================================
       LLENAR FORMULARIO
    ========================================================== */

    function populateItemForm(
        item
    ) {

        console.log(
            '[ITEM FORM] Producto recibido:',
            item
        );


        /*
         * INFORMACIÓN GENERAL
         */

        setFieldValue(
            'itemName',
            item.name
        );


        setFieldValue(
            'itemSku',
            item.sku
        );


        setFieldValue(
            'itemCategory',
            item.category_id
        );


        setFieldValue(
            'itemDescription',
            item.description
        );


        /*
         * PRECIOS
         */

        setFieldValue(
            'itemPrice',
            item.price
        );


        setFieldValue(
            'itemComboPrice',
            item.combo_price
        );


        /*
         * OPCIONES DE PRECIO
         */
        loadPriceOptions(
            item.price_options || []
        );


        setFieldValue(
            'itemCostPrice',
            item.cost_price
        );


        setFieldValue(
            'itemTaxRate',
            item.tax_rate
        );


        /*
         * INFORMACIÓN GASTRONÓMICA
         */

        setFieldValue(
            'itemIngredients',
            item.ingredients
        );


        setFieldValue(
            'itemPortion',
            item.portion_size
        );


        setFieldValue(
            'itemSpicyLevel',
            item.spicy_level
        );


        setFieldValue(
            'itemAllergens',
            item.allergens
        );


        setFieldValue(
            'itemPreparationNotes',
            item.preparation_notes
        );


        /*
         * CONFIGURACIÓN
         */

        setFieldValue(
            'itemDuration',
            item.duration_minutes
        );


        setFieldValue(
            'itemDisplayOrder',
            item.display_order
        );


        /*
         * ESTADOS
         */

        setChecked(
            'itemActive',
            toBoolean(
                item.is_active
            )
        );


        setChecked(
            'itemAvailable',
            toBoolean(
                item.is_available
            )
        );


        setChecked(
            'itemFeatured',
            toBoolean(
                item.is_featured
            )
        );


        /*
         * Guardamos algunos valores en el
         * contenedor principal del formulario.
         */

        const page =
            document.getElementById(
                'adminItemFormPage'
            );


        if (page) {

            page.dataset.itemId =
                String(
                    item.id ??
                    page.dataset.itemId ??
                    0
                );


            page.dataset.categoryId =
                String(
                    item.category_id ??
                    0
                );


            page.dataset.categoryName =
                String(
                    item.category_name ??
                    item.category ??
                    ''
                );


            page.dataset.spicyLevel =
                String(
                    item.spicy_level ??
                    0
                );
        }


        /*
         * Si la API trae imágenes, las dejamos
         * disponibles para futuras funciones.
         */

        if (
            Array.isArray(
                item.images
            )
        ) {

            window.ITEM_FORM_IMAGES =
                item.images;
        }

        initializeItemGallery();

        console.log(
            '[ITEM FORM] Formulario rellenado correctamente.'
        );
    }


    /* =========================================================
       SET VALUE
    ========================================================== */

    function setFieldValue(
        id,
        value
    ) {

        const element =
            document.getElementById(
                id
            );


        if (!element) {

            console.warn(
                `[ITEM FORM] No existe el campo #${id}`
            );

            return;
        }


        element.value =
            value === null ||
            typeof value === 'undefined'
                ? ''
                : String(value);
    }


    /* =========================================================
       SET CHECKBOX
    ========================================================== */

    function setChecked(
        id,
        checked
    ) {

        const element =
            document.getElementById(
                id
            );


        if (!element) {

            console.warn(
                `[ITEM FORM] No existe el checkbox #${id}`
            );

            return;
        }


        element.checked =
            Boolean(
                checked
            );
    }


    /* =========================================================
       CATEGORÍAS
    ========================================================== */

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
            !isSuccess(result)
        ) {

            throw new Error(
                result?.message ||
                'No fue posible cargar las categorías.'
            );
        }


        categories =
            Array.isArray(
                result.data
            )
                ? result.data
                : [];


        renderCategories();
    }


    function renderCategories() {

        const select =
            document.getElementById(
                'itemCategory'
            );


        if (!select) {
            return;
        }


        /*
         * Guardamos la categoría actual antes
         * de limpiar el select.
         */

        const page =
            document.getElementById(
                'adminItemFormPage'
            );


        const currentCategoryId =
            Number(
                page?.dataset.categoryId ||
                select.value ||
                0
            );


        const currentCategoryName =
            page?.dataset.categoryName ||
            '';


        /*
         * Limpiar
         */

        select.innerHTML = `
            <option value="">
                Selecciona una categoría
            </option>
        `;


        /*
         * Padres
         */

        const parents =
            categories.filter(
                category =>
                    !category.parent_id
            );


        /*
         * Hijos
         */

        const children =
            categories.filter(
                category =>
                    category.parent_id
            );


        /*
         * Renderizar categorías.
         */

        parents.forEach(
            parent => {

                const parentOption =
                    document.createElement(
                        'option'
                    );


                parentOption.value =
                    String(
                        parent.id
                    );


                parentOption.textContent =
                    parent.name;


                select.appendChild(
                    parentOption
                );


                children
                    .filter(
                        child =>
                            Number(
                                child.parent_id
                            ) ===
                            Number(
                                parent.id
                            )
                    )
                    .forEach(
                        child => {

                            const childOption =
                                document.createElement(
                                    'option'
                                );


                            childOption.value =
                                String(
                                    child.id
                                );


                            childOption.textContent =
                                `↳ ${child.name}`;


                            select.appendChild(
                                childOption
                            );
                        }
                    );
            }
        );


        /*
         * =====================================================
         * SI EL PRODUCTO TIENE UNA CATEGORÍA QUE NO ESTÁ
         * ACTUALMENTE EN LA LISTA, NO LA PERDEMOS.
         * =====================================================
         */

        if (
            currentCategoryId > 0
        ) {

            const exists =
                Array.from(
                    select.options
                ).some(
                    option =>
                        Number(
                            option.value
                        ) ===
                        currentCategoryId
                );


            if (!exists) {

                const option =
                    document.createElement(
                        'option'
                    );


                option.value =
                    String(
                        currentCategoryId
                    );


                option.textContent =
                    currentCategoryName ||
                    `Categoría #${currentCategoryId}`;


                select.appendChild(
                    option
                );
            }


            select.value =
                String(
                    currentCategoryId
                );
        }
    }


    /* =========================================================
       REGLAS DINÁMICAS DE CATEGORÍA
    ========================================================== */

    function applyCurrentCategoryRules() {

        const categoryId =
            Number(
                document.getElementById(
                    'itemCategory'
                )?.value ||
                0
            );


        if (!categoryId) {

            hideAllRuleFields();

            return;
        }


        const category =
            categories.find(
                item =>
                    Number(
                        item.id
                    ) ===
                    categoryId
            );


        /*
         * Si la categoría no está dentro de
         * las reglas devueltas por la API,
         * no destruimos los datos del producto.
         */

        if (!category) {

            console.warn(
                '[ITEM FORM] No se encontraron reglas para categoría:',
                categoryId
            );

            return;
        }


        toggleField(
            'fieldIngredients',
            Number(
                category.show_ingredients
            ) === 1
        );


        toggleField(
            'fieldComboPrice',
            Number(
                category.show_combo_price
            ) === 1
        );


        toggleField(
            'fieldPortion',
            Number(
                category.show_portion
            ) === 1
        );


        toggleField(
            'fieldSpicy',
            Number(
                category.show_spicy_level
            ) === 1
        );


        toggleField(
            'fieldAllergens',
            Number(
                category.show_allergens
            ) === 1
        );


        toggleField(
            'fieldFeatured',
            Number(
                category.show_featured
            ) === 1
        );
    }


    /* =========================================================
       OCULTAR CAMPOS
    ========================================================== */

    function hideAllRuleFields() {

        [
            'fieldIngredients',
            'fieldComboPrice',
            'fieldPortion',
            'fieldSpicy',
            'fieldAllergens',
            'fieldFeatured'
        ].forEach(
            id => {

                toggleField(
                    id,
                    false
                );
            }
        );
    }


    /* =========================================================
       MOSTRAR / OCULTAR CAMPO
    ========================================================== */

    function toggleField(
        id,
        visible
    ) {

        const element =
            document.getElementById(
                id
            );


        if (!element) {
            return;
        }


        element.classList.toggle(
            'is-hidden',
            !visible
        );
    }


    /* =========================================================
       GUARDAR PRODUCTO
    ========================================================== */

    async function submitForm(
        event
    ) {

        event.preventDefault();


        const button =
            document.getElementById(
                'btnSaveItem'
            );


        const originalHtml =
            button?.innerHTML;


        try {

            validateForm();


            if (button) {

                button.disabled =
                    true;


                button.innerHTML = `
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    <span>Guardando...</span>
                `;
            }


            const page =
                document.getElementById(
                    'adminItemFormPage'
                );


            const itemId =
                Number(
                    page?.dataset.itemId ||
                    new URLSearchParams(
                        window.location.search
                    ).get('id') ||
                    0
                );


            const mode =
                page?.dataset.mode ||
                (
                    itemId > 0
                        ? 'edit'
                        : 'create'
                );


            const payload =
                buildPayload();


            if (
                mode === 'edit' &&
                itemId > 0
            ) {

                payload.id =
                    itemId;
            }


            const endpoint =
                mode === 'edit'
                    ? '/api/admin/items/update'
                    : '/api/admin/items/store';


            const response =
                await fetch(
                    `${window.APP_URL}${endpoint}`,
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
                            JSON.stringify(
                                payload
                            )
                    }
                );


            const result =
                await parseResponse(
                    response
                );


            if (
                !response.ok ||
                !isSuccess(result)
            ) {

                throw new Error(
                    result?.message ||
                    'No fue posible guardar el producto.'
                );
            }

            /*
             * =====================================================
             * GUARDAR IMÁGENES
             * =====================================================
             */

            const savedItemId =
                Number(
                    result.item_id ||
                    result.data?.id ||
                    itemId
                );


            if (
                savedItemId > 0
            ) {

                await saveItemImages(
                    savedItemId
                );
            }


            showSuccess(
                result.message ||
                (
                    mode === 'edit'
                        ? 'Producto actualizado correctamente.'
                        : 'Producto creado correctamente.'
                )
            );


            setTimeout(
                () => {

                    window.location.href =
                        `${window.APP_URL}/admin/items`;

                },
                900
            );


        } catch (error) {

            console.error(
                '[SAVE ITEM]',
                error
            );


            showError(
                error.message ||
                'No fue posible guardar el producto.'
            );


        } finally {

            if (button) {

                button.disabled =
                    false;


                button.innerHTML =
                    originalHtml;
            }
        }
    }


    /* =========================================================
       CONSTRUIR PAYLOAD
    ========================================================== */

    function buildPayload() {

        return {

            name:
                value(
                    'itemName'
                ),


            sku:
                value(
                    'itemSku'
                ),


            category_id:
                numberValue(
                    'itemCategory'
                ),


            description:
                value(
                    'itemDescription'
                ),


            price:
                moneyValue(
                    'itemPrice'
                ),


            combo_price:
                nullableMoneyValue(
                    'itemComboPrice'
                ),


            price_options:
                getPriceOptions(),


            ingredients:
                value(
                    'itemIngredients'
                ),


            portion_size:
                value(
                    'itemPortion'
                ),


            spicy_level:
                numberValue(
                    'itemSpicyLevel'
                ),


            allergens:
                value(
                    'itemAllergens'
                ),


            preparation_notes:
                value(
                    'itemPreparationNotes'
                ),


            cost_price:
                moneyValue(
                    'itemCostPrice'
                ),


            tax_rate:
                numberValue(
                    'itemTaxRate'
                ),


            duration_minutes:
                numberValue(
                    'itemDuration'
                ),


            is_active:
                checked(
                    'itemActive'
                )
                    ? 1
                    : 0,


            is_available:
                checked(
                    'itemAvailable'
                )
                    ? 1
                    : 0,


            is_featured:
                checked(
                    'itemFeatured'
                )
                    ? 1
                    : 0,


            display_order:
                numberValue(
                    'itemDisplayOrder'
                )
        };
    }


    /* =========================================================
       VALIDACIONES
    ========================================================== */

    function validateForm() {

        const name =
            value(
                'itemName'
            );


        if (!name) {

            focusAndThrow(
                'itemName',
                'El nombre del producto es obligatorio.'
            );
        }


        const categoryId =
            numberValue(
                'itemCategory'
            );


        if (!categoryId) {

            focusAndThrow(
                'itemCategory',
                'Debes seleccionar una categoría.'
            );
        }


        const price =
            moneyValue(
                'itemPrice'
            );


        if (price < 0) {

            focusAndThrow(
                'itemPrice',
                'El precio no puede ser negativo.'
            );
        }


        const spicy =
            numberValue(
                'itemSpicyLevel'
            );


        if (
            spicy < 0 ||
            spicy > 3
        ) {

            focusAndThrow(
                'itemSpicyLevel',
                'El nivel de picante no es válido.'
            );
        }
    }


    /* =========================================================
       FOCUS + ERROR
    ========================================================== */

    function focusAndThrow(
        id,
        message
    ) {

        const element =
            document.getElementById(
                id
            );


        element?.focus();


        throw new Error(
            message
        );
    }


    /* =========================================================
   PREVIEW DE IMÁGENES
========================================================= */

    let itemGalleryImages = [];
    let itemGalleryActiveIndex = 0;
    let itemGalleryDeletedIds = [];

    /**
     * Inicializa las imágenes existentes
     * que vienen desde PHP / API.
     */
    function initializeItemGallery() {

        const existingImages =
            Array.isArray(window.ITEM_FORM?.images)
                ? window.ITEM_FORM.images
                : [];

        itemGalleryImages =
            existingImages.map(
                image => ({
                    id:
                        Number(
                            image.id ||
                            0
                        ),

                    item_id:
                        Number(
                            image.item_id ||
                            0
                        ),

                    url:
                        image.url ||
                        `${window.APP_URL}/public/${String(
                            image.image_path || ''
                        ).replace(/^[/\\]+/, '')}`,

                    image_alt:
                        image.image_alt ||
                        'Imagen del producto',

                    is_favorite:
                        Boolean(
                            image.is_favorite
                        ),

                    isNew:
                        false,

                    file:
                        null
                })
            );


        /*
         * Si existe una imagen favorita,
         * la mostramos primero.
         */
        const favoriteIndex =
            itemGalleryImages.findIndex(
                image =>
                    image.is_favorite
            );


        itemGalleryActiveIndex =
            favoriteIndex >= 0
                ? favoriteIndex
                : 0;


        renderItemGallery();
    }


    /**
     * Cuando el usuario selecciona nuevas imágenes.
     */
    function previewImages(event) {

        const files =
            Array.from(
                event.target.files || []
            );


        if (!files.length) {
            return;
        }


        /*
         * Validación básica.
         */
        const validFiles =
            files.filter(
                file => {

                    if (
                        !file.type.startsWith(
                            'image/'
                        )
                    ) {

                        return false;
                    }


                    if (
                        file.size >
                        8 * 1024 * 1024
                    ) {

                        showError(
                            `La imagen "${file.name}" supera los 8 MB.`
                        );

                        return false;
                    }


                    return true;
                }
            );


        /*
         * Convertimos las imágenes
         * a previews.
         */
        validFiles.forEach(
            file => {

                const reader =
                    new FileReader();


                reader.onload =
                    function (
                        loadEvent
                    ) {

                        itemGalleryImages.push({

                            id:
                                0,

                            item_id:
                                Number(
                                    document.getElementById(
                                        'itemId'
                                    )?.value ||
                                    0
                                ),

                            clientKey:
                                `new-${Date.now()}-${Math.random()
                                    .toString(36)
                                    .slice(2)}`,

                            url:
                            loadEvent.target.result,

                            image_alt:
                            file.name,

                            is_favorite:
                                itemGalleryImages.length === 0,

                            isNew:
                                true,

                            file:
                            file

                        });


                        /*
                         * Si es la primera imagen,
                         * se convierte automáticamente
                         * en principal.
                         */
                        if (
                            itemGalleryImages.length === 1
                        ) {

                            itemGalleryActiveIndex =
                                0;
                        }


                        renderItemGallery();

                        updateImageCounter();
                    };


                reader.readAsDataURL(
                    file
                );
            }
        );
    }


    /**
     * Renderiza:
     *
     * 1. Imagen grande en itemImagesStage
     * 2. Miniaturas en itemImagesThumbs
     */
    function renderItemGallery() {

        const stage =
            document.getElementById(
                'itemImagesStage'
            );


        const emptyState =
            document.getElementById(
                'itemImagesMainEmpty'
            );


        const mainView =
            document.getElementById(
                'itemImagesMainView'
            );


        const mainImage =
            document.getElementById(
                'itemImagesMainImage'
            );


        const favoriteBadge =
            document.getElementById(
                'itemImagesFavoriteBadge'
            );


        const newBadge =
            document.getElementById(
                'itemImagesNewBadge'
            );


        const thumbs =
            document.getElementById(
                'itemImagesThumbs'
            );


        const emptyGallery =
            document.getElementById(
                'itemImagesEmptyGallery'
            );


        if (
            !stage ||
            !emptyState ||
            !mainView ||
            !mainImage ||
            !thumbs
        ) {

            return;
        }


        /*
         * =====================================================
         * SIN IMÁGENES
         * =====================================================
         */

        if (
            itemGalleryImages.length === 0
        ) {

            stage.classList.add(
                'is-empty'
            );


            emptyState.hidden =
                false;


            mainView.hidden =
                true;


            thumbs.innerHTML =
                '';


            if (emptyGallery) {

                emptyGallery.classList.add(
                    'is-visible'
                );
            }


            return;
        }


        /*
         * =====================================================
         * HAY IMÁGENES
         * =====================================================
         */

        stage.classList.remove(
            'is-empty'
        );


        emptyState.hidden =
            true;


        mainView.hidden =
            false;


        if (emptyGallery) {

            emptyGallery.classList.remove(
                'is-visible'
            );
        }


        /*
         * Seguridad para índice.
         */
        if (
            itemGalleryActiveIndex < 0 ||
            itemGalleryActiveIndex >=
            itemGalleryImages.length
        ) {

            itemGalleryActiveIndex =
                0;
        }


        const activeImage =
            itemGalleryImages[
                itemGalleryActiveIndex
                ];


        /*
         * =====================================================
         * IMAGEN GRANDE
         * =====================================================
         */

        mainImage.src =
            activeImage.url;


        mainImage.alt =
            activeImage.image_alt ||
            'Vista previa del producto';


        /*
         * Badge principal.
         */
        if (favoriteBadge) {

            favoriteBadge.innerHTML = `
            <i class="fa-solid fa-star"></i>
            ${activeImage.is_favorite
                ? 'Favorita / Principal'
                : 'Imagen del producto'}
        `;

            favoriteBadge.style.display =
                activeImage.is_favorite
                    ? 'inline-flex'
                    : 'none';
        }


        /*
         * Badge "Nueva".
         */
        if (newBadge) {

            newBadge.hidden =
                !activeImage.isNew;
        }


        /*
         * =====================================================
         * MINIATURAS
         * =====================================================
         */

        thumbs.innerHTML =
            '';


        itemGalleryImages.forEach(
            (
                image,
                index
            ) => {

                const thumb =
                    document.createElement(
                        'div'
                    );


                thumb.className =
                    'item-images-thumb';


                if (
                    index ===
                    itemGalleryActiveIndex
                ) {

                    thumb.classList.add(
                        'is-active'
                    );
                }


                if (
                    image.is_favorite
                ) {

                    thumb.classList.add(
                        'is-favorite'
                    );
                }


                thumb.dataset.index =
                    String(index);


                thumb.dataset.existing =
                    image.isNew
                        ? '0'
                        : '1';


                thumb.draggable =
                    true;


                thumb.innerHTML = `
                <div class="item-images-thumb-media">

                    <img
                        src="${escapeHtmlAttribute(
                    image.url
                )}"
                        alt="${escapeHtmlAttribute(
                    image.image_alt ||
                    'Imagen del producto'
                )}"
                    >

                </div>


                <div class="item-images-thumb-overlay">

                    <div class="item-images-thumb-info">

                        ${
                    image.is_favorite
                        ? `
                                    <span class="item-images-thumb-primary">
                                        <i class="fa-solid fa-star"></i>
                                        Principal
                                    </span>
                                  `
                        : ''
                }

                        ${
                    image.isNew
                        ? `
                                    <span class="item-images-thumb-new">
                                        Nueva
                                    </span>
                                  `
                        : ''
                }

                    </div>


                    <div class="item-images-thumb-actions">

                        <button
                            type="button"
                            class="item-images-thumb-action favorite"
                            data-action="favorite"
                            title="Establecer como principal"
                        >
                            <i class="fa-solid fa-star"></i>
                        </button>


                        <button
                            type="button"
                            class="item-images-thumb-action delete"
                            data-action="delete"
                            title="Quitar imagen"
                        >
                            <i class="fa-solid fa-trash"></i>
                        </button>

                    </div>

                </div>
            `;


                /*
                 * Click en miniatura.
                 */
                thumb.addEventListener(
                    'click',

                    event => {

                        const action =
                            event.target.closest(
                                '[data-action]'
                            );


                        /*
                         * Si hizo click en
                         * una acción no seleccionamos
                         * la miniatura.
                         */
                        if (action) {
                            return;
                        }


                        itemGalleryActiveIndex =
                            index;


                        renderItemGallery();
                    }
                );

                setupItemGalleryDragAndDrop(
                    thumb,
                    index
                );


                /*
                 * FAVORITA
                 */
                thumb
                    .querySelector(
                        '[data-action="favorite"]'
                    )
                    ?.addEventListener(
                        'click',
                        event => {

                            event.stopPropagation();


                            itemGalleryImages.forEach(
                                galleryImage => {
                                    galleryImage.is_favorite = false;
                                }
                            );

                            image.is_favorite = true;


                            itemGalleryActiveIndex =
                                index;


                            renderItemGallery();
                        }
                    );


                /*
                 * ELIMINAR
                 */
                thumb
                    .querySelector(
                        '[data-action="delete"]'
                    )
                    ?.addEventListener(
                        'click',
                        event => {

                            event.stopPropagation();


                            const image =
                                itemGalleryImages[index];


                            if (!image) {
                                return;
                            }


                            /*
                             * Si es una imagen que ya existe
                             * en la base de datos, guardamos
                             * su ID para eliminarla cuando
                             * se presione "Guardar".
                             */
                            if (
                                !image.isNew &&
                                Number(image.id) > 0
                            ) {

                                const imageId =
                                    Number(image.id);


                                if (
                                    !itemGalleryDeletedIds.includes(
                                        imageId
                                    )
                                ) {

                                    itemGalleryDeletedIds.push(
                                        imageId
                                    );
                                }
                            }


                            /*
                             * La eliminamos inmediatamente
                             * de la interfaz.
                             */
                            itemGalleryImages.splice(
                                index,
                                1
                            );

                            /*
                             * Si eliminamos la imagen principal,
                             * convertir la primera restante
                             * en principal.
                             */
                            if (
                                image.is_favorite &&
                                itemGalleryImages.length > 0
                            ) {

                                itemGalleryImages.forEach(
                                    galleryImage => {

                                        galleryImage.is_favorite =
                                            false;
                                    }
                                );


                                itemGalleryImages[0].is_favorite =
                                    true;


                                itemGalleryActiveIndex =
                                    0;
                            }

                            /*
                             * Corregir índice de imagen activa.
                             */
                            if (
                                itemGalleryActiveIndex >=
                                itemGalleryImages.length
                            ) {

                                itemGalleryActiveIndex =
                                    Math.max(
                                        0,
                                        itemGalleryImages.length - 1
                                    );
                            }


                            renderItemGallery();

                            updateImageCounter();
                        }
                    );


                thumbs.appendChild(
                    thumb
                );
            }
        );


        updateImageCounter();
    }


    /**
     * Evita inyectar caracteres HTML
     * desde nombres de archivos.
     */
    function escapeHtmlAttribute(
        value
    ) {

        return String(
            value ?? ''
        )
            .replace(
                /&/g,
                '&amp;'
            )
            .replace(
                /"/g,
                '&quot;'
            )
            .replace(
                /'/g,
                '&#039;'
            )
            .replace(
                /</g,
                '&lt;'
            )
            .replace(
                />/g,
                '&gt;'
            );
    }

    /* =========================================================
   DRAG & DROP DE IMÁGENES
========================================================= */

    function setupItemGalleryDragAndDrop(
        thumb,
        index
    ) {

        /*
         * La tarjeta se puede arrastrar.
         */
        thumb.draggable = true;


        /*
         * =====================================================
         * DRAG START
         * =====================================================
         */

        thumb.addEventListener(
            'dragstart',
            event => {

                /*
                 * No permitir arrastrar si se inició
                 * sobre un botón.
                 */
                if (
                    event.target.closest(
                        '[data-action]'
                    )
                ) {

                    event.preventDefault();

                    return;
                }


                thumb.classList.add(
                    'is-dragging'
                );


                event.dataTransfer.effectAllowed =
                    'move';


                event.dataTransfer.setData(
                    'text/plain',
                    String(index)
                );
            }
        );


        /*
         * =====================================================
         * DRAG OVER
         * =====================================================
         */

        thumb.addEventListener(
            'dragover',
            event => {

                event.preventDefault();


                event.dataTransfer.dropEffect =
                    'move';


                /*
                 * No marcar como destino
                 * la misma imagen.
                 */
                const sourceIndex =
                    Number(
                        event.dataTransfer.getData(
                            'text/plain'
                        )
                    );


                if (
                    sourceIndex === index
                ) {

                    return;
                }


                thumb.classList.add(
                    'is-drag-over'
                );
            }
        );


        /*
         * =====================================================
         * DRAG LEAVE
         * =====================================================
         */

        thumb.addEventListener(
            'dragleave',
            () => {

                thumb.classList.remove(
                    'is-drag-over'
                );
            }
        );


        /*
         * =====================================================
         * DROP
         * =====================================================
         */

        thumb.addEventListener(
            'drop',
            event => {

                event.preventDefault();


                thumb.classList.remove(
                    'is-drag-over'
                );


                const sourceIndex =
                    Number(
                        event.dataTransfer.getData(
                            'text/plain'
                        )
                    );


                const targetIndex =
                    index;


                /*
                 * Validar índices.
                 */
                if (
                    Number.isNaN(
                        sourceIndex
                    ) ||
                    sourceIndex < 0 ||
                    sourceIndex >=
                    itemGalleryImages.length
                ) {

                    return;
                }


                if (
                    sourceIndex === targetIndex
                ) {

                    return;
                }


                /*
                 * =================================================
                 * CAMBIAR ORDEN
                 * =================================================
                 */

                const movedImage =
                    itemGalleryImages.splice(
                        sourceIndex,
                        1
                    )[0];


                itemGalleryImages.splice(
                    targetIndex,
                    0,
                    movedImage
                );


                /*
                 * La imagen activa sigue siendo
                 * la misma imagen, no simplemente
                 * el mismo índice.
                 */
                if (
                    itemGalleryActiveIndex ===
                    sourceIndex
                ) {

                    itemGalleryActiveIndex =
                        targetIndex;

                } else if (
                    sourceIndex <
                    itemGalleryActiveIndex &&
                    targetIndex >=
                    itemGalleryActiveIndex
                ) {

                    itemGalleryActiveIndex--;

                } else if (
                    sourceIndex >
                    itemGalleryActiveIndex &&
                    targetIndex <=
                    itemGalleryActiveIndex
                ) {

                    itemGalleryActiveIndex++;
                }


                /*
                 * Volver a pintar la galería.
                 *
                 * Esto también actualiza los índices
                 * de todas las miniaturas.
                 */
                renderItemGallery();
            }
        );


        /*
         * =====================================================
         * DRAG END
         * =====================================================
         */

        thumb.addEventListener(
            'dragend',
            () => {

                thumb.classList.remove(
                    'is-dragging'
                );


                document
                    .querySelectorAll(
                        '.item-images-thumb'
                    )
                    .forEach(
                        element => {

                            element.classList.remove(
                                'is-dragging'
                            );

                            element.classList.remove(
                                'is-drag-over'
                            );
                        }
                    );
            }
        );
    }


    /* =========================================================
       CONTADOR DE IMÁGENES
    ========================================================== */

    function updateImageCounter() {

        const existing =
            document.querySelectorAll(
                '#itemImagesThumbs [data-existing="1"]'
            ).length;


        const selected =
            document.getElementById(
                'itemImagesInput'
            )?.files?.length ||
            0;


        const counter =
            document.getElementById(
                'itemImagesCounter'
            ) ||
            document.getElementById(
                'itemImagesCount'
            );


        if (counter) {

            counter.textContent =
                `${existing + selected} imágenes`;
        }
    }


    /* =========================================================
       NIVEL DE PICANTE
    ========================================================== */

    function setupExistingSpicyLevel() {

        const page =
            document.getElementById(
                'adminItemFormPage'
            );


        const spicy =
            Number(
                page?.dataset.spicyLevel ||
                document.getElementById(
                    'itemSpicyLevel'
                )?.value ||
                0
            );


        const select =
            document.getElementById(
                'itemSpicyLevel'
            );


        if (
            select &&
            spicy >= 0 &&
            spicy <= 3
        ) {

            select.value =
                String(
                    spicy
                );
        }
    }


    /* =========================================================
       VALOR TEXTO
    ========================================================== */

    function value(
        id
    ) {

        return (
            document.getElementById(
                id
            )?.value ||
            ''
        ).trim();
    }


    /* =========================================================
       VALOR NUMÉRICO
    ========================================================== */

    function numberValue(
        id
    ) {

        return Number(
            document.getElementById(
                id
            )?.value ||
            0
        );
    }


    /* =========================================================
   VALOR MONETARIO
========================================================= */

    function moneyValue(
        id
    ) {

        const element =
            document.getElementById(
                id
            );


        if (!element) {
            return 0;
        }


        let raw =
            String(
                element.value ??
                ''
            ).trim();


        if (raw === '') {
            return 0;
        }


        /*
         * El input es type="number", por lo que
         * normalmente recibiremos valores como:
         *
         * 33500
         * 33500.00
         * 36500.50
         *
         * El punto representa decimales y NO
         * debe eliminarse.
         */
        raw =
            raw
                .replace(
                    /\s/g,
                    ''
                )
                .replace(
                    /\$/g,
                    ''
                );


        /*
         * Soporte para valores con coma decimal:
         *
         * 33500,50 -> 33500.50
         */
        if (
            raw.includes(',') &&
            !raw.includes('.')
        ) {

            raw =
                raw.replace(
                    ',',
                    '.'
                );
        }


        /*
         * Si por alguna razón llega un valor
         * con formato colombiano:
         *
         * 33.500,50
         *
         * lo convertimos correctamente a:
         *
         * 33500.50
         */
        if (
            raw.includes('.') &&
            raw.includes(',')
        ) {

            raw =
                raw
                    .replace(
                        /\./g,
                        ''
                    )
                    .replace(
                        ',',
                        '.'
                    );
        }


        const number =
            Number(
                raw
            );


        return Number.isFinite(
            number
        )
            ? number
            : 0;
    }


    /* =========================================================
       DINERO NULLABLE
    ========================================================== */

    function nullableMoneyValue(
        id
    ) {

        const element =
            document.getElementById(
                id
            );


        if (
            !element ||
            element.value === ''
        ) {

            return null;
        }


        return moneyValue(
            id
        );
    }


    /* =========================================================
       CHECKED
    ========================================================== */

    function checked(
        id
    ) {

        return Boolean(
            document.getElementById(
                id
            )?.checked
        );
    }


    /* =========================================================
       BOOLEAN
    ========================================================== */

    function toBoolean(
        value
    ) {

        return (
            value === true ||
            value === 1 ||
            value === '1' ||
            value === 'true'
        );
    }


    /* =========================================================
       PARSE RESPONSE
    ========================================================== */

    async function parseResponse(
        response
    ) {

        const text =
            await response.text();


        try {

            return JSON.parse(
                text
            );

        } catch (error) {

            console.error(
                '[ITEM FORM RESPONSE]',
                text
            );


            throw new Error(
                'El servidor devolvió una respuesta no válida.'
            );
        }
    }


    /* =========================================================
       SUCCESS
    ========================================================== */

    function isSuccess(
        result
    ) {

        return (
            result?.success === true ||
            result?.status === 'success'
        );
    }


    /* =========================================================
       TOAST SUCCESS
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
                icon:
                    'success',

                title:
                    'Correcto',

                text:
                message,

                timer:
                    1800,

                showConfirmButton:
                    false
            });

            return;
        }


        alert(
            message
        );
    }


    /* =========================================================
       TOAST ERROR
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


        if (
            typeof Swal !==
            'undefined'
        ) {

            Swal.fire({
                icon:
                    'error',

                title:
                    'Atención',

                text:
                message
            });

            return;
        }


        alert(
            message
        );
    }

    /* =========================================================
   OPCIONES DE PRECIO
========================================================= */

    function addPriceOption(
        option = {}
    ) {

        const container =
            document.getElementById(
                'itemPriceOptions'
            );

        if (!container) {
            return;
        }


        const row =
            document.createElement(
                'div'
            );


        row.className =
            'item-price-option';


        row.setAttribute(
            'data-price-option',
            ''
        );


        row.innerHTML = `

        <div class="item-price-option-field">

            <label>
                Cantidad
            </label>

            <input
                type="number"
                class="price-option-quantity"
                min="1"
                step="1"
                value="${escapeHtml(
            option.quantity ?? ''
        )}"
                placeholder="6"
            >

        </div>


        <div class="item-price-option-field">

            <label>
                Nombre
            </label>

            <input
                type="text"
                class="price-option-label"
                maxlength="100"
                value="${escapeHtml(
            option.quantity_label ?? ''
        )}"
                placeholder="Ej: 6 ALITAS"
            >

        </div>


        <div class="item-price-option-field">

            <label>
                Precio sola
            </label>

            <div class="item-money-input">

                <span>$</span>

                <input
                    type="number"
                    class="price-option-price"
                    min="0"
                    step="1"
                    value="${escapeHtml(
            option.price ?? ''
        )}"
                    placeholder="29900"
                >

            </div>

        </div>


        <div class="item-price-option-field">

            <label>
                Precio combo
            </label>

            <div class="item-money-input">

                <span>$</span>

                <input
                    type="number"
                    class="price-option-combo"
                    min="0"
                    step="1"
                    value="${escapeHtml(
            option.combo_price ?? ''
        )}"
                    placeholder="35900"
                >

            </div>

        </div>


        <button
            type="button"
            class="item-price-option-remove"
            title="Eliminar presentación"
        >

            <i class="fa-solid fa-trash"></i>

        </button>

    `;


        container.appendChild(
            row
        );


        updatePriceOptionsEmptyState();
    }


    /**
     * Obtiene las opciones de precio
     * actualmente configuradas en el formulario.
     */
    function getPriceOptions() {

        const container =
            document.getElementById(
                'itemPriceOptions'
            );

        if (!container) {
            return [];
        }


        const rows =
            container.querySelectorAll(
                '[data-price-option]'
            );


        const options = [];


        rows.forEach(
            function (row) {

                const quantity =
                    Number(
                        row.querySelector(
                            '.price-option-quantity'
                        )?.value
                        || 0
                    );


                const label =
                    row.querySelector(
                        '.price-option-label'
                    )?.value
                        ?.trim()
                    || '';


                const price =
                    Number(
                        row.querySelector(
                            '.price-option-price'
                        )?.value
                        || 0
                    );


                const comboInput =
                    row.querySelector(
                        '.price-option-combo'
                    );


                const comboValue =
                    comboInput?.value
                        ?.trim()
                    || '';


                const comboPrice =
                    comboValue === ''
                        ? null
                        : Number(
                            comboValue
                        );


                /*
                 * Ignoramos filas completamente vacías.
                 */
                if (
                    quantity <= 0 &&
                    label === '' &&
                    price <= 0 &&
                    comboPrice === null
                ) {
                    return;
                }


                options.push({

                    quantity:
                    quantity,

                    quantity_label:
                    label,

                    price:
                    price,

                    combo_price:
                    comboPrice

                });

            }
        );


        return options;
    }


    /**
     * Carga opciones existentes al editar.
     */
    function loadPriceOptions(
        options
    ) {

        const container =
            document.getElementById(
                'itemPriceOptions'
            );

        if (!container) {
            return;
        }


        container.innerHTML = '';


        if (
            !Array.isArray(options) ||
            options.length === 0
        ) {

            updatePriceOptionsEmptyState();

            return;
        }


        options.forEach(
            function (option) {

                addPriceOption(
                    option
                );

            }
        );


        updatePriceOptionsEmptyState();
    }


    /**
     * Estado vacío.
     */
    function updatePriceOptionsEmptyState() {

        const container =
            document.getElementById(
                'itemPriceOptions'
            );

        const empty =
            document.getElementById(
                'itemPriceOptionsEmpty'
            );


        if (!container || !empty) {
            return;
        }


        const count =
            container.querySelectorAll(
                '[data-price-option]'
            ).length;


        empty.hidden =
            count > 0;
    }


    /**
     * Escapa valores para insertar
     * dentro del HTML generado.
     */
    function escapeHtml(
        value
    ) {

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

    async function saveItemImages(
        itemId
    ) {

        if (
            !itemId ||
            Number(itemId) <= 0
        ) {

            return;
        }


        const input =
            document.getElementById(
                'itemImagesInput'
            );


        const formData =
            new FormData();


        formData.append(
            'item_id',
            String(itemId)
        );


        /*
         * =====================================================
         * IMÁGENES NUEVAS
         * =====================================================
         */

        const newImages =
            itemGalleryImages.filter(
                image =>
                    image.isNew &&
                    image.file
            );


        newImages.forEach(
            image => {

                formData.append(
                    'images[]',
                    image.file
                );
            }
        );


        /*
         * =====================================================
         * KEYS DE IMÁGENES NUEVAS
         * =====================================================
         */

        const newKeys =
            newImages.map(
                image =>
                    image.clientKey
            );


        formData.append(
            'new_keys',
            JSON.stringify(
                newKeys
            )
        );


        /*
         * =====================================================
         * IMÁGENES ELIMINADAS
         * =====================================================
         */

        formData.append(
            'deleted_ids',
            JSON.stringify(
                itemGalleryDeletedIds
            )
        );


        /*
         * =====================================================
         * ORDEN ACTUAL
         * =====================================================
         */

        const order =
            itemGalleryImages.map(
                image => {

                    if (
                        image.isNew
                    ) {

                        return (
                            'new:' +
                            image.clientKey
                        );
                    }


                    return (
                        'existing:' +
                        image.id
                    );
                }
            );


        formData.append(
            'order',
            JSON.stringify(
                order
            )
        );


        /*
         * =====================================================
         * IMAGEN FAVORITA
         * =====================================================
         */

        const favorite =
            itemGalleryImages.find(
                image =>
                    image.is_favorite
            );


        if (favorite) {

            formData.append(

                'favorite',

                favorite.isNew
                    ? (
                        'new:' +
                        favorite.clientKey
                    )
                    : (
                        'existing:' +
                        favorite.id
                    )
            );

        } else {

            formData.append(
                'favorite',
                ''
            );
        }


        /*
         * =====================================================
         * ENVIAR AL BACKEND
         * =====================================================
         */

        const response =
            await fetch(
                `${window.APP_URL}/api/admin/items/images/save`,
                {
                    method:
                        'POST',

                    credentials:
                        'same-origin',

                    headers: {
                        'Accept':
                            'application/json'
                    },

                    body:
                    formData
                }
            );


        const result =
            await parseResponse(
                response
            );


        if (
            !response.ok ||
            !isSuccess(result)
        ) {

            throw new Error(
                result?.message ||
                'No fue posible guardar las imágenes.'
            );
        }


        /*
         * Limpiar estado de eliminaciones
         * después de guardar correctamente.
         */
        itemGalleryDeletedIds = [];


        return result;
    }

})();