<?php
// app/Repositories/CustomerRepository.php

class CustomerRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Buscar cliente activo por correo electrónico.
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id
            FROM customers
            WHERE email = ?
              AND is_active = 1
            LIMIT 1
        ");

        $stmt->execute([$email]);

        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        return $customer ?: null;
    }

    /**
     * Buscar cliente activo por teléfono.
     */
    public function findByPhone(string $phone): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id
            FROM customers
            WHERE phone = ?
              AND is_active = 1
            LIMIT 1
        ");

        $stmt->execute([$phone]);

        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        return $customer ?: null;
    }

    /**
     * Crear cliente.
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO customers (
                first_name,
                last_name,
                phone,
                email,
                notes,
                is_active,
                created_at,
                updated_at
            )
            VALUES (
                ?,
                ?,
                ?,
                ?,
                ?,
                1,
                NOW(),
                NOW()
            )
        ");

        $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['phone'],
            $data['email'],
            $data['notes'] ?? null
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualizar cliente.
     */
    public function update(
        int $id,
        array $data
    ): void {
        $stmt = $this->db->prepare("
            UPDATE customers
            SET
                first_name = ?,
                last_name = ?,
                phone = ?,
                updated_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['phone'],
            $id
        ]);
    }
}