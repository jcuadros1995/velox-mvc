<?php
// services/Gateways/WompiAdapter.php

class WompiAdapter implements PaymentGatewayInterface
{
    private string $publicKey;
    private string $privateKey;
    private string $integritySecret;
    private string $eventsSecret;
    private bool $isSandbox;

    public function __construct(array $config)
    {
        $this->publicKey = (string) ($config['public_key'] ?? '');
        $this->privateKey = (string) ($config['private_key'] ?? '');
        $this->integritySecret = (string) ($config['integrity_secret'] ?? '');
        $this->eventsSecret = (string) ($config['events_secret'] ?? '');
        $this->isSandbox = (bool) ($config['is_sandbox'] ?? true);
    }

    /**
     * Prepara los parámetros para el widget / checkout web de Wompi.
     */
    public function createPayment(array $paymentData): array
    {
        if ($this->publicKey === '' || $this->integritySecret === '') {
            throw new DomainException(
                'La configuración de integridad de Wompi está incompleta.',
                503
            );
        }

        $reference = trim((string) ($paymentData['reference'] ?? ''));
        $amount = (float) ($paymentData['amount'] ?? 0);
        $amountInCents = (int) round($amount * 100);
        $currency = (string) ($paymentData['currency'] ?? 'COP');

        if ($reference === '' || $amountInCents <= 0 || $currency !== 'COP') {
            throw new InvalidArgumentException(
                'Los datos enviados a Wompi no son válidos.'
            );
        }

        // La firma del checkout utiliza el secreto de integridad,
        // que es diferente del secreto utilizado para los webhooks.
        $signatureRaw =
            $reference .
            $amountInCents .
            $currency .
            $this->integritySecret;

        return [
            'status' => 'ready',
            'gateway' => 'wompi',
            'environment' => $this->isSandbox ? 'sandbox' : 'production',
            'public_key' => $this->publicKey,
            'reference' => $reference,
            'amount_in_cents' => $amountInCents,
            'currency' => $currency,
            'signature' => hash('sha256', $signatureRaw)
        ];
    }

    /**
     * Valida la firma del evento antes de aceptar su contenido.
     */
    public function handleWebhook(array $payload): array
    {
        if ($this->eventsSecret === '') {
            throw new DomainException(
                'El secreto de eventos de Wompi no está configurado.',
                503
            );
        }

        if (($payload['event'] ?? '') !== 'transaction.updated') {
            throw new InvalidArgumentException(
                'Tipo de evento de Wompi no soportado.'
            );
        }

        $signature = $payload['signature'] ?? [];
        $properties = $signature['properties'] ?? [];
        $checksum = strtolower(trim((string) ($signature['checksum'] ?? '')));
        $timestamp = $payload['timestamp'] ?? null;
        $data = $payload['data'] ?? [];

        if (
            !is_array($properties) ||
            empty($properties) ||
            !is_array($data) ||
            !is_numeric($timestamp) ||
            !preg_match('/^[a-f0-9]{64}$/', $checksum)
        ) {
            throw new InvalidArgumentException(
                'La firma del evento de Wompi está incompleta.'
            );
        }

        // Wompi firma el timestamp; limitar su antigüedad reduce
        // la ventana para reutilizar webhooks capturados previamente.
        $eventTimestamp = (int) $timestamp;
        $allowedDrift = 300; // 5 minutos, configurable por entorno.
        $configuredDrift = getenv('WOMPI_WEBHOOK_MAX_DRIFT_SECONDS');
        if ($configuredDrift !== false && ctype_digit((string) $configuredDrift)) {
            $allowedDrift = max(60, min(900, (int) $configuredDrift));
        }

        if (
            $eventTimestamp <= 0 ||
            abs(time() - $eventTimestamp) > $allowedDrift
        ) {
            throw new DomainException(
                'El evento de Wompi está fuera de la ventana de tiempo permitida.',
                401
            );
        }

        $signatureRaw = '';

        foreach ($properties as $property) {
            if (!is_string($property) || trim($property) === '') {
                throw new InvalidArgumentException(
                    'La firma del evento contiene una propiedad inválida.'
                );
            }

            $value = $this->getNestedValue(
                $data,
                $property
            );

            if (!is_scalar($value)) {
                throw new InvalidArgumentException(
                    'No fue posible validar una propiedad de la firma del evento.'
                );
            }

            $signatureRaw .= (string) $value;
        }

        $signatureRaw .= (string) $timestamp . $this->eventsSecret;
        $expectedChecksum = hash('sha256', $signatureRaw);

        if (!hash_equals($expectedChecksum, $checksum)) {
            throw new DomainException(
                'La firma del evento de Wompi no es válida.',
                401
            );
        }

        $transaction = $data['transaction'] ?? [];

        if (!is_array($transaction)) {
            throw new InvalidArgumentException(
                'El evento de Wompi no contiene una transacción válida.'
            );
        }

        $status = match (strtoupper((string) ($transaction['status'] ?? ''))) {
            'APPROVED' => 'approved',
            'DECLINED' => 'declined',
            'VOIDED' => 'voided',
            'ERROR' => 'error',
            default => 'pending'
        };

        return [
            'reference' => $transaction['reference'] ?? null,
            'gateway_reference' => $transaction['id'] ?? null,
            'status' => $status,
            'amount' => isset($transaction['amount_in_cents'])
                ? ((float) $transaction['amount_in_cents'] / 100)
                : 0,
            'currency' => $transaction['currency'] ?? null,
            'raw' => $payload
        ];
    }

    /**
     * Resuelve propiedades del evento con formato transaction.id.
     */
    private function getNestedValue(
        array $source,
        string $path
    ): mixed {
        $value = $source;

        foreach (explode('.', $path) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return null;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}
