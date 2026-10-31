<?php
// services/Gateways/PaymentGatewayInterface.php

interface PaymentGatewayInterface {
    /**
     * Crea o inicia la transacción devolviendo la URL de pago o datos de la pasarela.
     */
    public function createPayment(array $paymentData): array;

    /**
     * Procesa la respuesta del Webhook / Notificación entrante de la pasarela.
     */
    public function handleWebhook(array $payload): array;
}