<?php
// app/Views/admin/category-form.php

$isEdit =
        ($categoryFormMode ?? 'create') === 'edit';

$category =
        $categoryFormCategory ?? null;

$parents =
        $categoryFormParents ?? [];


/*
|--------------------------------------------------------------------------
| PAGE HEADER
|--------------------------------------------------------------------------
*/

$pageHeaderIcon =
        $isEdit
                ? 'fa-solid fa-pen-to-square'
                : 'fa-solid fa-folder-plus';

$pageHeaderEyebrow =
        'CATÁLOGO';

$pageHeaderTitle =
        $isEdit
                ? 'Editar categoría'
                : 'Nueva categoría';

$pageHeaderDescription =
        $isEdit
                ? 'Actualiza la estructura y configuración básica de la categoría.'
                : 'Crea una nueva categoría o subcategoría para el catálogo de Burger Town.';

$pageHeaderBadge =
        $isEdit
                ? 'Edición de categoría'
                : 'Nueva categoría';
?>


<div
        class="admin-category-form-page"
        id="adminCategoryFormPage"

        data-mode="<?= $isEdit ? 'edit' : 'create' ?>"

        data-id="<?= $isEdit
                ? (int) $category['id']
                : 0
        ?>"
>


    <!-- =========================================================
         HEADER DE PÁGINA
    ========================================================== -->

    <?php
    require __DIR__ . '/components/page-header.php';
    ?>


    <!-- =========================================================
         TOPBAR
    ========================================================== -->

    <div class="category-form-topbar">

        <a
                href="<?= APP_URL ?>/admin/categories"
                class="category-form-back"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Categorías

        </a>


        <?php if ($isEdit): ?>

            <span class="category-form-id">

                ID #<?= (int) $category['id'] ?>

            </span>

        <?php endif; ?>

    </div>


    <!-- =========================================================
         INFORMACIÓN DE CATEGORÍA
    ========================================================== -->

    <section class="category-form-card">

        <div class="category-form-card-header">

            <div>

                <h2>

                    <i class="fa-solid fa-folder-open"></i>

                    Información de categoría

                </h2>

                <p>
                    Estos datos se utilizan en el administrador y
                    como base para la futura página pública.
                </p>

            </div>

        </div>


        <div class="category-form-grid">


            <!-- NOMBRE -->

            <div class="category-field category-field-wide">

                <label for="categoryName">

                    Nombre

                    <span>*</span>

                </label>

                <input
                        type="text"
                        id="categoryName"
                        maxlength="100"

                        value="<?= htmlspecialchars(
                                $category['name'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                        ) ?>"

                        placeholder="Ej. Hamburguesas"
                >

            </div>


            <!-- SLUG -->

            <div class="category-field">

                <label for="categorySlug">

                    Slug

                </label>

                <input
                        type="text"
                        id="categorySlug"
                        maxlength="120"

                        value="<?= htmlspecialchars(
                                $category['slug'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                        ) ?>"

                        placeholder="hamburguesas"
                >

                <small>
                    Se utilizará como identificador amigable.
                </small>

            </div>


            <!-- CATEGORÍA PADRE -->

            <div class="category-field">

                <label for="categoryParent">

                    Categoría padre

                </label>

                <select id="categoryParent">

                    <option value="">
                        Sin categoría padre
                    </option>

                    <?php foreach ($parents as $parent): ?>

                        <option
                                value="<?= (int) $parent['id'] ?>"

                                <?= (
                                        (int) (
                                                $category['parent_id'] ?? 0
                                        )
                                        ===
                                        (int) $parent['id']
                                )
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

                    <?php endforeach; ?>

                </select>

                <small>
                    Déjalo vacío para una categoría principal.
                </small>

            </div>


            <!-- TIPO -->

            <div class="category-field">

                <label for="categoryType">

                    Tipo de catálogo

                </label>

                <select id="categoryType">

                    <option
                            value="general"

                            <?= (
                                    ($category['catalog_type'] ?? 'general')
                                    === 'general'
                            )
                                    ? 'selected'
                                    : ''
                            ?>
                    >
                        General
                    </option>

                    <option
                            value="restaurant"

                            <?= (
                                    ($category['catalog_type'] ?? '')
                                    === 'restaurant'
                            )
                                    ? 'selected'
                                    : ''
                            ?>
                    >
                        Restaurante
                    </option>

                </select>

            </div>


            <!-- DESCRIPCIÓN -->

            <div class="category-field category-field-wide">

                <label for="categoryDescription">

                    Descripción

                </label>

                <textarea
                        id="categoryDescription"
                        rows="5"
                        maxlength="1000"
                        placeholder="Describe brevemente esta categoría..."
                ><?= htmlspecialchars(
                            $category['description'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                    ) ?></textarea>

            </div>


            <!-- ESTADO -->

            <div class="category-toggle-box">

                <div>

                    <strong>
                        Categoría activa
                    </strong>

                    <span>
                        Las categorías inactivas no deben utilizarse
                        en el catálogo público.
                    </span>

                </div>


                <label class="category-switch">

                    <input
                            type="checkbox"
                            id="categoryActive"

                            <?= (
                                    !$isEdit
                                    ||
                                    (int) (
                                            $category['is_active'] ?? 0
                                    ) === 1
                            )
                                    ? 'checked'
                                    : ''
                            ?>
                    >

                    <span></span>

                </label>

            </div>

        </div>

    </section>


    <!-- =========================================================
         ACCIONES
    ========================================================== -->

    <div class="category-form-actions">

        <a
                href="<?= APP_URL ?>/admin/categories"
                class="category-cancel-btn"
        >

            Cancelar

        </a>


        <button
                type="button"
                class="category-save-btn"
                id="btnSaveCategory"
        >

            <i class="fa-solid fa-floppy-disk"></i>

            <?= $isEdit
                    ? 'Guardar cambios'
                    : 'Crear categoría'
            ?>

        </button>

    </div>


</div>