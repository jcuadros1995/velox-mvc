document.addEventListener('DOMContentLoaded', () => {

    const root =
        document.getElementById('sitePageEditor');

    if (!root) {
        return;
    }


    const editor =
        document.getElementById('pageContentEditor');

    const title =
        document.getElementById('pageTitle');

    const slug =
        document.getElementById('pageSlug');

    const type =
        document.getElementById('pageType');

    const h1 =
        document.getElementById('pageH1');

    const metaTitle =
        document.getElementById('pageMetaTitle');

    const metaDescription =
        document.getElementById('pageMetaDescription');

    const published =
        document.getElementById('pagePublished');

    const saveButton =
        document.getElementById('saveSitePage');


    /* ========================================================
       EDITOR
    ========================================================= */

    document
        .querySelectorAll(
            '.editor-toolbar [data-cmd]'
        )
        .forEach(button => {

            button.addEventListener(
                'click',
                () => {

                    editor.focus();

                    document.execCommand(
                        button.dataset.cmd,
                        false,
                        button.dataset.value || null
                    );

                }
            );

        });


    /* ========================================================
       LINK
    ========================================================= */

    document
        .getElementById('insertLink')
        ?.addEventListener(
            'click',
            () => {

                const url =
                    prompt(
                        'URL del enlace:',
                        'https://'
                    );

                if (!url) {
                    return;
                }

                editor.focus();

                document.execCommand(
                    'createLink',
                    false,
                    url
                );

            }
        );


    /* ========================================================
       SLUG AUTOMÁTICO
    ========================================================= */

    title?.addEventListener(
        'input',
        event => {

            if (
                Number(root.dataset.id || 0) > 0
            ) {
                return;
            }

            if (
                slug.dataset.manual === '1'
            ) {
                return;
            }

            slug.value =
                slugify(event.target.value);

        }
    );


    slug?.addEventListener(
        'input',
        () => {

            slug.dataset.manual = '1';

        }
    );


    /* ========================================================
       CONTADORES
    ========================================================= */

    function updateCounters() {

        const editorCount =
            document.getElementById(
                'editorCharacterCount'
            );

        const metaTitleCount =
            document.getElementById(
                'metaTitleCount'
            );

        const metaDescriptionCount =
            document.getElementById(
                'metaDescriptionCount'
            );


        if (editorCount) {

            const text =
                editor.innerText
                    .replace(/\s+/g, ' ')
                    .trim();

            editorCount.textContent =
                `${text.length} caracteres`;

        }


        if (metaTitleCount) {

            metaTitleCount.textContent =
                metaTitle.value.length;

        }


        if (metaDescriptionCount) {

            metaDescriptionCount.textContent =
                metaDescription.value.length;

        }

    }


    editor?.addEventListener(
        'input',
        updateCounters
    );


    metaTitle?.addEventListener(
        'input',
        updateCounters
    );


    metaDescription?.addEventListener(
        'input',
        updateCounters
    );


    updateCounters();


    /* ========================================================
       GUARDAR
    ========================================================= */

    saveButton?.addEventListener(
        'click',
        async () => {

            const payload = {

                id:
                    Number(
                        root.dataset.id || 0
                    ),

                title:
                    title.value.trim(),

                slug:
                    slug.value.trim(),

                page_type:
                type.value,

                h1:
                    h1.value.trim(),

                meta_title:
                    metaTitle.value.trim(),

                meta_description:
                    metaDescription.value.trim(),

                content_html:
                editor.innerHTML,

                is_published:
                published.checked

            };


            if (!payload.title) {

                showToast?.(
                    'error',
                    'El título es obligatorio.'
                );

                title.focus();

                return;

            }


            if (!payload.slug) {

                showToast?.(
                    'error',
                    'El slug es obligatorio.'
                );

                slug.focus();

                return;

            }


            saveButton.disabled = true;


            try {

                const response =
                    await fetch(
                        `${window.APP_URL}/api/admin/pages/save`,
                        {
                            method: 'POST',

                            headers: {
                                'Content-Type':
                                    'application/json',

                                'Accept':
                                    'application/json'
                            },

                            credentials:
                                'same-origin',

                            body:
                                JSON.stringify(payload)
                        }
                    );


                const data =
                    await response.json();


                if (
                    !response.ok ||
                    !data.success
                ) {

                    throw new Error(
                        data.message ||
                        'No fue posible guardar la página.'
                    );

                }


                showToast?.(
                    'success',
                    data.message ||
                    'Página guardada correctamente.'
                );


                if (!payload.id) {

                    setTimeout(
                        () => {

                            location.href =
                                `${window.APP_URL}/admin/pages/edit?id=${data.id}`;

                        },
                        600
                    );

                }


            } catch (error) {

                console.error(
                    'Error guardando página:',
                    error
                );


                showToast?.(
                    'error',
                    error.message
                );


            } finally {

                saveButton.disabled = false;

            }

        }
    );


    /* ========================================================
       SLUGIFY
    ========================================================= */

    function slugify(value) {

        return value
            .normalize('NFD')
            .replace(
                /[\u0300-\u036f]/g,
                ''
            )
            .toLowerCase()
            .replace(
                /[^a-z0-9]+/g,
                '-'
            )
            .replace(
                /^-+|-+$/g,
                ''
            )
            .slice(
                0,
                180
            );

    }

});