<?php

class CouponController extends Controller
{
    private CouponService $service;

    public function __construct()
    {
        $this->service = new CouponService();
    }

    public function index(): void
    {
        $this->view('admin/module-layout', [
            'contentView' => __DIR__ . '/../Views/admin/coupons.php'
        ]);
    }

    public function data(): void
    {
        try {
            $this->json([
                'success' => true,
                'data' => $this->service->getAll()
            ]);
        } catch (Throwable $e) {
            error_log('[COUPON DATA] ' . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'No fue posible cargar los cupones.'
            ], 500);
        }
    }

    public function store(): void
    {
        $user = Auth::user() ?? [];
        try {
            $data = $this->getJsonInput();
            $id = $this->service->create($data, (int) ($user['id'] ?? 0));

            $this->json([
                'success' => true,
                'message' => 'Cupón creado correctamente.',
                'id' => $id
            ], 201);
        } catch (InvalidArgumentException $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (PDOException $e) {
            if (str_contains(strtolower($e->getMessage()), 'duplicate')) {
                $this->json(['success' => false, 'message' => 'Ya existe un cupón con ese código.'], 422);
                return;
            }
            error_log('[COUPON STORE] ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'No fue posible crear el cupón.'], 500);
        } catch (Throwable $e) {
            error_log('[COUPON STORE] ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'No fue posible crear el cupón.'], 500);
        }
    }

    public function update(): void
    {
        try {
            $data = $this->getJsonInput();
            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id || $id <= 0) {
                $this->json(['success' => false, 'message' => 'El cupón seleccionado no es válido.'], 422);
                return;
            }

            $this->service->update((int) $id, $data);
            $this->json(['success' => true, 'message' => 'Cupón actualizado correctamente.']);
        } catch (InvalidArgumentException $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (PDOException $e) {
            if (str_contains(strtolower($e->getMessage()), 'duplicate')) {
                $this->json(['success' => false, 'message' => 'Ya existe un cupón con ese código.'], 422);
                return;
            }
            error_log('[COUPON UPDATE] ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'No fue posible actualizar el cupón.'], 500);
        } catch (Throwable $e) {
            error_log('[COUPON UPDATE] ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'No fue posible actualizar el cupón.'], 500);
        }
    }

    public function toggle(): void
    {
        try {
            $data = $this->getJsonInput();
            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            $active = filter_var($data['is_active'] ?? null, FILTER_VALIDATE_INT);

            if (!$id || $id <= 0 || !in_array($active, [0, 1], true)) {
                $this->json(['success' => false, 'message' => 'Los datos enviados no son válidos.'], 422);
                return;
            }

            if (!$this->service->toggle((int) $id, (int) $active)) {
                $this->json(['success' => false, 'message' => 'No se encontró el cupón.'], 404);
                return;
            }

            $this->json(['success' => true, 'message' => $active ? 'Cupón activado.' : 'Cupón desactivado.']);
        } catch (Throwable $e) {
            error_log('[COUPON TOGGLE] ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'No fue posible cambiar el estado del cupón.'], 500);
        }
    }

    public function validatePublic(): void
    {
        $user = Auth::user() ?? [];
        if (strtolower((string) ($user['role_name'] ?? '')) !== 'customer') {
            $this->json(['success' => false, 'message' => 'Debes iniciar sesión como cliente para usar un cupón.'], 403);
            return;
        }

        try {
            $data = $this->getJsonInput();
            $subtotal = filter_var($data['subtotal'] ?? null, FILTER_VALIDATE_FLOAT);
            if ($subtotal === false) {
                $this->json(['success' => false, 'message' => 'El subtotal no es válido.'], 422);
                return;
            }

            $coupon = $this->service->validateForCheckout(
                (string) ($data['code'] ?? ''),
                (int) $user['id'],
                (float) $subtotal
            );

            $this->json([
                'success' => true,
                'message' => 'Cupón aplicado.',
                'data' => $coupon
            ]);
        } catch (InvalidArgumentException $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (DomainException $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], (int) $e->getCode() ?: 422);
        } catch (Throwable $e) {
            error_log('[COUPON VALIDATE] ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'No fue posible validar el cupón.'], 500);
        }
    }

    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw ?: '', true);

        return is_array($decoded) ? $decoded : (is_array($_POST) ? $_POST : []);
    }
}
