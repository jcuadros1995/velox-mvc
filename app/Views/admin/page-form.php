<?php
/**
 * app/Views/admin/page-form.php
 *
 * Crear / editar páginas públicas
 */

/* ============================================================
   DATOS DE LA PÁGINA
   ============================================================ */

$page = $adminPage ?? null;

$edit = $page !== null;


/* ============================================================
   HEADER
   ============================================================ */

$pageHeaderIcon =
        $edit
                ? 'fa-solid fa-file-pen'
                : 'fa-solid fa-file-circle-plus';

$pageHeaderEyebrow =
        'CONTENIDO WEB';

$pageHeaderTitle =
        $edit
                ? 'Editar página'
                : 'Nueva página';

$pageHeaderDescription =
        $edit
                ? 'Actualiza el contenido, configuración y posicionamiento SEO de esta página.'
                : 'Crea una nueva página pública para el sitio web de Burger Town.';

$pageHeaderBadge =
        $edit
                ? 'Edición de página'
                : 'Nueva página';


/* ============================================================
   VALORES
   ============================================================ */

$pageId =
        $edit
                ? (int)($page['id'] ?? 0)
                : 0;


$pageTitle =
        $edit
                ? (string)($page['title'] ?? '')
                : '';


$pageSlug =
        $edit
                ? (string)($page['slug'] ?? '')
                : '';


$pageType =
        $edit
                ? (string)($page['page_type'] ?? 'general')
                : 'general';


$pageH1 =
        $edit
                ? (string)($page['h1'] ?? '')
                : '';


$pageMetaTitle =
        $edit
                ? (string)($page['meta_title'] ?? '')
                : '';


$pageMetaDescription =
        $edit
                ? (string)($page['meta_description'] ?? '')
                : '';


$pageContent =
        $edit
                ? (string)($page['content_html'] ?? '')
                : '';


$pagePublished =
        !$edit ||
        (int)($page['is_published'] ?? 0) === 1;


$esc = static function ($value): string {

    return htmlspecialchars(
            (string)$value,
            ENT_QUOTES,
            'UTF-8'
    );

};

?>

<div
        class="admin-module-page bt-admin-page-editor"
        id="sitePageEditor"
        data-id="<?= $pageId ?>"
        data-edit="<?= $edit ? '1' : '0' ?>"
>

    <?php
    require __DIR__ . '/components/page-header.php';
    ?>


    <!-- ========================================================
         EDITOR
    ========================================================= -->

    <div class="page-editor-layout">


        <!-- ====================================================
             COLUMNA IZQUIERDA
        ===================================================== -->

        <div class="page-editor-main">

            <section class="editor-card">


                <!-- TOOLBAR -->

                <div class="editor-toolbar">

                    <div class="editor-toolbar-group">

                        <button
                                type="button"
                                class="editor-tool-btn"
                                data-cmd="bold"
                                title="Negrita"
                        >
                            <i class="fa-solid fa-bold"></i>
                        </button>


                        <button
                                type="button"
                                class="editor-tool-btn"
                                data-cmd="italic"
                                title="Cursiva"
                        >
                            <i class="fa-solid fa-italic"></i>
                        </button>


                        <button
                                type="button"
                                class="editor-tool-btn"
                                data-cmd="underline"
                                title="Subrayado"
                        >
                            <i class="fa-solid fa-underline"></i>
                        </button>

                    </div>


                    <span class="editor-toolbar-divider"></span>


                    <div class="editor-toolbar-group">

                        <button
                                type="button"
                                class="editor-tool-btn"
                                data-cmd="insertUnorderedList"
                                title="Lista"
                        >
                            <i class="fa-solid fa-list-ul"></i>
                        </button>


                        <button
                                type="button"
                                class="editor-tool-btn"
                                data-cmd="insertOrderedList"
                                title="Lista numerada"
                        >
                            <i class="fa-solid fa-list-ol"></i>
                        </button>

                    </div>


                    <span class="editor-toolbar-divider"></span>


                    <div class="editor-toolbar-group">

                        <button
                                type="button"
                                class="editor-tool-btn"
                                data-cmd="justifyLeft"
                                title="Alinear izquierda"
                        >
                            <i class="fa-solid fa-align-left"></i>
                        </button>


                        <button
                                type="button"
                                class="editor-tool-btn"
                                data-cmd="justifyCenter"
                                title="Centrar"
                        >
                            <i class="fa-solid fa-align-center"></i>
                        </button>


                        <button
                                type="button"
                                class="editor-tool-btn"
                                data-cmd="justifyRight"
                                title="Alinear derecha"
                        >
                            <i class="fa-solid fa-align-right"></i>
                        </button>

                    </div>


                    <span class="editor-toolbar-divider"></span>


                    <div class="editor-toolbar-group">

                        <button
                                type="button"
                                class="editor-tool-btn editor-heading-btn"
                                data-cmd="formatBlock"
                                data-value="H2"
                                title="Título H2"
                        >
                            H2
                        </button>


                        <button
                                type="button"
                                class="editor-tool-btn editor-heading-btn"
                                data-cmd="formatBlock"
                                data-value="H3"
                                title="Título H3"
                        >
                            H3
                        </button>


                        <button
                                type="button"
                                class="editor-tool-btn"
                                id="insertLink"
                                title="Insertar enlace"
                        >
                            <i class="fa-solid fa-link"></i>
                        </button>

                    </div>

                </div>


                <!-- INFORMACIÓN EDITOR -->

                <div class="editor-content-header">

                    <div>

                        <strong>
                            Contenido de la página
                        </strong>

                        <span>
                            Diseña el contenido que verá el visitante.
                        </span>

                    </div>


                    <span class="editor-content-status">

                        <i class="fa-solid fa-circle"></i>

                        Editor visual

                    </span>

                </div>


                <!-- EDITOR -->

                <div
                        id="pageContentEditor"
                        class="rich-editor"
                        contenteditable="true"
                        data-placeholder="Escribe aquí el contenido de la página..."
                ><?= $pageContent ?></div>


                <!-- FOOTER EDITOR -->

                <div class="editor-footer">

                    <span>

                        <i class="fa-solid fa-circle-info"></i>

                        Puedes utilizar títulos, listas, enlaces y formato de texto.

                    </span>

                    <span id="editorCharacterCount">
                        0 caracteres
                    </span>

                </div>

            </section>

        </div>


        <!-- ====================================================
             COLUMNA DERECHA
        ===================================================== -->

        <aside class="page-editor-sidebar">


            <!-- INFORMACIÓN BÁSICA -->

            <section class="editor-side-card">

                <div class="editor-side-header">

                    <div class="editor-side-icon">

                        <i class="fa-solid fa-file-lines"></i>

                    </div>


                    <div>

                        <strong>
                            Información básica
                        </strong>

                        <span>
                            Datos principales de la página.
                        </span>

                    </div>

                </div>


                <div class="editor-form">


                    <!-- TÍTULO -->

                    <div class="editor-field">

                        <label for="pageTitle">
                            Título
                            <span>*</span>
                        </label>

                        <input
                                id="pageTitle"
                                class="editor-control"
                                type="text"
                                maxlength="180"
                                value="<?= $esc($pageTitle) ?>"
                                placeholder="Ej. Contacto"
                        >

                        <small>
                            Nombre interno y principal de la página.
                        </small>

                    </div>


                    <!-- SLUG -->

                    <div class="editor-field">

                        <label for="pageSlug">
                            Slug
                            <span>*</span>
                        </label>

                        <div class="editor-slug-input">

                            <span>
                                /
                            </span>

                            <input
                                    id="pageSlug"
                                    class="editor-control"
                                    type="text"
                                    maxlength="180"
                                    value="<?= $esc($pageSlug) ?>"
                                    placeholder="mi-pagina"
                            >

                        </div>

                        <small>
                            URL amigable de la página.
                        </small>

                    </div>


                    <!-- TIPO -->

                    <div class="editor-field">

                        <label for="pageType">
                            Tipo
                        </label>

                        <select
                                id="pageType"
                                class="editor-control"
                        >

                            <option
                                    value="general"
                                    <?= $pageType === 'general' ? 'selected' : '' ?>
                            >
                                General
                            </option>


                            <option
                                    value="legal"
                                    <?= $pageType === 'legal' ? 'selected' : '' ?>
                            >
                                Legal
                            </option>


                            <option
                                    value="contact"
                                    <?= $pageType === 'contact' ? 'selected' : '' ?>
                            >
                                Contacto
                            </option>


                            <option
                                    value="event"
                                    <?= $pageType === 'event' ? 'selected' : '' ?>
                            >
                                Eventos
                            </option>

                        </select>

                    </div>


                    <!-- H1 -->

                    <div class="editor-field">

                        <label for="pageH1">
                            H1
                        </label>

                        <input
                                id="pageH1"
                                class="editor-control"
                                type="text"
                                maxlength="255"
                                value="<?= $esc($pageH1) ?>"
                                placeholder="Título principal visible"
                        >

                    </div>

                </div>

            </section>


            <!-- =================================================
                 SEO
            ================================================== -->

            <section class="editor-side-card">

                <div class="editor-side-header">

                    <div class="editor-side-icon seo">

                        <i class="fa-solid fa-magnifying-glass-chart"></i>

                    </div>


                    <div>

                        <strong>
                            SEO
                        </strong>

                        <span>
                            Optimiza cómo aparece en Google.
                        </span>

                    </div>

                </div>


                <div class="editor-form">


                    <!-- META TITLE -->

                    <div class="editor-field">

                        <label for="pageMetaTitle">
                            Meta título
                        </label>

                        <input
                                id="pageMetaTitle"
                                class="editor-control"
                                type="text"
                                maxlength="255"
                                value="<?= $esc($pageMetaTitle) ?>"
                                placeholder="Título para buscadores"
                        >

                        <div class="editor-counter">

                            <span>
                                Recomendado: hasta 60 caracteres.
                            </span>

                            <strong id="metaTitleCount">
                                <?= mb_strlen($pageMetaTitle) ?>
                            </strong>

                        </div>

                    </div>


                    <!-- META DESCRIPTION -->

                    <div class="editor-field">

                        <label for="pageMetaDescription">
                            Meta descripción
                        </label>

                        <textarea
                                id="pageMetaDescription"
                                class="editor-control editor-textarea"
                                rows="5"
                                maxlength="500"
                                placeholder="Descripción que aparecerá en los resultados de búsqueda..."
                        ><?= $esc($pageMetaDescription) ?></textarea>

                        <div class="editor-counter">

                            <span>
                                Recomendado: 140–160 caracteres.
                            </span>

                            <strong id="metaDescriptionCount">
                                <?= mb_strlen($pageMetaDescription) ?>
                            </strong>

                        </div>

                    </div>

                </div>

            </section>


            <!-- =================================================
                 PUBLICACIÓN
            ================================================== -->

            <section class="editor-side-card editor-publish-card">

                <div class="editor-side-header">

                    <div class="editor-side-icon publish">

                        <i class="fa-solid fa-globe"></i>

                    </div>


                    <div>

                        <strong>
                            Publicación
                        </strong>

                        <span>
                            Controla la visibilidad de la página.
                        </span>

                    </div>

                </div>


                <label
                        class="editor-switch"
                        for="pagePublished"
                >

                    <input
                            id="pagePublished"
                            type="checkbox"
                            <?= $pagePublished ? 'checked' : '' ?>
                    >

                    <span class="editor-switch-track"></span>

                    <span class="editor-switch-content">

                        <strong>
                            Página publicada
                        </strong>

                        <small>
                            Visible para los visitantes.
                        </small>

                    </span>

                </label>


                <button
                        id="saveSitePage"
                        type="button"
                        class="editor-save-btn"
                >

                    <i class="fa-solid fa-floppy-disk"></i>

                    <span>
                        <?= $edit
                                ? 'Guardar cambios'
                                : 'Crear página'
                        ?>
                    </span>

                </button>

            </section>

        </aside>

    </div>

</div>