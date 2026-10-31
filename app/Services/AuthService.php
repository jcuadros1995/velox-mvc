<?php
// app/Services/AuthService.php

class AuthService
{
    private UserRepository $userRepository;

    private SecurityService $securityService;
    private EmailService $emailService;

    public function __construct()
    {
        $this->userRepository =
            new UserRepository();

        $this->securityService =
            new SecurityService();
        $this->emailService =
            new EmailService();
    }

    /**
     * Registrar un nuevo cliente.
     */
    public function register(array $input): int
    {
        $email = strtolower(
            trim($input['email'])
        );

        if (
            $this->userRepository
                ->findByEmail($email)
        ) {
            throw new DomainException(
                'El correo electrónico ya está registrado',
                409
            );
        }

        $passwordHash =
            password_hash(
                $input['password'],
                PASSWORD_BCRYPT
            );

        return $this->userRepository
            ->createCustomer(
                trim($input['first_name']),
                trim($input['last_name'] ?? ''),
                $email,
                trim($input['phone'] ?? ''),
                $passwordHash
            );
    }


    /**
     * Iniciar sesión.
     */
    public function login(
        string $email,
        string $password,
        bool $remember = false
    ): array {

        $email = strtolower(
            trim($email)
        );

        $user =
            $this->userRepository
                ->findByEmail($email);

        /*
         * Usuario inexistente.
         *
         * No registramos el intento contra
         * un usuario inexistente para evitar
         * estados de seguridad innecesarios.
         */
        if (!$user) {

            error_log(
                "[AUTH] Usuario no encontrado: {$email}"
            );

            throw new DomainException(
                'Credenciales incorrectas',
                401
            );
        }


        $userId =
            (int) $user['id'];


        /*
         * Usuario inactivo.
         */
        if (
            (int) $user['is_active'] !== 1
        ) {

            error_log(
                "[AUTH] Usuario inactivo: {$email}"
            );

            throw new DomainException(
                'Usuario inactivo en el sistema',
                401
            );
        }


        /*
         * Verificar bloqueo.
         */
        if (
            $this->securityService
                ->isLocked($userId)
        ) {

            error_log(
                "[AUTH] Usuario bloqueado: {$email}"
            );

            throw new DomainException(
                'La cuenta está temporalmente bloqueada. Intenta nuevamente más tarde.',
                429
            );
        }


        /*
         * Verificar contraseña.
         */
        if (
            !isset($user['password_hash']) ||
            !password_verify(
                $password,
                $user['password_hash']
            )
        ) {

            /*
             * Registrar intento fallido.
             */
            $this->securityService
                ->registerFailedLogin(
                    (int) $user['id']
                );

            error_log(
                "[AUTH] password_verify() falló para: {$email}"
            );

            /*
             * Después de registrar el intento
             * verificamos si acaba de quedar bloqueado.
             */
            if (
                $this->securityService
                    ->isLocked($userId)
            ) {

                throw new DomainException(
                    'Se alcanzó el número máximo de intentos. La cuenta ha sido bloqueada temporalmente.',
                    429
                );
            }

            throw new DomainException(
                'Credenciales incorrectas',
                401
            );
        }


        /*
         * Login correcto.
         *
         * Reinicia contador de intentos
         * y elimina cualquier bloqueo anterior.
         */
        $this->securityService
            ->registerSuccessfulLogin(
                (int) $user['id']
            );


        /*
         * Actualizar hash si el algoritmo
         * actual necesita ser actualizado.
         */
        $this->rehashPasswordIfNeeded(
            $user,
            $password
        );


        /*
         * Crear sesión.
         *
         * Auth::login() regenera el ID
         * de sesión para evitar session fixation.
         */
        Auth::login($user);


        if ($remember) {

            Auth::createRememberToken(
                (int) $user['id']
            );

        } else {

            Auth::clearRememberToken();

        }


        /*
         * Crear una nueva sesión exclusiva.
         *
         * Si el usuario estaba conectado desde otro
         * navegador, esa sesión queda invalidada.
         */
        $this->securityService
            ->initializeSession(
                (int) $user['id']
            );


        /*
         * Verificar expiración de contraseña.
         *
         * Por ahora únicamente dejamos
         * el estado almacenado en sesión.
         *
         * La restricción de acceso y la
         * pantalla de cambio de contraseña
         * la implementaremos en el siguiente paso.
         */
        $_SESSION[
        'password_expired'
        ] =
            $this->securityService
                ->isPasswordExpired($user);


        return [
            'user_id' =>
                $userId,

            'email' =>
                $user['email'],

            'first_name' =>
                $user['first_name'],

            'last_name' =>
                $user['last_name'],

            'role_id' =>
                (int) $user['role_id'],

            'role_name' =>
                strtolower(
                    $user['role_name']
                ),

            'branch_id' =>
                strtolower(
                    trim(
                        $user['role_name']
                    )
                ) === 'superadmin'
                    ? null
                    : (
                $user['branch_id'] !== null
                    ? (int) $user['branch_id']
                    : null
                ),

            'branch_name' =>
                strtolower(
                    trim(
                        $user['role_name']
                    )
                ) === 'superadmin'
                    ? null
                    : (
                    $user['branch_name']
                    ?? null
                ),

            'password_expired' =>
                (bool) $_SESSION[
                'password_expired'
                ]
        ];
    }


    /** Solicitar recuperación de contraseña. */
    public function requestPasswordReset(string $identifier): void
    {
        $user = $this->userRepository->findForPasswordRecovery($identifier);

        if (
            !$user ||
            (int) ($user['is_active'] ?? 0) !== 1 ||
            !filter_var((string) ($user['email'] ?? ''), FILTER_VALIDATE_EMAIL)
        ) {
            return;
        }

        $userId = (int) $user['id'];
        $this->userRepository->deletePasswordResetTokens($userId);

        $plainToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $plainToken);
        $expiresAt = new DateTime();
        $expiresAt->modify('+30 minutes');

        $this->userRepository->createPasswordResetToken(
            $userId,
            $tokenHash,
            $expiresAt
        );

        $resetUrl = rtrim(APP_URL, '/') .
            '/restablecer-contrasena?token=' .
            rawurlencode($plainToken);

        try {
            $this->emailService->sendPasswordReset($user, $resetUrl);
        } catch (Throwable $e) {
            $this->userRepository->deletePasswordResetTokens($userId);

            error_log(
                '[AUTH_PASSWORD_RESET_EMAIL_ERROR] ' .
                $e->getMessage() . ' | ' .
                $e->getFile() . ':' .
                $e->getLine()
            );

            /*
             * No propagamos el error para evitar que una falla SMTP
             * permita enumerar qué cuentas existen.
             */
            return;
        }
    }

    /**
     * Restablecer contraseña con un token vigente.
     */
    public function resetPassword(
        string $token,
        string $password
    ): void {

        $token = trim($token);

        /*
         * Validar token recibido.
         */
        if ($token === '') {

            throw new DomainException(
                'El enlace de recuperación no es válido o ha expirado.',
                400
            );
        }


        /*
         * Validar longitud mínima.
         */
        if (strlen($password) < 8) {

            throw new DomainException(
                'La contraseña debe tener mínimo 8 caracteres.',
                422
            );
        }


        /*
         * Convertir el token plano recibido
         * al hash almacenado en BD.
         */
        $tokenHash = hash(
            'sha256',
            $token
        );


        /*
         * Buscar token vigente.
         */
        $tokenData =
            $this->userRepository
                ->findValidPasswordResetToken(
                    $tokenHash
                );


        /*
         * Token inexistente, expirado o
         * usuario inactivo.
         */
        if (
            !$tokenData ||
            (int) ($tokenData['is_active'] ?? 0) !== 1
        ) {

            throw new DomainException(
                'El enlace de recuperación no es válido o ha expirado.',
                400
            );
        }


        /*
         * ESTE es el usuario correcto.
         *
         * El user_id viene del token encontrado.
         */
        $userId = (int) $tokenData['user_id'];


        /*
         * Generar nuevo hash de contraseña.
         */
        $passwordHash = password_hash(
            $password,
            PASSWORD_BCRYPT
        );


        if ($passwordHash === false) {

            throw new RuntimeException(
                'No fue posible generar la contraseña.'
            );
        }


        /*
         * Actualizar contraseña.
         */
        $this->userRepository->updatePasswordHash(
            $userId,
            $passwordHash
        );


        /*
         * El token de recuperación es de UN SOLO USO.
         *
         * Después de cambiar la contraseña,
         * se eliminan los tokens de recuperación.
         */
        $this->userRepository->deletePasswordResetTokens(
            $userId
        );


        /*
         * Invalidar sesiones existentes.
         */
        $this->securityService->invalidateUserSessions(
            $userId
        );


        /*
         * Registrar evento de seguridad.
         */
        $this->userRepository->logAuthEvent(
            $userId,
            'password_reset'
        );
    }

    /**
     * Cerrar sesión.
     */
    public function logout(): void
    {
        Auth::logout();
    }


    /**
     * Actualiza el hash si el algoritmo
     * actual necesita ser actualizado.
     */
    private function rehashPasswordIfNeeded(
        array $user,
        string $password
    ): void {

        if (
            !password_needs_rehash(
                $user['password_hash'],
                PASSWORD_BCRYPT
            )
        ) {
            return;
        }

        $newHash =
            password_hash(
                $password,
                PASSWORD_BCRYPT
            );

        $this->userRepository
            ->updatePasswordHash(
                (int) $user['id'],
                $newHash
            );
    }
}