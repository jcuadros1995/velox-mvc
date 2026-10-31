document.addEventListener(
    'DOMContentLoaded',
    () => {

        const form =
            document.getElementById(
                'forgotPasswordForm'
            );


        if (!form) {
            return;
        }


        const identifierInput =
            document.getElementById(
                'recoveryIdentifier'
            );


        const button =
            document.getElementById(
                'btnForgotPassword'
            );


        form.addEventListener(
            'submit',
            async (event) => {

                event.preventDefault();


                const identifier =
                    identifierInput
                        ?.value
                        .trim() || '';


                if (!identifier) {

                    showToast(
                        'warning',
                        'Ingresa tu correo, teléfono o número de documento.'
                    );

                    identifierInput?.focus();

                    return;
                }


                button?.classList.add(
                    'loading'
                );


                try {

                    const response =
                        await fetch(
                            `${window.APP_URL}/api/forgot-password`,
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
                                    JSON.stringify({
                                        identifier:
                                        identifier
                                    })
                            }
                        );


                    const contentType =
                        response.headers.get('content-type') || '';

                    let data;

                    if (contentType.includes('application/json')) {

                        data = await response.json();

                    } else {

                        const rawResponse =
                            await response.text();

                        console.error(
                            '[FORGOT PASSWORD] RESPUESTA PHP:',
                            rawResponse
                        );

                        throw new Error(
                            'El servidor devolvió una respuesta no JSON.'
                        );
                    }


                    if (
                        response.ok &&
                        data.success === true
                    ) {

                        showToast(
                            'success',
                            data.message ||
                            'Si los datos coinciden, recibirás un correo con las instrucciones.'
                        );

                        form.reset();

                        return;
                    }


                    showToast(
                        'error',
                        data.message ||
                        'No fue posible procesar la solicitud.'
                    );


                } catch (error) {

                    console.error(
                        '[FORGOT PASSWORD]',
                        error
                    );

                    showToast(
                        'error',
                        'No fue posible comunicarse con el servidor.'
                    );

                } finally {

                    button?.classList.remove(
                        'loading'
                    );
                }

            }
        );

    }
);