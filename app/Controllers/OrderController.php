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
        $input = $this->getInput();

        if (
            empty($input['customer_id']) ||
            empty($input['items']) ||
            !is_array($input['items'])
        ) {
            $this->json([
                'error' => 'Datos de pedido inválidos o sin ítems'
            ], 400);

            return;
        }

        try {
            $result = $this->orderService->createOrder($input);

            $this->json([
                'status'   => 'success',
                'message'  => 'Pedido registrado exitosamente',
                'order_id' => $result['order_id'],
                'total'    => $result['total']
            ], 201);

        } catch (Exception $e) {
            $this->json([
                'error' => 'Error al procesar la orden: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/orders/show?id=1
     */
    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        $order = $this->orderService->getOrderDetails($id);

        if (!$order) {
            $this->json([
                'error' => 'Orden no encontrada'
            ], 404);

            return;
        }

        $this->json([
            'status' => 'success',
            'data'   => $order
        ]);
    }

    /**
     * Obtiene los datos enviados en JSON o POST.
     */
    private function getInput(): array
    {
        return json_decode(
            file_get_contents('php://input'),
            true
        ) ?? $_POST;
    }
}