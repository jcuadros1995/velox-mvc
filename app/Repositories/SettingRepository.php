<?php
// app/Repositories/SettingRepository.php

class SettingRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene todas las configuraciones agrupadas.
     *
     * Las configuraciones específicas de sucursal
     * tienen prioridad sobre las globales.
     */
    public function getAllGrouped(?int $branchId = null): array
    {
        if ($branchId !== null) {
            $sql = "
                SELECT
                    id,
                    branch_id,
                    setting_group,
                    setting_key,
                    setting_value,
                    value_type,
                    is_public
                FROM system_settings
                WHERE branch_id = :branch_id
                   OR branch_id IS NULL
                ORDER BY
                    setting_group ASC,
                    setting_key ASC,
                    branch_id IS NULL ASC
            ";

            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                'branch_id' => $branchId
            ]);

        } else {

            $sql = "
                SELECT
                    id,
                    branch_id,
                    setting_group,
                    setting_key,
                    setting_value,
                    value_type,
                    is_public
                FROM system_settings
                WHERE branch_id IS NULL
                ORDER BY
                    setting_group ASC,
                    setting_key ASC
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->groupRows($rows);
    }

    /**
     * Obtiene una configuración concreta.
     */
    public function get(
        string $group,
        string $key,
        ?int $branchId = null
    ): ?array {

        /*
         * Primero buscamos la configuración específica
         * de la sucursal.
         */
        if ($branchId !== null) {

            $sql = "
                SELECT
                    setting_value,
                    value_type
                FROM system_settings
                WHERE setting_group = :setting_group
                  AND setting_key = :setting_key
                  AND branch_id = :branch_id
                LIMIT 1
            ";

            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                'setting_group' => $group,
                'setting_key' => $key,
                'branch_id' => $branchId
            ]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                return $row;
            }
        }

        /*
         * Si no existe configuración específica,
         * buscamos la configuración global.
         */
        $sql = "
            SELECT
                setting_value,
                value_type
            FROM system_settings
            WHERE setting_group = :setting_group
              AND setting_key = :setting_key
              AND branch_id IS NULL
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'setting_group' => $group,
            'setting_key' => $key
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Buscar configuración existente.
     */
    public function find(
        string $group,
        string $key,
        ?int $branchId = null
    ): ?int {

        if ($branchId === null) {

            $sql = "
                SELECT id
                FROM system_settings
                WHERE setting_group = :setting_group
                  AND setting_key = :setting_key
                  AND branch_id IS NULL
                LIMIT 1
            ";

            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                'setting_group' => $group,
                'setting_key' => $key
            ]);

        } else {

            $sql = "
                SELECT id
                FROM system_settings
                WHERE setting_group = :setting_group
                  AND setting_key = :setting_key
                  AND branch_id = :branch_id
                LIMIT 1
            ";

            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                'setting_group' => $group,
                'setting_key' => $key,
                'branch_id' => $branchId
            ]);
        }

        $id = $stmt->fetchColumn();

        return $id !== false
            ? (int) $id
            : null;
    }

    /**
     * Actualiza una configuración existente.
     */
    public function update(
        int $id,
        string $storedValue,
        string $valueType,
        int $isPublic
    ): bool {

        $sql = "
            UPDATE system_settings
            SET
                setting_value = :setting_value,
                value_type = :value_type,
                is_public = :is_public
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'setting_value' => $storedValue,
            'value_type' => $valueType,
            'is_public' => $isPublic,
            'id' => $id
        ]);
    }

    /**
     * Crea una nueva configuración.
     */
    public function insert(
        ?int $branchId,
        string $group,
        string $key,
        string $storedValue,
        string $valueType,
        int $isPublic
    ): bool {

        $sql = "
            INSERT INTO system_settings
            (
                branch_id,
                setting_group,
                setting_key,
                setting_value,
                value_type,
                is_public
            )
            VALUES
            (
                :branch_id,
                :setting_group,
                :setting_key,
                :setting_value,
                :value_type,
                :is_public
            )
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'branch_id' => $branchId,
            'setting_group' => $group,
            'setting_key' => $key,
            'setting_value' => $storedValue,
            'value_type' => $valueType,
            'is_public' => $isPublic
        ]);
    }

    /**
     * Inicia una transacción.
     */
    public function beginTransaction(): void
    {
        $this->db->beginTransaction();
    }

    /**
     * Confirma una transacción.
     */
    public function commit(): void
    {
        $this->db->commit();
    }

    /**
     * Revierte una transacción.
     */
    public function rollBack(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
    }

    /**
     * Comprueba si existe una transacción activa.
     */
    public function inTransaction(): bool
    {
        return $this->db->inTransaction();
    }

    /**
     * Agrupa los resultados.
     *
     * Las configuraciones de sucursal prevalecen
     * sobre las configuraciones globales.
     */
    private function groupRows(array $rows): array
    {
        $grouped = [];

        foreach ($rows as $row) {

            $group = $row['setting_group'];
            $key = $row['setting_key'];

            if (
                !isset($grouped[$group][$key])
                || $row['branch_id'] !== null
            ) {
                $grouped[$group][$key] = $this->castValue(
                    $row['setting_value'],
                    $row['value_type']
                );
            }
        }

        return $grouped;
    }

    /**
     * Convierte el valor almacenado según value_type.
     */
    private function castValue(
        $value,
        ?string $type
    ) {
        switch ($type) {

            case 'integer':
                return (int) $value;

            case 'boolean':
                return (bool) ((int) $value);

            case 'json':

                if ($value === null || $value === '') {
                    return [];
                }

                $decoded = json_decode(
                    $value,
                    true
                );

                return json_last_error() === JSON_ERROR_NONE
                    ? $decoded
                    : [];

            case 'string':
            default:
                return $value ?? '';
        }
    }
}