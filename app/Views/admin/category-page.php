<?php

/*
|--------------------------------------------------------------------------
| CATEGORY PAGE
|--------------------------------------------------------------------------
*/

$category =
        $categoryPageCategory
        ?? [];


$page =
        $categoryPageData
        ?? null;


$images =
        $categoryPageImages
        ?? [];


$categoryId =
        (int) (
                $category['id']
                ?? 0
        );


$title =
        $page['title']
        ?? $category['name']
        ?? '';


$description =
        $page['intro_text']
        ?? '';


$pageId =
        (int) (
                $page['id']
                ?? 0
        );


/*
|--------------------------------------------------------------------------
| IMÁGENES PARA JAVASCRIPT
|--------------------------------------------------------------------------
|
| Igual que item-form.php:
| normalizamos las imágenes antes de enviarlas a JS.
|
*/

$imagesForJs =
        array_map(
                static function (
                        array $image
                ) use ($pageId): array {

                    $path =
                            ltrim(
                                    (string) (
                                            $image['image_path']
                                            ?? $image['file_path']
                                            ?? ''
                                    ),
                                    '/\\'
                            );


                    return [

                            'id' =>
                                    (int) (
                                            $image['id']
                                            ?? 0
                                    ),


                            'category_page_id' =>
                                    (int) (
                                            $image['category_page_id']
                                            ?? $pageId
                                    ),


                            'image_path' =>
                                    $path,


                            'mobile_image_path' =>
                                    ltrim(
                                            (string) (
                                                    $image['mobile_image_path']
                                                    ?? ''
                                            ),
                                            '/\\'
                                    ),


                            'image_alt' =>
                                    (string) (
                                            $image['image_alt']
                                            ?? $image['original_name']
                                            ?? ''
                                    ),


                            'display_order' =>
                                    (int) (
                                            $image['display_order']
                                            ?? $image['sort_order']
                                            ?? 0
                                    ),


                            'is_favorite' =>
                                    (int) (
                                            $image['is_favorite']
                                            ?? 0
                                    ) === 1,


                            'url' =>
                                    APP_URL .
                                    '/public/' .
                                    $path

                    ];

                },
                $images
        );

?>

<div
        class="category-page-admin"
        id="categoryPageAdmin"
        data-category-id="<?= $categoryId ?>"
        data-page-id="<?= $pageId ?>"
>

    <?php

    /*
    |--------------------------------------------------------------------------
    | HEADER
    |--------------------------------------------------------------------------
    */

    $pageHeaderIcon =
            'fa-solid fa-layer-group';


    $pageHeaderEyebrow =
            'CATEGORÍA';


    $pageHeaderTitle =
            'Configurar página';


    $pageHeaderDescription =
            'Personaliza la presentación de esta categoría en la página pública.';


    $pageHeaderBadge =
            htmlspecialchars(
                    $category['name'] ?? '',
                    ENT_QUOTES,
                    'UTF-8'
            );


    require __DIR__ .
            '/components/page-header.php';

    ?>


    <!-- =========================================================
         VOLVER
    ========================================================== -->

    <div class="category-page-back">

        <a
                href="<?= APP_URL ?>/admin/categories"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Volver a categorías

        </a>

    </div>


    <!-- =========================================================
         IMÁGENES
    ========================================================== -->

    <section
            class="category-page-card category-page-images-card"
    >

        <div
                class="category-page-card-header"
        >

            <div
                    class="category-page-card-icon"
            >

                <i
                        class="fa-regular fa-images"
                ></i>

            </div>


            <div>

                <h2>
                    Imágenes de la categoría
                </h2>

                <p>
                    Carga varias imágenes, cambia su orden
                    y selecciona la imagen principal.
                </p>

            </div>


            <div
                    class="category-page-image-count"
                    id="categoryPageImageCount"
            >

                <?= count($images) ?>

                <?= count($images) === 1
                        ? 'imagen'
                        : 'imágenes'
                ?>

            </div>

        </div>


        <!-- =====================================================
             GESTOR DE IMÁGENES
        ====================================================== -->

        <div
                class="category-page-images-manager"
                id="categoryPageImagesManager"
        >


            <!-- =================================================
                 INPUT
            ================================================== -->

            <input
                    type="file"
                    id="categoryPageImages"
                    accept="image/jpeg,image/png,image/webp"
                    multiple
                    hidden
            >


            <!-- =================================================
                 DROPZONE
            ================================================== -->

            <label
                    for="categoryPageImages"
                    class="category-page-upload-area"
                    id="categoryPageUploadArea"
            >

                <span
                        class="category-page-upload-icon"
                >

                    <i
                            class="fa-solid fa-cloud-arrow-up"
                    ></i>

                </span>


                <span
                        class="category-page-upload-content"
                >

                    <strong>
                        Agrega imágenes de la categoría
                    </strong>


                    <span>
                        Arrastra archivos aquí o
                        haz clic para seleccionarlos.
                    </span>


                    <small>
                        JPG, PNG o WEBP · máximo 8 MB
                        por imagen · hasta 20 imágenes
                    </small>

                </span>


                <span
                        class="category-page-upload-action"
                >

                    Seleccionar imágenes

                </span>

            </label>


            <!-- =================================================
                 IMAGEN PRINCIPAL
            ================================================== -->

            <div
                    class="category-page-main-image is-empty"
                    id="categoryPageMainImage"
            >


                <!-- SIN IMAGEN -->

                <div
                        class="category-page-main-empty"
                        id="categoryPageMainEmpty"
                >

                    <div
                            class="category-page-main-empty-icon"
                    >

                        <i
                                class="fa-regular fa-image"
                        ></i>

                    </div>


                    <strong>
                        No hay imágenes seleccionadas
                    </strong>


                    <span>
                        La primera imagen se utilizará inicialmente
                        como imagen principal.
                    </span>

                </div>


                <!-- IMAGEN ACTIVA -->

                <div
                        class="category-page-main-view"
                        id="categoryPageMainView"
                        hidden
                >

                    <img
                            src=""
                            alt="Vista previa de la categoría"
                            id="categoryPageMainImageElement"
                    >


                    <div
                            class="category-page-main-overlay"
                    >

                        <span
                                class="category-page-main-favorite"
                                id="categoryPageMainFavorite"
                                hidden
                        >
                            <i class="fa-solid fa-star"></i>
                            Favorita / Principal
                        </span>


                        <span
                                class="category-page-main-new"
                                id="categoryPageMainNew"
                                hidden
                        >

                            Nueva

                        </span>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 GALERÍA
            ================================================== -->

            <div
                    class="category-page-gallery-header"
            >

                <div>

                    <strong>
                        Galería
                    </strong>


                    <span>
                        Haz clic para visualizar.
                        Arrastra para cambiar el orden.
                    </span>

                </div>


                <span
                        class="category-page-drag-help"
                >

                    <i
                            class="fa-solid fa-grip-vertical"
                    ></i>

                    Arrastra

                </span>

            </div>


            <!-- =================================================
                 MINIATURAS
            ================================================== -->

            <div
                    class="category-page-gallery"
                    id="categoryPageGallery"
            >

                <?php foreach (
                        $images
                        as $image
                ): ?>

                    <?php

                    $imagePath =
                            ltrim(
                                    (string) (
                                            $image['image_path']
                                            ?? ''
                                    ),
                                    '/\\'
                            );


                    $imageUrl =
                            APP_URL .
                            '/public/' .
                            htmlspecialchars(
                                    $imagePath,
                                    ENT_QUOTES,
                                    'UTF-8'
                            );


                    $isFavorite =
                            (int) (
                                    $image['is_favorite']
                                    ?? 0
                            ) === 1;

                    ?>


                    <article
                            class="category-page-image <?= $isFavorite
                                    ? 'is-favorite'
                                    : '' ?>"
                            draggable="true"
                            data-image-id="<?= (int) $image['id'] ?>"
                    >

                        <div
                                class="category-page-image-thumb"
                        >


                            <!-- IMAGEN -->

                            <button
                                    type="button"
                                    class="category-page-image-view"
                                    title="Ver imagen"
                            >

                                <img
                                        src="<?= $imageUrl ?>"
                                        alt="<?= htmlspecialchars(
                                                $image['image_alt']
                                                ?? '',
                                                ENT_QUOTES,
                                                'UTF-8'
                                        ) ?>"
                                >

                            </button>


                            <!-- PRINCIPAL -->

                            <?php if ($isFavorite): ?>

                                <span
                                        class="category-page-image-primary-label"
                                >

                                    <i
                                            class="fa-solid fa-star"
                                    ></i>

                                    Principal

                                </span>

                            <?php endif; ?>


                            <!-- OVERLAY -->

                            <div
                                    class="category-page-image-overlay"
                            >


                                <!-- ORDEN -->

                                <span
                                        class="category-page-image-order"
                                >

                                    <i
                                            class="fa-solid fa-grip-vertical"
                                    ></i>

                                </span>


                                <!-- ACCIONES -->

                                <div
                                        class="category-page-image-actions"
                                >

                                    <button
                                            type="button"
                                            class="category-page-image-action category-page-favorite-btn"
                                            data-image-id="<?= (int) $image['id'] ?>"
                                            title="Marcar como principal"
                                    >

                                        <i
                                                class="fa-<?= $isFavorite ? 'solid' : 'regular' ?> fa-star"
                                        ></i>

                                    </button>


                                    <button
                                            type="button"
                                            class="category-page-image-action category-page-delete-btn"
                                            data-image-id="<?= (int) $image['id'] ?>"
                                            title="Eliminar imagen"
                                    >

                                        <i
                                                class="fa-solid fa-trash"
                                        ></i>

                                    </button>

                                </div>

                            </div>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>


            <!-- =================================================
                 GALERÍA VACÍA
            ================================================== -->

            <div
                    class="category-page-empty-gallery"
                    id="categoryPageEmptyGallery"
                    <?= count($images) > 0
                            ? 'hidden'
                            : '' ?>
            >

                <i
                        class="fa-regular fa-images"
                ></i>


                <span>
                    Tus imágenes aparecerán aquí.
                </span>

            </div>


            <!-- =================================================
                 AYUDA
            ================================================== -->

            <p
                    class="category-page-image-help"
            >

                <i
                        class="fa-solid fa-circle-info"
                ></i>


                La imagen marcada con


                <i
                        class="fa-solid fa-star"
                ></i>


                será la imagen principal de la categoría.

            </p>

        </div>

    </section>


    <!-- =========================================================
         INFORMACIÓN
    ========================================================== -->

    <section
            class="category-page-card"
    >

        <div
                class="category-page-card-header"
        >

            <div
                    class="category-page-card-icon"
            >

                <i
                        class="fa-solid fa-pen"
                ></i>

            </div>


            <div>

                <h2>
                    Información de la página
                </h2>

                <p>
                    Esta información aparecerá en la página pública.
                </p>

            </div>

        </div>


        <div
                class="category-page-form-grid"
        >


            <!-- =================================================
                 CATEGORÍA
            ================================================== -->

            <div
                    class="category-page-field"
            >

                <label>
                    Categoría
                </label>


                <div
                        class="category-page-category-name"
                >

                    <i
                            class="fa-solid fa-folder-open"
                    ></i>


                    <?= htmlspecialchars(
                            $category['name']
                            ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                    ) ?>

                </div>

            </div>


            <!-- =================================================
                 TÍTULO
            ================================================== -->

            <div
                    class="category-page-field"
            >

                <label
                        for="categoryPageTitle"
                >

                    Título de la página

                    <span>
                        *
                    </span>

                </label>


                <input
                        type="text"
                        id="categoryPageTitle"
                        class="category-page-input"
                        maxlength="180"
                        value="<?= htmlspecialchars(
                                $title,
                                ENT_QUOTES,
                                'UTF-8'
                        ) ?>"
                        placeholder="Ej: Hamburguesas Burger Town"
                >

            </div>


            <!-- =================================================
                 DESCRIPCIÓN
            ================================================== -->

            <div
                    class="category-page-field category-page-field-full"
            >

                <label
                        for="categoryPageDescription"
                >

                    Descripción

                </label>


                <textarea
                        id="categoryPageDescription"
                        class="category-page-input"
                        rows="6"
                        maxlength="5000"
                        placeholder="Escribe una descripción atractiva para esta categoría..."
                ><?= htmlspecialchars(
                            $description,
                            ENT_QUOTES,
                            'UTF-8'
                    ) ?></textarea>


                <small>
                    Describe brevemente lo que encontrará
                    el cliente en esta sección.
                </small>

            </div>

        </div>

    </section>


    <!-- =========================================================
         GUARDAR
    ========================================================== -->

    <div
            class="category-page-footer"
    >

        <button
                type="button"
                class="category-page-save-btn"
                id="categoryPageSave"
        >

            <i
                    class="fa-solid fa-check"
            ></i>

            Guardar cambios

        </button>

    </div>


</div>


<!-- =========================================================
     CONFIGURACIÓN JAVASCRIPT
========================================================== -->

<script>

    window.CATEGORY_PAGE_DATA = <?= json_encode(
            [
                    'categoryId' =>
                            $categoryId,

                    'pageId' =>
                            $pageId,

                    'images' =>
                            $imagesForJs
            ],
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
    ) ?>;

</script>