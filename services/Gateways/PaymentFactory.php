<?php
// services/Gateways/PaymentFactory.php

class PaymentFactory {
    public static function create(string $gatewayCode, array $config): PaymentGatewayInterface {
        return match (strtolower($gatewayCode)) {
            'wompi' => new WompiAdapter($config),
            default => throw new InvalidArgumentException("La pasarela '{$gatewayCode}' no está soportada.")
        };
    }
}