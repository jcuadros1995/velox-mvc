<?php
// app/Services/OrderService.php

class OrderService
{
    private OrderRepository $orderRepository;
    private ItemRepository $itemRepository;
    private BranchRepository $branchRepository;

    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
        $this->itemRepository = new ItemRepository();
        $this->branchRepository = new BranchRepository();
    }

    /**
     * Crea un pedido de ecommerce para el cliente autenticado.
     *
     * Los precios, combos, impuestos e inventario se determinan
     * exclusivamente con información almacenada en el servidor.
     */
    public function createOrder(
        array $input,
        int $customerId
    ): array {
        if ($customerId <= 0) {
            throw new DomainException(
                'Debes iniciar sesión para realizar un pedido.',
                401
            );
        }

        $branchId = filter_var(
            $input['branch_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$branchId || $branchId <= 0) {
            throw new InvalidArgumentException(
                'Debes seleccionar una sede válida.'
            );
        }

        if (!$this->branchRepository->findActiveById((int) $branchId)) {
            throw new DomainException(
                'La sede seleccionada no está disponible para pedidos.',
                409
            );
        }

        $requestedItems = $input['items'] ?? null;

        if (
            !is_array($requestedItems) ||
            empty($requestedItems) ||
            count($requestedItems) > 50
        ) {
            throw new InvalidArgumentException(
                'El pedido debe contener entre 1 y 50 productos.'
            );
        }

        $orderItems = [];
        $subtotal = 0.00;
        $taxAmount = 0.00;

        foreach ($requestedItems as $requestedItem) {
            if (!is_array($requestedItem)) {
                throw new InvalidArgumentException(
                    'Uno de los productos del pedido no es válido.'
                );
            }

            $itemId = filter_var(
                $requestedItem['item_id'] ?? null,
                FILTER_VALIDATE_INT
            );

            $quantity = filter_var(
                $requestedItem['quantity'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$itemId || $itemId <= 0) {
                throw new InvalidArgumentException(
                    'Uno de los productos seleccionados no es válido.'
                );
            }

            if (!$quantity || $quantity < 1 || $quantity > 99) {
                throw new InvalidArgumentException(
                    'La cantidad de cada producto debe estar entre 1 y 99.'
                );
            }

            $product = $this->itemRepository->find((int) $itemId);

            if (
                !$product ||
                (int) ($product['is_active'] ?? 0) !== 1 ||
                (int) ($product['is_available'] ?? 0) !== 1
            ) {
                throw new DomainException(
                    'Uno de los productos ya no está disponible.',
                    409
                );
            }

            $categoryRules = $this->itemRepository->getCategoryRules(
                (int) ($product['category_id'] ?? 0)
            );

            if (!$categoryRules) {
                throw new DomainException(
                    'Uno de los productos no tiene una configuración válida de catálogo.',
                    409
                );
            }

            $requiresInventory = (int) (
                $categoryRules['requires_inventory'] ?? 0
            ) === 1;

            $isCombo = filter_var(
                $requestedItem['is_combo'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );

            $priceOptionId = filter_var(
                $requestedItem['price_option_id']
                    ?? ($requestedItem['option_id'] ?? null),
                FILTER_VALIDATE_INT
            );

            $unitsPerPackage = 1;
            $optionLabel = null;

            if ($priceOptionId !== false && $priceOptionId !== null) {
                if ($priceOptionId <= 0) {
                    throw new InvalidArgumentException(
                        'La presentación seleccionada no es válida.'
                    );
                }

                $options = $this->itemRepository->getPriceOptionsByItemId(
                    (int) $itemId
                );

                $selectedOption = null;

                foreach ($options as $option) {
                    if ((int) $option['id'] === (int) $priceOptionId) {
                        $selectedOption = $option;
                        break;
                    }
                }

                if (!$selectedOption) {
                    throw new DomainException(
                        'La presentación seleccionada ya no está disponible.',
                        409
                    );
                }

                $unitsPerPackage = (int) $selectedOption['quantity'];
                $optionLabel = (string) $selectedOption['quantity_label'];

                if ($unitsPerPackage <= 0) {
                    throw new DomainException(
                        'La presentación del producto tiene una configuración inválida.',
                        409
                    );
                }

                $rawPrice = $isCombo
                    ? ($selectedOption['combo_price'] ?? null)
                    : ($selectedOption['price'] ?? null);

            } else {
                $rawPrice = $isCombo
                    ? ($product['combo_price'] ?? null)
                    : ($product['price'] ?? null);
            }

            if ($rawPrice === null || $rawPrice === '' || !is_numeric($rawPrice)) {
                throw new DomainException(
                    $isCombo
                        ? 'Este producto no tiene un precio de combo disponible.'
                        : 'Uno de los productos no tiene un precio válido.',
                    409
                );
            }

            $unitPrice = round((float) $rawPrice, 2);

            if ($unitPrice < 0) {
                throw new DomainException(
                    'El precio de uno de los productos no es válido.',
                    409
                );
            }

            $taxRate = $product['tax_rate'] ?? 0;

            if (!is_numeric($taxRate) || (float) $taxRate < 0 || (float) $taxRate > 100) {
                throw new DomainException(
                    'La tarifa de impuestos de uno de los productos no es válida.',
                    409
                );
            }

            $lineSubtotal = round($unitPrice * (int) $quantity, 2);
            $lineTax = round(
                $lineSubtotal * ((float) $taxRate / 100),
                2
            );

            $subtotal += $lineSubtotal;
            $taxAmount += $lineTax;

            $orderItems[] = [
                'item_id' => (int) $itemId,
                'quantity' => (int) $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $lineSubtotal,
                'requires_inventory' => $requiresInventory,
                'inventory_quantity' => (
                    (int) $quantity * $unitsPerPackage
                ),
                'metadata' => [
                    'price_option_id' => $priceOptionId ?: null,
                    'price_option_label' => $optionLabel,
                    'units_per_package' => $unitsPerPackage,
                    'is_combo' => $isCombo,
                    'tax_rate' => (float) $taxRate,
                    'requires_inventory' => $requiresInventory
                ]
            ];
        }

        $subtotal = round($subtotal, 2);
        $taxAmount = round($taxAmount, 2);
        $discountAmount = 0.00;
        $total = round($subtotal + $taxAmount, 2);

        if ($total <= 0) {
            throw new DomainException(
                'El total del pedido debe ser mayor que cero.',
                422
            );
        }

        $notes = trim(strip_tags((string) ($input['notes'] ?? '')));

        if (function_exists('mb_substr')) {
            $notes = mb_substr($notes, 0, 1000);
        } else {
            $notes = substr($notes, 0, 1000);
        }

        $orderNumber = 'ORD-' . strtoupper(bin2hex(random_bytes(6)));

        $orderId = $this->orderRepository->createOrderWithItems(
            [
                'order_number' => $orderNumber,
                'branch_id' => (int) $branchId,
                'customer_id' => $customerId,
                'order_type' => 'ecommerce',
                'status' => 'pending',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total' => $total,
                'notes' => $notes !== '' ? $notes : null
            ],
            $orderItems
        );

        return [
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total' => $total
        ];
    }

    /**
     * Obtiene el detalle de un pedido del cliente autenticado.
     */
    public function getOrderDetails(
        int $orderId,
        int $customerId
    ): ?array {
        if ($orderId <= 0 || $customerId <= 0) {
            return null;
        }

        return $this->orderRepository->getOrderDetailsForCustomer(
            $orderId,
            $customerId
        );
    }

    /**
     * Obtiene la cabecera de un pedido del cliente autenticado.
     */
    public function getOrderForCustomer(
        int $orderId,
        int $customerId
    ): ?array {
        return $this->orderRepository->findForCustomer(
            $orderId,
            $customerId
        );
    }
}
