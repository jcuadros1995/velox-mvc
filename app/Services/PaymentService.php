<?php
// app/Services/PaymentService.php

class PaymentService
{
    private OrderRepository $orderRepository;

    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
    }

    /**
     * Prepara el checkout únicamente para una orden del cliente.
     *
     * El monto nunca se toma del navegador: se lee de la orden
     * almacenada en la base de datos.
     */
    public function checkout(
        array $input,
        int $customerId
    ): array {
        $orderId = filter_var(
            $input['order_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$orderId || $orderId <= 0 || $customerId <= 0) {
            throw new InvalidArgumentException(
                'El pedido seleccionado no es válido.'
            );
        }

        $gateway = strtolower(trim((string) ($input['gateway'] ?? '')));

        if ($gateway !== 'wompi') {
            throw new InvalidArgumentException(
                'La pasarela de pago seleccionada no está disponible.'
            );
        }

        $order = $this->orderRepository->findForCustomer(
            (int) $orderId,
            $customerId
        );

        if (!$order) {
            throw new DomainException(
                'Pedido no encontrado.',
                404
            );
        }

        if (($order['status'] ?? '') !== 'pending') {
            throw new DomainException(
                'Este pedido ya no está pendiente de pago.',
                409
            );
        }

        $amount = round((float) ($order['total'] ?? 0), 2);

        if ($amount <= 0) {
            throw new DomainException(
                'El monto del pedido no es válido.',
                409
            );
        }

        $gatewayConfig = $this->getGatewayConfig();

        $adapter = PaymentFactory::create(
            'wompi',
            $gatewayConfig
        );

        return $adapter->createPayment([
            'reference' => (string) $order['order_number'],
            'amount' => $amount,
            'currency' => 'COP'
        ]);
    }

    /**
     * Procesa y valida la autenticidad de un webhook de Wompi.
     *
     * La persistencia del pago y la transición del estado de la
     * orden requieren completar el repositorio de pagos.
     */
    public function processWebhook(array $payload): array
    {
        $adapter = PaymentFactory::create(
            'wompi',
            $this->getGatewayConfig()
        );

        return $adapter->handleWebhook($payload);
    }

    /**
     * Lee los secretos desde variables de entorno.
     * No se deben almacenar llaves reales dentro del repositorio.
     */
    private function getGatewayConfig(): array
    {
        $config = [
            'public_key' => trim((string) getenv('WOMPI_PUBLIC_KEY')),
            'private_key' => trim((string) getenv('WOMPI_PRIVATE_KEY')),
            'integrity_secret' => trim((string) getenv('WOMPI_INTEGRITY_SECRET')),
            'events_secret' => trim((string) getenv('WOMPI_EVENTS_SECRET')),
            'is_sandbox' => strtolower(
                trim((string) (getenv('WOMPI_ENVIRONMENT') ?: 'sandbox'))
            ) !== 'production'
        ];

        foreach ([
            'public_key',
            'private_key',
            'integrity_secret',
            'events_secret'
        ] as $key) {
            if ($config[$key] === '') {
                throw new DomainException(
                    'La pasarela Wompi no está configurada en el servidor.',
                    503
                );
            }
        }

        return $config;
    }
}
