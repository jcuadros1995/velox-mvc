document.addEventListener('DOMContentLoaded', () => {

 const pageTable = document.querySelector('#pagesTable tbody');

 if (!pageTable) {
  return;
 }


 const searchInput =
     document.getElementById('pageSearch');

 const typeSelect =
     document.getElementById('pageType');

 const statusSelect =
     document.getElementById('pageStatus');

 const refreshButton =
     document.getElementById('btnRefreshPages');


 let pages = [];


 /* =========================================================
    CARGAR PÁGINAS
 ========================================================== */

 async function loadPages() {

  setLoading();

  try {

   const response =
       await fetch(
           `${window.APP_URL}/api/admin/pages`,
           {
            credentials: 'same-origin'
           }
       );


   const data =
       await response.json();


   if (!response.ok || !data.success) {

    throw new Error(
        data.message ||
        'No fue posible cargar las páginas.'
    );

   }


   pages =
       Array.isArray(data.data)
           ? data.data
           : [];


   updateStats();

   renderPages();

  } catch (error) {

   console.error(
       'Error cargando páginas:',
       error
   );


   pageTable.innerHTML = `

                <tr>

                    <td
                        colspan="6"
                        class="pages-loading pages-error"
                    >

                        <i class="fa-solid fa-triangle-exclamation"></i>

                        ${escapeHtml(error.message)}

                    </td>

                </tr>

            `;

  }

 }


 /* =========================================================
    ESTADÍSTICAS
 ========================================================== */

 function updateStats() {

  const total =
      pages.length;


  const published =
      pages.filter(
          page =>
              Number(page.is_published) === 1
      ).length;


  const drafts =
      total - published;


  const legal =
      pages.filter(
          page =>
              page.page_type === 'legal'
      ).length;


  setText(
      'pagesStatTotal',
      total
  );


  setText(
      'pagesStatPublished',
      published
  );


  setText(
      'pagesStatDrafts',
      drafts
  );


  setText(
      'pagesStatLegal',
      legal
  );

 }


 /* =========================================================
    RENDER
 ========================================================== */

 function renderPages() {

  const query =
      (
          searchInput?.value ||
          ''
      )
          .trim()
          .toLowerCase();


  const type =
      typeSelect?.value ||
      '';


  const status =
      statusSelect?.value ||
      '';


  const filtered =
      pages.filter(page => {

       const searchable =
           `
                    ${page.title || ''}
                    ${page.slug || ''}
                    `.toLowerCase();


       const matchesSearch =
           !query ||
           searchable.includes(query);


       const matchesType =
           !type ||
           page.page_type === type;


       const matchesStatus =
           !status ||
           String(page.is_published) === status;


       return (
           matchesSearch &&
           matchesType &&
           matchesStatus
       );

      });


  if (!filtered.length) {

   pageTable.innerHTML = `

                <tr>

                    <td
                        colspan="6"
                        class="pages-empty"
                    >

                        <i class="fa-regular fa-file-lines"></i>

                        <br><br>

                        No hay páginas que coincidan con los filtros.

                    </td>

                </tr>

            `;

   return;
  }


  pageTable.innerHTML =
      filtered
          .map(renderPageRow)
          .join('');


  bindActions();

 }


 /* =========================================================
    FILA
 ========================================================== */

 function renderPageRow(page) {

  const published =
      Number(page.is_published) === 1;


  const type =
      page.page_type === 'legal'
          ? 'legal'
          : 'general';


  const typeLabel =
      type === 'legal'
          ? 'Legal'
          : 'General';


  const statusClass =
      published
          ? 'published'
          : 'draft';


  const statusLabel =
      published
          ? 'Publicada'
          : 'Borrador';


  const actionClass =
      published
          ? 'unpublish'
          : 'publish';


  const actionIcon =
      published
          ? 'fa-eye-slash'
          : 'fa-eye';


  const actionTitle =
      published
          ? 'Pasar a borrador'
          : 'Publicar';


  return `

            <tr>

                <td>

                    <div class="page-name">

                        <div class="page-icon">

                            <i class="fa-solid fa-file-lines"></i>

                        </div>


                        <div class="page-name-content">

                            <strong>
                                ${escapeHtml(page.title)}
                            </strong>

                            <span>
                                ID #${Number(page.id)}
                            </span>

                        </div>

                    </div>

                </td>


                <td>

                    <span class="page-slug">

                        /pages/${escapeHtml(page.slug)}

                    </span>

                </td>


                <td>

                    <span class="page-type-badge ${type}">

                        <i class="fa-solid ${
      type === 'legal'
          ? 'fa-scale-balanced'
          : 'fa-globe'
  }"></i>

                        ${typeLabel}

                    </span>

                </td>


                <td>

                    <span class="page-status ${statusClass}">

                        ${statusLabel}

                    </span>

                </td>


                <td>

                    ${formatDate(page.updated_at)}

                </td>


                <td>

                    <div class="pages-actions">

                        <a
                            href="${window.APP_URL}/admin/pages/edit?id=${Number(page.id)}"
                            class="pages-action-btn"
                            title="Editar página"
                        >

                            <i class="fa-solid fa-pen"></i>

                        </a>


                        <button
                            type="button"
                            class="pages-action-btn ${actionClass}"
                            data-toggle-page="${Number(page.id)}"
                            data-state="${published ? 0 : 1}"
                            title="${actionTitle}"
                        >

                            <i class="fa-solid ${actionIcon}"></i>

                        </button>

                    </div>

                </td>

            </tr>

        `;

 }


 /* =========================================================
    ACCIONES
 ========================================================== */

 function bindActions() {

  pageTable
      .querySelectorAll('[data-toggle-page]')
      .forEach(button => {

       button.addEventListener(
           'click',
           async () => {

            await togglePage(
                Number(button.dataset.togglePage),
                Number(button.dataset.state)
            );

           }
       );

      });

 }


 async function togglePage(id, state) {

  try {

   const response =
       await fetch(
           `${window.APP_URL}/api/admin/pages/toggle`,
           {
            method: 'POST',

            headers: {
             'Content-Type':
                 'application/json'
            },

            credentials:
                'same-origin',

            body:
                JSON.stringify({
                 id,
                 is_published: state
                })

           }
       );


   const data =
       await response.json();


   if (!response.ok || !data.success) {

    throw new Error(
        data.message ||
        'No fue posible cambiar el estado.'
    );

   }


   await loadPages();


  } catch (error) {

   console.error(
       'Error actualizando página:',
       error
   );


   alert(
       error.message ||
       'No fue posible cambiar el estado.'
   );

  }

 }


 /* =========================================================
    LOADING
 ========================================================== */

 function setLoading() {

  pageTable.innerHTML = `

            <tr>

                <td
                    colspan="6"
                    class="pages-loading"
                >

                    <i class="fa-solid fa-spinner fa-spin"></i>

                    Cargando páginas...

                </td>

            </tr>

        `;

 }


 /* =========================================================
    UTILIDADES
 ========================================================== */

 function setText(id, value) {

  const element =
      document.getElementById(id);

  if (element) {

   element.textContent =
       value;

  }

 }


 function escapeHtml(value) {

  return String(
      value ?? ''
  ).replace(
      /[&<>"']/g,
      char => ({
       '&': '&amp;',
       '<': '&lt;',
       '>': '&gt;',
       '"': '&quot;',
       "'": '&#039;'
      }[char])
  );

 }


 function formatDate(value) {

  if (!value) {

   return `
                <div class="page-date">
                    <span>Sin información</span>
                </div>
            `;

  }


  const date =
      new Date(
          String(value)
              .replace(' ', 'T')
      );


  if (Number.isNaN(date.getTime())) {

   return `
                <div class="page-date">
                    <span>
                        ${escapeHtml(value)}
                    </span>
                </div>
            `;

  }


  return `

            <div class="page-date">

                <strong>
                    ${date.toLocaleDateString(
      'es-CO'
  )}
                </strong>

                <span>
                    ${date.toLocaleTimeString(
      'es-CO',
      {
       hour: '2-digit',
       minute: '2-digit'
      }
  )}
                </span>

            </div>

        `;

 }


 /* =========================================================
    EVENTOS FILTROS
 ========================================================== */

 searchInput?.addEventListener(
     'input',
     renderPages
 );


 typeSelect?.addEventListener(
     'change',
     renderPages
 );


 statusSelect?.addEventListener(
     'change',
     renderPages
 );


 refreshButton?.addEventListener(
     'click',
     loadPages
 );


 /* =========================================================
    INICIALIZAR
 ========================================================== */

 loadPages();

});