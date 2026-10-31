<?php
// app/Controllers/OrderController.php

class OrderController extends Controller
{
    private OrderService $orderService;

    public function __construct()
    {
        $this->orderService = new OrderService();
    }

    /**
     * POST /api/orders/create
     */
    public function create(): void
    {
        $user = Auth::user();

        if (
            !$user ||
            strtolower((string) ($user['role_name'] ?? '')) !== 'customer'
        ) {
            $this->json([
                'success' => false,
                'message' => 'Solo los clientes pueden realizar pedidos desde la tienda.'
            ], 403);

            return;
        }

        $input = $this->getInput();

        try {
            $result = $this->orderService->createOrder(
                $input,
                (int) $user['id']
            );

            $this->json([
                'success' => true,
                'message' => 'Pedido registrado exitosamente.',
                'data' => $result
            ], 201);

        } catch (InvalidArgumentException $e) {
            $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);

        } catch (DomainException $e) {
            $statusCode = (int) $e->getCode();

            if ($statusCode < 400 || $statusCode > 599) {
                $statusCode = 409;
            }

            $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $statusCode);

        } catch (Throwable $e) {
            error_log(
                '[PUBLIC_ORDER_CREATE_ERROR] ' .
                $e->getMessage() . ' | ' .
                $e->getFile() . ':' . $e->getLine()
            );

            $this->json([
                'success' => false,
                'message' => 'No fue posible registrar el pedido en este momento.'
            ], 500);
        }
    }

    /**
     * GET /api/orders/show?id=1
     */
    public function show(): void
    {
        $user = Auth::user();

        if (
            !$user ||
            strtolower((string) ($user['role_name'] ?? '')) !== 'customer'
        ) {
            $this->json([
                'success' => false,
                'message' => 'No tienes autorización para consultar pedidos.'
            ], 403);

            return;
        }

        $id = (int) ($_GET['id'] ?? 0);

        $order = $this->orderService->getOrderDetails(
            $id,
            (int) $user['id']
        );

        if (!$order) {
            $this->json([
                'success' => false,
                'message' => 'Pedido no encontrado.'
            ], 404);

            return;
        }

        $this->json([
            'success' => true,
            'data' => $order
        ]);
    }

    /**
     * Obtiene los datos enviados en JSON o POST.
     */
    private function getInput(): array
    {
        $rawInput = file_get_contents('php://input');
        $decoded = json_decode($rawInput ?: '', true);

        if (is_array($decoded)) {
            return $decoded;
        }

        return is_array($_POST) ? $_POST : [];
    }
}
