<?php
// app/Services/OrderService.php

class OrderService
{
    private OrderRepository $orderRepository;

    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
    }

    /**
     * Crea una orden con sus ítems.
     *
     * @param array $input
     * @return array
     * @throws Exception
     */
    public function createOrder(array $input): array
    {
        $subtotal = $this->calculateSubtotal(
            $input['items']
        );

        $taxAmount = (float) (
            $input['tax_amount'] ?? 0
        );

        $discountAmount = (float) (
            $input['discount_amount'] ?? 0
        );

        $total = $subtotal
            + $taxAmount
            - $discountAmount;

        $orderId = $this->orderRepository->createOrderWithItems(
            [
                'order_number' => 'ORD-' . strtoupper(uniqid()),

                'branch_id' => (int) (
                    $input['branch_id'] ?? 1
                ),

                'customer_id' => (int) $input['customer_id'],

                'order_type' => $input['order_type']
                    ?? 'ecommerce',

                'status' => 'pending',

                'subtotal' => $subtotal,

                'tax_amount' => $taxAmount,

                'discount_amount' => $discountAmount,

                'total' => $total,

                'notes' => htmlspecialchars(
                    $input['notes'] ?? ''
                )
            ],
            $input['items']
        );

        return [
            'order_id' => $orderId,
            'total' => $total
        ];
    }

    /**
     * Obtiene el detalle de una orden.
     */
    public function getOrderDetails(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        return $this->orderRepository
            ->getOrderDetails($id);
    }

    /**
     * Calcula el subtotal de los ítems.
     */
    private function calculateSubtotal(
        array $items
    ): float {
        $subtotal = 0;

        foreach ($items as $item) {
            $subtotal +=
                (float) ($item['unit_price'] ?? 0)
                *
                (int) ($item['quantity'] ?? 0);
        }

        return $subtotal;
    }
}