/**
 * VELOX MVC - LOGIN
 * Manejo de autenticación y UI del formulario
 */

document.addEventListener('DOMContentLoaded', () => {

    initLoginForm();
    initTogglePassword();

});


/**
 * ================================================================
 * MOSTRAR / OCULTAR CONTRASEÑA
 * ================================================================
 */
function initTogglePassword() {

    const btnToggle =
        document.getElementById('btnTogglePassword');

    const passwordInput =
        document.getElementById('password');

    const icon =
        document.getElementById('toggleIcon');


    if (!btnToggle || !passwordInput || !icon) {
        return;
    }


    btnToggle.addEventListener('click', () => {

        const isPassword =
            passwordInput.type === 'password';


        passwordInput.type =
            isPassword ? 'text' : 'password';


        icon.className =
            isPassword
                ? 'fa-regular fa-eye-slash'
                : 'fa-regular fa-eye';


        btnToggle.setAttribute(
            'aria-label',
            isPassword
                ? 'Ocultar contraseña'
                : 'Mostrar contraseña'
        );

    });

}


/**
 * ================================================================
 * LOGIN
 * ================================================================
 */
function initLoginForm() {

    const form =
        document.getElementById('loginForm');


    if (!form) {
        return;
    }


    form.addEventListener('submit', async (event) => {

        event.preventDefault();


        const emailInput =
            document.getElementById('email');

        const passwordInput =
            document.getElementById('password');

        const rememberInput =
            document.getElementById('rememberSession');

        const btnSubmit =
            document.getElementById('btnLoginSubmit');


        const email =
            emailInput?.value.trim() || '';

        const password =
            passwordInput?.value || '';

        const remember =
            rememberInput?.checked === true;


        /**
         * ------------------------------------------------------------
         * VALIDACIÓN FRONTEND
         * ------------------------------------------------------------
         */

        if (!email || !password) {

            showToast(
                'warning',
                'Ingresa tu correo y contraseña'
            );

            return;
        }


        if (!isValidEmail(email)) {

            showToast(
                'warning',
                'Ingresa un correo electrónico válido'
            );

            emailInput?.focus();

            return;
        }


        /**
         * ------------------------------------------------------------
         * ESTADO LOADING
         * ------------------------------------------------------------
         */

        setLoginLoading(btnSubmit);


        try {

            /**
             * --------------------------------------------------------
             * PETICIÓN AL SERVIDOR
             * --------------------------------------------------------
             */

            const response = await fetch(
                `${window.APP_URL}/api/login`,
                {
                    method: 'POST',

                    headers: {
                        'Content-Type':
                            'application/json',

                        'Accept':
                            'application/json'
                    },

                    credentials: 'same-origin',

                    body: JSON.stringify({
                        email: email,
                        password: password,
                        remember: remember
                    })
                }
            );


            /**
             * --------------------------------------------------------
             * LEER RESPUESTA
             * --------------------------------------------------------
             */

            const text =
                await response.text();


            let data;


            try {

                data =
                    JSON.parse(text);

            } catch (error) {

                console.error(
                    'Respuesta no JSON del servidor:',
                    text
                );

                throw new Error(
                    'El servidor no devolvió una respuesta válida.'
                );
            }

            /**
             * --------------------------------------------------------
             * LOGIN EXITOSO
             * --------------------------------------------------------
             */

            if (
                response.ok &&
                data.success === true
            ) {

                showToast(
                    'success',
                    data.message ||
                    '¡Inicio de sesión exitoso!'
                );


                /**
                 * Pequeña pausa para que el usuario
                 * pueda visualizar el mensaje de éxito.
                 */

                setTimeout(() => {

                    const roleName = String(
                        data.data?.role_name || ''
                    ).toLowerCase();

                    window.location.href = roleName === 'customer'
                        ? `${window.APP_URL}/`
                        : `${window.APP_URL}/admin/dashboard`;

                }, 700);


                return;
            }


            /**
             * --------------------------------------------------------
             * LOGIN FALLIDO
             * --------------------------------------------------------
             */

            showToast(
                'error',
                data.message ||
                'Credenciales incorrectas'
            );


            resetButton(btnSubmit);


            /**
             * Si el error corresponde a credenciales,
             * dejamos el foco en la contraseña.
             */

            if (
                response.status === 401 ||
                response.status === 403
            ) {

                passwordInput?.focus();

            }


        } catch (error) {

            console.error(
                '[LOGIN] Error:',
                error
            );


            showToast(
                'error',
                error.message ||
                'Error de comunicación con el servidor'
            );


            resetButton(btnSubmit);

        }

    });

}


/**
 * ================================================================
 * VALIDAR EMAIL
 * ================================================================
 */
function isValidEmail(email) {

    const emailRegex =
        /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    return emailRegex.test(email);

}


/**
 * ================================================================
 * BOTÓN - LOADING
 * ================================================================
 */
function setLoginLoading(btn) {

    if (!btn) {
        return;
    }


    btn.disabled = true;

    btn.classList.add('loading');

    btn.setAttribute(
        'aria-busy',
        'true'
    );

}


/**
 * ================================================================
 * BOTÓN - RESTAURAR
 * ================================================================
 */
function resetButton(btn) {

    if (!btn) {
        return;
    }


    btn.disabled = false;

    btn.classList.remove('loading');

    btn.removeAttribute(
        'aria-busy'
    );

}