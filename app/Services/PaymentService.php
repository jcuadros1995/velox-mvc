<?php
// app/Services/PaymentService.php

class PaymentService
{
    private OrderRepository $orderRepository;
    private PaymentRepository $paymentRepository;

    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
        $this->paymentRepository = new PaymentRepository();
    }

    /**
     * Prepara el checkout únicamente para una orden del cliente.
     *
     * El monto nunca se toma del navegador: se lee de la orden
     * almacenada en la base de datos.
     */
    public function checkout(
        array $input,
        int $customerId,
        array $customerData = []
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

        // Cada intento de pago usa una referencia exclusiva de Wompi.
        $reference = (string) $order['order_number'] . '-' . strtoupper(bin2hex(random_bytes(4)));

        $payment = $adapter->createPayment([
            'reference' => $reference,
            'amount' => $amount,
            'currency' => 'COP'
        ]);

        $this->paymentRepository->createPendingAttempt(
            (int) $order['id'],
            $customerId,
            $reference,
            $amount,
            'COP'
        );

        $fullName = trim(
            (string) ($customerData['first_name'] ?? '') . ' ' .
            (string) ($customerData['last_name'] ?? '')
        );

        $payment['checkout_url'] = 'https://checkout.wompi.co/p/';
        $payment['redirect_url'] = rtrim(APP_URL, '/') .
            '/checkout/resultado?reference=' . rawurlencode($reference);
        $payment['customer_email'] = (string) ($customerData['email'] ?? '');
        $payment['customer_full_name'] = $fullName;
        $payment['customer_phone'] = preg_replace('/\\D+/', '', (string) ($customerData['phone'] ?? '')) ?: '';
        $payment['customer_phone_prefix'] = $payment['customer_phone'] !== '' ? '+57' : '';

        return $payment;
    }

    /**
     * Verifica la configuración de Wompi antes de crear la orden,
     * para no reservar inventario ni consumir cupones por un checkout
     * que no puede iniciar.
     */
    public function assertCheckoutAvailable(): array
    {
        $config = $this->getGatewayConfig();

        return [
            'gateway' => 'wompi',
            'environment' => $config['is_sandbox'] ? 'sandbox' : 'production'
        ];
    }

    /**
     * Procesa y valida la autenticidad de un webhook de Wompi.
     *
     * El resultado validado se guarda y actualiza la orden de forma
     * idempotente mediante PaymentRepository.
     */
    public function processWebhook(array $payload): array
    {
        $adapter = PaymentFactory::create(
            'wompi',
            $this->getGatewayConfig(true)
        );

        $verifiedEvent = $adapter->handleWebhook($payload);

        return $this->paymentRepository->processVerifiedEvent($verifiedEvent);
    }

    /**
     * Lee los secretos desde variables de entorno.
     * No se deben almacenar llaves reales dentro del repositorio.
     */
    private function getGatewayConfig(bool $forWebhook = false): array
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

        $requiredKeys = $forWebhook
            ? ['events_secret']
            : ['public_key', 'integrity_secret', 'events_secret'];

        foreach ($requiredKeys as $key) {
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
