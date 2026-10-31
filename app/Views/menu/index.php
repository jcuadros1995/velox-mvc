<?php
/**
 * Catálogo público principal.
 * El listado proviene exclusivamente de páginas publicadas y categorías activas.
 */
$menuCategories = is_array($menuCategories ?? null) ? $menuCategories : [];
$menuBurgerPage = is_array($menuBurgerPage ?? null) ? $menuBurgerPage : null;
$menuPdfAvailable = !empty($menuPdfAvailable);

$resolveMenuImage = static function ($path): string {
    $path = trim(str_replace('\\', '/', (string) $path));
    $path = ltrim($path, '/');

    if (
        $path === '' ||
        str_contains($path, '..') ||
        !preg_match('#^(uploads|assets)/[a-zA-Z0-9._/-]+$#', $path)
    ) {
        return '';
    }

    return rtrim(APP_URL, '/') . '/public/' . $path;
};

$burgerImage = $resolveMenuImage($menuBurgerPage['cover_image'] ?? '');
if ($burgerImage === '') {
    $burgerImage = APP_URL . '/assets/img/public/REQUISICION_WEB_BURGER_MASTER_21.webp';
}

$burgerPageSlug = trim((string) ($menuBurgerPage['page_slug'] ?? ''));
$burgerUrl = $burgerPageSlug !== ''
    ? APP_URL . '/menu/' . rawurlencode($burgerPageSlug)
    : APP_URL . '/menu/hamburguesas';

$menuPdfUrl = APP_URL . '/assets/docs/menu-burguer-town.pdf';
?>

<main class="velox-menu-page">
    <section class="velox-menu-hero">
        <div class="velox-menu-hero-backdrop" aria-hidden="true"></div>
        <div class="container velox-menu-hero-inner">
            <div class="velox-menu-hero-copy">
                <span class="velox-menu-eyebrow">
                    <span class="velox-menu-eyebrow-dot"></span>
                    <?= htmlspecialchars(SiteConfig::siteName(), ENT_QUOTES, 'UTF-8') ?>
                    <span class="velox-menu-eyebrow-separator">/</span>
                    CARTA
                </span>

                <h1>Encuentra tu próximo <span>favorito.</span></h1>
                <p>Explora nuestras categorías, descubre las preparaciones de la casa y elige qué se te antoja hoy.</p>

                <div class="velox-menu-hero-actions">
                    <a href="#menu-catalogo" class="btn btn-velox-primary">
                        Explorar el menú <i class="fa-solid fa-arrow-down ms-2"></i>
                    </a>
                    <a href="<?= htmlspecialchars(APP_URL . '/reservas', ENT_QUOTES, 'UTF-8') ?>" class="velox-menu-outline-button">
                        <i class="fa-regular fa-calendar-check me-2"></i>Reservar una mesa
                    </a>
                </div>

                <div class="velox-menu-hero-footnote">
                    <span><i class="fa-solid fa-utensils"></i> Preparaciones de la casa</span>
                    <span><i class="fa-solid fa-bag-shopping"></i> Compra en línea</span>
                    <span><i class="fa-solid fa-location-dot"></i> Elige tu sede</span>
                </div>
            </div>

            <a class="velox-menu-hero-feature" href="<?= htmlspecialchars($burgerUrl, ENT_QUOTES, 'UTF-8') ?>">
                <img src="<?= htmlspecialchars($burgerImage, ENT_QUOTES, 'UTF-8') ?>" alt="Explora las hamburguesas de la casa" loading="eager">
                <span class="velox-menu-feature-overlay"></span>
                <span class="velox-menu-feature-content">
                    <span class="velox-menu-feature-label">DESTACADO</span>
                    <strong>Hamburguesas</strong>
                    <span>Conoce nuestras recetas <i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                </span>
            </a>
        </div>
    </section>

    <section class="velox-menu-catalog-section" id="menu-catalogo">
        <div class="container">
            <div class="velox-menu-section-heading">
                <div>
                    <span class="velox-menu-section-kicker">EXPLORA LA CARTA</span>
                    <h2>¿Qué se te antoja <span>hoy?</span></h2>
                    <p>Entra a una categoría para ver sus productos, presentaciones, precios y recomendaciones.</p>
                </div>
                <div class="velox-menu-category-total">
                    <strong><?= count($menuCategories) + 1 ?></strong>
                    <span>formas de<br>descubrir el menú</span>
                </div>
            </div>

            <div class="velox-menu-search-row">
                <label class="velox-menu-search" for="menuCategorySearch">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input type="search" id="menuCategorySearch" placeholder="Buscar una categoría..." autocomplete="off">
                    <kbd>Ctrl K</kbd>
                </label>
                <span class="velox-menu-search-hint">Busca por nombre o descripción</span>
            </div>

            <div class="velox-menu-category-grid" id="menuCategoryGrid">
                <a
                    class="velox-menu-category-card velox-menu-category-card-featured"
                    href="<?= htmlspecialchars($burgerUrl, ENT_QUOTES, 'UTF-8') ?>"
                    data-menu-card
                    data-menu-search="<?= htmlspecialchars(mb_strtolower('Hamburguesas recetas de la casa burgers', 'UTF-8'), ENT_QUOTES, 'UTF-8') ?>"
                >
                    <div class="velox-menu-category-image">
                        <img src="<?= htmlspecialchars($burgerImage, ENT_QUOTES, 'UTF-8') ?>" alt="Hamburguesas" loading="lazy">
                    </div>
                    <div class="velox-menu-category-shade"></div>
                    <div class="velox-menu-category-content">
                        <span class="velox-menu-category-tag">LA ESPECIALIDAD</span>
                        <h3>Hamburguesas</h3>
                        <p>Recetas clásicas y creaciones de la casa.</p>
                        <span class="velox-menu-category-link">Explorar categoría <i class="fa-solid fa-arrow-right"></i></span>
                    </div>
                </a>

                <?php foreach ($menuCategories as $index => $category): ?>
                    <?php
                    $categoryName = trim((string) ($category['category_name'] ?? $category['page_title'] ?? 'Categoría'));
                    $categoryTitle = trim((string) ($category['page_title'] ?? $categoryName));
                    $categoryDescription = trim((string) ($category['page_description'] ?? $category['category_description'] ?? ''));
                    $categoryImage = $resolveMenuImage($category['cover_image'] ?? '');
                    $categoryUrl = APP_URL . '/menu/' . rawurlencode((string) ($category['page_slug'] ?? ''));
                    $categorySearch = mb_strtolower(
                        $categoryName . ' ' . $categoryTitle . ' ' . $categoryDescription . ' ' . (string) ($category['parent_name'] ?? ''),
                        'UTF-8'
                    );
                    ?>
                    <a
                        class="velox-menu-category-card <?= $categoryImage === '' ? 'velox-menu-category-card-no-image' : '' ?>"
                        href="<?= htmlspecialchars($categoryUrl, ENT_QUOTES, 'UTF-8') ?>"
                        data-menu-card
                        data-menu-search="<?= htmlspecialchars($categorySearch, ENT_QUOTES, 'UTF-8') ?>"
                    >
                        <div class="velox-menu-category-image">
                            <?php if ($categoryImage !== ''): ?>
                                <img src="<?= htmlspecialchars($categoryImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                            <?php else: ?>
                                <span class="velox-menu-category-placeholder"><i class="fa-solid fa-utensils"></i></span>
                            <?php endif; ?>
                        </div>
                        <div class="velox-menu-category-card-body">
                            <span class="velox-menu-category-parent">
                                <?= htmlspecialchars((string) ($category['parent_name'] ?? 'NUESTRA CARTA'), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <h3><?= htmlspecialchars($categoryTitle, ENT_QUOTES, 'UTF-8') ?></h3>
                            <p><?= htmlspecialchars($categoryDescription !== '' ? $categoryDescription : 'Descubre los productos disponibles en esta categoría.', ENT_QUOTES, 'UTF-8') ?></p>
                            <span class="velox-menu-category-card-footer">
                                <span>
                                    <?= (int) ($category['product_count'] ?? 0) ?>
                                    <?= (int) ($category['product_count'] ?? 0) === 1 ? 'producto' : 'productos' ?>
                                </span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="velox-menu-no-results d-none" id="menuCategoryNoResults">
                <i class="fa-solid fa-magnifying-glass"></i>
                <strong>No encontramos esa categoría.</strong>
                <span>Prueba con otro nombre o revisa la carta completa en PDF.</span>
            </div>
        </div>
    </section>

    <section class="velox-menu-official-section" id="menu-pdf">
        <div class="container">
            <div class="velox-menu-official-card">
                <div class="velox-menu-official-copy">
                    <span class="velox-menu-section-kicker">CARTA OFICIAL</span>
                    <h2>Todo el menú, <span>en un solo lugar.</span></h2>
                    <p>Consulta la carta completa en PDF. Puedes abrirla en una nueva pestaña o revisarla aquí mismo.</p>
                    <?php if ($menuPdfAvailable): ?>
                        <a class="btn btn-velox-primary" href="<?= htmlspecialchars($menuPdfUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">
                            Abrir carta completa <i class="fa-solid fa-arrow-up-right-from-square ms-2"></i>
                        </a>
                    <?php else: ?>
                        <span class="velox-menu-pdf-unavailable">La carta PDF no está disponible en este momento.</span>
                    <?php endif; ?>
                </div>
                <div class="velox-menu-official-icon" aria-hidden="true">
                    <i class="fa-regular fa-file-pdf"></i>
                    <span>PDF</span>
                </div>
            </div>

            <?php if ($menuPdfAvailable): ?>
                <details class="velox-menu-pdf-details">
                    <summary>
                        <span><i class="fa-regular fa-file-pdf me-2"></i>Ver la carta aquí</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </summary>
                    <div class="velox-menu-pdf-viewer">
                        <iframe
                            src="<?= htmlspecialchars($menuPdfUrl, ENT_QUOTES, 'UTF-8') ?>#toolbar=0&navpanes=0&scrollbar=1"
                            title="Carta completa de <?= htmlspecialchars(SiteConfig::siteName(), ENT_QUOTES, 'UTF-8') ?>"
                            loading="lazy"
                        ></iframe>
                    </div>
                </details>
            <?php endif; ?>
        </div>
    </section>

    <section class="velox-menu-bottom-cta">
        <div class="container">
            <div class="velox-menu-bottom-cta-card">
                <div>
                    <span class="velox-menu-section-kicker">TU SIGUIENTE PLAN</span>
                    <h2>¿Ya elegiste <span>qué pedir?</span></h2>
                    <p>Haz tu pedido para recoger en tu sede favorita o reserva una mesa y ven a disfrutar.</p>
                </div>
                <div class="velox-menu-bottom-cta-actions">
                    <a href="<?= htmlspecialchars(APP_URL . '/carrito', ENT_QUOTES, 'UTF-8') ?>" class="btn btn-velox-primary">
                        <i class="fa-solid fa-basket-shopping me-2"></i>Ir al carrito
                    </a>
                    <a href="<?= htmlspecialchars(APP_URL . '/reservas', ENT_QUOTES, 'UTF-8') ?>" class="velox-menu-bottom-outline">
                        <i class="fa-regular fa-calendar-check me-2"></i>Reservar mesa
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>
