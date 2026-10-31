<?php
// app/Views/admin/categories.php

$pageHeaderIcon = 'fa-solid fa-layer-group';
$pageHeaderEyebrow = 'CATÁLOGO';
$pageHeaderTitle = 'Categorías';
$pageHeaderDescription = 'Organiza el menú de Burger Town y prepara cada categoría para tener su propia página pública.';
$pageHeaderBadge = 'Gestión de categorías';

require __DIR__ . '/components/page-header.php';
?>

<div class="admin-categories-page" id="adminCategoriesPage">

    <section class="categories-stats">
        <article class="categories-stat-card">
            <div class="categories-stat-icon"><i class="fa-solid fa-layer-group"></i></div>
            <div><span>Total categorías</span><strong id="categoriesStatTotal">0</strong></div>
        </article>

        <article class="categories-stat-card">
            <div class="categories-stat-icon"><i class="fa-solid fa-circle-check"></i></div>
            <div><span>Activas</span><strong id="categoriesStatActive">0</strong></div>
        </article>

        <article class="categories-stat-card">
            <div class="categories-stat-icon"><i class="fa-solid fa-globe"></i></div>
            <div><span>Páginas configuradas</span><strong id="categoriesStatPages">0</strong></div>
        </article>

        <article class="categories-stat-card">
            <div class="categories-stat-icon"><i class="fa-solid fa-eye"></i></div>
            <div><span>Páginas publicadas</span><strong id="categoriesStatPublished">0</strong></div>
        </article>
    </section>

    <section class="categories-panel">
        <div class="categories-panel-header">
            <div>
                <strong>Estructura del menú</strong>
                <span>Las categorías y subcategorías controlan cómo se organiza el catálogo.</span>
            </div>

            <div class="categories-panel-actions">
                <button type="button" class="categories-secondary-btn" id="btnRefreshCategories">
                    <i class="fa-solid fa-rotate"></i>
                    Actualizar
                </button>

                <a href="<?= APP_URL ?>/admin/categories/create" class="categories-primary-btn">
                    <i class="fa-solid fa-plus"></i>
                    Nueva categoría
                </a>
            </div>
        </div>

        <div class="categories-toolbar">
            <div class="categories-search-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" id="categoriesSearch" class="categories-input" placeholder="Buscar categoría, slug o descripción..." autocomplete="off">
            </div>

            <select id="categoriesStatus" class="categories-input categories-select">
                <option value="">Todos los estados</option>
                <option value="1">Activas</option>
                <option value="0">Inactivas</option>
            </select>

            <select id="categoriesPageStatus" class="categories-input categories-select">
                <option value="">Todas las páginas</option>
                <option value="configured">Con página</option>
                <option value="pending">Sin página</option>
                <option value="published">Publicadas</option>
            </select>
        </div>

        <div class="categories-table-wrap">
            <table class="categories-table">
                <thead>
                <tr>
                    <th>Categoría</th>
                    <th>Jerarquía</th>
                    <th>Productos</th>
                    <th>Página pública</th>
                    <th>Estado</th>
                    <th class="categories-actions-col">Acciones</th>
                </tr>
                </thead>
                <tbody id="categoriesTableBody">
                <tr>
                    <td colspan="6" class="categories-loading">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        Cargando categorías...
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </section>
</div>
