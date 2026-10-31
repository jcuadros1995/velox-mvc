<?php
// services/Gateways/WompiAdapter.php

class WompiAdapter implements PaymentGatewayInterface {
    private string $publicKey;
    private string $privateKey;
    private string $eventsSecret;
    private bool $isSandbox;

    public function __construct(array $config) {
        $this->publicKey    = $config['public_key'] ?? '';
        $this->privateKey   = $config['private_key'] ?? '';
        $this->eventsSecret = $config['events_secret'] ?? '';
        $this->isSandbox    = $config['is_sandbox'] ?? true;
    }

    public function createPayment(array $paymentData): array {
        // En Wompi normalmente generas el widget o transacción con la firma de integridad SHA256
        $reference = $paymentData['reference'];
        $amountInCents = (int) ($paymentData['amount'] * 100); // Wompi requiere montos en centavos
        $currency = $paymentData['currency'] ?? 'COP';

        // Generar firma SHA256: Referencia + MontoEnCentavos + Moneda + EventsSecret
        $signatureRaw = $reference . $amountInCents . $currency . $this->eventsSecret;
        $signature = hash('sha256', $signatureRaw);

        return [
            'status'     => 'ready',
            'gateway'    => 'wompi',
            'public_key' => $this->publicKey,
            'reference'  => $reference,
            'amount_in_cents' => $amountInCents,
            'currency'   => $currency,
            'signature'  => $signature
        ];
    }

    public function handleWebhook(array $payload): array {
        // Validar el evento traído por la llamada webhook
        $event = $payload['event'] ?? '';
        $transaction = $payload['data']['transaction'] ?? [];

        $status = match ($transaction['status'] ?? '') {
            'APPROVED' => 'approved',
            'DECLINED' => 'declined',
            'VOIDED'   => 'refunded',
            default    => 'error'
        };

        return [
            'reference'            => $transaction['reference'] ?? null,
            'gateway_reference'    => $transaction['id'] ?? null,
            'status'               => $status,
            'amount'               => isset($transaction['amount_in_cents']) ? ($transaction['amount_in_cents'] / 100) : 0,
            'raw'                  => $payload
        ];
    }
}