<?php

class CouponService
{
    private CouponRepository $repository;

    public function __construct()
    {
        $this->repository = new CouponRepository();
    }

    public function getAll(): array
    {
        return $this->repository->all();
    }

    public function create(array $input, int $createdBy): int
    {
        return $this->repository->create(
            $this->normalizeInput($input, $createdBy)
        );
    }

    public function update(int $id, array $input): bool
    {
        if ($id <= 0 || !$this->repository->findById($id)) {
            throw new InvalidArgumentException('El cupón seleccionado no existe.');
        }

        $data = $this->normalizeInput($input, null);
        unset($data['created_by']);

        return $this->repository->update($id, $data);
    }

    public function toggle(int $id, int $isActive): bool
    {
        if ($id <= 0 || !in_array($isActive, [0, 1], true)) {
            throw new InvalidArgumentException('Los datos del cupón no son válidos.');
        }

        if (!$this->repository->findById($id)) {
            return false;
        }

        return $this->repository->setActive($id, $isActive);
    }

    /**
     * Valida el cupón para mostrar una estimación en checkout.
     * El importe recibido aquí nunca se usa para crear o cobrar la orden:
     * OrderService vuelve a calcularlo desde los productos guardados.
     */
    public function validateForCheckout(
        string $code,
        int $customerId,
        float $subtotal
    ): array {
        $code = strtoupper(trim($code));

        if ($customerId <= 0 || !preg_match('/^[A-Z0-9_-]{3,40}$/', $code)) {
            throw new InvalidArgumentException('Ingresa un código de cupón válido.');
        }

        if (!is_finite($subtotal) || $subtotal <= 0) {
            throw new InvalidArgumentException('El subtotal del carrito no es válido.');
        }

        $coupon = $this->repository->findByCode($code);

        if (!$coupon || !$this->isAvailable($coupon, $customerId, $subtotal)) {
            throw new DomainException('El cupón no es válido o no cumple las condiciones de uso.', 422);
        }

        $discount = $this->calculateDiscount($coupon, $subtotal);

        return [
            'id' => (int) $coupon['id'],
            'code' => (string) $coupon['code'],
            'description' => (string) ($coupon['description'] ?? ''),
            'discount_type' => (string) $coupon['discount_type'],
            'discount_value' => (float) $coupon['discount_value'],
            'discount_amount' => $discount
        ];
    }

    public function validateForOrder(
        string $code,
        int $customerId,
        float $subtotal
    ): array {
        return $this->validateForCheckout($code, $customerId, $subtotal);
    }

    private function isAvailable(array $coupon, int $customerId, float $subtotal): bool
    {
        if ((int) ($coupon['is_active'] ?? 0) !== 1) {
            return false;
        }

        $now = new DateTimeImmutable('now', new DateTimeZone('America/Bogota'));

        if (!empty($coupon['starts_at'])) {
            $startsAt = new DateTimeImmutable((string) $coupon['starts_at'], new DateTimeZone('America/Bogota'));
            if ($startsAt > $now) {
                return false;
            }
        }

        if (!empty($coupon['expires_at'])) {
            $expiresAt = new DateTimeImmutable((string) $coupon['expires_at'], new DateTimeZone('America/Bogota'));
            if ($expiresAt < $now) {
                return false;
            }
        }

        if ($subtotal < (float) ($coupon['minimum_purchase'] ?? 0)) {
            return false;
        }

        if (
            $coupon['usage_limit'] !== null &&
            (int) $coupon['usage_count'] >= (int) $coupon['usage_limit']
        ) {
            return false;
        }

        $perCustomerLimit = (int) ($coupon['per_customer_limit'] ?? 1);
        if (
            $perCustomerLimit > 0 &&
            $this->repository->countUsesByCustomer((int) $coupon['id'], $customerId) >= $perCustomerLimit
        ) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(array $coupon, float $subtotal): float
    {
        $value = (float) $coupon['discount_value'];

        $discount = $coupon['discount_type'] === 'percentage'
            ? ($subtotal * $value / 100)
            : min($value, $subtotal);

        return round(min($discount, $subtotal), 2);
    }

    private function normalizeInput(array $input, ?int $createdBy): array
    {
        $code = strtoupper(trim((string) ($input['code'] ?? '')));
        $description = trim(strip_tags((string) ($input['description'] ?? '')));
        $type = strtolower(trim((string) ($input['discount_type'] ?? '')));
        $value = filter_var($input['discount_value'] ?? null, FILTER_VALIDATE_FLOAT);
        $minimum = filter_var($input['minimum_purchase'] ?? 0, FILTER_VALIDATE_FLOAT);
        $usageLimitRaw = $input['usage_limit'] ?? null;
        $usageLimit = ($usageLimitRaw === '' || $usageLimitRaw === null)
            ? null
            : filter_var($usageLimitRaw, FILTER_VALIDATE_INT);
        $perCustomer = filter_var($input['per_customer_limit'] ?? 1, FILTER_VALIDATE_INT);
        $isActive = !empty($input['is_active']) ? 1 : 0;

        if (!preg_match('/^[A-Z0-9_-]{3,40}$/', $code)) {
            throw new InvalidArgumentException('El código debe tener entre 3 y 40 caracteres: letras, números, guion o guion bajo.');
        }

        if (!in_array($type, ['percentage', 'fixed'], true)) {
            throw new InvalidArgumentException('Selecciona un tipo de descuento válido.');
        }

        if ($value === false || $value <= 0 || ($type === 'percentage' && $value > 100)) {
            throw new InvalidArgumentException('El valor del descuento no es válido.');
        }

        if ($minimum === false || $minimum < 0) {
            throw new InvalidArgumentException('El mínimo de compra no es válido.');
        }

        if ($usageLimit !== null && ($usageLimit === false || $usageLimit < 1)) {
            throw new InvalidArgumentException('El límite total de usos debe ser mayor que cero o quedar vacío.');
        }

        if ($perCustomer === false || $perCustomer < 1 || $perCustomer > 1000) {
            throw new InvalidArgumentException('El límite por cliente debe estar entre 1 y 1000.');
        }

        $startsAt = $this->normalizeDate($input['starts_at'] ?? '');
        $expiresAt = $this->normalizeDate($input['expires_at'] ?? '');

        if ($startsAt !== null && $expiresAt !== null && $expiresAt <= $startsAt) {
            throw new InvalidArgumentException('La fecha de vencimiento debe ser posterior a la fecha de inicio.');
        }

        if (strlen($description) > 255) {
            $description = substr($description, 0, 255);
        }

        return [
            'code' => $code,
            'description' => $description !== '' ? $description : null,
            'discount_type' => $type,
            'discount_value' => round((float) $value, 2),
            'minimum_purchase' => round((float) $minimum, 2),
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'usage_limit' => $usageLimit,
            'per_customer_limit' => (int) $perCustomer,
            'is_active' => $isActive,
            'created_by' => $createdBy
        ];
    }

    private function normalizeDate($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $value, new DateTimeZone('America/Bogota'));
        if (!$date) {
            throw new InvalidArgumentException('Una de las fechas del cupón no tiene un formato válido.');
        }

        return $date->format('Y-m-d H:i:s');
    }
}
