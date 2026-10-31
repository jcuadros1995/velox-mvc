<?php
// app/Views/admin/items.php

$pageHeaderIcon =
    'fa-solid fa-burger';

$pageHeaderEyebrow =
    'CATÁLOGO';

$pageHeaderTitle =
    'Productos';

$pageHeaderDescription =
    'Administra el catálogo gastronómico, precios, disponibilidad e imágenes de Burger Town.';

$pageHeaderBadge =
    'Gestión de productos';

require __DIR__ .
    '/components/page-header.php';
?>

<div
    class="admin-items-page"
    id="adminItemsPage"
>

    <!-- =========================================================
         ESTADÍSTICAS
    ========================================================== -->

    <section class="items-stats">

        <!-- TOTAL -->

        <article class="items-stat-card">

            <div class="items-stat-icon">

                <i class="fa-solid fa-box"></i>

            </div>

            <div class="items-stat-content">

                <span>
                    Total productos
                </span>

                <strong id="itemsStatTotal">
                    0
                </strong>

            </div>

        </article>


        <!-- ACTIVOS -->

        <article class="items-stat-card">

            <div class="items-stat-icon">

                <i class="fa-solid fa-circle-check"></i>

            </div>

            <div class="items-stat-content">

                <span>
                    Activos
                </span>

                <strong id="itemsStatActive">
                    0
                </strong>

            </div>

        </article>


        <!-- DISPONIBLES -->

        <article class="items-stat-card">

            <div class="items-stat-icon">

                <i class="fa-solid fa-store"></i>

            </div>

            <div class="items-stat-content">

                <span>
                    Disponibles
                </span>

                <strong id="itemsStatAvailable">
                    0
                </strong>

            </div>

        </article>


        <!-- DESTACADOS -->

        <article class="items-stat-card">

            <div class="items-stat-icon">

                <i class="fa-solid fa-star"></i>

            </div>

            <div class="items-stat-content">

                <span>
                    Destacados
                </span>

                <strong id="itemsStatFeatured">
                    0
                </strong>

            </div>

        </article>

    </section>


    <!-- =========================================================
         PANEL PRINCIPAL
    ========================================================== -->

    <section class="items-panel">

        <!-- =====================================================
             CABECERA
        ====================================================== -->

        <div class="items-panel-header">

            <div class="items-panel-title">

                <div>

                    <strong>
                        Catálogo Burger Town
                    </strong>

                    <span>
                        Consulta, edita y administra tus productos.
                    </span>

                </div>

            </div>


            <div class="items-panel-actions">

                <!-- REFRESCAR -->

                <button
                    type="button"
                    class="items-secondary-btn"
                    id="btnRefreshItems"
                >

                    <i class="fa-solid fa-rotate"></i>

                    <span>
                        Actualizar
                    </span>

                </button>


                <!-- NUEVO -->

                <a
                    href="<?= APP_URL ?>/admin/items/create"
                    class="items-primary-btn"
                >

                    <i class="fa-solid fa-plus"></i>

                    <span>
                        Nuevo producto
                    </span>

                </a>

            </div>

        </div>


        <!-- =====================================================
             FILTROS
        ====================================================== -->

        <div class="items-filters">

            <!-- BÚSQUEDA -->

            <div class="items-filter items-filter-search">

                <label
                    for="itemsSearch"
                >
                    Buscar
                </label>

                <div class="items-search-wrapper">

                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input
                        type="search"
                        id="itemsSearch"
                        class="items-input"
                        placeholder="Nombre, SKU o descripción..."
                        autocomplete="off"
                    >

                </div>

            </div>


            <!-- CATEGORÍA -->

            <div class="items-filter">

                <label
                    for="itemsCategory"
                >
                    Categoría
                </label>

                <select
                    id="itemsCategory"
                    class="items-input"
                >

                    <option value="">
                        Todas las categorías
                    </option>

                </select>

            </div>


            <!-- ESTADO -->

            <div class="items-filter">

                <label
                    for="itemsStatus"
                >
                    Estado
                </label>

                <select
                    id="itemsStatus"
                    class="items-input"
                >

                    <option value="">
                        Todos
                    </option>

                    <option value="1">
                        Activos
                    </option>

                    <option value="0">
                        Inactivos
                    </option>

                </select>

            </div>


            <!-- DISPONIBILIDAD -->

            <div class="items-filter">

                <label
                    for="itemsAvailability"
                >
                    Disponibilidad
                </label>

                <select
                    id="itemsAvailability"
                    class="items-input"
                >

                    <option value="">
                        Todas
                    </option>

                    <option value="1">
                        Disponible
                    </option>

                    <option value="0">
                        No disponible
                    </option>

                </select>

            </div>


            <!-- DESTACADOS -->

            <div class="items-filter">

                <label
                    for="itemsFeatured"
                >
                    Destacados
                </label>

                <select
                    id="itemsFeatured"
                    class="items-input"
                >

                    <option value="">
                        Todos
                    </option>

                    <option value="1">
                        Destacados
                    </option>

                    <option value="0">
                        No destacados
                    </option>

                </select>

            </div>

        </div>


        <!-- =====================================================
             INFORMACIÓN RESULTADOS
        ====================================================== -->

        <div class="items-results-bar">

            <div>

                <span class="items-results-label">
                    Productos encontrados:
                </span>

                <strong
                    id="itemsResultCount"
                >
                    0
                </strong>

            </div>

            <div
                id="itemsLoadingIndicator"
                class="items-loading-indicator d-none"
            >

                <i class="fa-solid fa-spinner fa-spin"></i>

                Cargando...

            </div>

        </div>


        <!-- =====================================================
             TABLA
        ====================================================== -->

        <div class="items-table-wrapper">

            <table
                class="items-table"
                id="itemsTable"
            >

                <thead>

                <tr>

                    <th
                        class="items-col-product"
                    >
                        Producto
                    </th>

                    <th>
                        Categoría
                    </th>

                    <th>
                        Precio
                    </th>

                    <th>
                        Estado
                    </th>

                    <th>
                        Disponibilidad
                    </th>

                    <th>
                        Destacado
                    </th>

                    <th>
                        Imágenes
                    </th>

                    <th
                        class="items-col-actions"
                    >
                        Acciones
                    </th>

                </tr>

                </thead>


                <tbody
                    id="itemsTableBody"
                >

                <tr>

                    <td
                        colspan="8"
                        class="items-empty-state"
                    >

                        <div>

                            <i class="fa-solid fa-spinner fa-spin"></i>

                            <strong>
                                Cargando productos...
                            </strong>

                            <span>
                                Estamos consultando el catálogo.
                            </span>

                        </div>

                    </td>

                </tr>

                </tbody>

            </table>

        </div>


        <!-- =====================================================
             ESTADO VACÍO
        ====================================================== -->

        <div
            id="itemsEmptyState"
            class="items-empty-state d-none"
        >

            <div>

                <div class="items-empty-icon">

                    <i
                        class="fa-solid fa-box-open"
                    ></i>

                </div>

                <strong>
                    No hay productos
                </strong>

                <span>
                    No encontramos productos con los filtros seleccionados.
                </span>

                <a
                    href="<?= APP_URL ?>/admin/items/create"
                    class="items-primary-btn"
                >

                    <i class="fa-solid fa-plus"></i>

                    Crear producto

                </a>

            </div>

        </div>

    </section>

</div>