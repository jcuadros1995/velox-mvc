(function () {
    'use strict';
    const appUrl = (window.APP_URL || '').replace(/\/$/, '');
    const logout = document.querySelector('[data-customer-logout]');
    if (logout) {
        logout.addEventListener('click', async function (event) {
            event.preventDefault();
            const confirmLogout = window.Swal
                ? await Swal.fire({title:'¿Cerrar sesión?',text:'Tu sesión se cerrará en este dispositivo.',icon:'question',showCancelButton:true,confirmButtonText:'Cerrar sesión',cancelButtonText:'Volver',confirmButtonColor:'#d91f2b'})
                : {isConfirmed: window.confirm('¿Deseas cerrar sesión?')};
            if (!confirmLogout.isConfirmed) return;
            try {
                const response = await fetch(appUrl + '/api/logout', {
                    method:'POST', credentials:'same-origin',
                    headers:{'X-Requested-With':'XMLHttpRequest'}
                });
                const result = await response.json();
                if (!response.ok || !result.success) throw new Error(result.message || 'No fue posible cerrar la sesión.');
                window.location.assign(appUrl + '/');
            } catch (error) {
                if (window.Swal) Swal.fire({icon:'error',title:'No se pudo cerrar sesión',text:error.message});
                else window.alert(error.message);
            }
        });
    }

    const form = document.getElementById('customer-profile-form');
    if (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            const button = form.querySelector('button[type="submit"]');
            const feedback = document.getElementById('customer-profile-feedback');
            const original = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando…';
            feedback.textContent = '';
            try {
                const response = await fetch(form.dataset.endpoint, {
                    method:'POST', body:new FormData(form), credentials:'same-origin',
                    headers:{'X-Requested-With':'XMLHttpRequest'}
                });
                const result = await response.json();
                if (!response.ok || !result.success) throw new Error(result.message || 'No fue posible guardar los cambios.');
                feedback.textContent = result.message;
                feedback.style.color = '#187347';
                if (window.Swal) await Swal.fire({icon:'success',title:'Información actualizada',text:result.message,confirmButtonColor:'#d91f2b'});
                window.location.reload();
            } catch (error) {
                feedback.textContent = error.message;
                feedback.style.color = '#b32d36';
            } finally {
                button.disabled = false;
                button.innerHTML = original;
            }
        });
    }
})();