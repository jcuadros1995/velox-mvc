<?php

class SecurityRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db =
            Database::getConnection();
    }


    /*
    |--------------------------------------------------------------------------
    | SECURITY STATE
    |--------------------------------------------------------------------------
    */

    public function getByUserId(
        int $userId
    ): array {

        $stmt =
            $this->db->prepare(
                "
                SELECT
                    user_id,
                    failed_attempts,
                    locked_until,
                    session_version
                FROM auth_security
                WHERE user_id = :user_id
                LIMIT 1
                "
            );

        $stmt->execute([
            ':user_id' => $userId
        ]);

        $row =
            $stmt->fetch(
                PDO::FETCH_ASSOC
            );

        if (!$row) {

            $this->create($userId);

            return [
                'user_id' => $userId,
                'failed_attempts' => 0,
                'locked_until' => null,
                'session_version' => 1
            ];
        }

        return $row;
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create(
        int $userId
    ): void {

        $stmt =
            $this->db->prepare(
                "
                INSERT INTO auth_security (
                    user_id,
                    failed_attempts,
                    locked_until,
                    session_version
                )
                VALUES (
                    :user_id,
                    0,
                    NULL,
                    1
                )
                ON DUPLICATE KEY UPDATE
                    user_id = user_id
                "
            );

        $stmt->execute([
            ':user_id' => $userId
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | FAILED LOGIN
    |--------------------------------------------------------------------------
    */

    public function incrementFailedAttempts(
        int $userId
    ): int {

        $this->create($userId);

        $stmt =
            $this->db->prepare(
                "
                UPDATE auth_security
                SET failed_attempts =
                    failed_attempts + 1
                WHERE user_id = :user_id
                "
            );

        $stmt->execute([
            ':user_id' => $userId
        ]);

        $state =
            $this->getByUserId(
                $userId
            );

        return (int)
        $state['failed_attempts'];
    }


    /*
    |--------------------------------------------------------------------------
    | RESET FAILED ATTEMPTS
    |--------------------------------------------------------------------------
    */

    public function resetFailedAttempts(
        int $userId
    ): void {

        $this->create($userId);

        $stmt =
            $this->db->prepare(
                "
                UPDATE auth_security
                SET
                    failed_attempts = 0,
                    locked_until = NULL
                WHERE user_id = :user_id
                "
            );

        $stmt->execute([
            ':user_id' => $userId
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | LOCK
    |--------------------------------------------------------------------------
    */

    public function lock(
        int $userId,
        DateTimeInterface $until
    ): void {

        $this->create($userId);

        $stmt =
            $this->db->prepare(
                "
                UPDATE auth_security
                SET locked_until = :locked_until
                WHERE user_id = :user_id
                "
            );

        $stmt->execute([
            ':user_id' =>
                $userId,

            ':locked_until' =>
                $until->format(
                    'Y-m-d H:i:s'
                )
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | SESSION VERSION
    |--------------------------------------------------------------------------
    */

    public function getSessionVersion(
        int $userId
    ): int {

        $state =
            $this->getByUserId(
                $userId
            );

        return (int)
        $state['session_version'];
    }


    public function incrementSessionVersion(
        int $userId
    ): int {

        $this->create($userId);

        $stmt =
            $this->db->prepare(
                "
                UPDATE auth_security
                SET session_version =
                    session_version + 1
                WHERE user_id = :user_id
                "
            );

        $stmt->execute([
            ':user_id' => $userId
        ]);

        return $this->getSessionVersion(
            $userId
        );
    }

    /**
     * Iniciar una nueva sesión exclusiva para el usuario.
     *
     * Cada nuevo login incrementa session_version.
     * Esto invalida cualquier sesión anterior.
     */
    public function startExclusiveSession(
        int $userId,
        string $sessionToken
    ): int {

        $this->create($userId);

        $stmt = $this->db->prepare(
            "
        UPDATE auth_security
        SET
            session_version =
                session_version + 1,
            active_session_token =
                :active_session_token,
            last_login_at =
                NOW()
        WHERE user_id = :user_id
        "
        );

        $stmt->execute([
            ':user_id' =>
                $userId,

            ':active_session_token' =>
                $sessionToken
        ]);

        return $this->getSessionVersion(
            $userId
        );
    }


    /**
     * Verificar si el token corresponde
     * a la sesión actualmente activa.
     */
    public function isActiveSession(
        int $userId,
        string $sessionToken
    ): bool {

        $stmt = $this->db->prepare(
            "
        SELECT 1
        FROM auth_security
        WHERE
            user_id = :user_id
            AND active_session_token = :token
        LIMIT 1
        "
        );

        $stmt->execute([
            ':user_id' =>
                $userId,

            ':token' =>
                $sessionToken
        ]);

        return (bool) $stmt->fetchColumn();
    }


    /**
     * Elimina la sesión activa únicamente
     * si el token corresponde a la sesión actual.
     *
     * Esto evita que una sesión antigua pueda
     * cerrar accidentalmente la sesión nueva.
     */
    public function clearActiveSession(
        int $userId,
        string $sessionToken
    ): void {

        $stmt = $this->db->prepare(
            "
        UPDATE auth_security
        SET
            active_session_token = NULL
        WHERE
            user_id = :user_id
            AND active_session_token = :token
        "
        );

        $stmt->execute([
            ':user_id' =>
                $userId,

            ':token' =>
                $sessionToken
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | GLOBAL SESSION INVALIDATION
    |--------------------------------------------------------------------------
    */

    public function incrementAllSessionVersions(): void
    {
        $this->db->exec(
            "
            UPDATE auth_security
            SET session_version =
                session_version + 1
            "
        );
    }
}