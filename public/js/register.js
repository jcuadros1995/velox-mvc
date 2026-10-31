/**
 * VELOX MVC - REGISTRO PÚBLICO
 * Creación de cuentas de cliente desde la tienda.
 */

document.addEventListener('DOMContentLoaded', () => {
    initRegisterForm();
    initRegisterPasswordToggles();
});

function initRegisterPasswordToggles() {
    const toggles = [
        {
            button: document.getElementById('btnToggleRegisterPassword'),
            input: document.getElementById('registerPassword'),
            icon: document.getElementById('registerPasswordIcon')
        },
        {
            button: document.getElementById('btnToggleRegisterConfirm'),
            input: document.getElementById('registerPasswordConfirm'),
            icon: document.getElementById('registerConfirmIcon')
        }
    ];

    toggles.forEach(({ button, input, icon }) => {
        if (!button || !input || !icon) {
            return;
        }

        button.addEventListener('click', () => {
            const shouldShow = input.type === 'password';

            input.type = shouldShow ? 'text' : 'password';
            icon.className = shouldShow
                ? 'fa-regular fa-eye-slash'
                : 'fa-regular fa-eye';

            button.setAttribute(
                'aria-label',
                shouldShow ? 'Ocultar contraseña' : 'Mostrar contraseña'
            );
        });
    });
}

function initRegisterForm() {
    const form = document.getElementById('registerForm');

    if (!form) {
        return;
    }

    const firstNameInput = document.getElementById('registerFirstName');
    const lastNameInput = document.getElementById('registerLastName');
    const emailInput = document.getElementById('registerEmail');
    const phoneInput = document.getElementById('registerPhone');
    const passwordInput = document.getElementById('registerPassword');
    const confirmInput = document.getElementById('registerPasswordConfirm');
    const submitButton = document.getElementById('btnRegisterSubmit');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const firstName = firstNameInput?.value.trim() || '';
        const lastName = lastNameInput?.value.trim() || '';
        const email = emailInput?.value.trim().toLowerCase() || '';
        const phone = phoneInput?.value.trim() || '';
        const password = passwordInput?.value || '';
        const confirmation = confirmInput?.value || '';

        if (!firstName || !lastName || !email || !password || !confirmation) {
            showToast('warning', 'Completa todos los campos obligatorios.');
            return;
        }

        if (!isValidRegisterEmail(email)) {
            showToast('warning', 'Ingresa un correo electrónico válido.');
            emailInput?.focus();
            return;
        }

        if (password.length < 8 || new TextEncoder().encode(password).length > 72) {
            showToast('warning', 'La contraseña debe tener entre 8 y 72 bytes.');
            passwordInput?.focus();
            return;
        }

        if (password !== confirmation) {
            showToast('warning', 'Las contraseñas no coinciden.');
            confirmInput?.focus();
            return;
        }

        setRegisterLoading(submitButton);

        try {
            const response = await fetch(
                `${window.APP_URL}/api/register`,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        first_name: firstName,
                        last_name: lastName,
                        email,
                        phone,
                        password
                    })
                }
            );

            const contentType = response.headers.get('content-type') || '';

            if (!contentType.includes('application/json')) {
                const rawResponse = await response.text();
                console.error('[REGISTER] Respuesta no JSON:', rawResponse);
                throw new Error('El servidor no devolvió una respuesta válida.');
            }

            const data = await response.json();

            if (response.ok && data.success === true) {
                const requestedRedirect = new URLSearchParams(window.location.search).get('redirect');
                const redirectQuery = requestedRedirect === '/checkout'
                    ? '&redirect=%2Fcheckout'
                    : '';

                window.location.href =
                    `${String(window.APP_URL || '').replace(/\/$/, '')}/login?registered=1${redirectQuery}`;
                return;
            }

            showToast(
                'error',
                data.message || 'No fue posible crear tu cuenta.'
            );

        } catch (error) {
            console.error('[REGISTER] Error:', error);

            showToast(
                'error',
                error.message || 'No fue posible comunicarse con el servidor.'
            );

        } finally {
            resetRegisterButton(submitButton);
        }
    });
}

function isValidRegisterEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function setRegisterLoading(button) {
    if (!button) {
        return;
    }

    button.disabled = true;
    button.classList.add('loading');
    button.setAttribute('aria-busy', 'true');
}

function resetRegisterButton(button) {
    if (!button) {
        return;
    }

    button.disabled = false;
    button.classList.remove('loading');
    button.removeAttribute('aria-busy');
}
