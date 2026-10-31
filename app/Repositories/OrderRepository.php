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

        return $this->attachFulfillmentDetails($order ?: null);
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

        return $this->attachFulfillmentDetails($order ?: null);
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
        array $items,
        ?array $couponData = null
    ): int {
        try {
            $this->db->beginTransaction();

            if ($couponData !== null) {
                $this->applyCouponRedemption($orderData, $couponData);
            }

            $fulfillmentDetails = is_array($orderData['fulfillment_details'] ?? null)
                ? $orderData['fulfillment_details']
                : ['fulfillment_type' => 'pickup'];

            $deliveryFee = round((float) ($orderData['delivery_fee'] ?? 0), 2);
            unset($orderData['fulfillment_details'], $orderData['delivery_fee']);

            $orderId = $this->create($orderData);

            $stmtFulfillment = $this->db->prepare("
                INSERT INTO order_fulfillment_details
                (
                    order_id,
                    fulfillment_type,
                    delivery_address,
                    delivery_neighborhood,
                    delivery_city,
                    delivery_reference,
                    contact_phone,
                    delivery_fee
                )
                VALUES
                (
                    :order_id,
                    :fulfillment_type,
                    :delivery_address,
                    :delivery_neighborhood,
                    :delivery_city,
                    :delivery_reference,
                    :contact_phone,
                    :delivery_fee
                )
            ");

            $stmtFulfillment->execute([
                'order_id' => $orderId,
                'fulfillment_type' => ($fulfillmentDetails['fulfillment_type'] ?? 'pickup') === 'delivery'
                    ? 'delivery'
                    : 'pickup',
                'delivery_address' => $fulfillmentDetails['delivery_address'] ?? null,
                'delivery_neighborhood' => $fulfillmentDetails['delivery_neighborhood'] ?? null,
                'delivery_city' => $fulfillmentDetails['delivery_city'] ?? null,
                'delivery_reference' => $fulfillmentDetails['delivery_reference'] ?? null,
                'contact_phone' => $fulfillmentDetails['contact_phone'] ?? null,
                'delivery_fee' => $deliveryFee
            ]);

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

            if ($couponData !== null) {
                $this->recordCouponRedemption($orderId, $orderData, $couponData);
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
     * Revalida y consume el cupón dentro de la misma transacción
     * que registra la orden. SELECT ... FOR UPDATE evita superar
     * el límite global cuando llegan compras simultáneas.
     */
    private function applyCouponRedemption(array &$orderData, array $couponData): void
    {
        $couponId = (int) ($couponData['id'] ?? 0);
        $customerId = (int) ($couponData['customer_id'] ?? 0);
        $code = strtoupper(trim((string) ($couponData['code'] ?? '')));
        $subtotal = (float) ($orderData['subtotal'] ?? 0);

        if ($couponId <= 0 || $customerId <= 0 || $code === '') {
            throw new DomainException('El cupón enviado no es válido.', 422);
        }

        $stmt = $this->db->prepare("
            SELECT *
            FROM coupons
            WHERE id = :id AND code = :code
            LIMIT 1
            FOR UPDATE
        ");
        $stmt->execute(['id' => $couponId, 'code' => $code]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$coupon || (int) $coupon['is_active'] !== 1) {
            throw new DomainException('El cupón ya no está disponible.', 422);
        }

        $now = new DateTimeImmutable('now', new DateTimeZone('America/Bogota'));
        if (!empty($coupon['starts_at']) && new DateTimeImmutable((string) $coupon['starts_at'], new DateTimeZone('America/Bogota')) > $now) {
            throw new DomainException('El cupón todavía no está vigente.', 422);
        }
        if (!empty($coupon['expires_at']) && new DateTimeImmutable((string) $coupon['expires_at'], new DateTimeZone('America/Bogota')) < $now) {
            throw new DomainException('El cupón ya venció.', 422);
        }
        if ($subtotal < (float) $coupon['minimum_purchase']) {
            throw new DomainException('El pedido ya no cumple la compra mínima del cupón.', 422);
        }
        if ($coupon['usage_limit'] !== null && (int) $coupon['usage_count'] >= (int) $coupon['usage_limit']) {
            throw new DomainException('El cupón alcanzó su límite total de usos.', 422);
        }

        $perCustomerLimit = (int) $coupon['per_customer_limit'];
        if ($perCustomerLimit > 0) {
            $uses = $this->db->prepare("
                SELECT COUNT(*)
                FROM coupon_redemptions
                WHERE coupon_id = :coupon_id
                  AND customer_id = :customer_id
            ");
            $uses->execute(['coupon_id' => $couponId, 'customer_id' => $customerId]);
            if ((int) $uses->fetchColumn() >= $perCustomerLimit) {
                throw new DomainException('Ya alcanzaste el límite de uso de este cupón.', 422);
            }
        }

        $value = (float) $coupon['discount_value'];
        $discount = $coupon['discount_type'] === 'percentage'
            ? ($subtotal * $value / 100)
            : min($value, $subtotal);
        $discount = round(min($discount, $subtotal), 2);

        if (
            abs($discount - (float) ($couponData['discount_amount'] ?? -1)) > 0.01 ||
            abs($discount - (float) ($orderData['discount_amount'] ?? 0)) > 0.01
        ) {
            throw new DomainException('El descuento del cupón cambió. Vuelve a validar el código.', 409);
        }

        $expectedTotal = round(
            (float) $orderData['subtotal'] +
            (float) $orderData['tax_amount'] +
            (float) ($orderData['delivery_fee'] ?? 0) -
            $discount,
            2
        );

        if (abs($expectedTotal - (float) $orderData['total']) > 0.01 || $expectedTotal <= 0) {
            throw new DomainException('El total del pedido no es válido.', 409);
        }
    }

    /**
     * Registra el uso del cupón y actualiza el contador.
     */
    private function recordCouponRedemption(int $orderId, array $orderData, array $couponData): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO coupon_redemptions (
                coupon_id, order_id, customer_id, code, discount_amount
            ) VALUES (
                :coupon_id, :order_id, :customer_id, :code, :discount_amount
            )
        ");
        $stmt->execute([
            'coupon_id' => (int) $couponData['id'],
            'order_id' => $orderId,
            'customer_id' => (int) $couponData['customer_id'],
            'code' => (string) $couponData['code'],
            'discount_amount' => (float) $orderData['discount_amount']
        ]);

        $update = $this->db->prepare("
            UPDATE coupons
            SET usage_count = usage_count + 1,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        $update->execute(['id' => (int) $couponData['id']]);
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
     * Adjunta al pedido la modalidad y los datos de entrega.
     * Los pedidos anteriores a esta funcionalidad se interpretan como recogida.
     */
    private function attachFulfillmentDetails(?array $order): ?array
    {
        if (!$order) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT
                fulfillment_type,
                delivery_address,
                delivery_neighborhood,
                delivery_city,
                delivery_reference,
                contact_phone,
                delivery_fee
            FROM order_fulfillment_details
            WHERE order_id = :order_id
            LIMIT 1
        ");

        $stmt->execute(['order_id' => (int) $order['id']]);
        $fulfillment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$fulfillment) {
            $fulfillment = [
                'fulfillment_type' => 'pickup',
                'delivery_address' => null,
                'delivery_neighborhood' => null,
                'delivery_city' => null,
                'delivery_reference' => null,
                'contact_phone' => null,
                'delivery_fee' => 0
            ];
        }

        $order['fulfillment_type'] = (string) ($fulfillment['fulfillment_type'] ?? 'pickup');
        $order['fulfillment'] = $fulfillment;

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
