<?php
$escapeStore = static fn ($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$formatStorePrice = static fn ($value): string => '$' . number_format((float) ($value ?? 0), 0, ',', '.');

$categories = is_array($storeCategories ?? null) ? $storeCategories : [];
$products = is_array($storeProducts ?? null) ? $storeProducts : [];
$selectedCategory = (int) ($storeSelectedCategory ?? 0);
$productCount = count($products);

$imageUrlForStore = static function ($path): string {
    $path = ltrim(trim((string) ($path ?? '')), '/\\');
    if ($path === '') {
        return '';
    }

    return rtrim(APP_URL, '/') . '/public/' . $path;
};

$categoryIsSelected = static function (array $category) use ($selectedCategory): bool {
    return $selectedCategory > 0 && (int) ($category['id'] ?? 0) === $selectedCategory;
};
?>

<main class="storefront-shell" data-store-catalog data-initial-category="<?= $selectedCategory ?>">
    <section class="storefront-hero">
        <div class="container">
            <div class="storefront-hero-content">
                <div class="storefront-hero-copy">
                    <span class="storefront-eyebrow"><i class="fa-solid fa-bag-shopping"></i> TIENDA OFICIAL</span>
                    <h1>Todo lo que te gusta,<br><em>en un solo lugar.</em></h1>
                    <p>Explora nuestros productos, elige tus presentaciones favoritas y arma tu pedido en pocos pasos.</p>
                    <div class="storefront-hero-actions">
                        <a href="#storeCatalogGrid" class="storefront-hero-button">Explorar productos <i class="fa-solid fa-arrow-down"></i></a>
                        <a href="<?= htmlspecialchars(APP_URL . '/menu', ENT_QUOTES, 'UTF-8') ?>" class="storefront-hero-link">Conocer el menú <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                    <div class="storefront-hero-benefits">
                        <span><i class="fa-solid fa-check"></i> Productos disponibles</span>
                        <span><i class="fa-solid fa-check"></i> Recogida en sede</span>
                        <span><i class="fa-solid fa-check"></i> Pago seguro</span>
                    </div>
                </div>
                <div class="storefront-hero-art" aria-hidden="true">
                    <div class="storefront-hero-orbit storefront-hero-orbit-one"></div>
                    <div class="storefront-hero-orbit storefront-hero-orbit-two"></div>
                    <div class="storefront-hero-art-card">
                        <span class="storefront-hero-art-icon"><i class="fa-solid fa-burger"></i></span>
                        <span class="storefront-hero-art-kicker">HECHO PARA DISFRUTAR</span>
                        <strong>Tu próximo<br>favorito está aquí.</strong>
                        <span class="storefront-hero-art-line"></span>
                        <small>Elige · Personaliza · Disfruta</small>
                    </div>
                    <span class="storefront-hero-spark storefront-hero-spark-a"><i class="fa-solid fa-star"></i></span>
                    <span class="storefront-hero-spark storefront-hero-spark-b"><i class="fa-solid fa-plus"></i></span>
                </div>
            </div>
        </div>
    </section>

    <section class="container storefront-main" id="storeCatalogGrid">
        <div class="storefront-heading-row">
            <div>
                <span class="storefront-section-kicker">CATÁLOGO</span>
                <h2>Encuentra tu favorito</h2>
                <p>Productos preparados para que elijas exactamente lo que quieres.</p>
            </div>
            <div class="storefront-catalog-count">
                <strong><?= $productCount ?></strong>
                <span><?= $productCount === 1 ? 'producto disponible' : 'productos disponibles' ?></span>
            </div>
        </div>

        <?php if (!empty($storeCatalogError)): ?>
            <div class="storefront-alert" role="alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?= $escapeStore($storeCatalogError) ?></span>
            </div>
        <?php endif; ?>

        <div class="storefront-toolbar">
            <label class="storefront-search" for="storeProductSearch">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input
                    type="search"
                    id="storeProductSearch"
                    placeholder="Buscar hamburguesas, acompañamientos..."
                    autocomplete="off"
                    aria-label="Buscar productos"
                >
                <button type="button" class="storefront-search-clear" data-store-search-clear aria-label="Limpiar búsqueda">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </label>
            <label class="storefront-sort">
                <span>Ordenar por</span>
                <select id="storeProductSort" aria-label="Ordenar productos">
                    <option value="featured">Recomendados</option>
                    <option value="name">Nombre: A a Z</option>
                    <option value="price-asc">Precio: menor a mayor</option>
                    <option value="price-desc">Precio: mayor a menor</option>
                </select>
            </label>
        </div>

        <div class="storefront-products-layout">
            <aside class="storefront-sidebar">
                <div class="storefront-sidebar-heading">
                    <span><i class="fa-solid fa-sliders"></i> Categorías</span>
                    <span class="storefront-sidebar-caption">Filtra el catálogo</span>
                </div>

                <div class="storefront-category-list">
                    <button type="button" class="storefront-category-filter <?= $selectedCategory === 0 ? 'is-active' : '' ?>" data-store-filter="all" aria-pressed="<?= $selectedCategory === 0 ? 'true' : 'false' ?>">
                        <span class="storefront-category-name"><i class="fa-solid fa-grid-2"></i> Todos los productos</span>
                        <span class="storefront-category-count"><?= $productCount ?></span>
                    </button>

                    <?php foreach ($categories as $category): ?>
                        <?php if (empty($category['parent_id'])): ?>
                            <?php $categoryId = (int) ($category['id'] ?? 0); ?>
                            <button
                                type="button"
                                class="storefront-category-filter <?= $categoryIsSelected($category) ? 'is-active' : '' ?>"
                                data-store-filter="<?= $categoryId ?>"
                                aria-pressed="<?= $categoryIsSelected($category) ? 'true' : 'false' ?>"
                            >
                                <span class="storefront-category-name"><i class="fa-solid fa-layer-group"></i> <?= $escapeStore($category['name'] ?? '') ?></span>
                                <span class="storefront-category-count"><?= (int) ($category['product_count'] ?? 0) ?></span>
                            </button>

                            <?php foreach ($categories as $childCategory): ?>
                                <?php if ((int) ($childCategory['parent_id'] ?? 0) === $categoryId): ?>
                                    <button
                                        type="button"
                                        class="storefront-category-filter is-child <?= $categoryIsSelected($childCategory) ? 'is-active' : '' ?>"
                                        data-store-filter="<?= (int) ($childCategory['id'] ?? 0) ?>"
                                        aria-pressed="<?= $categoryIsSelected($childCategory) ? 'true' : 'false' ?>"
                                    >
                                        <span class="storefront-category-name"><i class="fa-solid fa-turn-up"></i> <?= $escapeStore($childCategory['name'] ?? '') ?></span>
                                        <span class="storefront-category-count"><?= (int) ($childCategory['product_count'] ?? 0) ?></span>
                                    </button>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <div class="storefront-sidebar-note">
                    <i class="fa-solid fa-circle-info"></i>
                    <p>Las presentaciones y opciones disponibles se muestran dentro de cada producto.</p>
                </div>
            </aside>

            <div class="storefront-results">
                <div class="storefront-results-heading">
                    <div>
                        <strong id="storeProductCount"><?= $productCount ?> <?= $productCount === 1 ? 'producto' : 'productos' ?></strong>
                        <span>en el catálogo</span>
                    </div>
                    <span class="storefront-results-live"><i class="fa-solid fa-circle"></i> Disponibilidad actual</span>
                </div>

                <div class="storefront-product-grid" id="storeProductGrid">
                    <?php foreach ($products as $index => $product): ?>
                        <?php
                        $productId = (int) ($product['id'] ?? 0);
                        $categoryId = (int) ($product['category_id'] ?? 0);
                        $parentCategoryId = (int) ($product['parent_category_id'] ?? 0);
                        $productName = (string) ($product['name'] ?? 'Producto');
                        $productDescription = trim((string) ($product['description'] ?? ''));
                        $productIngredients = trim((string) ($product['ingredients'] ?? ''));
                        $categoryName = (string) ($product['category_name'] ?? 'Productos');
                        $parentCategoryName = (string) ($product['parent_category_name'] ?? $categoryName);
                        $imageUrl = $imageUrlForStore($product['favorite_image'] ?? '');
                        $imageAlt = trim((string) ($product['favorite_image_alt'] ?? $productName));
                        $priceOptions = is_array($product['price_options'] ?? null) ? $product['price_options'] : [];
                        $firstOption = $priceOptions[0] ?? null;
                        $availablePrices = array_values(array_filter(
                            array_map(static fn (array $option): float => (float) ($option['price'] ?? 0), $priceOptions),
                            static fn (float $price): bool => $price > 0
                        ));
                        $availableComboPrices = array_values(array_filter(
                            array_map(static fn (array $option): float => (float) ($option['combo_price'] ?? 0), $priceOptions),
                            static fn (float $price): bool => $price > 0
                        ));
                        $basePrice = !empty($availablePrices)
                            ? min($availablePrices)
                            : (float) ($product['price'] ?? 0);
                        $hasCombo = !empty($priceOptions)
                            ? !empty($availableComboPrices)
                            : (float) ($product['combo_price'] ?? 0) > 0;
                        $baseComboPrice = !empty($priceOptions)
                            ? (!empty($availableComboPrices) ? min($availableComboPrices) : 0)
                            : (float) ($product['combo_price'] ?? 0);

                        $cartProductJson = json_encode(
                            [
                                'id' => $productId,
                                'name' => $productName,
                                'image' => $imageUrl,
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

                        $searchText = implode(' ', [
                            $productName,
                            $productDescription,
                            $productIngredients,
                            $categoryName,
                            $parentCategoryName
                        ]);
                        ?>
                        <article
                            class="storefront-product-card"
                            data-store-product
                            data-store-category-id="<?= $categoryId ?>"
                            data-store-parent-id="<?= $parentCategoryId ?>"
                            data-store-name="<?= $escapeStore($productName) ?>"
                            data-store-search="<?= $escapeStore($searchText) ?>"
                            data-store-price="<?= htmlspecialchars((string) $basePrice, ENT_QUOTES, 'UTF-8') ?>"
                            data-store-featured="<?= (int) ($product['is_featured'] ?? 0) ?>"
                            data-store-order="<?= (int) $index ?>"
                            data-cart-product="<?= $escapeStore($cartProductJson ?: '{}') ?>"
                        >
                            <div class="storefront-product-media">
                                <?php if ($imageUrl !== ''): ?>
                                    <img src="<?= $escapeStore($imageUrl) ?>" alt="<?= $escapeStore($imageAlt) ?>" loading="lazy" draggable="false">
                                <?php else: ?>
                                    <div class="storefront-product-placeholder"><i class="fa-solid fa-burger"></i></div>
                                <?php endif; ?>

                                <?php if ((int) ($product['is_featured'] ?? 0) === 1): ?>
                                    <span class="storefront-product-badge"><i class="fa-solid fa-star"></i> Destacado</span>
                                <?php endif; ?>

                                <span class="storefront-product-category"><?= $escapeStore($categoryName) ?></span>
                            </div>

                            <div class="storefront-product-body">
                                <div class="storefront-product-title-row">
                                    <div>
                                        <span class="storefront-product-parent"><?= $escapeStore($parentCategoryName) ?></span>
                                        <h3><?= $escapeStore($productName) ?></h3>
                                    </div>
                                </div>

                                <?php if ($productDescription !== ''): ?>
                                    <p class="storefront-product-description"><?= $escapeStore($productDescription) ?></p>
                                <?php elseif ($productIngredients !== ''): ?>
                                    <p class="storefront-product-description"><?= $escapeStore($productIngredients) ?></p>
                                <?php else: ?>
                                    <p class="storefront-product-description">Una opción para disfrutar. Selecciona tu presentación y agrégala al pedido.</p>
                                <?php endif; ?>

                                <div class="storefront-product-price-row">
                                    <div>
                                        <span><?= !empty($priceOptions) ? 'DESDE' : 'PRECIO' ?></span>
                                        <strong data-store-current-price><?= $formatStorePrice($basePrice) ?></strong>
                                    </div>
                                    <?php if ($hasCombo): ?>
                                        <div class="storefront-combo-price">
                                            <span>COMBO DESDE</span>
                                            <strong data-store-current-combo><?= $formatStorePrice($baseComboPrice) ?></strong>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="storefront-cart-controls">
                                    <?php if (!empty($priceOptions)): ?>
                                        <label class="storefront-control-label">
                                            Presentación
                                            <select class="form-select" data-cart-option aria-label="Seleccionar presentación para <?= $escapeStore($productName) ?>">
                                                <?php foreach ($priceOptions as $option): ?>
                                                    <?php
                                                    $optionQuantity = (int) ($option['quantity'] ?? 1);
                                                    $optionLabel = trim((string) ($option['quantity_label'] ?? ''));
                                                    if ($optionLabel === '') {
                                                        $optionLabel = $optionQuantity . ' unidades';
                                                    } elseif (stripos($optionLabel, (string) $optionQuantity) === false) {
                                                        $optionLabel = $optionQuantity . ' ' . $optionLabel;
                                                    }
                                                    ?>
                                                    <option
                                                        value="<?= (int) ($option['id'] ?? 0) ?>"
                                                        data-price="<?= htmlspecialchars((string) ($option['price'] ?? 0), ENT_QUOTES, 'UTF-8') ?>"
                                                        data-combo-price="<?= htmlspecialchars((string) ($option['combo_price'] ?? 0), ENT_QUOTES, 'UTF-8') ?>"
                                                        data-label="<?= $escapeStore($optionLabel) ?>"
                                                        data-quantity="<?= $optionQuantity ?>"
                                                    ><?= $escapeStore($optionLabel) ?> · <?= $formatStorePrice($option['price'] ?? 0) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </label>
                                    <?php endif; ?>

                                    <?php if ($hasCombo): ?>
                                        <label class="storefront-combo-toggle">
                                            <input type="checkbox" data-cart-combo <?= !empty($priceOptions) && (float) ($firstOption['combo_price'] ?? 0) <= 0 ? 'disabled' : '' ?>>
                                            <span>Convertir en combo</span>
                                        </label>
                                    <?php endif; ?>

                                    <div class="storefront-add-row">
                                        <label class="storefront-quantity-field">
                                            <span class="visually-hidden">Cantidad</span>
                                            <input type="number" class="form-control" min="1" max="99" value="1" data-cart-quantity aria-label="Cantidad de <?= $escapeStore($productName) ?>">
                                        </label>
                                        <button type="button" class="storefront-add-button" data-cart-add>
                                            <i class="fa-solid fa-cart-plus"></i>
                                            <span>Agregar</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="storefront-no-results <?= $productCount > 0 ? 'd-none' : '' ?>" id="storeProductsEmpty">
                    <span><i class="fa-solid fa-magnifying-glass"></i></span>
                    <h3>No encontramos productos</h3>
                    <p>Prueba con otra búsqueda o selecciona una categoría diferente.</p>
                    <button type="button" data-store-reset-filters>Ver todos los productos</button>
                </div>
            </div>
        </div>

        <section class="storefront-bottom-banner">
            <div>
                <span class="storefront-section-kicker">¿YA SABES QUÉ QUIERES?</span>
                <h2>Arma tu pedido a tu manera.</h2>
                <p>Agrega tus productos al carrito y elige la sede donde quieres recogerlos.</p>
            </div>
            <a href="<?= htmlspecialchars(APP_URL . '/carrito', ENT_QUOTES, 'UTF-8') ?>">Ir al carrito <i class="fa-solid fa-arrow-right"></i></a>
        </section>
    </section>
</main>
