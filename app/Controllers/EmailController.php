<?php

class EmailController extends Controller
{
    private EmailService $emailService;

    public function __construct()
    {
        $this->emailService =
            new EmailService();
    }

    /**
     * POST /api/admin/email/test
     */
    public function test(): void
    {
        try {

            $user = Auth::user();

            if (!$user) {
                $this->json([
                    'success' => false,
                    'message' =>
                        'Sesión no válida.'
                ], 401);

                return;
            }

            $role =
                strtolower(
                    (string) (
                        $user['role_name'] ?? ''
                    )
                );

            if ($role !== 'superadmin') {

                $this->json([
                    'success' => false,
                    'message' =>
                        'Solo el SuperAdmin puede probar la configuración de correo.'
                ], 403);

                return;
            }


            $email =
                trim(
                    $_POST['email']
                    ?? ''
                );


            if (
                !filter_var(
                    $email,
                    FILTER_VALIDATE_EMAIL
                )
            ) {

                $this->json([
                    'success' => false,
                    'message' =>
                        'Ingresa un correo válido.'
                ], 400);

                return;
            }


            $this->emailService
                ->testConnection(
                    $email
                );


            $this->json([
                'success' => true,
                'message' =>
                    'Correo de prueba enviado correctamente.'
            ]);

        } catch (Throwable $e) {

            error_log(
                '[EMAIL_TEST] ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' =>
                    'No fue posible enviar el correo de prueba.'
            ], 500);
        }
    }
}