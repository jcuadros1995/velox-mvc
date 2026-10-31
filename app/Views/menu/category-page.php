<?php

/*
|--------------------------------------------------------------------------
| PÁGINA PÚBLICA DINÁMICA DE CATEGORÍA
|--------------------------------------------------------------------------
*/

$page =
    $categoryPublicPage
    ?? [];

$images =
    $categoryPublicImages
    ?? [];

$products =
    $categoryPublicProducts
    ?? [];

$featuredProducts =
    $categoryPublicFeaturedProducts
    ?? [];

$regularProducts =
    $categoryPublicRegularProducts
    ?? [];

$rules =
    $categoryPublicRules
    ?? [];


/*
|--------------------------------------------------------------------------
| DATOS DE LA PÁGINA
|--------------------------------------------------------------------------
*/

$categoryName =
    trim(
        (string) (
            $page['category_name']
            ?? 'Burger Town'
        )
    );

$title =
    trim(
        (string) (
            $page['title']
            ?? $categoryName
        )
    );

$h1 =
    trim(
        (string) (
            $page['h1']
            ?? $title
        )
    );

$description =
    trim(
        (string) (
            $page['intro_text']
            ?? ''
        )
    );


/*
|--------------------------------------------------------------------------
| IMAGEN PRINCIPAL
|--------------------------------------------------------------------------
*/

$favoriteImage = null;

foreach ($images as $image) {

    if (
        (int) (
            $image['is_favorite']
            ?? 0
        ) === 1
    ) {

        $favoriteImage =
            $image;

        break;
    }
}

if (
    !$favoriteImage
    && !empty($images)
) {

    $favoriteImage =
        $images[0];
}

$heroImageUrl = '';

if (
    $favoriteImage
) {

    $path =
        ltrim(
            (string) (
                $favoriteImage['image_path']
                ?? ''
            ),
            '/\\'
        );

    if (
        $path !== ''
    ) {

        $heroImageUrl =
            rtrim(
                APP_URL,
                '/'
            )
            . '/public/'
            . $path;
    }
}


/*
|--------------------------------------------------------------------------
| REGLAS DE LA CATEGORÍA
|--------------------------------------------------------------------------
*/

$showComboPrice =
    (int) (
        $rules['show_combo_price']
        ?? 1
    ) === 1;

$showIngredients =
    (int) (
        $rules['show_ingredients']
        ?? 0
    ) === 1;

$showFeatured =
    (int) (
        $rules['show_featured']
        ?? 1
    ) === 1;


/*
|--------------------------------------------------------------------------
| SI NO HAY DESTACADOS
|--------------------------------------------------------------------------
|
| Si la categoría no tiene productos marcados como destacados,
| utilizamos los primeros productos disponibles para que la sección
| no quede vacía.
|
*/

if (
    empty($featuredProducts)
    && !empty($regularProducts)
) {

    $featuredProducts =
        array_slice(
            $regularProducts,
            0,
            min(
                3,
                count($regularProducts)
            )
        );

    $featuredIds = [];

    foreach (
        $featuredProducts
        as $product
    ) {

        $featuredIds[] =
            (int) (
                $product['id']
                ?? 0
            );
    }

    $regularProducts =
        array_values(
            array_filter(
                $regularProducts,
                static function (
                    array $product
                ) use (
                    $featuredIds
                ): bool {

                    return !in_array(
                        (int) (
                            $product['id']
                            ?? 0
                        ),
                        $featuredIds,
                        true
                    );
                }
            )
        );
}


/*
|--------------------------------------------------------------------------
| ICONOS DINÁMICOS
|--------------------------------------------------------------------------
*/

$featuredIcons = [
    'fa-burger',
    'fa-fire',
    'fa-crown'
];

?>

<div class="bt-burgers-page">


    <!-- =========================================================
         HERO SLIDER
    ========================================================== -->

    <?php

    /*
    |--------------------------------------------------------------------------
    | PREPARAR IMÁGENES DEL HERO
    |--------------------------------------------------------------------------
    */

    $heroSlides = [];

    foreach ($images as $image) {

        $imagePath =
            ltrim(
                (string) (
                    $image['image_path']
                    ?? ''
                ),
                '/\\'
            );

        if ($imagePath === '') {
            continue;
        }

        $heroSlides[] = [
            'url' =>
                rtrim(
                    APP_URL,
                    '/'
                )
                . '/public/'
                . $imagePath,

            'alt' =>
                trim(
                    (string) (
                        $image['image_alt']
                        ?? $categoryName
                        ?? 'Burger Town'
                    )
                ),

            'mobile_url' => !empty($image['mobile_image_path'])
                    ? rtrim(APP_URL, '/') . '/public/' .
                    ltrim($image['mobile_image_path'], '/\\')
                    : '',

            'favorite' =>
                (int) (
                    $image['is_favorite']
                    ?? 0
                ) === 1
        ];
    }

    $heroSlideCount = count($heroSlides);

    ?>


    <section
            class="bt-burgers-hero bt-hero-slider"
            data-hero-slider
            data-slide-count="<?= $heroSlideCount ?>"
    >


        <!-- =====================================================
             BACKGROUND SLIDER
        ====================================================== -->

        <div
                class="bt-burgers-hero-bg bt-hero-slider-track"
        >

            <?php if (!empty($heroSlides)): ?>

                <?php foreach (
                    $heroSlides
                    as $index => $slide
                ): ?>

                    <div
                            class="bt-hero-slide <?= $index === 0
                                ? 'is-active'
                                : '' ?>"
                            data-slide="<?= $index ?>"
                    >


                        <picture>
                            <?php if (!empty($slide['mobile_url'])): ?>
                                <source
                                        media="(max-width: 767px)"
                                        srcset="<?= htmlspecialchars(
                                                $slide['mobile_url'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                        ) ?>"
                                >
                            <?php endif; ?>

                            <img
                                    src="<?= htmlspecialchars(
                                            $slide['url'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                    ) ?>"
                                    alt="<?= htmlspecialchars(
                                            $slide['alt'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                    ) ?>"
                                    draggable="false"
                                    loading="<?= $index === 0 ? 'eager' : 'lazy' ?>"
                            >
                        </picture>


                    </div>

                <?php endforeach; ?>

            <?php endif; ?>


            <!-- Overlay oscuro -->

            <div
                    class="bt-burgers-hero-overlay"
            ></div>

        </div>


        <!-- =====================================================
             EFECTO DE LUZ
        ====================================================== -->

        <div
                class="bt-burgers-hero-glow"
        ></div>


        <!-- =====================================================
             CONTENIDO
        ====================================================== -->

        <div
                class="bt-burgers-hero-content"
        >

            <div
                    class="bt-burgers-hero-actions"
            >

                <a
                        href="#category-products"
                        class="bt-burgers-primary-button"
                >

                    Explorar productos

                    <i
                            class="fa-solid fa-arrow-down"
                    ></i>

                </a>


                <a
                        href="<?= APP_URL ?>/reservas"
                        class="bt-burgers-secondary-button"
                >

                    <i
                            class="fa-solid fa-calendar-check"
                    ></i>

                    Reservar mesa

                </a>

            </div>

        </div>


        <!-- =====================================================
             CONTROLES DEL SLIDER
        ====================================================== -->

        <?php if ($heroSlideCount > 1): ?>

            <div
                    class="bt-hero-slider-controls"
            >


                <!-- Flecha anterior -->

                <button
                        type="button"
                        class="bt-hero-slider-arrow bt-hero-slider-prev"
                        data-hero-prev
                        aria-label="Imagen anterior"
                >

                    <i
                            class="fa-solid fa-arrow-left"
                    ></i>

                </button>


                <!-- CONTADOR -->

                <div
                        class="bt-hero-slider-counter"
                >

                <span
                        data-hero-current
                >
                    01
                </span>

                    <span
                            class="bt-hero-slider-counter-line"
                    ></span>

                    <span
                            data-hero-total
                    >
                    <?= str_pad(
                        (string) $heroSlideCount,
                        2,
                        '0',
                        STR_PAD_LEFT
                    ) ?>
                </span>

                </div>


                <!-- Flecha siguiente -->

                <button
                        type="button"
                        class="bt-hero-slider-arrow bt-hero-slider-next"
                        data-hero-next
                        aria-label="Siguiente imagen"
                >

                    <i
                            class="fa-solid fa-arrow-right"
                    ></i>

                </button>

            </div>


            <!-- =================================================
                 BARRA DE PROGRESO
            ================================================== -->

            <div
                    class="bt-hero-slider-progress"
            >

                <div
                        class="bt-hero-slider-progress-bar"
                        data-hero-progress
                ></div>

            </div>

        <?php endif; ?>


    </section>

    <!-- =========================================================
         INTRO
    ========================================================== -->

    <section
            class="bt-burgers-intro"
    >

        <div
                class="bt-burgers-container"
        >

            <div
                    class="bt-burgers-intro-grid"
            >


                <div
                        class="bt-burgers-intro-copy"
                >

                    <span
                            class="bt-burgers-section-eyebrow"
                    >

                        NUESTRA CARTA

                    </span>


                    <h2>

                        Más que una

                        <span>
                            <?= htmlspecialchars(
                                strtolower(
                                    $categoryName
                                ),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>.
                        </span>

                    </h2>


                    <p>

                        <?= $description !== ''
                            ? nl2br(
                                htmlspecialchars(
                                    $description,
                                    ENT_QUOTES,
                                    'UTF-8'
                                )
                            )
                            : 'En Burger Town construimos nuestra carta alrededor del sabor, el contraste y ese punto que hace que quieras volver por otra.'
                        ?>

                    </p>


                    <div
                            class="bt-burgers-feature-list"
                    >

                        <div>

                            <i
                                    class="fa-solid fa-fire"
                            ></i>

                            <span>
                                Recetas de nuestra creación
                            </span>

                        </div>


                        <div>

                            <i
                                    class="fa-solid fa-burger"
                            ></i>

                            <span>
                                Ingredientes seleccionados
                            </span>

                        </div>


                        <div>

                            <i
                                    class="fa-solid fa-star"
                            ></i>

                            <span>
                                Preparadas para el plan
                            </span>

                        </div>

                    </div>

                </div>


                <!-- =========================================================
                     SLIDER DE PRODUCTOS DESTACADOS
                ========================================================== -->

                <div
                        class="bt-burgers-intro-card bt-intro-featured-slider"
                        data-featured-slider
                >

                    <?php
                    $introFeaturedProducts = array_values(
                        array_filter(
                            array_slice(
                                $featuredProducts,
                                0,
                                3
                            ),
                            static function (array $product): bool {
                                return !empty(
                                    $product['favorite_image']
                                    ?? ''
                                );
                            }
                        )
                    );
                    ?>

                    <?php if (!empty($introFeaturedProducts)): ?>

                        <!-- ================================================
                             SLIDER
                        ================================================= -->

                        <div
                                class="bt-intro-slider-viewport"
                        >

                            <div
                                    class="bt-intro-slider-track"
                                    data-featured-track
                            >

                                <?php foreach (
                                    $introFeaturedProducts
                                    as $index => $featuredProduct
                                ): ?>

                                    <?php

                                    $introImagePath =
                                        ltrim(
                                            (string) (
                                                $featuredProduct['favorite_image']
                                                ?? ''
                                            ),
                                            '/\\'
                                        );

                                    $introImageUrl =
                                        rtrim(
                                            APP_URL,
                                            '/'
                                        )
                                        . '/public/'
                                        . $introImagePath;

                                    $introImageAlt =
                                        trim(
                                            (string) (
                                                $featuredProduct['favorite_image_alt']
                                                ?? $featuredProduct['name']
                                                ?? 'Producto destacado'
                                            )
                                        );

                                    ?>

                                    <div
                                            class="bt-intro-featured-slide <?= $index === 0
                                                ? 'is-active'
                                                : '' ?>"
                                            data-featured-slide="<?= $index ?>"
                                    >

                                        <img
                                                src="<?= htmlspecialchars(
                                                    $introImageUrl,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                                alt="<?= htmlspecialchars(
                                                    $introImageAlt,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                                loading="<?= $index === 0
                                                    ? 'eager'
                                                    : 'lazy' ?>"
                                                draggable="false"
                                        >

                                    </div>

                                <?php endforeach; ?>

                            </div>

                        </div>


                        <!-- ================================================
                             FLECHAS
                        ================================================= -->

                        <?php if (
                            count($introFeaturedProducts) > 1
                        ): ?>

                            <button
                                    type="button"
                                    class="bt-intro-slider-arrow bt-intro-slider-prev"
                                    data-featured-prev
                                    aria-label="Producto destacado anterior"
                            >

                                <i
                                        class="fa-solid fa-chevron-left"
                                ></i>

                            </button>


                            <button
                                    type="button"
                                    class="bt-intro-slider-arrow bt-intro-slider-next"
                                    data-featured-next
                                    aria-label="Siguiente producto destacado"
                            >

                                <i
                                        class="fa-solid fa-chevron-right"
                                ></i>

                            </button>


                            <!-- ============================================
                                 INDICADORES
                            ============================================= -->

                            <div
                                    class="bt-intro-slider-indicators"
                            >

                                <?php foreach (
                                    $introFeaturedProducts
                                    as $index => $featuredProduct
                                ): ?>

                                    <button
                                            type="button"
                                            class="bt-intro-slider-dot <?= $index === 0
                                                ? 'is-active'
                                                : '' ?>"
                                            data-featured-dot="<?= $index ?>"
                                            aria-label="Ver producto destacado <?= $index + 1 ?>"
                                    ></button>

                                <?php endforeach; ?>

                            </div>

                        <?php endif; ?>


                        <!-- ================================================
                             CONTADOR
                        ================================================= -->

                        <?php if (
                            count($introFeaturedProducts) > 1
                        ): ?>

                            <div
                                    class="bt-intro-slider-counter"
                            >

                <span
                        data-featured-current
                >
                    01
                </span>

                                <span>
                    /
                </span>

                                <span>
                    <?= str_pad(
                        (string) count(
                            $introFeaturedProducts
                        ),
                        2,
                        '0',
                        STR_PAD_LEFT
                    ) ?>
                </span>

                            </div>

                        <?php endif; ?>

                    <?php else: ?>

                        <div
                                class="bt-intro-slider-empty"
                        >

                            <i
                                    class="fa-solid fa-burger"
                            ></i>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </section>


    <!-- =========================================================
         NAVEGACIÓN INTERNA
    ========================================================== -->

    <section
            class="bt-burgers-nav-section"
    >

        <div
                class="bt-burgers-container"
        >

            <div
                    class="bt-burgers-category-nav"
            >

                <?php if (
                    $showFeatured
                    && !empty($featuredProducts)
                ): ?>

                    <a
                            href="#category-featured"
                            class="is-active"
                    >

                        Destacados

                    </a>

                <?php endif; ?>


                <?php if (
                    !empty($regularProducts)
                ): ?>

                    <a
                            href="#category-products"
                    >

                        Todos los productos

                    </a>

                <?php endif; ?>


                <a
                        href="#category-notes"
                >

                    Nuestra recomendación

                </a>

            </div>

        </div>

    </section>


    <!-- =========================================================
         PRODUCTOS DESTACADOS
    ========================================================== -->

    <?php if (
        $showFeatured
        && !empty($featuredProducts)
    ): ?>

        <section
                id="category-featured"
                class="bt-burgers-products-section"
        >

            <div
                    class="bt-burgers-container"
            >

                <!-- =================================================
                     ENCABEZADO CENTRADO
                ================================================== -->

                <header
                        class="bt-burgers-section-header bt-burgers-featured-heading"
                >

                    <span
                            class="bt-burgers-section-eyebrow"
                    >

                        BURGER MASTER

                    </span>


                    <h2>

                        Productos

                        <span>
                            destacados
                        </span>

                    </h2>


                    <p>

                        Las opciones que representan
                        la esencia de esta categoría.

                    </p>

                </header>


                <!-- =================================================
                     GRID DE DESTACADOS
                ================================================== -->

                <div
                        class="bt-burgers-featured-grid"
                >

                    <?php
                    $featuredIndex = 0;
                    ?>

                    <?php foreach (
                        $featuredProducts
                        as $product
                    ): ?>

                        <?php

                        $featuredIndex++;

                        $icon =
                            $featuredIcons[
                            (
                                $featuredIndex - 1
                            )
                            % count(
                                $featuredIcons
                            )
                            ];


                        /*
                        |--------------------------------------------------------------------------
                        | IMAGEN DEL PRODUCTO
                        |--------------------------------------------------------------------------
                        */

                        $productImagePath =
                            ltrim(
                                (string) (
                                    $product['favorite_image']
                                    ?? ''
                                ),
                                '/\\'
                            );

                        $productImageUrl = '';

                        if (
                            $productImagePath !== ''
                        ) {

                            $productImageUrl =
                                rtrim(
                                    APP_URL,
                                    '/'
                                )
                                . '/public/'
                                . $productImagePath;
                        }


                        $productImageAlt =
                            trim(
                                (string) (
                                    $product['favorite_image_alt']
                                    ?? $product['name']
                                    ?? 'Burger Town'
                                )
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | NÚMERO
                        |--------------------------------------------------------------------------
                        */

                        $productNumber =
                            str_pad(
                                (string) $featuredIndex,
                                2,
                                '0',
                                STR_PAD_LEFT
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | ETIQUETA
                        |--------------------------------------------------------------------------
                        */

                        $productBadge =
                            $featuredIndex === 2
                                ? 'FAVORITA'
                                : 'DESTACADA';

                        $featuredCartOptions = $product['price_options'] ?? [];
                        $featuredHasCombo = (float) ($product['combo_price'] ?? 0) > 0;
                        foreach ($featuredCartOptions as $featuredCartOption) {
                            if ((float) ($featuredCartOption['combo_price'] ?? 0) > 0) {
                                $featuredHasCombo = true;
                                break;
                            }
                        }

                        $featuredCartJson = json_encode(
                            [
                                'id' => (int) ($product['id'] ?? 0),
                                'name' => (string) ($product['name'] ?? ''),
                                'image' => $productImageUrl,
                                'price' => (float) ($product['price'] ?? 0),
                                'combo_price' => (float) ($product['combo_price'] ?? 0)
                            ],
                            JSON_UNESCAPED_UNICODE |
                            JSON_UNESCAPED_SLASHES |
                            JSON_HEX_APOS |
                            JSON_HEX_QUOT |
                            JSON_HEX_AMP |
                            JSON_HEX_TAG
                        );

                        ?>


                        <article
                                class="bt-burger-featured-card <?= $featuredIndex === 2
                                    ? 'featured'
                                    : '' ?>"
                                data-cart-product="<?= htmlspecialchars($featuredCartJson ?: '{}', ENT_QUOTES, 'UTF-8') ?>"
                        >


                            <!-- =====================================
                                 IMAGEN + INFORMACIÓN SUPERPUESTA
                            ====================================== -->

                            <div
                                    class="bt-burger-featured-media"
                            >

                                <?php if (
                                    $productImageUrl !== ''
                                ): ?>

                                    <img
                                            src="<?= htmlspecialchars(
                                                $productImageUrl,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            alt="<?= htmlspecialchars(
                                                $productImageAlt,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            loading="lazy"
                                            draggable="false"
                                    >

                                <?php else: ?>

                                    <div
                                            class="bt-burger-featured-image-placeholder"
                                    >

                                        <i
                                                class="fa-solid fa-burger"
                                        ></i>

                                    </div>

                                <?php endif; ?>


                                <!-- OVERLAY DE LA IMAGEN -->

                                <div
                                        class="bt-burger-featured-overlay"
                                >

                                    <span
                                            class="bt-burger-featured-number"
                                    >

                                        <?= htmlspecialchars(
                                            $productNumber,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </span>


                                    <span
                                            class="bt-burger-featured-badge"
                                    >

                                        <?= htmlspecialchars(
                                            $productBadge,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </span>

                                </div>


                                <!-- ICONO SOBRE LA IMAGEN -->

                                <div
                                        class="bt-burger-featured-icon"
                                >

                                    <i
                                            class="fa-solid <?= htmlspecialchars(
                                                $icon,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                    ></i>

                                </div>

                            </div>


                            <!-- =====================================
                                 CONTENIDO
                            ====================================== -->

                            <div
                                    class="bt-burger-featured-content"
                            >


                                <h3>

                                    <?= htmlspecialchars(
                                        $product['name']
                                        ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </h3>


                                <?php if (
                                    !empty(
                                        $product['description']
                                        ?? ''
                                    )
                                ): ?>

                                    <p>

                                        <?= htmlspecialchars(
                                            $product['description'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </p>

                                <?php else: ?>

                                    <p>
                                        Una creación Burger Town
                                        preparada para disfrutar.
                                    </p>

                                <?php endif; ?>


                                <?php
                                $priceOptions =
                                        $product['price_options']
                                        ?? [];
                                ?>

                                <?php if (!empty($priceOptions)): ?>

                                    <div class="bt-featured-price-options">

                                        <div class="bt-featured-price-options-title">
                                            PRESENTACIONES
                                        </div>


                                        <!-- ENCABEZADOS -->

                                        <div class="bt-featured-price-options-head">

            <span>
                CANTIDAD
            </span>

                                            <span>
                SOLA
            </span>

                                            <?php if ($showComboPrice): ?>

                                                <span>
                    COMBO
                </span>

                                            <?php endif; ?>

                                        </div>


                                        <!-- OPCIONES -->

                                        <?php foreach (
                                                $priceOptions
                                                as $priceOption
                                        ): ?>

                                            <div class="bt-featured-price-option">

                                                <!-- CANTIDAD -->

                                                <div
                                                        class="bt-featured-price-option-quantity"
                                                >

                                                    <strong>

                                                        <?php

                                                        $quantity =
                                                                (int) (
                                                                        $priceOption['quantity']
                                                                        ?? 0
                                                                );

                                                        $label =
                                                                trim(
                                                                        (string) (
                                                                                $priceOption[
                                                                                'quantity_label'
                                                                                ]
                                                                                ?? ''
                                                                        )
                                                                );


                                                        if ($label !== '') {

                                                            echo htmlspecialchars(
                                                                    $quantity .
                                                                    ' ' .
                                                                    $label,
                                                                    ENT_QUOTES,
                                                                    'UTF-8'
                                                            );

                                                        } else {

                                                            echo htmlspecialchars(
                                                                    $quantity .
                                                                    ' UNIDADES',
                                                                    ENT_QUOTES,
                                                                    'UTF-8'
                                                            );
                                                        }

                                                        ?>

                                                    </strong>

                                                </div>


                                                <!-- PRECIO SOLA -->

                                                <div
                                                        class="bt-featured-price-option-value"
                                                >


                                                    <strong>

                                                        $<?= number_format(
                                                                (float) (
                                                                        $priceOption['price']
                                                                        ?? 0
                                                                ),
                                                                0,
                                                                ',',
                                                                '.'
                                                        ) ?>

                                                    </strong>

                                                </div>


                                                <!-- PRECIO COMBO -->

                                                <?php if ($showComboPrice): ?>

                                                    <div
                                                            class="
                            bt-featured-price-option-value
                            bt-featured-price-option-combo
                        "
                                                    >

                                                        <?php

                                                        $comboPrice =
                                                                (float) (
                                                                        $priceOption[
                                                                        'combo_price'
                                                                        ]
                                                                        ?? 0
                                                                );

                                                        ?>

                                                        <?php if ($comboPrice > 0): ?>



                                                            <strong>

                                                                $<?= number_format(
                                                                        $comboPrice,
                                                                        0,
                                                                        ',',
                                                                        '.'
                                                                ) ?>

                                                            </strong>

                                                        <?php else: ?>

                                                            <span
                                                                    class="bt-featured-price-option-empty"
                                                            >
                                —
                            </span>

                                                        <?php endif; ?>

                                                    </div>

                                                <?php endif; ?>

                                            </div>

                                        <?php endforeach; ?>

                                    </div>


                                <?php else: ?>

                                    <!-- PRECIO NORMAL SI NO HAY PRESENTACIONES -->

                                    <div class="bt-burger-featured-prices">

                                        <div>

            <span>
                SOLA
            </span>

                                            <strong>

                                                $<?= number_format(
                                                        (float) (
                                                                $product['price']
                                                                ?? 0
                                                        ),
                                                        0,
                                                        ',',
                                                        '.'
                                                ) ?>

                                            </strong>

                                        </div>


                                        <?php if (
                                                $showComboPrice
                                                && isset(
                                                        $product['combo_price']
                                                )
                                                && (float) (
                                                $product['combo_price']
                                                ) > 0
                                        ): ?>

                                            <div>

                <span>
                    EN COMBO
                </span>

                                                <strong>

                                                    $<?= number_format(
                                                            (float) (
                                                            $product['combo_price']
                                                            ),
                                                            0,
                                                            ',',
                                                            '.'
                                                    ) ?>

                                                </strong>

                                            </div>

                                        <?php endif; ?>

                                    </div>

                                <?php endif; ?>


                                <!-- =================================
                                     BOTÓN ROJO
                                ================================== -->

                                <a
                                        href="<?= APP_URL ?>/reservas"
                                        class="bt-burger-featured-button"
                                >

                                    <span>
                                        Reservar mesa
                                    </span>

                                    <i
                                            class="fa-solid fa-arrow-right"
                                    ></i>

                                </a>

                                <div class="store-product-cart-controls store-featured-cart-controls">
                                    <?php if (!empty($featuredCartOptions)): ?>
                                        <label class="store-product-cart-option-label">
                                            Presentación
                                            <select class="form-select" data-cart-option aria-label="Seleccionar presentación">
                                                <?php foreach ($featuredCartOptions as $featuredOption): ?>
                                                    <option
                                                        value="<?= (int) ($featuredOption['id'] ?? 0) ?>"
                                                        data-price="<?= htmlspecialchars((string) ($featuredOption['price'] ?? 0), ENT_QUOTES, 'UTF-8') ?>"
                                                        data-combo-price="<?= htmlspecialchars((string) ($featuredOption['combo_price'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                                        data-label="<?= htmlspecialchars((string) ($featuredOption['quantity_label'] ?? ($featuredOption['quantity'] . ' unidades')), ENT_QUOTES, 'UTF-8') ?>"
                                                        data-quantity="<?= (int) ($featuredOption['quantity'] ?? 1) ?>"
                                                    >
                                                        <?= htmlspecialchars((string) ($featuredOption['quantity_label'] ?? ($featuredOption['quantity'] . ' unidades')), ENT_QUOTES, 'UTF-8') ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </label>
                                    <?php endif; ?>

                                    <?php if ($showComboPrice && $featuredHasCombo): ?>
                                        <label class="store-product-cart-combo">
                                            <input type="checkbox" data-cart-combo>
                                            <span>Convertir en combo</span>
                                        </label>
                                    <?php endif; ?>

                                    <div class="store-product-cart-actions">
                                        <input class="form-control" type="number" min="1" max="99" value="1" data-cart-quantity aria-label="Cantidad">
                                        <button type="button" class="btn store-product-add-button" data-cart-add>
                                            <i class="fa-solid fa-cart-plus me-2"></i>Agregar al carrito
                                        </button>
                                    </div>
                                </div>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            </div>

        </section>

    <?php endif; ?>


    <!-- =========================================================
         TODOS LOS PRODUCTOS
    ========================================================== -->

    <?php if (!empty($regularProducts)): ?>

    <section
            id="category-products"
            class="bt-burgers-classic-section"
    >

        <div
                class="bt-burgers-container"
        >

            <header
                    class="bt-burgers-section-header"
            >

                <span
                        class="bt-burgers-section-eyebrow"
                >

                    CARTA

                </span>


                <h2>

                    Nuestra

                    <span>
                        <?= htmlspecialchars(
                            strtolower(
                                $categoryName
                            ),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </span>

                </h2>


                <p>

                    Explora todos los productos
                    disponibles en esta categoría.

                </p>

            </header>

                <div
                        class="bt-burgers-classic-grid"
                >

                    <?php
                    $productIndex = 0;
                    ?>

                    <?php foreach (
                        $regularProducts
                        as $product
                    ): ?>

                        <?php

                        $productIndex++;

                        $productImagePath =
                            ltrim(
                                (string) (
                                    $product['favorite_image']
                                    ?? ''
                                ),
                                '/\\'
                            );

                        $productImageUrl = '';

                        if (
                            $productImagePath !== ''
                        ) {

                            $productImageUrl =
                                rtrim(
                                    APP_URL,
                                    '/'
                                )
                                . '/public/'
                                . $productImagePath;
                        }

                        $productImageAlt =
                            trim(
                                (string) (
                                    $product['favorite_image_alt']
                                    ?? $product['name']
                                    ?? 'Burger Town'
                                )
                            );

                        $cartPriceOptions = $product['price_options'] ?? [];
                        $cartHasComboPrice = (float) ($product['combo_price'] ?? 0) > 0;
                        foreach ($cartPriceOptions as $cartOption) {
                            if ((float) ($cartOption['combo_price'] ?? 0) > 0) {
                                $cartHasComboPrice = true;
                                break;
                            }
                        }

                        $cartProductJson = json_encode(
                            [
                                'id' => (int) ($product['id'] ?? 0),
                                'name' => (string) ($product['name'] ?? ''),
                                'image' => $productImageUrl,
                                'price' => (float) ($product['price'] ?? 0),
                                'combo_price' => (float) ($product['combo_price'] ?? 0)
                            ],
                            JSON_UNESCAPED_UNICODE |
                            JSON_UNESCAPED_SLASHES |
                            JSON_HEX_APOS |
                            JSON_HEX_QUOT |
                            JSON_HEX_AMP |
                            JSON_HEX_TAG
                        );

                        ?>


                        <article
                                class="bt-burger-classic-card"
                                data-cart-product="<?= htmlspecialchars($cartProductJson ?: '{}', ENT_QUOTES, 'UTF-8') ?>"
                        >


                            <!-- IMAGEN -->

                            <?php if (
                                $productImageUrl !== ''
                            ): ?>

                                <div
                                        class="bt-burger-classic-image"
                                >

                                    <img
                                            src="<?= htmlspecialchars(
                                                $productImageUrl,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            alt="<?= htmlspecialchars(
                                                $productImageAlt,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            loading="lazy"
                                            draggable="false"
                                    >

                                </div>

                            <?php endif; ?>


                            <!-- CABECERA -->

                            <div
                                    class="bt-burger-classic-header"
                            >

                                <span>

                                    <?= str_pad(
                                        (string) $productIndex,
                                        2,
                                        '0',
                                        STR_PAD_LEFT
                                    ) ?>

                                </span>


                                <h3>

                                    <?= htmlspecialchars(
                                        $product['name']
                                        ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </h3>

                            </div>


                            <?php if (
                                !empty(
                                    $product['description']
                                    ?? ''
                                )
                            ): ?>

                                <p>

                                    <?= htmlspecialchars(
                                        $product['description'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </p>

                            <?php endif; ?>


                            <?php if (
                                $showIngredients
                                && !empty(
                                    $product['ingredients']
                                    ?? ''
                                )
                            ): ?>

                                <p
                                        class="bt-category-product-ingredients"
                                >

                                    <?= htmlspecialchars(
                                        $product['ingredients'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </p>

                            <?php endif; ?>


                            <?php

                            $priceOptions =
                                    $product['price_options']
                                    ?? [];

                            ?>

                            <?php if (!empty($priceOptions)): ?>

                                <!-- =====================================================
                                     PRESENTACIONES Y PRECIOS
                                ====================================================== -->

                                <div
                                        class="bt-product-price-options"
                                >

                                    <div
                                            class="bt-product-price-options-title"
                                    >
                                        PRESENTACIONES
                                    </div>


                                    <div
                                            class="bt-product-price-options-head"
                                    >

            <span>
                CANTIDAD
            </span>

                                        <span>
                SOLA
            </span>

                                        <?php if ($showComboPrice): ?>

                                            <span>
                    COMBO
                </span>

                                        <?php endif; ?>

                                    </div>


                                    <?php foreach (
                                            $priceOptions as $priceOption
                                    ): ?>

                                        <div
                                                class="bt-product-price-option"
                                        >

                                            <!-- CANTIDAD -->

                                            <div
                                                    class="bt-product-price-option-quantity"
                                            >

                                                <strong>

                                                    <?php

                                                    $quantity =
                                                            (int) (
                                                                    $priceOption[
                                                                    'quantity'
                                                                    ]
                                                                    ?? 0
                                                            );


                                                    $label =
                                                            trim(
                                                                    (string) (
                                                                            $priceOption[
                                                                            'quantity_label'
                                                                            ]
                                                                            ?? ''
                                                                    )
                                                            );


                                                    /*
                                                     * Si el administrador puso:
                                                     *
                                                     * 6 + Alitas
                                                     *
                                                     * mostramos:
                                                     *
                                                     * 6 ALITAS
                                                     */
                                                    if ($label !== '') {

                                                        echo htmlspecialchars(
                                                                $quantity .
                                                                ' ' .
                                                                $label,
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                        );

                                                    } else {

                                                        echo htmlspecialchars(
                                                                $quantity .
                                                                ' UNIDADES',
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                        );
                                                    }

                                                    ?>

                                                </strong>

                                            </div>


                                            <!-- PRECIO SOLA -->

                                            <div
                                                    class="bt-product-price-option-value"
                                            >

                                                <small>
                                                    SOLA
                                                </small>

                                                <strong>

                                                    $<?= number_format(
                                                            (float) (
                                                                    $priceOption[
                                                                    'price'
                                                                    ]
                                                                    ?? 0
                                                            ),
                                                            0,
                                                            ',',
                                                            '.'
                                                    ) ?>

                                                </strong>

                                            </div>


                                            <!-- PRECIO COMBO -->

                                            <?php if ($showComboPrice): ?>

                                                <div
                                                        class="
                            bt-product-price-option-value
                            bt-product-price-option-combo
                        "
                                                >

                                                    <?php

                                                    $comboPrice =
                                                            (float) (
                                                                    $priceOption[
                                                                    'combo_price'
                                                                    ]
                                                                    ?? 0
                                                            );

                                                    ?>


                                                    <?php if (
                                                            $comboPrice > 0
                                                    ): ?>

                                                        <small>
                                                            COMBO
                                                        </small>

                                                        <strong>

                                                            $<?= number_format(
                                                                    $comboPrice,
                                                                    0,
                                                                    ',',
                                                                    '.'
                                                            ) ?>

                                                        </strong>

                                                    <?php else: ?>

                                                        <span
                                                                class="bt-price-option-empty"
                                                        >
                                —
                            </span>

                                                    <?php endif; ?>

                                                </div>

                                            <?php endif; ?>

                                        </div>

                                    <?php endforeach; ?>

                                </div>


                            <?php else: ?>

                                <!-- =====================================================
                                     PRECIO NORMAL
                                     PRODUCTOS SIN PRESENTACIONES
                                ====================================================== -->

                                <div
                                        class="bt-burger-classic-prices"
                                >

        <span>

            <small>
                SOLA
            </small>

            $<?= number_format(
                    (float) (
                            $product['price']
                            ?? 0
                    ),
                    0,
                    ',',
                    '.'
            ) ?>

        </span>


                                    <?php if (
                                            $showComboPrice
                                            &&
                                            isset(
                                                    $product['combo_price']
                                            )
                                            &&
                                            (float) (
                                            $product['combo_price']
                                            ) > 0
                                    ): ?>

                                        <span>

                <small>
                    COMBO
                </small>

                $<?= number_format(
                                                    (float) (
                                                    $product[
                                                    'combo_price'
                                                    ]
                                                    ),
                                                    0,
                                                    ',',
                                                    '.'
                                            ) ?>

            </span>

                                    <?php endif; ?>

                                </div>

                            <?php endif; ?>

                            <div class="store-product-cart-controls">
                                <?php if (!empty($cartPriceOptions)): ?>
                                    <label class="store-product-cart-option-label">
                                        Presentación
                                        <select class="form-select" data-cart-option aria-label="Seleccionar presentación">
                                            <?php foreach ($cartPriceOptions as $cartOption): ?>
                                                <option
                                                    value="<?= (int) ($cartOption['id'] ?? 0) ?>"
                                                    data-price="<?= htmlspecialchars((string) ($cartOption['price'] ?? 0), ENT_QUOTES, 'UTF-8') ?>"
                                                    data-combo-price="<?= htmlspecialchars((string) ($cartOption['combo_price'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                                    data-label="<?= htmlspecialchars((string) ($cartOption['quantity_label'] ?? ($cartOption['quantity'] . ' unidades')), ENT_QUOTES, 'UTF-8') ?>"
                                                    data-quantity="<?= (int) ($cartOption['quantity'] ?? 1) ?>"
                                                >
                                                    <?= htmlspecialchars((string) ($cartOption['quantity_label'] ?? ($cartOption['quantity'] . ' unidades')), ENT_QUOTES, 'UTF-8') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </label>
                                <?php endif; ?>

                                <?php if ($showComboPrice && $cartHasComboPrice): ?>
                                    <label class="store-product-cart-combo">
                                        <input type="checkbox" data-cart-combo>
                                        <span>Convertir en combo</span>
                                    </label>
                                <?php endif; ?>

                                <div class="store-product-cart-actions">
                                    <input class="form-control" type="number" min="1" max="99" value="1" data-cart-quantity aria-label="Cantidad">
                                    <button type="button" class="btn store-product-add-button" data-cart-add>
                                        <i class="fa-solid fa-cart-plus me-2"></i>Agregar
                                    </button>
                                </div>
                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php elseif (
                empty($featuredProducts)
            ): ?>

                <div
                        class="bt-category-public-empty"
                >

                    <i
                            class="fa-solid fa-utensils"
                    ></i>

                    <h3>
                        Próximamente
                    </h3>

                    <p>
                        Estamos preparando los productos
                        de esta categoría.
                    </p>

                </div>

        </div>

    </section>

    <?php endif; ?>

    <!-- =========================================================
     RECOMENDACIÓN DE LA CASA
========================================================== -->

    <?php

    $recommendationProducts = array_values(
            array_filter(
                    $categoryPublicProducts ?? [],
                    static function (array $product): bool {

                        return
                                (int) ($product['category_id'] ?? 0) === 6
                                &&
                                (int) ($product['is_active'] ?? 0) === 1
                                &&
                                (int) ($product['is_available'] ?? 0) === 1
                                &&
                                !empty(
                                        $product['favorite_image']
                                        ?? ''
                                );
                    }
            )
    );


    /*
    |--------------------------------------------------------------------------
    | REGLAS DE LA CATEGORÍA
    |--------------------------------------------------------------------------
    */

    $showIngredients =
        (int) (
            $categoryPublicRules['show_ingredients']
            ?? 1
        ) === 1;

    $showComboPrice =
        (int) (
            $categoryPublicRules['show_combo_price']
            ?? 1
        ) === 1;

    $showPortion =
        (int) (
            $categoryPublicRules['show_portion']
            ?? 1
        ) === 1;

    $showSpicyLevel =
        (int) (
            $categoryPublicRules['show_spicy_level']
            ?? 0
        ) === 1;

    $showAllergens =
        (int) (
            $categoryPublicRules['show_allergens']
            ?? 0
        ) === 1;

    ?>




</div>


<?php if (!empty($recommendationProducts)): ?>

    <section
            id="category-notes"
            class="bt-burgers-notes-section bt-recommendation-section"
    >

        <div class="bt-burgers-container">


            <!-- =================================================
                 ENCABEZADO
            ================================================== -->

            <div class="bt-recommendation-header">

                <div class="bt-recommendation-heading">

                <span
                        class="bt-recommendation-eyebrow"
                >

                    <i class="fa-solid fa-star"></i>

                    RECOMENDACIÓN DE LA CASA

                </span>


                    <h2>

                        Nuestra elección
                        <span>para ti.</span>

                    </h2>


                    <p>

                        Descubre una selección de nuestras
                        preparaciones favoritas y encuentra
                        la combinación perfecta para tu próximo plan.

                    </p>

                </div>


                <!-- =================================================
                     CONTROLES
                ================================================== -->

                <?php if (count($recommendationProducts) > 1): ?>

                    <div
                            class="bt-recommendation-controls"
                    >

                        <button
                                type="button"
                                class="bt-recommendation-arrow"
                                data-recommendation-prev
                                aria-label="Producto anterior"
                        >

                            <i class="fa-solid fa-arrow-left"></i>

                        </button>


                        <div
                                class="bt-recommendation-counter"
                        >

                        <span
                                data-recommendation-current
                        >
                            01
                        </span>

                            <span class="separator">
                            /
                        </span>

                            <span>

                            <?= str_pad(
                                    (string) count(
                                            $recommendationProducts
                                    ),
                                    2,
                                    '0',
                                    STR_PAD_LEFT
                            ) ?>

                        </span>

                        </div>


                        <button
                                type="button"
                                class="bt-recommendation-arrow"
                                data-recommendation-next
                                aria-label="Siguiente producto"
                        >

                            <i class="fa-solid fa-arrow-right"></i>

                        </button>

                    </div>

                <?php endif; ?>

            </div>


            <!-- =================================================
                 SLIDER
            ================================================== -->

            <div
                    class="bt-recommendation-slider"
                    data-recommendation-slider
            >

                <div
                        class="bt-recommendation-track"
                        data-recommendation-track
                >


                    <?php foreach (
                            $recommendationProducts
                            as $index => $product
                    ): ?>


                        <?php

                        /*
                        |--------------------------------------------------------------------------
                        | IMAGEN
                        |--------------------------------------------------------------------------
                        */

                        $imagePath =
                                ltrim(
                                        (string) (
                                                $product['favorite_image']
                                                ?? ''
                                        ),
                                        '/\\'
                                );


                        $imageUrl =
                                rtrim(
                                        APP_URL,
                                        '/'
                                )
                                . '/public/'
                                . $imagePath;


                        $imageAlt =
                                trim(
                                        (string) (
                                                $product['favorite_image_alt']
                                                ?? $product['name']
                                                ?? 'Producto Burger Town'
                                        )
                                );


                        /*
                        |--------------------------------------------------------------------------
                        | DATOS
                        |--------------------------------------------------------------------------
                        */

                        $productName =
                                trim(
                                        (string) (
                                                $product['name']
                                                ?? ''
                                        )
                                );


                        $productDescription =
                                trim(
                                        (string) (
                                                $product['description']
                                                ?? ''
                                        )
                                );


                        $ingredients =
                                trim(
                                        (string) (
                                                $product['ingredients']
                                                ?? ''
                                        )
                                );


                        $price =
                                (float) (
                                        $product['price']
                                        ?? 0
                                );


                        $comboPrice =
                                (float) (
                                        $product['combo_price']
                                        ?? 0
                                );


                        $portion =
                                trim(
                                        (string) (
                                                $product['portion_size']
                                                ?? ''
                                        )
                                );


                        $spicyLevel =
                                trim(
                                        (string) (
                                                $product['spicy_level']
                                                ?? ''
                                        )
                                );


                        $allergens =
                                trim(
                                        (string) (
                                                $product['allergens']
                                                ?? ''
                                        )
                                );

                        ?>


                        <article
                                class="bt-recommendation-card <?= $index === 0
                                        ? 'is-active'
                                        : '' ?>"
                                data-recommendation-slide="<?= $index ?>"
                        >


                            <!-- =====================================
                                 IMAGEN
                            ====================================== -->

                            <div
                                    class="bt-recommendation-image"
                            >

                                <img
                                        src="<?= htmlspecialchars(
                                                $imageUrl,
                                                ENT_QUOTES,
                                                'UTF-8'
                                        ) ?>"
                                        alt="<?= htmlspecialchars(
                                                $imageAlt,
                                                ENT_QUOTES,
                                                'UTF-8'
                                        ) ?>"
                                        loading="<?= $index === 0
                                                ? 'eager'
                                                : 'lazy' ?>"
                                        draggable="false"
                                >


                                <div
                                        class="bt-recommendation-image-overlay"
                                ></div>


                                <span
                                        class="bt-recommendation-badge"
                                >

                                <i class="fa-solid fa-star"></i>

                                DESTACADA

                            </span>

                            </div>


                            <!-- =====================================
                                 INFORMACIÓN
                            ====================================== -->

                            <div
                                    class="bt-recommendation-content"
                            >

                                <div
                                        class="bt-recommendation-main"
                                >

                                <span
                                        class="bt-recommendation-number"
                                >

                                    <?= str_pad(
                                            (string) (
                                                    $index + 1
                                            ),
                                            2,
                                            '0',
                                            STR_PAD_LEFT
                                    ) ?>

                                </span>


                                    <h3>

                                        <?= htmlspecialchars(
                                                $productName,
                                                ENT_QUOTES,
                                                'UTF-8'
                                        ) ?>

                                    </h3>


                                    <?php if (
                                            $productDescription !== ''
                                    ): ?>

                                        <p
                                                class="bt-recommendation-description"
                                        >

                                            <?= htmlspecialchars(
                                                    $productDescription,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                            ) ?>

                                        </p>

                                    <?php endif; ?>


                                    <?php if (
                                            $showIngredients
                                            &&
                                            $ingredients !== ''
                                    ): ?>

                                        <div
                                                class="bt-recommendation-ingredients"
                                        >

                                            <i
                                                    class="fa-solid fa-utensils"
                                            ></i>

                                            <span>

                                            <?= htmlspecialchars(
                                                    $ingredients,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                            ) ?>

                                        </span>

                                        </div>

                                    <?php endif; ?>


                                    <?php if (
                                            $showPortion
                                            &&
                                            $portion !== ''
                                    ): ?>

                                        <div
                                                class="bt-recommendation-meta"
                                        >

                                        <span>

                                            <i
                                                    class="fa-solid fa-weight-scale"
                                            ></i>

                                            <?= htmlspecialchars(
                                                    $portion,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                            ) ?>

                                        </span>

                                        </div>

                                    <?php endif; ?>


                                    <?php if (
                                            $showSpicyLevel
                                            &&
                                            $spicyLevel !== ''
                                    ): ?>

                                        <div
                                                class="bt-recommendation-meta"
                                        >

                                        <span>

                                            <i
                                                    class="fa-solid fa-pepper-hot"
                                            ></i>

                                            <?= htmlspecialchars(
                                                    $spicyLevel,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                            ) ?>

                                        </span>

                                        </div>

                                    <?php endif; ?>


                                    <?php if (
                                            $showAllergens
                                            &&
                                            $allergens !== ''
                                    ): ?>

                                        <div
                                                class="bt-recommendation-allergens"
                                        >

                                            <i
                                                    class="fa-solid fa-circle-exclamation"
                                            ></i>

                                            <?= htmlspecialchars(
                                                    $allergens,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                            ) ?>

                                        </div>

                                    <?php endif; ?>

                                </div>


                                <!-- =================================
                                     PRECIO
                                ================================== -->

                                <div
                                        class="bt-recommendation-footer"
                                >

                                    <div
                                            class="bt-recommendation-prices"
                                    >

                                        <div
                                                class="bt-recommendation-price"
                                        >

                                        <span>
                                            DESDE
                                        </span>

                                            <strong>

                                                $
                                                <?= number_format(
                                                        $price,
                                                        0,
                                                        ',',
                                                        '.'
                                                ) ?>

                                            </strong>

                                        </div>


                                        <?php if (
                                                $showComboPrice
                                                &&
                                                $comboPrice > 0
                                        ): ?>

                                            <div
                                                    class="bt-recommendation-combo"
                                            >

                                            <span>
                                                COMBO
                                            </span>

                                                <strong>

                                                    $
                                                    <?= number_format(
                                                            $comboPrice,
                                                            0,
                                                            ',',
                                                            '.'
                                                    ) ?>

                                                </strong>

                                            </div>

                                        <?php endif; ?>

                                    </div>


                                    <a
                                            href="<?= APP_URL ?>/reservas"
                                            class="bt-recommendation-button"
                                    >

                                        Reservar mesa

                                        <i
                                                class="fa-solid fa-arrow-right"
                                        ></i>

                                    </a>

                                </div>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>


                <!-- =================================================
                     DOTS
                ================================================== -->

                <?php if (
                        count($recommendationProducts) > 1
                ): ?>

                    <div
                            class="bt-recommendation-dots"
                    >

                        <?php foreach (
                                $recommendationProducts
                                as $index => $product
                        ): ?>

                            <button
                                    type="button"
                                    class="bt-recommendation-dot <?= $index === 0
                                            ? 'is-active'
                                            : '' ?>"
                                    data-recommendation-dot="<?= $index ?>"
                                    aria-label="Ver recomendación <?= $index + 1 ?>"
                            ></button>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </section>

<?php endif; ?>


<!-- =========================================================
         CTA
    ========================================================== -->

<section
        class="bt-burgers-cta"
>

    <div
            class="bt-burgers-cta-glow"
    ></div>


    <div
            class="bt-burgers-cta-content"
    >

            <span>

                BURGER TOWN

            </span>


        <h2>

            Ya viste la carta.

            <strong>
                Ahora falta probarla.
            </strong>

        </h2>


        <p>

            Elige tu sede favorita en Bogotá
            y reserva tu mesa.

        </p>


        <div
                class="bt-burgers-cta-actions"
        >

            <a
                    href="<?= APP_URL ?>/reservas"
                    class="bt-burgers-primary-button"
            >

                <i
                        class="fa-solid fa-calendar-check"
                ></i>

                Reservar mesa

            </a>


            <a
                    href="<?= APP_URL ?>/menu"
                    class="bt-burgers-secondary-button"
            >

                Ver menú completo

                <i
                        class="fa-solid fa-arrow-right"
                ></i>

            </a>

        </div>

    </div>

</section>


<script>
    document.addEventListener('DOMContentLoaded', function () {

        const slider = document.querySelector('[data-hero-slider]');

        if (!slider) {
            return;
        }

        const slides = Array.from(
            slider.querySelectorAll('.bt-hero-slide')
        );

        if (slides.length <= 1) {
            return;
        }

        const prevButton =
            slider.querySelector('[data-hero-prev]');

        const nextButton =
            slider.querySelector('[data-hero-next]');

        const currentCounter =
            slider.querySelector('[data-hero-current]');

        const progressBar =
            slider.querySelector('[data-hero-progress]');


        /*
        |--------------------------------------------------------------------------
        | CONFIGURACIÓN
        |--------------------------------------------------------------------------
        */

        const AUTOPLAY_TIME = 5000;

        let currentIndex = 0;

        let autoplayTimer = null;

        let progressTimer = null;

        let startX = 0;

        let endX = 0;


        /*
        |--------------------------------------------------------------------------
        | MOSTRAR SLIDE
        |--------------------------------------------------------------------------
        */

        function showSlide(index) {

            if (index < 0) {
                index = slides.length - 1;
            }

            if (index >= slides.length) {
                index = 0;
            }

            slides.forEach(function (slide, i) {

                slide.classList.toggle(
                    'is-active',
                    i === index
                );

            });

            currentIndex = index;


            /*
            | Contador
            */

            if (currentCounter) {

                currentCounter.textContent =
                    String(index + 1).padStart(2, '0');

            }


            /*
            | Reiniciar barra
            */

            resetProgress();

        }


        /*
        |--------------------------------------------------------------------------
        | SIGUIENTE
        |--------------------------------------------------------------------------
        */

        function nextSlide() {

            showSlide(
                currentIndex + 1
            );

        }


        /*
        |--------------------------------------------------------------------------
        | ANTERIOR
        |--------------------------------------------------------------------------
        */

        function previousSlide() {

            showSlide(
                currentIndex - 1
            );

        }


        /*
        |--------------------------------------------------------------------------
        | AUTOPLAY
        |--------------------------------------------------------------------------
        */

        function startAutoplay() {

            stopAutoplay();

            autoplayTimer =
                setInterval(
                    function () {

                        nextSlide();

                    },
                    AUTOPLAY_TIME
                );

        }


        function stopAutoplay() {

            if (autoplayTimer) {

                clearInterval(
                    autoplayTimer
                );

                autoplayTimer = null;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | BARRA DE PROGRESO
        |--------------------------------------------------------------------------
        */

        function resetProgress() {

            if (!progressBar) {
                return;
            }

            if (progressTimer) {

                cancelAnimationFrame(
                    progressTimer
                );

            }

            const startTime =
                performance.now();


            function animateProgress(
                currentTime
            ) {

                const elapsed =
                    currentTime - startTime;

                const percentage =
                    Math.min(
                        (elapsed / AUTOPLAY_TIME) * 100,
                        100
                    );

                progressBar.style.width =
                    percentage + '%';


                if (percentage < 100) {

                    progressTimer =
                        requestAnimationFrame(
                            animateProgress
                        );

                }

            }


            progressBar.style.width = '0%';

            progressTimer =
                requestAnimationFrame(
                    animateProgress
                );

        }


        /*
        |--------------------------------------------------------------------------
        | BOTONES
        |--------------------------------------------------------------------------
        */

        if (nextButton) {

            nextButton.addEventListener(
                'click',
                function () {

                    nextSlide();

                    startAutoplay();

                }
            );

        }


        if (prevButton) {

            prevButton.addEventListener(
                'click',
                function () {

                    previousSlide();

                    startAutoplay();

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | PAUSAR AL PASAR EL MOUSE
        |--------------------------------------------------------------------------
        */

        slider.addEventListener(
            'mouseenter',
            function () {

                stopAutoplay();

                if (progressTimer) {

                    cancelAnimationFrame(
                        progressTimer
                    );

                }

            }
        );


        slider.addEventListener(
            'mouseleave',
            function () {

                startAutoplay();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | TOUCH / SWIPE
        |--------------------------------------------------------------------------
        */

        slider.addEventListener(
            'touchstart',
            function (event) {

                startX =
                    event.touches[0].clientX;

                stopAutoplay();

            },
            {
                passive: true
            }
        );


        slider.addEventListener(
            'touchend',
            function (event) {

                endX =
                    event.changedTouches[0].clientX;

                const difference =
                    startX - endX;


                /*
                | Swipe hacia la izquierda
                */

                if (difference > 50) {

                    nextSlide();

                }


                /*
                | Swipe hacia la derecha
                */

                else if (difference < -50) {

                    previousSlide();

                }


                startAutoplay();

            },
            {
                passive: true
            }
        );


        /*
        |--------------------------------------------------------------------------
        | TECLADO
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'keydown',
            function (event) {

                /*
                | Evitamos interferir con inputs
                */

                const tag =
                    event.target.tagName.toLowerCase();

                if (
                    tag === 'input' ||
                    tag === 'textarea' ||
                    tag === 'select'
                ) {
                    return;
                }


                if (event.key === 'ArrowRight') {

                    nextSlide();

                    startAutoplay();

                }


                if (event.key === 'ArrowLeft') {

                    previousSlide();

                    startAutoplay();

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | INICIAR
        |--------------------------------------------------------------------------
        */

        showSlide(0);

        startAutoplay();

    });

    document.addEventListener('DOMContentLoaded', function () {

        const slider =
            document.querySelector(
                '[data-featured-slider]'
            );

        if (!slider) {
            return;
        }


        const track =
            slider.querySelector(
                '[data-featured-track]'
            );

        const slides =
            Array.from(
                slider.querySelectorAll(
                    '[data-featured-slide]'
                )
            );

        const prevButton =
            slider.querySelector(
                '[data-featured-prev]'
            );

        const nextButton =
            slider.querySelector(
                '[data-featured-next]'
            );

        const dots =
            Array.from(
                slider.querySelectorAll(
                    '[data-featured-dot]'
                )
            );

        const currentCounter =
            slider.querySelector(
                '[data-featured-current]'
            );


        if (
            !track ||
            slides.length === 0
        ) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | CONFIGURACIÓN
        |--------------------------------------------------------------------------
        */

        const AUTOPLAY =
            5000;

        let currentIndex =
            0;

        let autoplayTimer =
            null;

        let startX =
            0;

        let endX =
            0;


        /*
        |--------------------------------------------------------------------------
        | OBTENER ANCHO DEL SLIDE
        |--------------------------------------------------------------------------
        */

        function getSlideStep() {

            if (!slides[0]) {
                return 0;
            }

            const style =
                window.getComputedStyle(
                    track
                );

            const gap =
                parseFloat(
                    style.columnGap
                ) || 0;

            return (
                slides[0].offsetWidth
                + gap
            );
        }


        /*
        |--------------------------------------------------------------------------
        | ACTUALIZAR SLIDER
        |--------------------------------------------------------------------------
        */

        function updateSlider() {

            const step =
                getSlideStep();

            const viewport =
                slider.querySelector(
                    '.bt-intro-slider-viewport'
                );

            if (!viewport) {
                return;
            }


            const viewportCenter =
                viewport.offsetWidth / 2;

            const slideCenter =
                slides[0].offsetWidth / 2;


            const offset =
                viewportCenter
                - slideCenter
                - (
                    currentIndex
                    * step
                );


            track.style.transform =
                `translate(
                calc(-50% + ${offset}px),
                -50%
            )`;


            slides.forEach(
                function (
                    slide,
                    index
                ) {

                    slide.classList.toggle(
                        'is-active',
                        index === currentIndex
                    );

                }
            );


            dots.forEach(
                function (
                    dot,
                    index
                ) {

                    dot.classList.toggle(
                        'is-active',
                        index === currentIndex
                    );

                }
            );


            if (currentCounter) {

                currentCounter.textContent =
                    String(
                        currentIndex + 1
                    ).padStart(
                        2,
                        '0'
                    );

            }

        }


        /*
        |--------------------------------------------------------------------------
        | IR A SLIDE
        |--------------------------------------------------------------------------
        */

        function goToSlide(
            index
        ) {

            if (index < 0) {

                index =
                    slides.length - 1;

            }

            if (
                index >= slides.length
            ) {

                index = 0;

            }

            currentIndex =
                index;

            updateSlider();

        }


        /*
        |--------------------------------------------------------------------------
        | SIGUIENTE
        |--------------------------------------------------------------------------
        */

        function nextSlide() {

            goToSlide(
                currentIndex + 1
            );

        }


        /*
        |--------------------------------------------------------------------------
        | ANTERIOR
        |--------------------------------------------------------------------------
        */

        function previousSlide() {

            goToSlide(
                currentIndex - 1
            );

        }


        /*
        |--------------------------------------------------------------------------
        | AUTOPLAY
        |--------------------------------------------------------------------------
        */

        function startAutoplay() {

            stopAutoplay();

            if (
                slides.length <= 1
            ) {
                return;
            }

            autoplayTimer =
                setInterval(
                    function () {

                        nextSlide();

                    },
                    AUTOPLAY
                );

        }


        function stopAutoplay() {

            if (
                autoplayTimer
            ) {

                clearInterval(
                    autoplayTimer
                );

                autoplayTimer =
                    null;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | BOTÓN ANTERIOR
        |--------------------------------------------------------------------------
        */

        if (prevButton) {

            prevButton.addEventListener(
                'click',
                function () {

                    previousSlide();

                    startAutoplay();

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | BOTÓN SIGUIENTE
        |--------------------------------------------------------------------------
        */

        if (nextButton) {

            nextButton.addEventListener(
                'click',
                function () {

                    nextSlide();

                    startAutoplay();

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | DOTS
        |--------------------------------------------------------------------------
        */

        dots.forEach(
            function (
                dot,
                index
            ) {

                dot.addEventListener(
                    'click',
                    function () {

                        goToSlide(
                            index
                        );

                        startAutoplay();

                    }
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | PAUSAR AL PASAR EL MOUSE
        |--------------------------------------------------------------------------
        */

        slider.addEventListener(
            'mouseenter',
            function () {

                stopAutoplay();

            }
        );


        slider.addEventListener(
            'mouseleave',
            function () {

                startAutoplay();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | SWIPE
        |--------------------------------------------------------------------------
        */

        slider.addEventListener(
            'touchstart',
            function (
                event
            ) {

                startX =
                    event.touches[0]
                        .clientX;

                stopAutoplay();

            },
            {
                passive: true
            }
        );


        slider.addEventListener(
            'touchend',
            function (
                event
            ) {

                endX =
                    event.changedTouches[0]
                        .clientX;


                const difference =
                    startX - endX;


                if (
                    difference > 45
                ) {

                    nextSlide();

                }
                else if (
                    difference < -45
                ) {

                    previousSlide();

                }


                startAutoplay();

            },
            {
                passive: true
            }
        );


        /*
        |--------------------------------------------------------------------------
        | REDIMENSIONAMIENTO
        |--------------------------------------------------------------------------
        */

        let resizeTimer = null;

        window.addEventListener(
            'resize',
            function () {

                clearTimeout(
                    resizeTimer
                );

                resizeTimer =
                    setTimeout(
                        function () {

                            updateSlider();

                        },
                        150
                    );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | INICIAR
        |--------------------------------------------------------------------------
        */

        updateSlider();

        startAutoplay();

    });

    document.addEventListener('DOMContentLoaded', function () {

        const navSection =
            document.querySelector(
                '.bt-burgers-nav-section'
            );

        const nav =
            document.querySelector(
                '.bt-burgers-category-nav'
            );


        if (!navSection || !nav) {
            return;
        }


        const navLinks =
            Array.from(
                nav.querySelectorAll(
                    'a[href^="#"]'
                )
            );


        if (!navLinks.length) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | OBTENER NAVBAR PRINCIPAL
        |--------------------------------------------------------------------------
        */

        const mainNavbar =
            document.querySelector(
                'header'
            ) ||
            document.querySelector(
                '.navbar'
            ) ||
            document.querySelector(
                '.bt-navbar'
            );


        /*
        |--------------------------------------------------------------------------
        | CALCULAR ALTURA DEL NAVBAR
        |--------------------------------------------------------------------------
        */

        function updateNavbarHeight() {

            let navbarHeight = 0;


            if (mainNavbar) {

                navbarHeight =
                    mainNavbar.getBoundingClientRect().height;

            }


            /*
            | Guardamos la altura como variable CSS
            */

            document.documentElement.style.setProperty(
                '--bt-main-navbar-height',
                navbarHeight + 'px'
            );


            navSection.style.setProperty(
                '--bt-category-nav-top',
                navbarHeight + 'px'
            );

        }


        updateNavbarHeight();


        /*
        |--------------------------------------------------------------------------
        | RESIZE
        |--------------------------------------------------------------------------
        */

        window.addEventListener(
            'resize',
            updateNavbarHeight
        );


        /*
        |--------------------------------------------------------------------------
        | DETECTAR SECCIONES
        |--------------------------------------------------------------------------
        */

        const sections = [];


        navLinks.forEach(
            function (link) {

                const href =
                    link.getAttribute(
                        'href'
                    );


                if (
                    !href ||
                    href === '#'
                ) {
                    return;
                }


                const section =
                    document.querySelector(
                        href
                    );


                if (!section) {
                    return;
                }


                sections.push({
                    link: link,
                    section: section
                });

            }
        );


        if (!sections.length) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | ACTIVAR ITEM
        |--------------------------------------------------------------------------
        */

        function setActiveLink(
            activeLink
        ) {

            navLinks.forEach(
                function (link) {

                    link.classList.remove(
                        'active',
                        'is-active'
                    );

                }
            );


            if (!activeLink) {
                return;
            }


            activeLink.classList.add(
                'active'
            );


        }


        /*
        |--------------------------------------------------------------------------
        | OBSERVER
        |--------------------------------------------------------------------------
        */

        const observer =
            new IntersectionObserver(
                function (entries) {

                    /*
                    | Buscamos la sección más visible
                    */

                    let mostVisible =
                        null;

                    let highestRatio =
                        0;


                    entries.forEach(
                        function (entry) {

                            if (
                                entry.isIntersecting &&
                                entry.intersectionRatio
                                > highestRatio
                            ) {

                                highestRatio =
                                    entry.intersectionRatio;

                                mostVisible =
                                    entry.target;

                            }

                        }
                    );


                    if (!mostVisible) {
                        return;
                    }


                    const item =
                        sections.find(
                            function (item) {

                                return (
                                    item.section ===
                                    mostVisible
                                );

                            }
                        );


                    if (item) {

                        setActiveLink(
                            item.link
                        );

                    }

                },
                {
                    root: null,

                    /*
                    | Dejamos espacio para navbar
                    | + navegación sticky
                    */

                    rootMargin:
                        '-140px 0px -45% 0px',

                    threshold: [
                        0.1,
                        0.25,
                        0.5,
                        0.75
                    ]

                }
            );


        /*
        |--------------------------------------------------------------------------
        | OBSERVAR SECCIONES
        |--------------------------------------------------------------------------
        */

        sections.forEach(
            function (item) {

                observer.observe(
                    item.section
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | CLICK EN ITEM
        |--------------------------------------------------------------------------
        */

        navLinks.forEach(
            function (link) {

                link.addEventListener(
                    'click',
                    function (event) {

                        const href =
                            link.getAttribute(
                                'href'
                            );


                        if (
                            !href ||
                            href === '#'
                        ) {
                            return;
                        }


                        const target =
                            document.querySelector(
                                href
                            );


                        if (!target) {
                            return;
                        }


                        event.preventDefault();


                        /*
                        | Altura actual del navbar
                        */

                        const navbarHeight =
                            mainNavbar
                                ? mainNavbar.getBoundingClientRect().height
                                : 0;


                        const navHeight =
                            navSection.getBoundingClientRect().height;


                        /*
                        | Posición final
                        */

                        const targetTop =
                            target.getBoundingClientRect().top
                            +
                            window.scrollY
                            -
                            navbarHeight
                            -
                            navHeight
                            -
                            12;


                        window.scrollTo({

                            top: targetTop,

                            behavior: 'smooth'

                        });


                        setActiveLink(
                            link
                        );

                    }
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | ESTADO VISUAL STICKY
        |--------------------------------------------------------------------------
        */

        const navInitialTop =
            navSection.getBoundingClientRect().top
            +
            window.scrollY;


        function updateStickyState() {

            const navbarHeight =
                mainNavbar
                    ? mainNavbar.getBoundingClientRect().height
                    : 0;


            if (
                window.scrollY
                >=
                navInitialTop
                -
                navbarHeight
            ) {

                navSection.classList.add(
                    'is-sticky'
                );

            }
            else {

                navSection.classList.remove(
                    'is-sticky'
                );

            }

        }


        window.addEventListener(
            'scroll',
            updateStickyState,
            {
                passive: true
            }
        );


        updateStickyState();


        /*
        |--------------------------------------------------------------------------
        | SECCIÓN INICIAL
        |--------------------------------------------------------------------------
        */

        let initialSection =
            null;

        let initialDistance =
            Infinity;


        sections.forEach(
            function (item) {

                const rect =
                    item.section.getBoundingClientRect();


                const distance =
                    Math.abs(
                        rect.top -
                        (
                            window.innerHeight * .30
                        )
                    );


                if (
                    distance <
                    initialDistance
                ) {

                    initialDistance =
                        distance;

                    initialSection =
                        item;

                }

            }
        );


        if (initialSection) {

            setActiveLink(
                initialSection.link
            );

        }

    });

    document.addEventListener(
        'DOMContentLoaded',
        function () {

            const slider =
                document.querySelector(
                    '[data-recommendation-slider]'
                );

            if (!slider) {
                return;
            }


            const slides =
                Array.from(
                    slider.querySelectorAll(
                        '[data-recommendation-slide]'
                    )
                );


            const prevButton =
                document.querySelector(
                    '[data-recommendation-prev]'
                );


            const nextButton =
                document.querySelector(
                    '[data-recommendation-next]'
                );


            const dots =
                Array.from(
                    slider.querySelectorAll(
                        '[data-recommendation-dot]'
                    )
                );


            const counter =
                document.querySelector(
                    '[data-recommendation-current]'
                );


            if (!slides.length) {
                return;
            }


            let currentIndex = 0;

            let autoplayTimer = null;

            const autoplayDelay = 6000;


            /*
            |--------------------------------------------------------------------------
            | ACTUALIZAR
            |--------------------------------------------------------------------------
            */

            function updateRecommendation() {

                slides.forEach(
                    function (
                        slide,
                        index
                    ) {

                        slide.classList.toggle(
                            'is-active',
                            index === currentIndex
                        );

                    }
                );


                dots.forEach(
                    function (
                        dot,
                        index
                    ) {

                        dot.classList.toggle(
                            'is-active',
                            index === currentIndex
                        );

                    }
                );


                if (counter) {

                    counter.textContent =
                        String(
                            currentIndex + 1
                        ).padStart(
                            2,
                            '0'
                        );

                }

            }


            /*
            |--------------------------------------------------------------------------
            | IR AL SIGUIENTE
            |--------------------------------------------------------------------------
            */

            function nextRecommendation() {

                currentIndex =
                    (
                        currentIndex + 1
                    )
                    %
                    slides.length;

                updateRecommendation();

            }


            /*
            |--------------------------------------------------------------------------
            | IR AL ANTERIOR
            |--------------------------------------------------------------------------
            */

            function previousRecommendation() {

                currentIndex =
                    (
                        currentIndex - 1
                        + slides.length
                    )
                    %
                    slides.length;

                updateRecommendation();

            }


            /*
            |--------------------------------------------------------------------------
            | AUTOPLAY
            |--------------------------------------------------------------------------
            */

            function startAutoplay() {

                stopAutoplay();


                if (
                    slides.length <= 1
                ) {

                    return;

                }


                autoplayTimer =
                    setInterval(
                        function () {

                            nextRecommendation();

                        },
                        autoplayDelay
                    );

            }


            function stopAutoplay() {

                if (
                    autoplayTimer
                ) {

                    clearInterval(
                        autoplayTimer
                    );

                    autoplayTimer = null;

                }

            }


            /*
            |--------------------------------------------------------------------------
            | BOTÓN ANTERIOR
            |--------------------------------------------------------------------------
            */

            if (prevButton) {

                prevButton.addEventListener(
                    'click',
                    function () {

                        previousRecommendation();

                        startAutoplay();

                    }
                );

            }


            /*
            |--------------------------------------------------------------------------
            | BOTÓN SIGUIENTE
            |--------------------------------------------------------------------------
            */

            if (nextButton) {

                nextButton.addEventListener(
                    'click',
                    function () {

                        nextRecommendation();

                        startAutoplay();

                    }
                );

            }


            /*
            |--------------------------------------------------------------------------
            | DOTS
            |--------------------------------------------------------------------------
            */

            dots.forEach(
                function (
                    dot,
                    index
                ) {

                    dot.addEventListener(
                        'click',
                        function () {

                            currentIndex =
                                index;

                            updateRecommendation();

                            startAutoplay();

                        }
                    );

                }
            );


            /*
            |--------------------------------------------------------------------------
            | PAUSAR AL PASAR EL MOUSE
            |--------------------------------------------------------------------------
            */

            slider.addEventListener(
                'mouseenter',
                function () {

                    stopAutoplay();

                }
            );


            slider.addEventListener(
                'mouseleave',
                function () {

                    startAutoplay();

                }
            );


            /*
            |--------------------------------------------------------------------------
            | SWIPE
            |--------------------------------------------------------------------------
            */

            let touchStartX = 0;

            let touchEndX = 0;


            slider.addEventListener(
                'touchstart',
                function (event) {

                    touchStartX =
                        event.touches[0].clientX;

                    stopAutoplay();

                },
                {
                    passive: true
                }
            );


            slider.addEventListener(
                'touchend',
                function (event) {

                    touchEndX =
                        event.changedTouches[0].clientX;


                    const difference =
                        touchStartX
                        -
                        touchEndX;


                    if (
                        difference > 45
                    ) {

                        nextRecommendation();

                    }
                    else if (
                        difference < -45
                    ) {

                        previousRecommendation();

                    }


                    startAutoplay();

                },
                {
                    passive: true
                }
            );


            /*
            |--------------------------------------------------------------------------
            | INICIALIZAR
            |--------------------------------------------------------------------------
            */

            updateRecommendation();

            startAutoplay();

        }
    );
</script>