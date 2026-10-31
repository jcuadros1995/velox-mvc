<?php
// app/Repositories/OrderRepository.php

class OrderRepository
{
    private PDO $db;

    private string $table = 'orders';

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Busca una orden por ID.
     */
    public function find(int $id): ?array
    {
        $sql = "
            SELECT *
            FROM {$this->table}
            WHERE id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Crea una orden junto con sus ítems
     * y descuenta el inventario correspondiente.
     */
    public function createOrderWithItems(
        array $orderData,
        array $items
    ): int {
        try {
            $this->db->beginTransaction();

            /*
             * 1. Crear cabecera de la orden.
             */
            $orderId = $this->create($orderData);

            /*
             * 2. Preparar inserción de ítems.
             */
            $stmtItem = $this->db->prepare("
                INSERT INTO order_items
                (
                    order_id,
                    item_id,
                    quantity,
                    unit_price,
                    total_price,
                    metadata
                )
                VALUES
                (
                    :order_id,
                    :item_id,
                    :quantity,
                    :unit_price,
                    :total_price,
                    :metadata
                )
            ");

            /*
             * 3. Preparar actualización de inventario.
             *
             * IMPORTANTE:
             * Se utilizan dos placeholders diferentes para quantity.
             *
             * Esto evita el problema HY093 provocado por reutilizar
             * :qty en más de una posición de la misma sentencia.
             */
            $stmtStock = $this->db->prepare("
                UPDATE inventory
                SET stock_quantity = stock_quantity - :qty_update
                WHERE item_id = :item_id
                  AND branch_id = :branch_id
                  AND stock_quantity >= :qty_check
            ");

            foreach ($items as $item) {

                $unitPrice = (float) $item['unit_price'];
                $qty = (int) $item['quantity'];

                $totalPrice = $unitPrice * $qty;

                /*
                 * Insertar detalle de la orden.
                 */
                $stmtItem->execute([
                    'order_id' => $orderId,
                    'item_id' => $item['item_id'],
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'metadata' => !empty($item['metadata'])
                        ? json_encode($item['metadata'])
                        : null
                ]);

                /*
                 * Descontar inventario.
                 */
                $stmtStock->execute([
                    'qty_update' => $qty,
                    'qty_check' => $qty,
                    'item_id' => $item['item_id'],
                    'branch_id' => $orderData['branch_id']
                ]);
            }

            $this->db->commit();

            return $orderId;

        } catch (Throwable $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Obtiene una orden con todos sus ítems.
     */
    public function getOrderDetails(int $orderId): ?array
    {
        $order = $this->find($orderId);

        if (!$order) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT
                oi.*,
                i.name AS item_name,
                i.type AS item_type
            FROM order_items oi
            INNER JOIN items i
                ON oi.item_id = i.id
            WHERE oi.order_id = :order_id
        ");

        $stmt->execute([
            'order_id' => $orderId
        ]);

        $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $order;
    }

    /**
     * Crea un registro en orders.
     */
    private function create(array $data): int
    {
        $fields = array_keys($data);

        $fieldNames = implode(', ', $fields);

        $placeholders = ':' . implode(
                ', :',
                $fields
            );

        $sql = "
            INSERT INTO {$this->table}
            ({$fieldNames})
            VALUES
            ({$placeholders})
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }
}