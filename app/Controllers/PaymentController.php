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
        $user = Auth::user();

        if (
            !$user ||
            strtolower((string) ($user['role_name'] ?? '')) !== 'customer'
        ) {
            $this->json([
                'success' => false,
                'message' => 'No tienes autorización para iniciar este pago.'
            ], 403);

            return;
        }

        $input = $this->getInput();

        if (empty($input['order_id']) || empty($input['gateway'])) {
            $this->json([
                'success' => false,
                'message' => 'Selecciona un pedido y una pasarela de pago.'
            ], 400);

            return;
        }

        try {
            $paymentResponse = $this->paymentService->checkout(
                $input,
                (int) $user['id'],
                $user
            );

            $this->json([
                'success' => true,
                'data' => $paymentResponse
            ]);

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
                '[PAYMENT_CHECKOUT_ERROR] ' .
                $e->getMessage() . ' | ' .
                $e->getFile() . ':' . $e->getLine()
            );

            $this->json([
                'success' => false,
                'message' => 'No fue posible iniciar el pago en este momento.'
            ], 500);
        }
    }

    /**
     * POST /api/payments/webhook
     */
    public function webhook(): void
    {
        $rawPayload = file_get_contents('php://input');
        $payload = json_decode($rawPayload ?: '', true);

        if (!is_array($payload)) {
            $this->json([
                'success' => false,
                'message' => 'El evento recibido no tiene un formato válido.'
            ], 400);

            return;
        }

        try {
            $processedData = $this->paymentService->processWebhook($payload);

            error_log(
                '[WOMPI_WEBHOOK_VALIDATED] reference=' .
                (string) ($processedData['reference'] ?? '') .
                ' status=' .
                (string) ($processedData['status'] ?? '')
            );

            $this->json([
                'success' => true,
                'status' => 'acknowledged'
            ]);

        } catch (InvalidArgumentException $e) {
            $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);

        } catch (DomainException $e) {
            $statusCode = (int) $e->getCode();

            if ($statusCode < 400 || $statusCode > 599) {
                $statusCode = 400;
            }

            $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $statusCode);

        } catch (Throwable $e) {
            error_log(
                '[WOMPI_WEBHOOK_ERROR] ' .
                $e->getMessage() . ' | ' .
                $e->getFile() . ':' . $e->getLine()
            );

            $this->json([
                'success' => false,
                'message' => 'No fue posible procesar el evento recibido.'
            ], 500);
        }
    }


    /**
     * GET /api/payments/status?reference=...
     * Devuelve el estado persistido del intento del cliente.
     */
    public function status(): void
    {
        $user = Auth::user();

        if (!$user || strtolower((string) ($user['role_name'] ?? '')) !== 'customer') {
            $this->json(['success' => false, 'message' => 'No tienes autorización para consultar este pago.'], 403);
            return;
        }

        $reference = trim((string) ($_GET['reference'] ?? ''));
        if ($reference === '' || strlen($reference) > 140) {
            $this->json(['success' => false, 'message' => 'Referencia de pago no válida.'], 422);
            return;
        }

        try {
            $payment = (new PaymentRepository())->findForCustomer($reference, (int) $user['id']);
            if (!$payment) {
                $this->json(['success' => false, 'message' => 'No se encontró el pago solicitado.'], 404);
                return;
            }

            $this->json(['success' => true, 'data' => $payment]);
        } catch (Throwable $e) {
            error_log('[PAYMENT_STATUS_ERROR] ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'No fue posible consultar el estado del pago.'], 500);
        }
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
