document.addEventListener('DOMContentLoaded', () => {
    const page =
        document.getElementById(
            'categoryPageAdmin'
        );

    if (!page) {
        return;
    }

    const categoryId =
        Number(
            page.dataset.categoryId || 0
        );

    let pageId =
        Number(
            page.dataset.pageId || 0
        );

    let currentImageOrder = [];

    let pendingImages = [];

    let pendingDeletedImages = [];

    let pendingFavoriteImageId = null;

    let pendingFavoriteKey = null;

    let activeImageId = 0;

    let activePendingKey = null;

    const titleInput =
        document.getElementById(
            'categoryPageTitle'
        );

    const descriptionInput =
        document.getElementById(
            'categoryPageDescription'
        );

    const saveButton =
        document.getElementById(
            'categoryPageSave'
        );

    const uploadButton =
        document.getElementById(
            'categoryPageUploadButton'
        );

    const fileInput =
        document.getElementById(
            'categoryPageImages'
        );

    const gallery =
        document.getElementById(
            'categoryPageGallery'
        );

    const imageCount =
        document.getElementById(
            'categoryPageImageCount'
        );

    const emptyGallery =
        document.getElementById(
            'categoryPageEmptyGallery'
        );


    const mobileImageInput = document.createElement('input');

    mobileImageInput.type = 'file';
    mobileImageInput.accept = 'image/jpeg,image/png,image/webp';
    mobileImageInput.hidden = true;

    document.body.appendChild(mobileImageInput);

    let selectedMobileImageId = 0;


    /*
    |--------------------------------------------------------------------------
    | TOAST
    |--------------------------------------------------------------------------
    */

    function showMessage(
        message,
        type = 'success'
    ) {

        if (
            typeof window.showToast ===
            'function'
        ) {

            window.showToast(
                type,
                message
            );

            return;
        }

        alert(message);
    }


    /*
    |--------------------------------------------------------------------------
    | CONTADOR DE IMÁGENES
    |--------------------------------------------------------------------------
    */

    function updateImageCount() {

        if (!imageCount) {
            return;
        }

        const existingImages =
            gallery
                ? gallery.querySelectorAll(
                    '.category-page-image:not(.is-new-image)'
                ).length
                : 0;

        const total =
            existingImages +
            pendingImages.length;

        imageCount.textContent =
            `${total} ${
                total === 1
                    ? 'imagen'
                    : 'imágenes'
            }`;
    }


    /*
    |--------------------------------------------------------------------------
    | ESTADO DE GALERÍA VACÍA
    |--------------------------------------------------------------------------
    */

    function updateEmptyGallery() {

        if (!emptyGallery) {
            return;
        }

        const existingImages =
            gallery
                ? gallery.querySelectorAll(
                    '.category-page-image'
                ).length
                : 0;

        const hasImages =
            existingImages > 0 ||
            pendingImages.length > 0;

        emptyGallery.hidden =
            hasImages;
    }


    /*
    |--------------------------------------------------------------------------
    | GUARDAR TODO
    |--------------------------------------------------------------------------
    */

    saveButton?.addEventListener(
        'click',
        async () => {

            const title =
                titleInput
                    ? titleInput.value.trim()
                    : '';

            const description =
                descriptionInput
                    ? descriptionInput.value.trim()
                    : '';


            /*
            |--------------------------------------------------------------------------
            | VALIDAR TÍTULO
            |--------------------------------------------------------------------------
            */

            if (!title) {

                showMessage(
                    'Debes escribir un título.',
                    'error'
                );

                titleInput?.focus();

                return;
            }


            if (
                title.length >
                180
            ) {

                showMessage(
                    'El título no puede superar 180 caracteres.',
                    'error'
                );

                titleInput?.focus();

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | VALIDAR DESCRIPCIÓN
            |--------------------------------------------------------------------------
            */

            if (
                description.length >
                5000
            ) {

                showMessage(
                    'La descripción no puede superar 5000 caracteres.',
                    'error'
                );

                descriptionInput?.focus();

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | ESTADO BOTÓN
            |--------------------------------------------------------------------------
            */

            const originalHtml =
                saveButton.innerHTML;

            saveButton.disabled =
                true;

            saveButton.innerHTML = `
                <i class="fa-solid fa-spinner fa-spin"></i>
                Guardando...
            `;


            try {

                /*
                |==============================================================
                | 1. GUARDAR TÍTULO Y DESCRIPCIÓN
                |==============================================================
                */

                const pageFormData =
                    new FormData();

                pageFormData.append(
                    'category_id',
                    String(categoryId)
                );

                pageFormData.append(
                    'title',
                    title
                );

                pageFormData.append(
                    'description',
                    description
                );


                const pageResponse =
                    await fetch(
                        `${window.APP_URL}/api/admin/categories/page/save`,
                        {
                            method: 'POST',
                            credentials: 'same-origin',
                            body: pageFormData
                        }
                    );


                const pageResult =
                    await pageResponse.json();


                if (
                    !pageResponse.ok ||
                    !pageResult.success
                ) {

                    throw new Error(
                        pageResult.message ||
                        'No fue posible guardar la información de la página.'
                    );
                }


                pageId =
                    Number(
                        pageResult.page_id || 0
                    );


                page.dataset.pageId =
                    String(pageId);


                if (!pageId) {

                    throw new Error(
                        'No se recibió el identificador de la página.'
                    );
                }


                /*
                |==============================================================
                | 2. CAPTURAR IDs EXISTENTES
                |==============================================================
                */

                const existingIds =
                    new Set(
                        Array.from(
                            gallery?.querySelectorAll(
                                '.category-page-image:not(.is-new-image)'
                            ) || []
                        )
                            .map(
                                element =>
                                    Number(
                                        element.dataset.imageId
                                    )
                            )
                            .filter(
                                id =>
                                    id > 0
                            )
                    );


                /*
                |==============================================================
                | 3. SUBIR IMÁGENES NUEVAS
                |==============================================================
                */

                let uploadedImages = null;

                const uploadedNewIds =
                    new Map();


                if (
                    pendingImages.length >
                    0
                ) {

                    for (
                        const pendingImage
                        of pendingImages
                        ) {

                        const imageFormData =
                            new FormData();


                        imageFormData.append(
                            'page_id',
                            String(pageId)
                        );


                        imageFormData.append(
                            'images[]',
                            pendingImage.file
                        );


                        const imageResponse =
                            await fetch(
                                `${window.APP_URL}/api/admin/categories/page/images`,
                                {
                                    method: 'POST',
                                    credentials: 'same-origin',
                                    body: imageFormData
                                }
                            );


                        const imageResponseText =
                            await imageResponse.text();


                        let imageResult;

                        try {

                            imageResult =
                                JSON.parse(
                                    imageResponseText
                                );

                        } catch (jsonError) {

                            console.error(
                                '[CATEGORY PAGE IMAGES] Respuesta no válida del servidor:',
                                imageResponseText
                            );


                            throw new Error(
                                'El servidor devolvió un error PHP al subir la imagen. Revisa la consola para ver el mensaje exacto.'
                            );
                        }


                        if (
                            !imageResponse.ok ||
                            imageResult.status !==
                            'success'
                        ) {

                            throw new Error(
                                imageResult.message ||
                                `No fue posible subir "${pendingImage.name}".`
                            );
                        }


                        const serverImages =
                            Array.isArray(
                                imageResult.data?.images
                            )
                                ? imageResult.data.images
                                : [];


                        uploadedImages =
                            serverImages;


                        const newImage =
                            serverImages.find(
                                image =>
                                    !existingIds.has(
                                        Number(image.id)
                                    ) &&
                                    !Array.from(
                                        uploadedNewIds.values()
                                    ).includes(
                                        Number(image.id)
                                    )
                            );


                        if (newImage) {

                            uploadedNewIds.set(
                                pendingImage.key,
                                Number(
                                    newImage.id
                                )
                            );
                        }
                    }


                    /*
                    |------------------------------------------------------------------
                    | LIMPIAR URLS TEMPORALES
                    |------------------------------------------------------------------
                    */

                    pendingImages.forEach(
                        image => {

                            if (image.url) {

                                URL.revokeObjectURL(
                                    image.url
                                );
                            }
                        }
                    );


                    pendingImages = [];


                    /*
                    |------------------------------------------------------------------
                    | RENDERIZAR IMÁGENES REALES
                    |------------------------------------------------------------------
                    */

                    if (
                        uploadedImages
                    ) {

                        renderImages(
                            uploadedImages
                        );
                    }
                }


                /*
                |==============================================================
                | 4. ELIMINAR IMÁGENES MARCADAS
                |==============================================================
                */

                if (
                    pendingDeletedImages.length >
                    0
                ) {

                    for (
                        const imageId
                        of pendingDeletedImages
                        ) {

                        const deleteFormData =
                            new FormData();


                        deleteFormData.append(
                            'page_id',
                            String(pageId)
                        );


                        deleteFormData.append(
                            'image_id',
                            String(imageId)
                        );


                        const deleteResponse =
                            await fetch(
                                `${window.APP_URL}/api/admin/categories/page/images/delete`,
                                {
                                    method: 'POST',
                                    credentials: 'same-origin',
                                    body: deleteFormData
                                }
                            );


                        const deleteResult =
                            await deleteResponse.json();


                        if (
                            !deleteResponse.ok ||
                            deleteResult.status !==
                            'success'
                        ) {

                            throw new Error(
                                deleteResult.message ||
                                'No fue posible eliminar una de las imágenes.'
                            );
                        }


                        if (
                            Array.isArray(
                                deleteResult.data?.images
                            )
                        ) {

                            renderImages(
                                deleteResult.data.images
                            );
                        }
                    }


                    pendingDeletedImages = [];
                }


                /*
                |==============================================================
                | 5. GUARDAR IMAGEN PRINCIPAL
                |==============================================================
                */

                let favoriteImageId =
                    pendingFavoriteImageId;


                if (
                    pendingFavoriteKey
                ) {

                    favoriteImageId =
                        uploadedNewIds.get(
                            pendingFavoriteKey
                        ) || null;
                }


                if (
                    favoriteImageId &&
                    Number(favoriteImageId) > 0
                ) {

                    const favoriteFormData =
                        new FormData();


                    favoriteFormData.append(
                        'page_id',
                        String(pageId)
                    );


                    favoriteFormData.append(
                        'image_id',
                        String(
                            favoriteImageId
                        )
                    );


                    const favoriteResponse =
                        await fetch(
                            `${window.APP_URL}/api/admin/categories/page/images/favorite`,
                            {
                                method: 'POST',
                                credentials: 'same-origin',
                                body: favoriteFormData
                            }
                        );


                    const favoriteResult =
                        await favoriteResponse.json();


                    if (
                        !favoriteResponse.ok ||
                        favoriteResult.status !==
                        'success'
                    ) {

                        throw new Error(
                            favoriteResult.message ||
                            'No fue posible guardar la imagen principal.'
                        );
                    }


                    if (
                        Array.isArray(
                            favoriteResult.data?.images
                        )
                    ) {

                        renderImages(
                            favoriteResult.data.images
                        );
                    }
                }


                /*
                |------------------------------------------------------------------
                | LIMPIAR FAVORITA
                |------------------------------------------------------------------
                */

                pendingFavoriteImageId =
                    null;

                pendingFavoriteKey =
                    null;


                /*
                |==============================================================
                | 6. GUARDAR ORDEN
                |==============================================================
                */

                await saveOrder();


                /*
                |==============================================================
                | 7. MENSAJE FINAL
                |==============================================================
                */

                showMessage(
                    'Todos los cambios se guardaron correctamente.',
                    'success'
                );

                /*
                |--------------------------------------------------------------------------
                | VOLVER AL LISTADO DE CATEGORÍAS
                |--------------------------------------------------------------------------
                */

                setTimeout(
                    () => {

                        window.location.href =
                            `${window.APP_URL}/admin/categories`;

                    },
                    800
                );

            } catch (error) {

                console.error(
                    '[CATEGORY PAGE SAVE]',
                    error
                );


                showMessage(
                    error.message ||
                    'No fue posible guardar los cambios.',
                    'error'
                );


            } finally {

                saveButton.disabled =
                    false;


                saveButton.innerHTML =
                    originalHtml;


                /*
                |--------------------------------------------------------------------------
                | INICIALIZAR IMÁGENES DESDE PHP
                |--------------------------------------------------------------------------
                */

                if (
                    window.CATEGORY_PAGE_DATA &&
                    Array.isArray(
                        window.CATEGORY_PAGE_DATA.images
                    )
                ) {

                    renderImages(
                        window.CATEGORY_PAGE_DATA.images
                    );

                } else {

                    updateImageCount();

                    setupDragAndDrop();
                }
            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | ABRIR SELECTOR DE ARCHIVOS
    |--------------------------------------------------------------------------
    */

    uploadButton?.addEventListener(
        'click',
        () => {

            fileInput?.click();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | SELECCIONAR IMÁGENES
    |--------------------------------------------------------------------------
    */

    fileInput?.addEventListener(
        'change',
        () => {

            const files =
                Array.from(
                    fileInput.files || []
                );


            if (!files.length) {
                return;
            }


            const validFiles = [];


            for (
                const file
                of files
                ) {

                if (
                    ![
                        'image/jpeg',
                        'image/png',
                        'image/webp'
                    ].includes(
                        file.type
                    )
                ) {

                    showMessage(
                        `El archivo "${file.name}" no es una imagen JPG, PNG o WEBP.`,
                        'error'
                    );

                    continue;
                }


                if (
                    file.size >
                    8 * 1024 * 1024
                ) {

                    showMessage(
                        `La imagen "${file.name}" supera el límite de 8 MB.`,
                        'error'
                    );

                    continue;
                }


                validFiles.push(
                    file
                );
            }


            if (!validFiles.length) {

                fileInput.value =
                    '';

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | CONTAR IMÁGENES
            |--------------------------------------------------------------------------
            */

            const existingCount =
                gallery
                    ? gallery.querySelectorAll(
                        '.category-page-image:not(.is-new-image)'
                    ).length
                    : 0;


            const total =
                existingCount +
                pendingImages.length +
                validFiles.length;


            if (
                total > 20
            ) {

                showMessage(
                    'Puedes tener máximo 20 imágenes por categoría.',
                    'error'
                );

                fileInput.value =
                    '';

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | PREPARAR IMÁGENES
            |--------------------------------------------------------------------------
            */

            validFiles.forEach(
                (
                    file,
                    index
                ) => {

                    const objectUrl =
                        URL.createObjectURL(
                            file
                        );


                    pendingImages.push({

                        key:
                            `new-${Date.now()}-${index}-${Math.random().toString(36).slice(2)}`,

                        file,

                        url:
                        objectUrl,

                        name:
                        file.name,

                        isNew:
                            true
                    });

                }
            );


            /*
            |--------------------------------------------------------------------------
            | RENDERIZAR IMÁGENES NUEVAS
            |--------------------------------------------------------------------------
            |
            | Primero agregamos las imágenes a la galería.
            | Luego seleccionamos la primera como imagen principal.
            |
            */

            if (
                pendingImages.length > 0
            ) {

                renderPendingImages();
            }


            fileInput.value =
                '';

        }
    );


    /*
    |--------------------------------------------------------------------------
    | RENDER IMÁGENES PENDIENTES
    |--------------------------------------------------------------------------
    */

    function renderPendingImages() {

        if (!gallery) {
            return;
        }


        pendingImages.forEach(
            (
                image,
                index
            ) => {

                if (
                    gallery.querySelector(
                        `[data-pending-key="${image.key}"]`
                    )
                ) {

                    return;
                }


                const article =
                    document.createElement(
                        'article'
                    );


                article.className =
                    'category-page-image is-new-image';


                article.dataset.pendingKey =
                    image.key;


                article.draggable =
                    true;


                article.innerHTML = `

                    <div class="category-page-image-thumb">

                        <button
                            type="button"
                            class="category-page-image-view"
                            title="Ver imagen"
                        >

                            <img
                                src="${image.url}"
                                alt="${escapeHtml(image.name)}"
                            >

                        </button>


                        <span
                            class="category-page-image-primary-label new-label"
                        >

                            <i class="fa-solid fa-plus"></i>

                            Nueva

                        </span>
                        
                        
                        <div
                            class="category-page-mobile-preview"
                            ${image.mobile_image_path ? '' : 'hidden'}
                        >
                            <span class="category-page-mobile-preview-label">
                                <i class="fa-solid fa-mobile-screen-button"></i>
                                MÓVIL
                            </span>
                        
                            <img
                                class="category-page-mobile-preview-image"
                                src="${image.mobile_image_path
                                            ? `${window.APP_URL}/${image.mobile_image_path.replace(/^\/+/, '')}`
                                            : ''}"
                                alt="Vista previa de imagen móvil"
                                loading="lazy"
                            >
                        </div>



                        <div class="category-page-image-overlay">

                            <span class="category-page-image-order">

                                <i class="fa-solid fa-grip-vertical"></i>

                                ${index + 1}

                            </span>


                            <div class="category-page-image-actions">
                                
                                <button
                                    type="button"
                                    class="category-page-image-action category-page-mobile-btn"
                                    data-image-id="${image.id}"
                                    title="${image.mobile_image_path
                    ? 'Cambiar imagen móvil'
                    : 'Agregar imagen móvil'}"
                                >
                                    <i class="fa-solid fa-mobile-screen-button"></i>
                                </button>


                                <button
                                    type="button"
                                    class="category-page-image-action category-page-pending-delete-btn"
                                    data-pending-key="${image.key}"
                                    title="Quitar imagen"
                                >

                                    <i class="fa-solid fa-trash"></i>

                                </button>

                            </div>

                        </div>

                    </div>

                `;


                gallery.appendChild(
                    article
                );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Seleccionar primera imagen pendiente
        |--------------------------------------------------------------------------
        */

        if (
            !activePendingKey &&
            pendingImages.length > 0
        ) {

            activePendingKey =
                pendingImages[0].key;
        }


        /*
        |--------------------------------------------------------------------------
        | Mostrar imagen pendiente solamente si existe
        |--------------------------------------------------------------------------
        */

        if (
            pendingImages.length > 0
        ) {

            renderPendingMainImage();

        } else {

            /*
             * IMPORTANTE:
             *
             * No tocar la imagen principal existente
             * cuando no existen imágenes pendientes.
             *
             * renderImages() ya se encargó de mostrar
             * la imagen guardada en BD.
             */
        }


        /*
        |--------------------------------------------------------------------------
        | Actualizar contador
        |--------------------------------------------------------------------------
        */

        updateImageCount();

        updateEmptyGallery();
    }


    /*
    |--------------------------------------------------------------------------
    | EVENTOS DE GALERÍA
    |--------------------------------------------------------------------------
    */


    gallery?.addEventListener(
        'click',
        async event => {

            /*
            |--------------------------------------------------------------------------
            | IMAGEN NUEVA - VER
            |--------------------------------------------------------------------------
            */

            const pendingView =
                event.target.closest(
                    '.is-new-image .category-page-image-view'
                );


            if (pendingView) {

                const pendingArticle =
                    pendingView.closest(
                        '.category-page-image'
                    );


                if (
                    pendingArticle
                ) {

                    activePendingKey =
                        pendingArticle.dataset.pendingKey;


                    activeImageId =
                        0;


                    renderPendingMainImage();


                    gallery
                        .querySelectorAll(
                            '.category-page-image'
                        )
                        .forEach(
                            article => {

                                article.classList.remove(
                                    'is-active'
                                );

                            }
                        );


                    pendingArticle.classList.add(
                        'is-active'
                    );
                }


                return;
            }


            const mobileButton = event.target.closest(
                '.category-page-mobile-btn'
            );

            if (mobileButton) {
                selectedMobileImageId = Number(
                    mobileButton.dataset.imageId || 0
                );

                if (selectedMobileImageId > 0) {
                    mobileImageInput.value = '';
                    mobileImageInput.click();
                }

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | IMAGEN NUEVA - PRINCIPAL
            |--------------------------------------------------------------------------
            */

            const pendingFavorite =
                event.target.closest(
                    '.category-page-pending-favorite-btn'
                );


            if (pendingFavorite) {

                const key =
                    pendingFavorite.dataset.pendingKey;


                setPendingFavorite(
                    key
                );


                return;
            }


            /*
            |--------------------------------------------------------------------------
            | IMAGEN NUEVA - ELIMINAR
            |--------------------------------------------------------------------------
            */

            const pendingDelete =
                event.target.closest(
                    '.category-page-pending-delete-btn'
                );


            if (pendingDelete) {

                const key =
                    pendingDelete.dataset.pendingKey;


                await deletePendingImage(
                    key
                );


                return;
            }


            /*
            |--------------------------------------------------------------------------
            | IMAGEN EXISTENTE - VER
            |--------------------------------------------------------------------------
            */

            const viewButton =
                event.target.closest(
                    '.category-page-image-view'
                );


            if (
                viewButton &&
                !viewButton.classList.contains(
                    'category-page-pending-view'
                )
            ) {

                const article =
                    viewButton.closest(
                        '.category-page-image'
                    );


                const imageId =
                    Number(
                        article?.dataset.imageId || 0
                    );


                if (imageId) {

                    activeImageId =
                        imageId;


                    activePendingKey =
                        null;


                    renderGalleryActiveState();
                }


                return;
            }


            /*
            |--------------------------------------------------------------------------
            | IMAGEN EXISTENTE - PRINCIPAL
            |--------------------------------------------------------------------------
            */

            const favoriteButton =
                event.target.closest(
                    '.category-page-favorite-btn'
                );


            if (favoriteButton) {

                const imageId =
                    Number(
                        favoriteButton.dataset.imageId || 0
                    );


                if (imageId > 0) {

                    setFavorite(
                        imageId
                    );
                }


                return;
            }


            /*
            |--------------------------------------------------------------------------
            | IMAGEN EXISTENTE - ELIMINAR
            |--------------------------------------------------------------------------
            */

            const deleteButton =
                event.target.closest(
                    '.category-page-delete-btn'
                );


            if (deleteButton) {

                const imageId =
                    Number(
                        deleteButton.dataset.imageId || 0
                    );


                if (imageId > 0) {

                    await deleteImage(
                        imageId
                    );
                }
            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | FAVORITA PENDIENTE
    |--------------------------------------------------------------------------
    */

    function setPendingFavorite(
        key
    ) {

        pendingFavoriteKey =
            key;


        pendingFavoriteImageId =
            null;


        gallery
            ?.querySelectorAll(
                '.category-page-image:not(.is-new-image)'
            )
            .forEach(
                element => {

                    element.classList.remove(
                        'is-favorite'
                    );


                    const label =
                        element.querySelector(
                            '.category-page-image-primary-label'
                        );


                    label?.remove();


                    const icon =
                        element.querySelector(
                            '.category-page-favorite-btn i'
                        );


                    if (icon) {

                        icon.className =
                            'fa-regular fa-star';
                    }

                }
            );


        updatePendingFavoriteVisualState();


        activePendingKey =
            key;


        activeImageId =
            0;


        renderPendingMainImage();
    }


    /*
    |--------------------------------------------------------------------------
    | FAVORITA EXISTENTE
    |--------------------------------------------------------------------------
    */

    function setFavorite(
        imageId
    ) {

        pendingFavoriteImageId =
            Number(imageId);


        pendingFavoriteKey =
            null;


        gallery
            ?.querySelectorAll(
                '.category-page-image'
            )
            .forEach(
                element => {

                    const currentId =
                        Number(
                            element.dataset.imageId || 0
                        );


                    const isFavorite =
                        currentId ===
                        Number(imageId);


                    element.classList.toggle(
                        'is-favorite',
                        isFavorite
                    );


                    const label =
                        element.querySelector(
                            '.category-page-image-primary-label'
                        );


                    if (
                        isFavorite &&
                        !label
                    ) {

                        const newLabel =
                            document.createElement(
                                'span'
                            );


                        newLabel.className =
                            'category-page-image-primary-label';


                        newLabel.innerHTML = `
                            <i class="fa-solid fa-star"></i>
                            Principal
                        `;


                        const thumb =
                            element.querySelector(
                                '.category-page-image-thumb'
                            );


                        thumb?.prepend(
                            newLabel
                        );


                    } else if (
                        !isFavorite &&
                        label
                    ) {

                        label.remove();
                    }


                    const icon =
                        element.querySelector(
                            '.category-page-favorite-btn i'
                        );


                    if (icon) {

                        icon.className =
                            isFavorite
                                ? 'fa-solid fa-star'
                                : 'fa-regular fa-star';
                    }

                }
            );


        activeImageId =
            Number(imageId);


        activePendingKey =
            null;


        renderGalleryActiveState();
    }


    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR ESTADO VISUAL DE FAVORITAS NUEVAS
    |--------------------------------------------------------------------------
    */

    function updatePendingFavoriteVisualState() {

        gallery
            ?.querySelectorAll(
                '.is-new-image'
            )
            .forEach(
                element => {

                    const key =
                        element.dataset.pendingKey;


                    const isFavorite =
                        key ===
                        pendingFavoriteKey;


                    element.classList.toggle(
                        'is-favorite',
                        isFavorite
                    );


                    const label =
                        element.querySelector(
                            '.category-page-image-primary-label'
                        );


                    if (
                        isFavorite &&
                        label
                    ) {

                        label.classList.remove(
                            'new-label'
                        );


                        label.innerHTML = `
                            <i class="fa-solid fa-star"></i>
                            Principal
                        `;


                    } else if (
                        !isFavorite &&
                        label
                    ) {

                        label.classList.add(
                            'new-label'
                        );


                        label.innerHTML = `
                            <i class="fa-solid fa-plus"></i>
                            Nueva
                        `;
                    }


                    const icon =
                        element.querySelector(
                            '.category-page-pending-favorite-btn i'
                        );


                    if (icon) {

                        icon.className =
                            isFavorite
                                ? 'fa-solid fa-star'
                                : 'fa-regular fa-star';
                    }

                }
            );
    }


    /*
    |--------------------------------------------------------------------------
    | ELIMINAR IMAGEN NUEVA
    |--------------------------------------------------------------------------
    */

    async function deletePendingImage(
        key
    ) {

        const confirmed =
            await Swal.fire({

                icon:
                    'warning',

                title:
                    '¿Quitar imagen?',

                text:
                    'La imagen todavía no se ha guardado.',

                showCancelButton:
                    true,

                confirmButtonText:
                    'Sí, quitar',

                cancelButtonText:
                    'Cancelar',

                reverseButtons:
                    true
            });


        if (
            !confirmed.isConfirmed
        ) {

            return;
        }


        const image =
            pendingImages.find(
                item =>
                    item.key ===
                    key
            );


        if (image?.url) {

            URL.revokeObjectURL(
                image.url
            );
        }


        pendingImages =
            pendingImages.filter(
                item =>
                    item.key !==
                    key
            );


        gallery
            ?.querySelector(
                `[data-pending-key="${key}"]`
            )
            ?.remove();


        if (
            pendingFavoriteKey ===
            key
        ) {

            pendingFavoriteKey =
                null;
        }


        if (
            activePendingKey ===
            key
        ) {

            activePendingKey =
                null;


            renderGalleryActiveState();
        }


        updateImageCount();

        updateEmptyGallery();
    }


    /*
    |--------------------------------------------------------------------------
    | ELIMINAR IMAGEN EXISTENTE
    |--------------------------------------------------------------------------
    */

    async function deleteImage(
        imageId
    ) {

        const confirmed =
            await Swal.fire({

                icon:
                    'warning',

                title:
                    '¿Eliminar imagen?',

                text:
                    'La imagen se eliminará cuando guardes los cambios.',

                showCancelButton:
                    true,

                confirmButtonText:
                    'Sí, eliminar',

                cancelButtonText:
                    'Cancelar',

                reverseButtons:
                    true
            });


        if (
            !confirmed.isConfirmed
        ) {

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | AGREGAR A PENDIENTES
        |--------------------------------------------------------------------------
        */

        if (
            !pendingDeletedImages.includes(
                Number(imageId)
            )
        ) {

            pendingDeletedImages.push(
                Number(imageId)
            );
        }


        /*
        |--------------------------------------------------------------------------
        | SI ERA FAVORITA
        |--------------------------------------------------------------------------
        */

        if (
            pendingFavoriteImageId ===
            Number(imageId)
        ) {

            pendingFavoriteImageId =
                null;
        }


        /*
        |--------------------------------------------------------------------------
        | SI ERA LA ACTIVA
        |--------------------------------------------------------------------------
        */

        if (
            activeImageId ===
            Number(imageId)
        ) {

            activeImageId =
                0;
        }


        /*
        |--------------------------------------------------------------------------
        | QUITAR VISUALMENTE
        |--------------------------------------------------------------------------
        */

        gallery
            ?.querySelector(
                `.category-page-image[data-image-id="${imageId}"]`
            )
            ?.remove();


        /*
        |--------------------------------------------------------------------------
        | SELECCIONAR OTRA IMAGEN COMO ACTIVA
        |--------------------------------------------------------------------------
        */

        const firstExisting =
            gallery
                ?.querySelector(
                    '.category-page-image:not(.is-new-image)'
                );


        if (firstExisting) {

            activeImageId =
                Number(
                    firstExisting.dataset.imageId
                );


            renderGalleryActiveState();


        } else if (
            pendingImages.length
        ) {

            activePendingKey =
                pendingImages[0].key;


            activeImageId =
                0;


            renderPendingMainImage();


        } else {

            renderEmptyMainImage();
        }


        updateImageCount();

        updateEmptyGallery();
    }


    /*
    |--------------------------------------------------------------------------
    | RENDER IMÁGENES DESDE SERVIDOR
    |--------------------------------------------------------------------------
    */

    function renderImages(
        images
    ) {

        if (
            !Array.isArray(images)
        ) {

            images = [];
        }


        if (!gallery) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | FAVORITA
        |--------------------------------------------------------------------------
        */

        const favorite =
            images.find(
                image =>
                    Number(
                        image.is_favorite
                    ) === 1
            ) || images[0] || null;


        /*
        |--------------------------------------------------------------------------
        | IMAGEN ACTIVA
        |--------------------------------------------------------------------------
        */

        if (
            !activeImageId ||
            !images.some(
                image =>
                    Number(image.id) ===
                    Number(activeImageId)
            )
        ) {

            activeImageId =
                favorite
                    ? Number(favorite.id)
                    : images.length
                        ? Number(images[0].id)
                        : 0;
        }


        activePendingKey =
            null;


        /*
        |--------------------------------------------------------------------------
        | CONTADOR
        |--------------------------------------------------------------------------
        */

        if (imageCount) {

            imageCount.textContent =
                `${images.length} ${
                    images.length === 1
                        ? 'imagen'
                        : 'imágenes'
                }`;
        }


        /*
        |--------------------------------------------------------------------------
        | IMAGEN PRINCIPAL
        |--------------------------------------------------------------------------
        */

        renderMainImage(
            images
        );


        /*
        |--------------------------------------------------------------------------
        | LIMPIAR IMÁGENES EXISTENTES
        |--------------------------------------------------------------------------
        */

        gallery
            .querySelectorAll(
                '.category-page-image:not(.is-new-image)'
            )
            .forEach(
                element =>
                    element.remove()
            );


        /*
        |--------------------------------------------------------------------------
        | INSERTAR IMÁGENES DEL SERVIDOR
        |--------------------------------------------------------------------------
        */

        images.forEach(
            (
                image,
                index
            ) => {

                const article =
                    document.createElement(
                        'article'
                    );


                article.className =
                    'category-page-image';


                article.draggable =
                    true;


                article.dataset.imageId =
                    String(image.id);


                const path =
                    String(
                        image.image_path ||
                        ''
                    ).replace(
                        /^\/+/,
                        ''
                    );



                const imageUrl =
                    `${window.APP_URL}/public/${path}`;

                const mobilePath = String(
                    image.mobile_image_path || ''
                ).replace(/^\/+/, '');

                const mobileImageUrl = mobilePath
                    ? `${window.APP_URL}/public/${mobilePath}`
                    : '';



                const isFavorite =
                    Number(
                        image.is_favorite
                    ) === 1 ||
                    (
                        !images.some(
                            item =>
                                Number(
                                    item.is_favorite
                                ) === 1
                        ) &&
                        Number(image.id) ===
                        Number(activeImageId)
                    );


                if (
                    Number(image.id) ===
                    Number(activeImageId)
                ) {

                    article.classList.add(
                        'is-active'
                    );
                }


                if (isFavorite) {

                    article.classList.add(
                        'is-favorite'
                    );
                }


                article.innerHTML = `

                    <div class="category-page-image-thumb">

                        
                    <button
                        type="button"
                        class="category-page-image-view"
                        title="Ver imagen"
                    >
                        <img
                            src="${imageUrl}"
                            alt="${escapeHtml(image.image_alt || '')}"
                        >
                    </button>
                    
                    ${mobileImageUrl ? `
                        <div class="category-page-mobile-preview">
                            <span class="category-page-mobile-preview-label">
                                <i class="fa-solid fa-mobile-screen-button"></i>
                                MÓVIL
                            </span>
                            <img
                                class="category-page-mobile-preview-image"
                                src="${mobileImageUrl}"
                                alt="Vista previa móvil"
                            >
                        </div>
                    ` : ''}



                        ${
                    isFavorite
                        ? `
                                    <span class="category-page-image-primary-label">

                                        <i class="fa-solid fa-star"></i>

                                        Principal

                                    </span>
                                `
                        : ''
                }


                        <div class="category-page-image-overlay">

                            <span class="category-page-image-order">

                                <i class="fa-solid fa-grip-vertical"></i>

                                ${index + 1}

                            </span>


                            <div class="category-page-image-actions">

                                <button
                                    type="button"
                                    class="category-page-image-action category-page-favorite-btn"
                                    data-image-id="${image.id}"
                                    title="Marcar como principal"
                                >

                                    <i class="fa-${
                    isFavorite
                        ? 'solid'
                        : 'regular'
                } fa-star"></i>

                                </button>
                              
                                <button
                                    type="button"
                                    class="category-page-image-action category-page-mobile-btn"
                                    data-image-id="${image.id}"
                                    title="${image.mobile_image_path
                    ? 'Cambiar imagen móvil'
                    : 'Agregar imagen móvil'}"
                                >
                                    <i class="fa-solid fa-mobile-screen-button"></i>
                                </button>

                                <button
                                    type="button"
                                    class="category-page-image-action category-page-delete-btn"
                                    data-image-id="${image.id}"
                                    title="Eliminar imagen"
                                >

                                    <i class="fa-solid fa-trash"></i>

                                </button>

                            </div>

                        </div>

                    </div>

                `;


                gallery.appendChild(
                    article
                );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | VOLVER A INSERTAR PENDIENTES
        |--------------------------------------------------------------------------
        |
        | IMPORTANTE:
        | Si no hay imágenes pendientes, NO debemos ejecutar
        | renderPendingImages(), porque esa función controla
        | únicamente imágenes nuevas y podría ocultar la imagen
        | que acabamos de mostrar desde la base de datos.
        |
        */

        if (
            pendingImages.length > 0
        ) {

            renderPendingImages();
        }


        /*
        |--------------------------------------------------------------------------
        | ACTUALIZAR ESTADO DE GALERÍA VACÍA
        |--------------------------------------------------------------------------
        */

        updateEmptyGallery();


        setupDragAndDrop();
    }


    /*
    |--------------------------------------------------------------------------
    | IMAGEN PRINCIPAL DESDE SERVIDOR
    |--------------------------------------------------------------------------
    */

    function renderMainImage(
        images
    ) {

        const mainView =
            document.getElementById(
                'categoryPageMainView'
            );


        const mainEmpty =
            document.getElementById(
                'categoryPageMainEmpty'
            );


        const mainImage =
            document.getElementById(
                'categoryPageMainImageElement'
            );


        const favoriteBadge =
            document.getElementById(
                'categoryPageMainFavorite'
            );


        if (
            !Array.isArray(images) ||
            !images.length
        ) {

            if (mainView) {

                mainView.hidden =
                    true;
            }


            if (mainEmpty) {

                mainEmpty.hidden =
                    false;
            }


            if (mainImage) {

                mainImage.hidden =
                    true;

                mainImage.removeAttribute(
                    'src'
                );
            }


            if (favoriteBadge) {

                favoriteBadge.hidden =
                    true;
            }


            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Buscar imagen activa
        |--------------------------------------------------------------------------
        */

        const active =
            images.find(
                image =>
                    Number(image.id) ===
                    Number(activeImageId)
            ) || images[0];


        activeImageId =
            Number(active.id);


        const path =
            String(
                active.image_path || ''
            ).replace(
                /^\/+/,
                ''
            );


        /*
        |--------------------------------------------------------------------------
        | Mostrar principal
        |--------------------------------------------------------------------------
        */

        if (mainEmpty) {

            mainEmpty.hidden =
                true;
        }


        if (mainView) {

            mainView.hidden =
                false;
        }


        if (mainImage) {

            mainImage.hidden =
                false;


            mainImage.src =
                `${window.APP_URL}/public/${path}`;


            mainImage.alt =
                active.image_alt ||
                'Imagen de la categoría';
        }


        /*
        |--------------------------------------------------------------------------
        | Mostrar indicador de principal
        |--------------------------------------------------------------------------
        */

        if (favoriteBadge) {

            const favorite =
                images.find(
                    image =>
                        Number(
                            image.is_favorite
                        ) === 1
                ) || images[0] || null;


            favoriteBadge.hidden =
                !favorite ||
                Number(active.id) !==
                Number(favorite.id);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | IMAGEN PRINCIPAL PENDIENTE
    |--------------------------------------------------------------------------
    */

    function renderPendingMainImage() {

        const mainView =
            document.getElementById(
                'categoryPageMainView'
            );


        const mainEmpty =
            document.getElementById(
                'categoryPageMainEmpty'
            );


        const mainImage =
            document.getElementById(
                'categoryPageMainImageElement'
            );


        const favoriteBadge =
            document.getElementById(
                'categoryPageMainFavorite'
            );


        /*
        |--------------------------------------------------------------------------
        | NO HAY IMÁGENES NUEVAS ACTIVAS
        |--------------------------------------------------------------------------
        */

        if (
            !activePendingKey
        ) {

            if (
                !pendingImages.length
            ) {

                if (mainView) {

                    mainView.hidden =
                        true;
                }


                if (mainEmpty) {

                    mainEmpty.hidden =
                        false;
                }


                if (mainImage) {

                    mainImage.hidden =
                        true;

                    mainImage.removeAttribute(
                        'src'
                    );
                }


                if (favoriteBadge) {

                    favoriteBadge.hidden =
                        true;
                }

                return;
            }


            activePendingKey =
                pendingImages[0].key;
        }


        /*
        |--------------------------------------------------------------------------
        | BUSCAR IMAGEN PENDIENTE
        |--------------------------------------------------------------------------
        */

        const pendingImage =
            pendingImages.find(
                image =>
                    image.key ===
                    activePendingKey
            );


        if (!pendingImage) {

            if (mainView) {

                mainView.hidden =
                    true;
            }


            if (mainEmpty) {

                mainEmpty.hidden =
                    false;
            }

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | MOSTRAR CONTENEDOR PRINCIPAL
        |--------------------------------------------------------------------------
        */

        if (mainEmpty) {

            mainEmpty.hidden =
                true;
        }


        if (mainView) {

            mainView.hidden =
                false;
        }


        /*
        |--------------------------------------------------------------------------
        | MOSTRAR IMAGEN
        |--------------------------------------------------------------------------
        */

        if (mainImage) {

            mainImage.hidden =
                false;


            mainImage.src =
                pendingImage.url;


            mainImage.alt =
                pendingImage.name ||
                'Imagen de la categoría';
        }


        /*
        |--------------------------------------------------------------------------
        | INDICADOR PRINCIPAL
        |--------------------------------------------------------------------------
        */

        if (favoriteBadge) {

            favoriteBadge.hidden =
                true;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | IMAGEN PRINCIPAL VACÍA
    |--------------------------------------------------------------------------
    */

    function renderEmptyMainImage() {

        const mainView =
            document.getElementById(
                'categoryPageMainView'
            );


        const mainEmpty =
            document.getElementById(
                'categoryPageMainEmpty'
            );


        const favoriteBadge =
            document.getElementById(
                'categoryPageMainFavorite'
            );


        if (mainView) {

            mainView.hidden =
                true;
        }


        if (mainEmpty) {

            mainEmpty.hidden =
                false;
        }


        if (favoriteBadge) {

            favoriteBadge.hidden =
                true;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | ESTADO DE GALERÍA
    |--------------------------------------------------------------------------
    */

    function renderGalleryActiveState() {

        if (!gallery) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Estado activo de miniaturas
        |--------------------------------------------------------------------------
        */

        gallery
            .querySelectorAll(
                '.category-page-image:not(.is-new-image)'
            )
            .forEach(
                article => {

                    const imageId =
                        Number(
                            article.dataset.imageId || 0
                        );


                    article.classList.toggle(
                        'is-active',
                        imageId ===
                        Number(activeImageId)
                    );

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Buscar miniatura activa
        |--------------------------------------------------------------------------
        */

        const activeArticle =
            gallery.querySelector(
                `.category-page-image[data-image-id="${activeImageId}"]`
            );


        const image =
            activeArticle?.querySelector(
                'img'
            );


        const mainView =
            document.getElementById(
                'categoryPageMainView'
            );


        const mainEmpty =
            document.getElementById(
                'categoryPageMainEmpty'
            );


        /*
        |--------------------------------------------------------------------------
        | AQUÍ ESTÁ LA CORRECCIÓN
        |--------------------------------------------------------------------------
        */

        const mainImage =
            document.getElementById(
                'categoryPageMainImageElement'
            );


        const favoriteBadge =
            document.getElementById(
                'categoryPageMainFavorite'
            );


        if (
            image &&
            mainImage
        ) {

            mainImage.hidden =
                false;


            mainImage.src =
                image.src;


            mainImage.alt =
                image.alt;


            if (mainView) {

                mainView.hidden =
                    false;
            }


            if (mainEmpty) {

                mainEmpty.hidden =
                    true;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Principal
        |--------------------------------------------------------------------------
        */

        const favorite =
            activeArticle?.classList.contains(
                'is-favorite'
            );


        if (favoriteBadge) {

            favoriteBadge.hidden =
                !favorite;
        }


        activePendingKey =
            null;
    }


    /*
    |--------------------------------------------------------------------------
    | DRAG & DROP
    |--------------------------------------------------------------------------
    */

    function setupDragAndDrop() {

        if (!gallery) {
            return;
        }


        const elements =
            Array.from(
                gallery.querySelectorAll(
                    '.category-page-image'
                )
            );


        let dragged = null;


        elements.forEach(
            element => {

                element.addEventListener(
                    'dragstart',
                    () => {

                        dragged =
                            element;

                        element.classList.add(
                            'is-dragging'
                        );

                    }
                );


                element.addEventListener(
                    'dragend',
                    () => {

                        element.classList.remove(
                            'is-dragging'
                        );

                        dragged = null;


                        /*
                        |--------------------------------------------------------------------------
                        | GUARDAR EL ORDEN ELEGIDO POR EL USUARIO
                        |--------------------------------------------------------------------------
                        */

                        currentImageOrder =
                            Array.from(
                                gallery.querySelectorAll(
                                    '.category-page-image'
                                )
                            )
                                .map(
                                    element =>
                                        Number(
                                            element.dataset.imageId || 0
                                        )
                                )
                                .filter(
                                    id =>
                                        id > 0
                                );


                        console.log(
                            '[CATEGORY PAGE] Nuevo orden:',
                            currentImageOrder
                        );


                        updateImageOrderPreview();

                    }
                );


                element.addEventListener(
                    'dragover',
                    event => {

                        event.preventDefault();


                        if (
                            !dragged ||
                            dragged === element
                        ) {
                            return;
                        }


                        const rect =
                            element.getBoundingClientRect();


                        const middle =
                            rect.top +
                            rect.height / 2;


                        if (
                            event.clientY <
                            middle
                        ) {

                            gallery.insertBefore(
                                dragged,
                                element
                            );

                        } else {

                            gallery.insertBefore(
                                dragged,
                                element.nextSibling
                            );

                        }

                    }
                );

            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR NÚMEROS DEL ORDEN
    |--------------------------------------------------------------------------
    */

    function updateImageOrderPreview() {

        if (!gallery) {
            return;
        }


        Array.from(
            gallery.querySelectorAll(
                '.category-page-image'
            )
        ).forEach(
            (
                element,
                index
            ) => {

                const order =
                    element.querySelector(
                        '.category-page-image-order'
                    );


                if (!order) {
                    return;
                }


                const icon =
                    order.querySelector(
                        'i'
                    );


                order.innerHTML =
                    '';


                if (icon) {

                    order.appendChild(
                        icon
                    );
                }


                order.append(
                    ` ${index + 1}`
                );

            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GUARDAR ORDEN
    |--------------------------------------------------------------------------
    */

    async function saveOrder() {

        if (
            !pageId ||
            !gallery
        ) {

            return;
        }


        const order =
            currentImageOrder.length > 0
                ? currentImageOrder
                : Array.from(
                    gallery.querySelectorAll(
                        '.category-page-image'
                    )
                )
                    .map(
                        element =>
                            Number(
                                element.dataset.imageId || 0
                            )
                    )
                    .filter(
                        id =>
                            id > 0
                    );


        if (!order.length) {

            return;
        }


        const formData =
            new FormData();


        formData.append(
            'page_id',
            String(pageId)
        );


        order.forEach(
            id => {

                formData.append(
                    'order[]',
                    String(id)
                );

            }
        );


        const response =
            await fetch(
                `${window.APP_URL}/api/admin/categories/page/images/order`,
                {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                }
            );


        const result =
            await response.json();


        if (
            !response.ok ||
            (
                result.status &&
                result.status !==
                'success'
            )
        ) {

            throw new Error(
                result.message ||
                'No fue posible guardar el orden de las imágenes.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | ESCAPAR HTML
    |--------------------------------------------------------------------------
    */

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





    mobileImageInput.addEventListener('change', async () => {
        const file = mobileImageInput.files?.[0];

        if (!file || !selectedMobileImageId) {
            return;
        }

        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            showMessage(
                'Selecciona una imagen JPG, PNG o WEBP.',
                'error'
            );
            mobileImageInput.value = '';
            return;
        }

        if (file.size > 8 * 1024 * 1024) {
            showMessage(
                'La imagen no puede superar 8 MB.',
                'error'
            );
            mobileImageInput.value = '';
            return;
        }

        const imageId = Number(selectedMobileImageId);
        const formData = new FormData();

        formData.append('page_id', String(pageId));
        formData.append('image_id', String(imageId));
        formData.append('mobile_image', file);

        try {
            const response = await fetch(
                `${window.APP_URL}/api/admin/categories/page/images/mobile`,
                {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                }
            );

            const responseText = await response.text();
            let result;

            try {
                result = JSON.parse(responseText);
            } catch (error) {
                console.error(
                    '[CATEGORY MOBILE IMAGE] Respuesta inválida:',
                    responseText
                );

                throw new Error(
                    'El servidor devolvió una respuesta inválida al guardar la imagen móvil.'
                );
            }

            if (!response.ok || result.status !== 'success') {
                throw new Error(
                    result.message ||
                    'No fue posible guardar la imagen móvil.'
                );
            }

            const savedPath = String(
                result.data?.mobile_image_path || ''
            ).trim();

            if (!savedPath) {
                throw new Error(
                    'El servidor guardó la solicitud, pero no devolvió la ruta de la imagen móvil.'
                );
            }

            // Buscar la tarjeta de la imagen de escritorio.
            const article = gallery?.querySelector(
                `.category-page-image[data-image-id="${imageId}"]`
            );

            if (!article) {
                throw new Error(
                    'No se encontró la tarjeta de la imagen seleccionada.'
                );
            }

            // Actualizar la información local.
            const image = window.CATEGORY_PAGE_DATA?.images?.find(
                item => Number(item.id) === imageId
            );

            if (image) {
                image.mobile_image_path = savedPath;
            }

            // Actualizar el botón móvil.
            const button = article.querySelector(
                '.category-page-mobile-btn'
            );

            if (button) {
                button.title = 'Cambiar imagen móvil';
                button.classList.add('has-mobile-image');
            }

            // Crear la vista previa si todavía no existe.
            let preview = article.querySelector(
                '.category-page-mobile-preview'
            );

            if (!preview) {
                preview = document.createElement('div');
                preview.className = 'category-page-mobile-preview';

                preview.innerHTML = `
                    <span class="category-page-mobile-preview-label">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                        MÓVIL
                    </span>
                    <img
                        class="category-page-mobile-preview-image"
                        alt="Vista previa de imagen móvil"
                        loading="lazy"
                    >
                `;

                const thumb = article.querySelector(
                    '.category-page-image-thumb'
                );

                if (!thumb) {
                    throw new Error(
                        'No se encontró el contenedor de la imagen.'
                    );
                }

                thumb.appendChild(preview);
            }

            const previewImage = preview.querySelector(
                '.category-page-mobile-preview-image'
            );

            if (!previewImage) {
                throw new Error(
                    'No se encontró el elemento de vista previa móvil.'
                );
            }

            /*
             * Construir la URL de acuerdo con la ruta recibida.
             *
             * Si la ruta ya es absoluta, se conserva.
             * Si es relativa, se agrega APP_URL.
             * No se agrega /public/ automáticamente porque la ruta
             * guardada puede incluir ya ese segmento.
             */
            let mobileUrl;

            if (/^https?:\/\//i.test(savedPath)) {
                mobileUrl = savedPath;
            } else {
                const normalizedPath = savedPath.replace(/^\/+/, '');
                mobileUrl = `${window.APP_URL}/${normalizedPath}`;
            }

            // Mostrar la nueva imagen sin recargar la página.
            previewImage.onerror = () => {
                console.error(
                    '[CATEGORY MOBILE IMAGE] No se pudo cargar la vista previa:',
                    mobileUrl,
                    'Ruta recibida:',
                    savedPath
                );

                showMessage(
                    'La imagen se guardó, pero no se pudo mostrar. Revisa la ruta en la consola.',
                    'error'
                );
            };

            previewImage.onload = () => {
                preview.hidden = false;
            };

            previewImage.src = mobileUrl;
            preview.hidden = false;

            showMessage(
                'Imagen móvil guardada correctamente.',
                'success'
            );

        } catch (error) {
            console.error(
                '[CATEGORY MOBILE IMAGE]',
                error
            );

            showMessage(
                error.message ||
                'No fue posible guardar la imagen móvil.',
                'error'
            );

        } finally {
            mobileImageInput.value = '';
            selectedMobileImageId = 0;
        }
    });




    /*
    |--------------------------------------------------------------------------
    | INICIALIZACIÓN
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | CARGAR IMÁGENES EXISTENTES
    |--------------------------------------------------------------------------
    */

    if (
        window.CATEGORY_PAGE_DATA &&
        Array.isArray(
            window.CATEGORY_PAGE_DATA.images
        )
    ) {

        renderImages(
            window.CATEGORY_PAGE_DATA.images
        );

    } else {

        updateImageCount();

        updateEmptyGallery();

        setupDragAndDrop();
    }

});

