<?php
// app/Services/PaymentService.php

class PaymentService
{
    /**
     * Procesa la creación de un pago.
     *
     * @param array $input
     * @return array
     * @throws Exception
     */
    public function checkout(array $input): array
    {
        $gatewayConfig = $this->getGatewayConfig();

        $adapter = PaymentFactory::create(
            $input['gateway'],
            $gatewayConfig
        );

        return $adapter->createPayment([
            'reference' => 'ORDER-' . $input['order_id'] . '-' . time(),
            'amount'    => $input['amount'],
            'currency'  => 'COP'
        ]);
    }

    /**
     * Procesa un webhook recibido desde el gateway.
     *
     * @param array $payload
     * @return array
     * @throws Exception
     */
    public function processWebhook(array $payload): array
    {
        $adapter = PaymentFactory::create(
            'wompi',
            [
                'events_secret' => 'prod_events_XXXXXX'
            ]
        );

        return $adapter->handleWebhook($payload);
    }

    /**
     * Configuración temporal de los gateways.
     *
     * Posteriormente será reemplazada por configuración
     * proveniente de system_settings/payment_gateways.
     */
    private function getGatewayConfig(): array
    {
        return [
            'public_key'    => 'pub_test_XXXXXX',
            'private_key'   => 'prv_test_XXXXXX',
            'events_secret' => 'prod_events_XXXXXX',
            'is_sandbox'    => true
        ];
    }
}