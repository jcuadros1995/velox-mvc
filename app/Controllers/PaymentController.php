<?php
// app/Controllers/PaymentController.php

class PaymentController extends Controller
{
    private PaymentService $paymentService;

    public function __construct()
    {
        $this->paymentService = new PaymentService();
    }

    /**
     * POST /api/payments/checkout
     */
    public function checkout(): void
    {
        $input = $this->getInput();

        if (
            empty($input['order_id']) ||
            empty($input['amount']) ||
            empty($input['gateway'])
        ) {
            $this->json([
                'error' => 'Datos de pago incompletos'
            ], 400);

            return;
        }

        try {
            $paymentResponse = $this->paymentService->checkout($input);

            $this->json([
                'status' => 'success',
                'data'   => $paymentResponse
            ]);

        } catch (Exception $e) {
            $this->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/payments/webhook
     */
    public function webhook(): void
    {
        $rawPayload = file_get_contents('php://input');

        $payload = json_decode(
            $rawPayload,
            true
        ) ?? [];

        try {
            $processedData = $this->paymentService->processWebhook(
                $payload
            );

            error_log(
                'Webhook recibido para orden: ' .
                json_encode($processedData)
            );

            $this->json([
                'status' => 'acknowledged'
            ]);

        } catch (Exception $e) {
            $this->json([
                'error' => $e->getMessage()
            ], 500);
        }
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