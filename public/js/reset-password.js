document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('resetPasswordForm');

    if (!form) {
        return;
    }

    const params = new URLSearchParams(window.location.search);
    const token = params.get('token') || '';

    const passwordInput = document.getElementById('newPassword');
    const confirmationInput = document.getElementById('confirmPassword');
    const button = document.getElementById('btnResetPassword');

    initPasswordToggle(
        'btnToggleNewPassword',
        'newPassword',
        'newPasswordIcon'
    );

    initPasswordToggle(
        'btnToggleConfirmPassword',
        'confirmPassword',
        'confirmPasswordIcon'
    );

    if (!token) {
        showToast(
            'error',
            'El enlace de recuperación no es válido o ha expirado.'
        );

        if (button) {
            button.disabled = true;
        }

        return;
    }

    form.addEventListener('submit', async (event) => {

        event.preventDefault();

        const password = passwordInput?.value || '';
        const confirmation = confirmationInput?.value || '';

        if (password.length < 8) {
            showToast(
                'warning',
                'La contraseña debe tener mínimo 8 caracteres.'
            );

            passwordInput?.focus();
            return;
        }

        if (password !== confirmation) {
            showToast(
                'warning',
                'Las contraseñas no coinciden.'
            );

            confirmationInput?.focus();
            return;
        }

        button?.classList.add('loading');

        if (button) {
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
        }

        try {

            const response = await fetch(
                `${window.APP_URL}/api/reset-password`,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        token: token,
                        password: password,
                        password_confirmation: confirmation
                    })
                }
            );

            const text = await response.text();

            let data;

            try {
                data = JSON.parse(text);
            } catch (error) {
                throw new Error(
                    'El servidor no devolvió una respuesta válida.'
                );
            }

            if (response.ok && data.success === true) {

                showToast(
                    'success',
                    data.message || 'Tu contraseña fue actualizada correctamente.'
                );

                setTimeout(() => {
                    window.location.href =
                        `${window.APP_URL}/login`;
                }, 900);

                return;
            }

            showToast(
                'error',
                data.message ||
                'No fue posible restablecer la contraseña.'
            );

        } catch (error) {

            console.error(
                '[RESET PASSWORD]',
                error
            );

            showToast(
                'error',
                error.message ||
                'No fue posible comunicarse con el servidor.'
            );

        } finally {

            button?.classList.remove('loading');

            if (button) {
                button.disabled = false;
                button.removeAttribute('aria-busy');
            }
        }
    });
});


function initPasswordToggle(buttonId, inputId, iconId) {

    const button = document.getElementById(buttonId);
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);

    if (!button || !input || !icon) {
        return;
    }

    button.addEventListener('click', () => {

        const isPassword =
            input.type === 'password';

        input.type =
            isPassword ? 'text' : 'password';

        icon.className =
            isPassword
                ? 'fa-regular fa-eye-slash'
                : 'fa-regular fa-eye';

        button.setAttribute(
            'aria-label',
            isPassword
                ? 'Ocultar contraseña'
                : 'Mostrar contraseña'
        );
    });
}
