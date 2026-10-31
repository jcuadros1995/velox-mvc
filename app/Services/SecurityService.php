<?php

class SecurityService
{
    private SecurityRepository $repository;

    private SettingRepository $settingRepository;

    public function __construct()
    {
        $this->repository =
            new SecurityRepository();

        $this->settingRepository =
            new SettingRepository();
    }


    /*
    |--------------------------------------------------------------------------
    | SETTINGS
    |--------------------------------------------------------------------------
    */

    private function getSetting(
        string $key,
               $default
    ) {
        $setting = $this->settingRepository->get(
            'security',
            $key
        );

        if (!$setting) {
            return $default;
        }

        $value = $setting['setting_value'] ?? null;

        if ($setting['value_type'] === 'boolean') {
            return in_array(
                strtolower(trim((string) $value)),
                ['1', 'true', 'yes', 'on'],
                true
            );
        }

        if ($setting['value_type'] === 'integer') {
            return (int) $value;
        }

        return $value;
    }


    public function getMaxAttempts(): int
    {
        return max(
            1,
            (int) $this->getSetting(
                'max_attempts',
                5
            )
        );
    }


    public function getLockoutTime(): int
    {
        return max(
            1,
            (int) $this->getSetting(
                'lockout_time',
                15
            )
        );
    }


    public function getSessionTimeout(): int
    {
        return max(
            0,
            (int) $this->getSetting(
                'session_timeout',
                30
            )
        );
    }


    public function getPasswordExpiry(): int
    {
        return max(
            0,
            (int) $this->getSetting(
                'password_expiry',
                90
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | LOCKOUT
    |--------------------------------------------------------------------------
    */

    public function isLocked(
        int $userId
    ): bool {

        $state =
            $this->repository->getByUserId(
                $userId
            );

        if (
            empty(
            $state['locked_until']
            )
        ) {
            return false;
        }

        $lockedUntil =
            new DateTime(
                $state['locked_until']
            );

        $now =
            new DateTime();

        if ($lockedUntil <= $now) {

            $this->repository
                ->resetFailedAttempts(
                    $userId
                );

            return false;
        }

        return true;
    }


    public function registerFailedLogin(
        int $userId
    ): void {

        $attempts =
            $this->repository
                ->incrementFailedAttempts(
                    $userId
                );

        if (
            $attempts >=
            $this->getMaxAttempts()
        ) {

            $until =
                new DateTime();

            $until->modify(
                '+' .
                $this->getLockoutTime() .
                ' minutes'
            );

            $this->repository->lock(
                $userId,
                $until
            );
        }
    }


    public function registerSuccessfulLogin(
        int $userId
    ): void {

        $this->repository
            ->resetFailedAttempts(
                $userId
            );
    }


    /*
    |--------------------------------------------------------------------------
    | SESSION
    |--------------------------------------------------------------------------
    */

    public function initializeSession(
        int $userId
    ): void {

        /*
         * Auth::login() ya regeneró el ID
         * de sesión antes de llegar aquí.
         *
         * Nunca almacenamos el session_id()
         * directamente en la base de datos.
         */
        $sessionToken = hash(
            'sha256',
            session_id()
        );


        /*
         * Crear una nueva sesión exclusiva.
         *
         * Esto incrementa session_version.
         * Por lo tanto cualquier navegador
         * anterior queda automáticamente invalidado.
         */
        $sessionVersion =
            $this->repository
                ->startExclusiveSession(
                    $userId,
                    $sessionToken
                );


        $_SESSION[
        'security_session_version'
        ] = $sessionVersion;


        $_SESSION[
        'security_session_token'
        ] = $sessionToken;


        $_SESSION[
        'security_last_activity'
        ] = time();
    }


    public function isSessionValid(
        int $userId
    ): bool {

        /*
         * ---------------------------------------------------------
         * 1. VERSION DE SESIÓN
         * ---------------------------------------------------------
         */

        $sessionVersion = (int) (
            $_SESSION[
            'security_session_version'
            ] ?? 0
        );

        if ($sessionVersion <= 0) {
            return false;
        }


        $currentVersion =
            $this->repository
                ->getSessionVersion(
                    $userId
                );

        if (
            $sessionVersion !==
            $currentVersion
        ) {
            return false;
        }


        /*
         * ---------------------------------------------------------
         * 2. TOKEN DE SESIÓN
         * ---------------------------------------------------------
         */

        $sessionToken =
            (string) (
                $_SESSION[
                'security_session_token'
                ] ?? ''
            );

        if ($sessionToken === '') {
            return false;
        }


        if (
            !$this->repository
                ->isActiveSession(
                    $userId,
                    $sessionToken
                )
        ) {
            return false;
        }


        /*
         * ---------------------------------------------------------
         * 3. TIMEOUT
         * ---------------------------------------------------------
         */

        $timeout =
            $this->getSessionTimeout();


        /*
         * 0 = timeout desactivado.
         *
         * Las validaciones anteriores
         * siguen funcionando.
         */
        if ($timeout <= 0) {
            return true;
        }


        $lastActivity = (int) (
            $_SESSION[
            'security_last_activity'
            ] ?? 0
        );

        if ($lastActivity <= 0) {
            return false;
        }


        $elapsed =
            time() -
            $lastActivity;


        if (
            $elapsed >=
            ($timeout * 60)
        ) {
            return false;
        }


        return true;
    }


    public function refreshActivity(): void
    {
        $_SESSION[
        'security_last_activity'
        ] = time();
    }

    /**
     * Cierra la sesión activa del usuario.
     *
     * Solo puede limpiar el registro si el token
     * corresponde a la sesión actualmente activa.
     */
    public function clearCurrentSession(
        int $userId
    ): void {

        $sessionToken =
            (string) (
                $_SESSION[
                'security_session_token'
                ] ?? ''
            );

        if ($sessionToken !== '') {

            $this->repository
                ->clearActiveSession(
                    $userId,
                    $sessionToken
                );
        }

        unset(
            $_SESSION[
            'security_session_token'
            ],
            $_SESSION[
            'security_session_version'
            ],
            $_SESSION[
            'security_last_activity'
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PASSWORD EXPIRY
    |--------------------------------------------------------------------------
    */

    public function isPasswordExpired(
        array $user
    ): bool {

        $expiryDays =
            $this->getPasswordExpiry();

        /*
         * 0 = desactivado.
         */

        if ($expiryDays <= 0) {
            return false;
        }

        $changedAt =
            $user['password_changed_at']
            ?? null;

        if (!$changedAt) {
            return true;
        }

        $passwordDate =
            new DateTime(
                $changedAt
            );

        $expiresAt =
            clone $passwordDate;

        $expiresAt->modify(
            '+' .
            $expiryDays .
            ' days'
        );

        return new DateTime() >=
            $expiresAt;
    }


    /*
    |--------------------------------------------------------------------------
    | GLOBAL LOGOUT
    |--------------------------------------------------------------------------
    */

    /** Invalida las sesiones existentes de un usuario. */
    public function invalidateUserSessions(int $userId): void
    {
        $this->repository->incrementSessionVersion($userId);
    }

    public function invalidateAllSessions(): void
    {
        $this->repository
            ->incrementAllSessionVersions();
    }

    /**
     * Ejecuta el cierre global si la opción
     * force_logout está activada.
     *
     * Es una acción de una sola ejecución:
     *
     * force_logout = 1
     *      ↓
     * incrementa session_version
     *      ↓
     * todas las sesiones quedan inválidas
     *      ↓
     * force_logout = 0
     */
    public function consumeForceLogout(): bool
    {
        $setting = $this->settingRepository->get(
            'security',
            'force_logout'
        );

        if (!$setting) {
            return false;
        }

        $value = $setting['setting_value'] ?? null;

        $enabled = in_array(
            strtolower(trim((string) $value)),
            ['1', 'true', 'yes', 'on'],
            true
        );

        if (!$enabled) {
            return false;
        }


        /*
         * Invalidar absolutamente todas
         * las sesiones existentes.
         */
        $this->repository
            ->incrementAllSessionVersions();


        /*
         * Resetear la opción.
         *
         * force_logout NO debe quedarse en 1,
         * porque entonces se ejecutaría nuevamente
         * cada vez que se guarde configuración.
         */
        $settingId =
            $this->settingRepository->find(
                'security',
                'force_logout',
                null
            );

        if ($settingId !== null) {

            $this->settingRepository->update(
                $settingId,
                '0',
                'boolean',
                0
            );
        }


        return true;
    }
}