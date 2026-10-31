<?php
// app/Views/admin/pages.php

$pageHeaderIcon =
        'fa-solid fa-file-lines';

$pageHeaderEyebrow =
        'CONTENIDO WEB';

$pageHeaderTitle =
        'Páginas públicas';

$pageHeaderDescription =
        'Gestiona las páginas legales e institucionales que hacen parte del sitio web de Burger Town.';

$pageHeaderBadge =
        'Gestión de páginas';

require __DIR__ . '/components/page-header.php';
?>

<div
        class="bt-admin-pages"
        id="adminPagesPage"
>

    <!-- =========================================================
         ESTADÍSTICAS
    ========================================================== -->

    <section class="pages-stats">

        <article class="pages-stat-card">

            <div class="pages-stat-icon">
                <i class="fa-solid fa-file-lines"></i>
            </div>

            <div class="pages-stat-content">

                <span>
                    Total páginas
                </span>

                <strong id="pagesStatTotal">
                    0
                </strong>

            </div>

        </article>


        <article class="pages-stat-card">

            <div class="pages-stat-icon success">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <div class="pages-stat-content">

                <span>
                    Publicadas
                </span>

                <strong id="pagesStatPublished">
                    0
                </strong>

            </div>

        </article>


        <article class="pages-stat-card">

            <div class="pages-stat-icon warning">
                <i class="fa-solid fa-file-pen"></i>
            </div>

            <div class="pages-stat-content">

                <span>
                    Borradores
                </span>

                <strong id="pagesStatDrafts">
                    0
                </strong>

            </div>

        </article>


        <article class="pages-stat-card">

            <div class="pages-stat-icon legal">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>

            <div class="pages-stat-content">

                <span>
                    Páginas legales
                </span>

                <strong id="pagesStatLegal">
                    0
                </strong>

            </div>

        </article>

    </section>


    <!-- =========================================================
         PANEL PRINCIPAL
    ========================================================== -->

    <section class="pages-panel">

        <!-- HEADER -->

        <div class="pages-panel-header">

            <div class="pages-panel-title">

                <strong>
                    Contenido del sitio
                </strong>

                <span>
                    Administra el contenido público, su estado y configuración SEO.
                </span>

            </div>


            <div class="pages-panel-actions">

                <button
                        type="button"
                        class="pages-secondary-btn"
                        id="btnRefreshPages"
                >

                    <i class="fa-solid fa-rotate"></i>

                    Actualizar

                </button>


                <a
                        href="<?= APP_URL ?>/admin/pages/create"
                        class="pages-primary-btn"
                >

                    <i class="fa-solid fa-plus"></i>

                    Nueva página

                </a>

            </div>

        </div>


        <!-- =====================================================
             FILTROS
        ====================================================== -->

        <div class="pages-toolbar">

            <div class="pages-search-wrap">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                        type="search"
                        id="pageSearch"
                        class="pages-input"
                        placeholder="Buscar por título o slug..."
                        autocomplete="off"
                >

            </div>


            <select
                    id="pageType"
                    class="pages-input pages-select"
            >

                <option value="">
                    Todos los tipos
                </option>

                <option value="legal">
                    Legales
                </option>

                <option value="general">
                    Generales
                </option>

            </select>


            <select
                    id="pageStatus"
                    class="pages-input pages-select"
            >

                <option value="">
                    Todos los estados
                </option>

                <option value="1">
                    Publicadas
                </option>

                <option value="0">
                    Borradores
                </option>

            </select>

        </div>


        <!-- =====================================================
             TABLA
        ====================================================== -->

        <div class="pages-table-wrap">

            <table
                    class="pages-table"
                    id="pagesTable"
            >

                <thead>

                <tr>

                    <th>
                        Página
                    </th>

                    <th>
                        Slug
                    </th>

                    <th>
                        Tipo
                    </th>

                    <th>
                        Estado
                    </th>

                    <th>
                        Actualizada
                    </th>

                    <th class="pages-actions-col">
                        Acciones
                    </th>

                </tr>

                </thead>


                <tbody>

                <tr>

                    <td
                            colspan="6"
                            class="pages-loading"
                    >

                        <i class="fa-solid fa-spinner fa-spin"></i>

                        Cargando páginas...

                    </td>

                </tr>

                </tbody>

            </table>

        </div>

    </section>

</div>