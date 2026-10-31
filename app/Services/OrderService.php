<?php
// app/Services/OrderService.php

class OrderService
{
    private OrderRepository $orderRepository;
    private ItemRepository $itemRepository;
    private BranchRepository $branchRepository;
    private CouponService $couponService;

    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
        $this->itemRepository = new ItemRepository();
        $this->branchRepository = new BranchRepository();
        $this->couponService = new CouponService();
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

        $fulfillmentType = strtolower(trim((string) ($input['fulfillment_type'] ?? 'pickup')));

        if (!in_array($fulfillmentType, ['pickup', 'delivery'], true)) {
            throw new InvalidArgumentException(
                'Selecciona si deseas recoger tu pedido o recibirlo a domicilio.'
            );
        }

        $branchId = null;
        $deliveryFee = 0.00;
        $fulfillmentDetails = [
            'fulfillment_type' => $fulfillmentType,
            'delivery_address' => null,
            'delivery_neighborhood' => null,
            'delivery_city' => null,
            'delivery_reference' => null,
            'contact_phone' => null
        ];

        if ($fulfillmentType === 'pickup') {
            $branchId = filter_var(
                $input['branch_id'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$branchId || $branchId <= 0) {
                throw new InvalidArgumentException(
                    'Debes seleccionar la sede donde recogerás tu pedido.'
                );
            }

            if (!$this->branchRepository->findActiveById((int) $branchId)) {
                throw new DomainException(
                    'La sede seleccionada no está disponible para pedidos.',
                    409
                );
            }
        } else {
            $deliveryInput = $input['delivery'] ?? [];

            if (!is_array($deliveryInput)) {
                throw new InvalidArgumentException(
                    'Los datos de la dirección de entrega no son válidos.'
                );
            }

            $deliveryAddress = trim(strip_tags((string) ($deliveryInput['address'] ?? '')));
            $deliveryNeighborhood = trim(strip_tags((string) ($deliveryInput['neighborhood'] ?? '')));
            $deliveryCity = trim(strip_tags((string) ($deliveryInput['city'] ?? '')));
            $deliveryReference = trim(strip_tags((string) ($deliveryInput['reference'] ?? '')));
            $deliveryPhone = preg_replace('/\\D+/', '', (string) ($deliveryInput['phone'] ?? '')) ?: '';

            if (strlen($deliveryAddress) < 6 || strlen($deliveryAddress) > 255) {
                throw new InvalidArgumentException(
                    'Ingresa una dirección de entrega válida (entre 6 y 255 caracteres).'
                );
            }

            if (strlen($deliveryNeighborhood) < 2 || strlen($deliveryNeighborhood) > 120) {
                throw new InvalidArgumentException(
                    'Ingresa un barrio válido para la entrega.'
                );
            }

            if (strlen($deliveryCity) < 2 || strlen($deliveryCity) > 120) {
                throw new InvalidArgumentException(
                    'Ingresa la ciudad o municipio de entrega.'
                );
            }

            if (strlen($deliveryPhone) < 7 || strlen($deliveryPhone) > 15) {
                throw new InvalidArgumentException(
                    'Ingresa un teléfono de contacto válido para el domicilio.'
                );
            }

            if (strlen($deliveryReference) > 255) {
                throw new InvalidArgumentException(
                    'Las indicaciones de entrega no pueden superar los 255 caracteres.'
                );
            }

            $configuredDeliveryFee = getenv('STORE_DELIVERY_FEE');
            $configuredDeliveryFee = ($configuredDeliveryFee === false || trim((string) $configuredDeliveryFee) === '')
                ? '0'
                : trim((string) $configuredDeliveryFee);

            if (!is_numeric($configuredDeliveryFee) || (float) $configuredDeliveryFee < 0) {
                throw new DomainException(
                    'La tarifa de domicilio no está configurada correctamente. Contacta al comercio.',
                    503
                );
            }

            $deliveryFee = round((float) $configuredDeliveryFee, 0);

            $fulfillmentDetails['delivery_address'] = $deliveryAddress;
            $fulfillmentDetails['delivery_neighborhood'] = $deliveryNeighborhood;
            $fulfillmentDetails['delivery_city'] = $deliveryCity;
            $fulfillmentDetails['delivery_reference'] = $deliveryReference !== '' ? $deliveryReference : null;
            $fulfillmentDetails['contact_phone'] = $deliveryPhone;
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

        if ($fulfillmentType === 'delivery') {
            $dispatchBranch = $this->branchRepository->findActiveBranchWithInventory($orderItems);

            if (!$dispatchBranch) {
                throw new DomainException(
                    'No encontramos una sede con inventario suficiente para preparar tu pedido a domicilio. Selecciona recogida en tienda o ajusta los productos.',
                    409
                );
            }

            $branchId = (int) $dispatchBranch['id'];
        }

        if (!$branchId || $branchId <= 0) {
            throw new DomainException(
                'No fue posible determinar la sede que preparará tu pedido.',
                409
            );
        }

        $subtotal = round($subtotal, 2);
        $taxAmount = round($taxAmount, 2);
        $couponData = null;
        $couponCode = trim((string) ($input['coupon_code'] ?? ''));

        if ($couponCode !== '') {
            $couponData = $this->couponService->validateForOrder(
                $couponCode,
                $customerId,
                $subtotal
            );
        }

        $discountAmount = (float) ($couponData['discount_amount'] ?? 0);
        $total = round($subtotal + $taxAmount + $deliveryFee - $discountAmount, 2);

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
                'notes' => $notes !== '' ? $notes : null,
                // Metadatos consumidos por OrderRepository y guardados
                // en order_fulfillment_details, no en la tabla orders.
                'delivery_fee' => $deliveryFee,
                'fulfillment_details' => $fulfillmentDetails
            ],
            $orderItems,
            $couponData === null ? null : [
                'id' => (int) $couponData['id'],
                'code' => (string) $couponData['code'],
                'customer_id' => $customerId,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount
            ]
        );

        return [
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'delivery_fee' => $deliveryFee,
            'fulfillment_type' => $fulfillmentType,
            'branch_id' => (int) $branchId,
            'coupon_code' => $couponData['code'] ?? null,
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
