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
        $stmt = $this->db->prepare("
            SELECT *
            FROM {$this->table}
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id
        ]);

        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        return $order ?: null;
    }

    /**
     * Busca una orden que pertenezca al cliente autenticado.
     */
    public function findForCustomer(
        int $orderId,
        int $customerId
    ): ?array {
        if ($orderId <= 0 || $customerId <= 0) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT *
            FROM {$this->table}
            WHERE id = :id
              AND customer_id = :customer_id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $orderId,
            'customer_id' => $customerId
        ]);

        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        return $order ?: null;
    }

    /**
     * Crea la orden y sus detalles en una sola transacción.
     *
     * El inventario se descuenta únicamente cuando la categoría
     * del producto tiene requires_inventory = 1. Si no existe
     * inventario suficiente, la orden completa se revierte.
     */
    public function createOrderWithItems(
        array $orderData,
        array $items
    ): int {
        try {
            $this->db->beginTransaction();

            $orderId = $this->create($orderData);

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

            $stmtStock = $this->db->prepare("
                UPDATE inventory
                SET stock_quantity = stock_quantity - :quantity_to_subtract
                WHERE item_id = :item_id
                  AND branch_id = :branch_id
                  AND stock_quantity >= :minimum_quantity
            ");

            foreach ($items as $item) {
                $quantity = (int) $item['quantity'];
                $inventoryQuantity = (int) ($item['inventory_quantity'] ?? 0);
                $unitPrice = (float) $item['unit_price'];
                $totalPrice = (float) $item['total_price'];

                $metadata = json_encode(
                    $item['metadata'] ?? [],
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );

                if ($metadata === false) {
                    throw new RuntimeException(
                        'No fue posible preparar los datos del producto.'
                    );
                }

                $stmtItem->execute([
                    'order_id' => $orderId,
                    'item_id' => (int) $item['item_id'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'metadata' => $metadata
                ]);

                if (!empty($item['requires_inventory'])) {
                    if ($inventoryQuantity <= 0) {
                        throw new DomainException(
                            'La cantidad de inventario solicitada no es válida.',
                            409
                        );
                    }

                    $stmtStock->execute([
                        'quantity_to_subtract' => $inventoryQuantity,
                        'minimum_quantity' => $inventoryQuantity,
                        'item_id' => (int) $item['item_id'],
                        'branch_id' => (int) $orderData['branch_id']
                    ]);

                    if ($stmtStock->rowCount() !== 1) {
                        throw new DomainException(
                            'Uno de los productos no tiene inventario suficiente en la sede seleccionada.',
                            409
                        );
                    }
                }
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
     * Obtiene el detalle de una orden solamente si pertenece
     * al cliente que realiza la consulta.
     */
    public function getOrderDetailsForCustomer(
        int $orderId,
        int $customerId
    ): ?array {
        $order = $this->findForCustomer(
            $orderId,
            $customerId
        );

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
            ORDER BY oi.id ASC
        ");

        $stmt->execute([
            'order_id' => $orderId
        ]);

        $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $order;
    }

    /**
     * Inserta la cabecera de la orden.
     */
    private function create(array $data): int
    {
        $fields = array_keys($data);
        $fieldNames = implode(', ', $fields);
        $placeholders = ':' . implode(', :', $fields);

        $stmt = $this->db->prepare("
            INSERT INTO {$this->table}
            ({$fieldNames})
            VALUES
            ({$placeholders})
        ");

        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }
}
