<?php

class PaymentRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function createPendingAttempt(
        int $orderId,
        int $customerId,
        string $reference,
        float $amount,
        string $currency = 'COP'
    ): void {
        if ($orderId <= 0 || $customerId <= 0 || $reference === '' || $amount <= 0 || $currency !== 'COP') {
            throw new InvalidArgumentException('No fue posible preparar el intento de pago.');
        }

        try {
            $this->db->beginTransaction();

            $orderStmt = $this->db->prepare("
                SELECT status
                FROM orders
                WHERE id = :order_id
                  AND customer_id = :customer_id
                LIMIT 1
                FOR UPDATE
            ");
            $orderStmt->execute([
                'order_id' => $orderId,
                'customer_id' => $customerId
            ]);
            $orderStatus = $orderStmt->fetchColumn();

            if ($orderStatus !== 'pending') {
                throw new DomainException('El pedido ya no está pendiente de pago.', 409);
            }

            $pendingStmt = $this->db->prepare("
                SELECT id
                FROM payment_transactions
                WHERE order_id = :order_id
                  AND status = 'pending'
                LIMIT 1
                FOR UPDATE
            ");
            $pendingStmt->execute(['order_id' => $orderId]);

            if ($pendingStmt->fetchColumn()) {
                throw new DomainException('Este pedido ya tiene un intento de pago pendiente. Espera su confirmación antes de iniciar otro.', 409);
            }

            $stmt = $this->db->prepare("
                INSERT INTO payment_transactions (
                    order_id,
                    customer_id,
                    gateway,
                    reference,
                    amount,
                    currency,
                    status
                ) VALUES (
                    :order_id,
                    :customer_id,
                    'wompi',
                    :reference,
                    :amount,
                    :currency,
                    'pending'
                )
            ");
            $stmt->execute([
                'order_id' => $orderId,
                'customer_id' => $customerId,
                'reference' => $reference,
                'amount' => round($amount, 2),
                'currency' => $currency
            ]);

            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Persiste un evento únicamente después de que WompiAdapter valide
     * la firma. La referencia y el importe se contrastan con el intento
     * generado por el servidor, nunca con valores del navegador.
     */
    public function processVerifiedEvent(array $event): array
    {
        $reference = trim((string) ($event['reference'] ?? ''));
        $transactionId = trim((string) ($event['gateway_reference'] ?? ''));
        $status = strtolower(trim((string) ($event['status'] ?? '')));
        $amount = (float) ($event['amount'] ?? 0);
        $currency = strtoupper(trim((string) ($event['currency'] ?? '')));

        if (
            $reference === '' ||
            !in_array($status, ['pending', 'approved', 'declined', 'refunded', 'error'], true) ||
            $amount <= 0 ||
            $currency !== 'COP'
        ) {
            throw new InvalidArgumentException('La transacción recibida por Wompi no es válida.');
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                SELECT *
                FROM payment_transactions
                WHERE reference = :reference
                  AND gateway = 'wompi'
                LIMIT 1
                FOR UPDATE
            ");
            $stmt->execute(['reference' => $reference]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$payment) {
                throw new DomainException('No existe un intento de pago para la referencia recibida.', 404);
            }

            if (
                abs(round((float) $payment['amount'], 2) - round($amount, 2)) > 0.01 ||
                (string) $payment['currency'] !== $currency
            ) {
                throw new DomainException('El importe o la moneda del pago no coinciden con la orden.', 409);
            }

            if (
                $transactionId !== '' &&
                !empty($payment['gateway_transaction_id']) &&
                !hash_equals((string) $payment['gateway_transaction_id'], $transactionId)
            ) {
                throw new DomainException('La referencia está asociada a otra transacción de Wompi.', 409);
            }

            $currentStatus = (string) $payment['status'];
            $nextStatus = $this->resolveStatusTransition($currentStatus, $status);

            $rawPayload = json_encode(
                $event['raw'] ?? [],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );

            $update = $this->db->prepare("
                UPDATE payment_transactions
                SET
                    gateway_transaction_id = COALESCE(NULLIF(:transaction_id, ''), gateway_transaction_id),
                    status = :status,
                    raw_payload = :raw_payload,
                    processed_at = CURRENT_TIMESTAMP,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            $update->execute([
                'transaction_id' => $transactionId,
                'status' => $nextStatus,
                'raw_payload' => $rawPayload === false ? null : $rawPayload,
                'id' => (int) $payment['id']
            ]);

            if ($nextStatus !== $currentStatus) {
                if ($nextStatus === 'approved') {
                    $this->markOrderPaid((int) $payment['order_id']);
                    $this->recordLegacyApprovedPayment($payment);
                } elseif (in_array($nextStatus, ['declined', 'error'], true)) {
                    $this->cancelOrderAndReleaseResources((int) $payment['order_id']);
                } elseif ($nextStatus === 'refunded') {
                    if ($currentStatus === 'pending') {
                        $this->cancelOrderAndReleaseResources((int) $payment['order_id']);
                    } else {
                        $this->markOrderRefunded((int) $payment['order_id']);
                    }
                }
            }

            $this->db->commit();

            return [
                'reference' => $reference,
                'status' => $nextStatus,
                'order_id' => (int) $payment['order_id']
            ];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    private function resolveStatusTransition(string $current, string $incoming): string
    {
        if ($current === 'approved') {
            return $incoming === 'refunded' ? 'refunded' : 'approved';
        }

        if ($current === 'refunded') {
            return 'refunded';
        }

        if (in_array($current, ['declined', 'error'], true)) {
            return $current;
        }

        return $incoming;
    }

    private function markOrderPaid(int $orderId): void
    {
        $check = $this->db->prepare("SELECT status FROM orders WHERE id = :id LIMIT 1 FOR UPDATE");
        $check->execute(['id' => $orderId]);
        $status = $check->fetchColumn();

        if ($status === false) {
            throw new DomainException('No se encontró la orden asociada al pago.', 404);
        }

        if ($status !== 'pending') {
            // El pago se conserva en el ledger; una orden cancelada
            // requiere revisión operativa y no se reactiva automáticamente.
            return;
        }

        $stmt = $this->db->prepare("
            UPDATE orders
            SET status = 'confirmed'
            WHERE id = :id
              AND status = 'pending'
        ");
        $stmt->execute(['id' => $orderId]);
    }

    private function markOrderRefunded(int $orderId): void
    {
        $stmt = $this->db->prepare("
            UPDATE orders
            SET status = 'refunded'
            WHERE id = :id
              AND status <> 'cancelled'
        ");
        $stmt->execute(['id' => $orderId]);
    }

    /**
     * Las órdenes pendientes reservan inventario desde su creación.
     * Si el pago falla, libera ese inventario y devuelve el cupón
     * consumido, todo dentro de la transacción del webhook.
     */
    private function cancelOrderAndReleaseResources(int $orderId): void
    {
        $orderStmt = $this->db->prepare("
            SELECT id, branch_id, status
            FROM orders
            WHERE id = :id
            LIMIT 1
            FOR UPDATE
        ");
        $orderStmt->execute(['id' => $orderId]);
        $order = $orderStmt->fetch(PDO::FETCH_ASSOC);

        if (!$order || $order['status'] !== 'pending') {
            return;
        }

        $itemsStmt = $this->db->prepare("
            SELECT item_id, quantity, metadata
            FROM order_items
            WHERE order_id = :order_id
        ");
        $itemsStmt->execute(['order_id' => $orderId]);

        foreach ($itemsStmt->fetchAll(PDO::FETCH_ASSOC) as $item) {
            $metadata = json_decode((string) ($item['metadata'] ?? ''), true);
            if (!is_array($metadata) || empty($metadata['requires_inventory'])) {
                continue;
            }

            $units = max(1, (int) ($metadata['units_per_package'] ?? 1));
            $restoreQuantity = (int) $item['quantity'] * $units;
            $restore = $this->db->prepare("
                UPDATE inventory
                SET stock_quantity = stock_quantity + :quantity
                WHERE item_id = :item_id
                  AND branch_id = :branch_id
            ");
            $restore->execute([
                'quantity' => $restoreQuantity,
                'item_id' => (int) $item['item_id'],
                'branch_id' => (int) $order['branch_id']
            ]);
        }

        $redemption = $this->db->prepare("
            SELECT coupon_id
            FROM coupon_redemptions
            WHERE order_id = :order_id
            LIMIT 1
        ");
        $redemption->execute(['order_id' => $orderId]);
        $couponId = $redemption->fetchColumn();

        if ($couponId) {
            $delete = $this->db->prepare("DELETE FROM coupon_redemptions WHERE order_id = :order_id");
            $delete->execute(['order_id' => $orderId]);

            if ($delete->rowCount() === 1) {
                $decrement = $this->db->prepare("
                    UPDATE coupons
                    SET usage_count = GREATEST(usage_count - 1, 0),
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id
                ");
                $decrement->execute(['id' => (int) $couponId]);
            }
        }

        $cancel = $this->db->prepare("
            UPDATE orders
            SET status = 'cancelled'
            WHERE id = :id
              AND status = 'pending'
        ");
        $cancel->execute(['id' => $orderId]);
    }

    /**
     * Mantiene la tabla de pagos existente que utiliza el dashboard.
     * El ledger payment_transactions es la fuente de verdad del checkout.
     */
    private function recordLegacyApprovedPayment(array $payment): void
    {
        try {
            $gateway = $this->db->query("
                SELECT id
                FROM payment_gateways
                WHERE LOWER(name) = 'wompi'
                LIMIT 1
            ")->fetchColumn();

            if (!$gateway) {
                return;
            }

            $exists = $this->db->prepare("
                SELECT id
                FROM payments
                WHERE order_id = :order_id
                  AND gateway_id = :gateway_id
                  AND status = 'approved'
                LIMIT 1
            ");
            $exists->execute([
                'order_id' => (int) $payment['order_id'],
                'gateway_id' => (int) $gateway
            ]);

            if ($exists->fetchColumn()) {
                return;
            }

            $insert = $this->db->prepare("
                INSERT INTO payments (
                    order_id, gateway_id, amount, status, created_at
                ) VALUES (
                    :order_id, :gateway_id, :amount, 'approved', CURRENT_TIMESTAMP
                )
            ");
            $insert->execute([
                'order_id' => (int) $payment['order_id'],
                'gateway_id' => (int) $gateway,
                'amount' => (float) $payment['amount']
            ]);
        } catch (Throwable $e) {
            // La persistencia principal ya queda en payment_transactions.
            error_log('[PAYMENT_LEGACY_SYNC] ' . $e->getMessage());
        }
    }

    public function findForCustomer(string $reference, int $customerId): ?array
    {
        if ($reference === '' || $customerId <= 0) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT
                pt.reference,
                pt.gateway_transaction_id,
                pt.amount,
                pt.currency,
                pt.status AS payment_status,
                pt.created_at,
                pt.updated_at,
                o.id AS order_id,
                o.order_number,
                o.status AS order_status,
                o.total AS order_total
            FROM payment_transactions pt
            INNER JOIN orders o ON o.id = pt.order_id
            WHERE pt.reference = :reference
              AND pt.customer_id = :customer_id
            ORDER BY pt.id DESC
            LIMIT 1
        ");
        $stmt->execute([
            'reference' => $reference,
            'customer_id' => $customerId
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
