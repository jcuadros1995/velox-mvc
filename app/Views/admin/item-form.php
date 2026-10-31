<?php

/*
|--------------------------------------------------------------------------
| ITEM FORM
|--------------------------------------------------------------------------
*/

$mode =
        $itemFormMode
        ?? 'create';


$isEdit =
        $mode === 'edit';


$item =
        $itemFormItem
        ?? null;


$categories =
        $itemFormCategories
        ?? [];


$images =
        $itemFormImages
        ?? (
        $item['images']
        ?? []
);


/*
|--------------------------------------------------------------------------
| IMÁGENES PARA JAVASCRIPT
|--------------------------------------------------------------------------
|
| Misma idea utilizada por branch-form.php.
|
*/

$imagesForJs =
        array_map(
                static function (
                        array $image
                ): array {

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

                            'item_id' =>
                                    (int) (
                                            $image['item_id']
                                            ?? 0
                                    ),

                            'image_path' =>
                                    $path,

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


/*
|--------------------------------------------------------------------------
| DATOS DEL PRODUCTO
|--------------------------------------------------------------------------
*/

$itemId =
        $item
                ? (int) (
                $item['id']
                ?? 0
        )
                : 0;


$itemName =
        $item['name']
        ?? '';


$itemSku =
        $item['sku']
        ?? '';


$itemCategoryId =
        $item['category_id']
        ?? '';


$itemDescription =
        $item['description']
        ?? '';


$itemIngredients =
        $item['ingredients']
        ?? '';


$itemPrice =
        $item['price']
        ?? '';


$itemComboPrice =
        $item['combo_price']
        ?? '';


$itemPortion =
        $item['portion_size']
        ?? '';


$itemSpicyLevel =
        (int) (
                $item['spicy_level']
                ?? 0
        );


$itemAllergens =
        $item['allergens']
        ?? '';


$itemPreparationNotes =
        $item['preparation_notes']
        ?? '';


$itemCostPrice =
        $item['cost_price']
        ?? '';


$itemTaxRate =
        $item['tax_rate']
        ?? '';


$itemDuration =
        $item['duration_minutes']
        ?? 0;


$itemDisplayOrder =
        $item['display_order']
        ?? 0;


$itemIsActive =
        !isset(
                $item['is_active']
        )
        ||
        (int) $item['is_active'] === 1;


$itemIsAvailable =
        !isset(
                $item['is_available']
        )
        ||
        (int) $item['is_available'] === 1;


$itemIsFeatured =
        isset(
                $item['is_featured']
        )
        &&
        (int) $item['is_featured'] === 1;

?>

<div
        class="admin-item-form-page"
        id="adminItemFormPage"
        data-mode="<?= htmlspecialchars(
                $mode,
                ENT_QUOTES,
                'UTF-8'
        ) ?>"
        data-item-id="<?= (int) $itemId ?>"
        data-category-id="<?= (int) $itemCategoryId ?>"
        data-category-name="<?= htmlspecialchars(
                $item['category_name'] ?? '',
                ENT_QUOTES,
                'UTF-8'
        ) ?>"
        data-spicy-level="<?= (int) $itemSpicyLevel ?>"
>

    <!-- =========================================================
         HEADER
    ========================================================= -->

    <?php

    $pageHeaderIcon =
            $isEdit
                    ? 'fa-solid fa-burger'
                    : 'fa-solid fa-burger';


    $pageHeaderEyebrow =
            'CONFIGURACIÓN DE PRODUCTO';


    $pageHeaderTitle =
            $isEdit
                    ? 'Editar producto'
                    : 'Nuevo producto';


    $pageHeaderDescription =
            $isEdit
                    ? 'Actualiza la información, precios e imágenes del producto.'
                    : 'Registra un nuevo producto y configura su información.';


    $pageHeaderBadge =
            $isEdit
                    ? 'Edición de producto'
                    : 'Nuevo producto';


    require __DIR__ .
            '/components/page-header.php';

    ?>


    <div class="mt-3">

        <a
                href="<?= APP_URL ?>/admin/items"
                class="item-back-link"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Productos

        </a>

    </div>


    <!-- =========================================================
         FORMULARIO
    ========================================================= -->

    <form
            id="itemForm"
            novalidate
    >

        <input
                type="hidden"
                id="itemId"
                value="<?= $itemId ?>"
        >


        <!-- =====================================================
             GALERÍA DE IMÁGENES
        ====================================================== -->

        <section
                class="item-form-card item-images-card"
        >

            <div
                    class="item-form-card-header item-images-card-header"
            >

                <div
                        class="item-form-card-icon item-images-card-icon"
                >

                    <i class="fa-regular fa-images"></i>

                </div>


                <div>

                    <h2>
                        Imágenes del producto
                    </h2>

                    <p>
                        Carga varias imágenes, cambia su orden
                        y selecciona la imagen principal.
                    </p>

                </div>


                <div
                        class="item-images-counter"
                        id="itemImagesCounter"
                >

                    <?= count($images) ?>
                    /
                    20 imágenes

                </div>

            </div>


            <div
                    class="item-images-manager"
                    id="itemImagesManager"
            >


                <!-- =================================================
                     INPUT
                ================================================== -->

                <input
                        type="file"
                        id="itemImagesInput"
                        accept="image/jpeg,image/png,image/webp"
                        multiple
                        hidden
                >


                <!-- =================================================
                     DROPZONE
                ================================================== -->

                <label
                        for="itemImagesInput"
                        class="item-images-dropzone"
                        id="itemImagesDropzone"
                >

                    <div
                            class="item-images-dropzone-icon"
                    >

                        <i
                                class="fa-solid fa-cloud-arrow-up"
                        ></i>

                    </div>


                    <div
                            class="item-images-dropzone-content"
                    >

                        <strong>
                            Agrega imágenes del producto
                        </strong>


                        <span>
                            Arrastra archivos aquí o
                            haz clic para seleccionarlos.
                        </span>


                        <small>
                            JPG, PNG o WEBP · máximo 8 MB
                            por imagen · hasta 20 imágenes
                        </small>

                    </div>


                    <span
                            class="item-images-select-button"
                    >
                        Seleccionar imágenes
                    </span>

                </label>


                <!-- =================================================
                     IMAGEN PRINCIPAL
                ================================================== -->

                <div
                        class="item-images-stage is-empty"
                        id="itemImagesStage"
                >


                    <!-- SIN IMAGEN -->

                    <div
                            class="item-images-main-empty"
                            id="itemImagesMainEmpty"
                    >

                        <div
                                class="item-images-main-empty-icon"
                        >

                            <i class="fa-regular fa-image"></i>

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
                            class="item-images-main-view"
                            id="itemImagesMainView"
                            hidden
                    >

                        <img
                                src=""
                                alt="Vista previa del producto"
                                id="itemImagesMainImage"
                        >


                        <div
                                class="item-images-main-overlay"
                        >

                            <span
                                    class="item-images-favorite-badge"
                                    id="itemImagesFavoriteBadge"
                            >

                                <i
                                        class="fa-solid fa-star"
                                ></i>

                                Favorita / Principal

                            </span>


                            <span
                                    class="item-images-new-badge"
                                    id="itemImagesNewBadge"
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
                        class="item-images-gallery-head"
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
                            class="item-images-gallery-hint"
                    >

                        <i
                                class="fa-solid fa-grip-vertical"
                        ></i>

                        Arrastra

                    </span>

                </div>


                <div
                        class="item-images-thumbs"
                        id="itemImagesThumbs"
                ></div>


                <div
                        class="item-images-empty-gallery"
                        id="itemImagesEmptyGallery"
                >

                    <i
                            class="fa-regular fa-images"
                    ></i>


                    <span>
                        Tus imágenes aparecerán aquí.
                    </span>

                </div>


                <p
                        class="item-images-help"
                >

                    <i
                            class="fa-solid fa-circle-info"
                    ></i>


                    La imagen marcada con


                    <i
                            class="fa-solid fa-star"
                    ></i>


                    será la imagen principal del producto.

                </p>

            </div>

        </section>


        <!-- =====================================================
             INFORMACIÓN GENERAL
        ====================================================== -->

        <section
                class="item-form-card"
        >

            <div
                    class="item-form-card-header"
            >

                <div
                        class="item-form-card-icon"
                >

                    <i
                            class="fa-solid fa-burger"
                    ></i>

                </div>


                <div>

                    <h2>
                        Información general
                    </h2>


                    <p>
                        Datos principales del producto.
                    </p>

                </div>

            </div>


            <div
                    class="item-form-grid"
            >


                <!-- NOMBRE -->

                <div
                        class="item-form-field"
                >

                    <label
                            for="itemName"
                    >

                        Nombre del producto

                    </label>


                    <input
                            type="text"
                            id="itemName"
                            maxlength="150"
                            value="<?= htmlspecialchars(
                                    $itemName,
                                    ENT_QUOTES,
                                    'UTF-8'
                            ) ?>"
                            placeholder="Ej. Burger Town Classic"
                            required
                    >


                    <small>
                        Nombre que verá el cliente.
                    </small>

                </div>


                <!-- SKU -->

                <div
                        class="item-form-field"
                >

                    <label
                            for="itemSku"
                    >

                        SKU

                    </label>


                    <input
                            type="text"
                            id="itemSku"
                            maxlength="50"
                            value="<?= htmlspecialchars(
                                    $itemSku,
                                    ENT_QUOTES,
                                    'UTF-8'
                            ) ?>"
                            placeholder="Se genera automáticamente"
                    >


                    <small>
                        Puedes dejarlo vacío al crear.
                    </small>

                </div>


                <!-- CATEGORÍA -->

                <div
                        class="item-form-field"
                >

                    <label
                            for="itemCategory"
                    >

                        Categoría

                    </label>


                    <select
                            id="itemCategory"
                            required
                    >

                        <option value="">
                            Selecciona una categoría
                        </option>


                        <?php

                        $parents =
                                array_filter(
                                        $categories,
                                        static function (
                                                array $category
                                        ): bool {

                                            return empty(
                                            $category['parent_id']
                                            );

                                        }
                                );


                        $children =
                                array_filter(
                                        $categories,
                                        static function (
                                                array $category
                                        ): bool {

                                            return !empty(
                                            $category['parent_id']
                                            );

                                        }
                                );

                        ?>


                        <?php foreach (
                                $parents
                                as $parent
                        ): ?>

                            <option
                                    value="<?= (int) $parent['id'] ?>"
                                    <?= (int) $itemCategoryId ===
                                    (int) $parent['id']
                                            ? 'selected'
                                            : ''
                                    ?>
                            >

                                <?= htmlspecialchars(
                                        $parent['name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                ) ?>

                            </option>


                            <?php foreach (
                                    $children
                                    as $child
                            ): ?>

                                <?php if (
                                        (int) (
                                                $child['parent_id']
                                                ?? 0
                                        )
                                        ===
                                        (int) $parent['id']
                                ): ?>

                                    <option
                                            value="<?= (int) $child['id'] ?>"
                                            <?= (int) $itemCategoryId ===
                                            (int) $child['id']
                                                    ? 'selected'
                                                    : ''
                                            ?>
                                    >

                                        &nbsp;&nbsp;↳

                                        <?= htmlspecialchars(
                                                $child['name'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                        ) ?>

                                    </option>

                                <?php endif; ?>

                            <?php endforeach; ?>

                        <?php endforeach; ?>

                    </select>


                    <small>
                        La categoría determina los campos gastronómicos.
                    </small>

                </div>


                <!-- ESTADO -->

                <div
                        class="item-form-field"
                >

                    <label>
                        Estado del producto
                    </label>


                    <label
                            class="item-switch"
                    >

                        <input
                                type="checkbox"
                                id="itemActive"
                                <?= $itemIsActive
                                        ? 'checked'
                                        : ''
                                ?>
                        >


                        <span></span>


                        <strong>
                            Producto activo
                        </strong>

                    </label>

                </div>


                <!-- DESCRIPCIÓN -->

                <div
                        class="item-form-field item-form-field-full"
                >

                    <label
                            for="itemDescription"
                    >

                        Descripción

                    </label>


                    <textarea
                            id="itemDescription"
                            rows="4"
                            maxlength="2000"
                            placeholder="Describe el producto..."
                    ><?= htmlspecialchars(
                                $itemDescription,
                                ENT_QUOTES,
                                'UTF-8'
                        ) ?></textarea>


                    <small>
                        Descripción visible para el cliente.
                    </small>

                </div>

            </div>

        </section>


        <!-- =====================================================
             PRECIOS
        ====================================================== -->

        <section
                class="item-form-card"
        >

            <div
                    class="item-form-card-header"
            >

                <div
                        class="item-form-card-icon"
                >

                    <i
                            class="fa-solid fa-money-bill-wave"
                    ></i>

                </div>


                <div>

                    <h2>
                        Precios
                    </h2>


                    <p>
                        Configura los precios de venta y costos.
                    </p>

                </div>

            </div>


            <div
                    class="item-form-grid"
            >


                <!-- PRECIO -->

                <div
                        class="item-form-field"
                >

                    <label
                            for="itemPrice"
                    >

                        Precio de venta

                    </label>


                    <div
                            class="item-money-input"
                    >

                        <span>
                            $
                        </span>


                        <input
                                type="number"
                                id="itemPrice"
                                min="0"
                                step="0.01"
                                value="<?= htmlspecialchars(
                                        $itemPrice,
                                        ENT_QUOTES,
                                        'UTF-8'
                                ) ?>"
                                placeholder="0"
                                required
                        >

                    </div>

                </div>


                <!-- COMBO -->

                <div
                        class="item-form-field item-rule-field"
                        id="fieldComboPrice"
                >

                    <label
                            for="itemComboPrice"
                    >

                        Precio combo

                    </label>


                    <div
                            class="item-money-input"
                    >

                        <span>
                            $
                        </span>


                        <input
                                type="number"
                                id="itemComboPrice"
                                min="0"
                                step="0.01"
                                value="<?= htmlspecialchars(
                                        $itemComboPrice,
                                        ENT_QUOTES,
                                        'UTF-8'
                                ) ?>"
                                placeholder="0"
                        >

                    </div>

                </div>

                <!-- =====================================================
     PRESENTACIONES / OPCIONES DE PRECIO
====================================================== -->

                <div
                        class="item-form-field item-form-field-full"
                        style="grid-column: 1 / -1;"
                >

                    <div class="price-options-header">

                        <div>

                            <label>
                                Presentaciones y precios
                            </label>

                            <small>
                                Agrega diferentes cantidades o presentaciones
                                con su precio individual y precio combo.
                            </small>

                        </div>


                        <button
                                type="button"
                                class="item-price-option-add"
                                id="btnAddPriceOption"
                        >

                            <i class="fa-solid fa-plus"></i>

                            Agregar presentación

                        </button>

                    </div>


                    <div
                            id="itemPriceOptions"
                            class="item-price-options"
                    >

                        <?php

                        $priceOptions =
                                $item['price_options']
                                ?? [];

                        ?>


                        <?php if (!empty($priceOptions)): ?>

                            <?php foreach (
                                    $priceOptions
                                    as $index => $option
                            ): ?>

                                <div
                                        class="item-price-option"
                                        data-price-option
                                >

                                    <div
                                            class="item-price-option-field"
                                    >

                                        <label>
                                            Cantidad
                                        </label>

                                        <input
                                                type="number"
                                                class="price-option-quantity"
                                                min="1"
                                                step="1"
                                                value="<?= htmlspecialchars(
                                                        $option['quantity']
                                                        ?? '',
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                ) ?>"
                                        >

                                    </div>


                                    <div
                                            class="item-price-option-field"
                                    >

                                        <label>
                                            Nombre
                                        </label>

                                        <input
                                                type="text"
                                                class="price-option-label"
                                                maxlength="100"
                                                value="<?= htmlspecialchars(
                                                        $option['quantity_label']
                                                        ?? '',
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                ) ?>"
                                                placeholder="Ej: 6 ALITAS"
                                        >

                                    </div>


                                    <div
                                            class="item-price-option-field"
                                    >

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
                                                    value="<?= htmlspecialchars(
                                                            $option['price']
                                                            ?? '',
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                    ) ?>"
                                            >

                                        </div>

                                    </div>


                                    <div
                                            class="item-price-option-field"
                                    >

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
                                                    value="<?= htmlspecialchars(
                                                            $option['combo_price']
                                                            ?? '',
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                    ) ?>"
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

                                </div>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </div>


                    <div
                            id="itemPriceOptionsEmpty"
                            class="item-price-options-empty"
                            <?= !empty($priceOptions)
                                    ? 'hidden'
                                    : '' ?>
                    >

                        <i class="fa-solid fa-tags"></i>

                        <span>
                            No hay presentaciones configuradas.
                        </span>

                        <small>
                            Haz clic en "Agregar presentación" para comenzar.
                        </small>

                    </div>

                </div>


                <!-- COSTO -->

                <div
                        class="item-form-field"
                >

                    <label
                            for="itemCostPrice"
                    >

                        Costo

                    </label>


                    <div
                            class="item-money-input"
                    >

                        <span>
                            $
                        </span>


                        <input
                                type="number"
                                id="itemCostPrice"
                                min="0"
                                step="0.01"
                                value="<?= htmlspecialchars(
                                        $itemCostPrice,
                                        ENT_QUOTES,
                                        'UTF-8'
                                ) ?>"
                                placeholder="0"
                        >

                    </div>

                </div>


                <!-- IMPUESTO -->

                <div
                        class="item-form-field"
                >

                    <label
                            for="itemTaxRate"
                    >

                        Impuesto

                    </label>


                    <div
                            class="item-percent-input"
                    >

                        <input
                                type="number"
                                id="itemTaxRate"
                                min="0"
                                max="100"
                                step="0.01"
                                value="<?= htmlspecialchars(
                                        $itemTaxRate,
                                        ENT_QUOTES,
                                        'UTF-8'
                                ) ?>"
                                placeholder="0"
                        >


                        <span>
                            %
                        </span>

                    </div>

                </div>


            </div>

        </section>


        <!-- =====================================================
             INFORMACIÓN GASTRONÓMICA
        ====================================================== -->

        <section
                class="item-form-card"
        >

            <div
                    class="item-form-card-header"
            >

                <div
                        class="item-form-card-icon"
                >

                    <i
                            class="fa-solid fa-utensils"
                    ></i>

                </div>


                <div>

                    <h2>
                        Información gastronómica
                    </h2>


                    <p>
                        Estos campos se muestran según la categoría.
                    </p>

                </div>

            </div>


            <div
                    class="item-form-grid"
            >


                <!-- INGREDIENTES -->

                <div
                        class="item-form-field item-form-field-full item-rule-field"
                        id="fieldIngredients"
                >

                    <label
                            for="itemIngredients"
                    >

                        Ingredientes

                    </label>


                    <textarea
                            id="itemIngredients"
                            rows="5"
                            maxlength="5000"
                            placeholder="Ej. Pan brioche, carne, queso cheddar..."
                    ><?= htmlspecialchars(
                                $itemIngredients,
                                ENT_QUOTES,
                                'UTF-8'
                        ) ?></textarea>


                </div>


                <!-- PORCIÓN -->

                <div
                        class="item-form-field item-rule-field"
                        id="fieldPortion"
                >

                    <label
                            for="itemPortion"
                    >

                        Porción

                    </label>


                    <input
                            type="text"
                            id="itemPortion"
                            maxlength="100"
                            value="<?= htmlspecialchars(
                                    $itemPortion,
                                    ENT_QUOTES,
                                    'UTF-8'
                            ) ?>"
                            placeholder="Ej. 350 g / 500 ml"
                    >

                </div>


                <!-- PICANTE -->

                <div
                        class="item-form-field item-rule-field"
                        id="fieldSpicy"
                >

                    <label
                            for="itemSpicyLevel"
                    >

                        Nivel de picante

                    </label>


                    <select
                            id="itemSpicyLevel"
                    >

                        <option
                                value="0"
                                <?= $itemSpicyLevel === 0
                                        ? 'selected'
                                        : ''
                                ?>
                        >
                            Sin picante
                        </option>


                        <option
                                value="1"
                                <?= $itemSpicyLevel === 1
                                        ? 'selected'
                                        : ''
                                ?>
                        >
                            Suave
                        </option>


                        <option
                                value="2"
                                <?= $itemSpicyLevel === 2
                                        ? 'selected'
                                        : ''
                                ?>
                        >
                            Medio
                        </option>


                        <option
                                value="3"
                                <?= $itemSpicyLevel === 3
                                        ? 'selected'
                                        : ''
                                ?>
                        >
                            Alto
                        </option>

                    </select>

                </div>


                <!-- ALÉRGENOS -->

                <div
                        class="item-form-field item-form-field-full item-rule-field"
                        id="fieldAllergens"
                >

                    <label
                            for="itemAllergens"
                    >

                        Alérgenos

                    </label>


                    <textarea
                            id="itemAllergens"
                            rows="4"
                            maxlength="3000"
                            placeholder="Ej. Gluten, leche, huevo..."
                    ><?= htmlspecialchars(
                                $itemAllergens,
                                ENT_QUOTES,
                                'UTF-8'
                        ) ?></textarea>

                </div>


                <!-- PREPARACIÓN -->

                <div
                        class="item-form-field item-form-field-full"
                >

                    <label
                            for="itemPreparationNotes"
                    >

                        Notas de preparación

                    </label>


                    <textarea
                            id="itemPreparationNotes"
                            rows="4"
                            maxlength="3000"
                            placeholder="Indicaciones internas para preparación..."
                    ><?= htmlspecialchars(
                                $itemPreparationNotes,
                                ENT_QUOTES,
                                'UTF-8'
                        ) ?></textarea>

                </div>


            </div>

        </section>


        <!-- =====================================================
             DISPONIBILIDAD
        ====================================================== -->

        <section
                class="item-form-card"
        >

            <div
                    class="item-form-card-header"
            >

                <div
                        class="item-form-card-icon"
                >

                    <i
                            class="fa-solid fa-store"
                    ></i>

                </div>


                <div>

                    <h2>
                        Disponibilidad
                    </h2>


                    <p>
                        Controla la disponibilidad del producto.
                    </p>

                </div>

            </div>


            <div
                    class="item-active-row"
            >


                <!-- DISPONIBLE -->

                <div>

                    <strong>
                        Producto disponible
                    </strong>


                    <span>
                        Los productos no disponibles
                        no podrán venderse.
                    </span>

                </div>


                <label
                        class="item-switch"
                >

                    <input
                            type="checkbox"
                            id="itemAvailable"
                            <?= $itemIsAvailable
                                    ? 'checked'
                                    : ''
                            ?>
                    >


                    <span></span>

                </label>

            </div>


            <div
                    class="item-active-row item-rule-field"
                    id="fieldFeatured"
            >


                <div>

                    <strong>
                        Producto destacado
                    </strong>


                    <span>
                        Permite destacar este producto
                        dentro del catálogo.
                    </span>

                </div>


                <label
                        class="item-switch"
                >

                    <input
                            type="checkbox"
                            id="itemFeatured"
                            <?= $itemIsFeatured
                                    ? 'checked'
                                    : ''
                            ?>
                    >


                    <span></span>

                </label>

            </div>

        </section>


        <!-- =====================================================
             CONFIGURACIÓN
        ====================================================== -->

        <section
                class="item-form-card"
        >

            <div
                    class="item-form-card-header"
            >

                <div
                        class="item-form-card-icon"
                >

                    <i
                            class="fa-solid fa-sliders"
                    ></i>

                </div>


                <div>

                    <h2>
                        Configuración
                    </h2>


                    <p>
                        Configuración adicional del producto.
                    </p>

                </div>

            </div>


            <div
                    class="item-form-grid"
            >


                <!-- ORDEN -->

                <div
                        class="item-form-field"
                >

                    <label
                            for="itemDisplayOrder"
                    >

                        Orden de visualización

                    </label>


                    <input
                            type="number"
                            id="itemDisplayOrder"
                            min="0"
                            step="1"
                            value="<?= htmlspecialchars(
                                    $itemDisplayOrder,
                                    ENT_QUOTES,
                                    'UTF-8'
                            ) ?>"
                    >


                    <small>
                        Los productos con menor número
                        aparecerán primero.
                    </small>

                </div>


                <!-- TIEMPO -->

                <div
                        class="item-form-field"
                >

                    <label
                            for="itemDuration"
                    >

                        Tiempo de preparación

                    </label>


                    <div
                            class="item-input-with-suffix"
                    >

                        <input
                                type="number"
                                id="itemDuration"
                                min="0"
                                max="1440"
                                step="1"
                                value="<?= htmlspecialchars(
                                        $itemDuration,
                                        ENT_QUOTES,
                                        'UTF-8'
                                ) ?>"
                        >


                        <span>
                            minutos
                        </span>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             ACCIONES
        ====================================================== -->

        <div
                class="item-form-actions"
        >

            <a
                    href="<?= APP_URL ?>/admin/items"
                    class="item-form-cancel"
            >

                Cancelar

            </a>


            <button
                    type="submit"
                    class="item-form-submit"
                    id="btnSaveItem"
            >

                <i
                        class="fa-solid fa-check"
                ></i>


                <?= $isEdit
                        ? 'Guardar cambios'
                        : 'Crear producto'
                ?>

            </button>

        </div>


    </form>

</div>


<!-- =========================================================
     CONFIGURACIÓN JAVASCRIPT
========================================================== -->

<script>

    window.ITEM_FORM = {

        mode:
                <?= json_encode(
                        $mode,
                        JSON_UNESCAPED_UNICODE |
                        JSON_UNESCAPED_SLASHES
                ) ?>,


        itemId:
                <?= $itemId > 0
                        ? $itemId
                        : 'null'
                        ?>,


        categoryId:
                <?= $itemCategoryId !== ''
                        ? (int) $itemCategoryId
                        : 'null'
                        ?>,


        spicyLevel:
                <?= $itemSpicyLevel ?>,


        categories:
                <?= json_encode(
                        $categories,
                        JSON_UNESCAPED_UNICODE |
                        JSON_UNESCAPED_SLASHES
                ) ?>,


        images:
                <?= json_encode(
                        $imagesForJs,
                        JSON_UNESCAPED_UNICODE |
                        JSON_UNESCAPED_SLASHES
                ) ?>

    };

</script>