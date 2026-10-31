<?php
// app/Views/admin/category-page-form.php

$category = $categoryPageCategory ?? [];
$page = $categoryPage ?? [];

$pageHeaderIcon = 'fa-solid fa-globe';
$pageHeaderEyebrow = 'CATÁLOGO / PÁGINA PÚBLICA';
$pageHeaderTitle = 'Página de categoría';
$pageHeaderDescription = 'Configura el contenido SEO y editorial que tendrá la página pública de esta categoría.';
$pageHeaderBadge = 'Configuración web';

require __DIR__ . '/components/page-header.php';
?>

<div class="admin-category-page-form" id="adminCategoryPageForm"
     data-category-id="<?= (int) ($category['id'] ?? 0) ?>">

    <div class="category-form-topbar">
        <a href="<?= APP_URL ?>/admin/categories" class="category-form-back">
            <i class="fa-solid fa-arrow-left"></i>
            Categorías
        </a>

        <div class="category-page-context">
            <span>Categoría</span>
            <strong><?= htmlspecialchars($category['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
    </div>

    <section class="category-form-card">
        <div class="category-form-card-header">
            <div>
                <h2><i class="fa-solid fa-compass"></i> Identidad de la página</h2>
                <p>Define la URL, título y encabezado principal.</p>
            </div>
        </div>

        <div class="category-form-grid">
            <div class="category-field">
                <label for="pageSlug">Slug público <span>*</span></label>
                <input type="text" id="pageSlug" maxlength="180" value="<?= htmlspecialchars($page['slug'] ?? $category['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="hamburguesas-bogota">
                <small>Ejemplo: /pages/hamburguesas-bogota</small>
            </div>

            <div class="category-field">
                <label for="pageTitle">Título <span>*</span></label>
                <input type="text" id="pageTitle" maxlength="180" value="<?= htmlspecialchars($page['title'] ?? $category['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="category-field category-field-wide">
                <label for="pageMetaTitle">Meta title</label>
                <input type="text" id="pageMetaTitle" maxlength="255" value="<?= htmlspecialchars($page['meta_title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="category-field category-field-wide">
                <label for="pageMetaDescription">Meta description</label>
                <textarea id="pageMetaDescription" rows="3" maxlength="500"><?= htmlspecialchars($page['meta_description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="category-field category-field-wide">
                <label for="pageH1">H1</label>
                <input type="text" id="pageH1" maxlength="255" value="<?= htmlspecialchars($page['h1'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="category-field category-field-wide">
                <label for="pageIntro">Texto introductorio</label>
                <textarea id="pageIntro" rows="5" placeholder="Texto que aparecerá antes del catálogo..."><?= htmlspecialchars($page['intro_text'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="category-field category-field-wide">
                <label for="pageHeroImage">Imagen hero</label>
                <input type="text" id="pageHeroImage" maxlength="500" value="<?= htmlspecialchars($page['hero_image'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="assets/img/public/mi-imagen.webp">
                <small>Por ahora se guarda la ruta. Después conectaremos el cargador de imágenes.</small>
            </div>
        </div>
    </section>

    <section class="category-form-card">
        <div class="category-form-card-header">
            <div>
                <h2><i class="fa-solid fa-file-lines"></i> Contenido editorial</h2>
                <p>Bloques de contenido que podremos reutilizar en la plantilla dinámica.</p>
            </div>
        </div>

        <div class="category-form-grid">
            <div class="category-field category-field-wide">
                <label for="pageContentTop">Contenido superior</label>
                <textarea id="pageContentTop" rows="8" placeholder="Contenido HTML o texto que irá antes de los productos..."><?= htmlspecialchars($page['content_top'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="category-field category-field-wide">
                <label for="pageContentBottom">Contenido inferior</label>
                <textarea id="pageContentBottom" rows="8" placeholder="Contenido HTML o texto que irá después de los productos..."><?= htmlspecialchars($page['content_bottom'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
        </div>
    </section>

    <section class="category-form-card">
        <div class="category-form-card-header">
            <div>
                <h2><i class="fa-solid fa-sliders"></i> Publicación</h2>
                <p>Controla si la página puede aparecer públicamente.</p>
            </div>
        </div>

        <div class="category-page-options">
            <label class="category-option">
                <input type="checkbox" id="pageFaqEnabled" <?= (!isset($page['faq_enabled']) || (int) $page['faq_enabled'] === 1) ? 'checked' : '' ?>>
                <span>
                    <strong>Mostrar FAQ</strong>
                    <small>La plantilla podrá mostrar la sección de preguntas frecuentes.</small>
                </span>
            </label>

            <label class="category-option">
                <input type="checkbox" id="pagePublished" <?= (!isset($page['is_published']) || (int) $page['is_published'] === 1) ? 'checked' : '' ?>>
                <span>
                    <strong>Publicada</strong>
                    <small>Permite que la página sea accesible desde su URL pública.</small>
                </span>
            </label>
        </div>
    </section>

    <div class="category-form-actions">
        <a href="<?= APP_URL ?>/admin/categories" class="category-cancel-btn">Cancelar</a>
        <button type="button" class="category-save-btn" id="btnSaveCategoryPage">
            <i class="fa-solid fa-floppy-disk"></i>
            Guardar página
        </button>
    </div>
</div>
