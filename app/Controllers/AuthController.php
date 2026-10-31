<?php
// app/Controllers/AuthController.php

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * GET /login
     */
    public function showLogin(): void
    {
        $this->view('layouts/public_layout', [
            'contentView' => __DIR__ . '/../Views/auth/login.php',
            'script' => 'login'
        ]);
    }

    /**
     * GET /recuperar-contrasena
     */
    public function showForgotPassword(): void
    {
        $this->view('layouts/module_public_layout', [
            'contentView' => __DIR__ . '/../Views/auth/forgot-password.php',
            'script' => 'forgot-password'
        ]);
    }

    /**
     * GET /restablecer-contrasena
     */
    public function showResetPassword(): void
    {
        $this->view('layouts/module_public_layout', [
            'contentView' => __DIR__ . '/../Views/auth/reset-password.php',
            'script' => 'reset-password'
        ]);
    }

    /**
     * POST /api/forgot-password
     */
    public function forgotPassword(): void
    {
        try {
            $input = $this->getJsonInput();
            $identifier = trim((string) ($input['identifier'] ?? ''));

            if ($identifier === '') {
                $this->json([
                    'success' => false,
                    'message' => 'Ingresa tu correo, teléfono o número de documento.'
                ], 400);
                return;
            }

            $this->authService->requestPasswordReset($identifier);

            /* Respuesta genérica para evitar enumeración de cuentas. */
            $this->json([
                'success' => true,
                'message' => 'Si los datos coinciden, recibirás un correo con las instrucciones para recuperar tu acceso.'
            ], 200);

        } catch (Throwable $e) {
            error_log(
                '[AUTH_FORGOT_PASSWORD_ERROR] ' .
                $e->getMessage() . ' | ' .
                $e->getFile() . ':' . $e->getLine()
            );

            $this->json([
                'success' => false,
                'message' => 'No fue posible procesar la solicitud en este momento.'
            ], 500);
        }
    }

    /**
     * POST /api/reset-password
     */
    public function resetPassword(): void
    {
        try {
            $input = $this->getJsonInput();
            $token = trim((string) ($input['token'] ?? ''));
            $password = (string) ($input['password'] ?? '');
            $confirmation = (string) ($input['password_confirmation'] ?? '');

            if ($token === '' || $password === '' || $confirmation === '') {
                $this->json([
                    'success' => false,
                    'message' => 'Todos los campos son obligatorios.'
                ], 400);
                return;
            }

            if ($password !== $confirmation) {
                $this->json([
                    'success' => false,
                    'message' => 'Las contraseñas no coinciden.'
                ], 422);
                return;
            }

            $this->authService->resetPassword($token, $password);

            $this->json([
                'success' => true,
                'message' => 'Tu contraseña fue actualizada correctamente.'
            ], 200);

        } catch (DomainException $e) {
            $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);

        } catch (Throwable $e) {
            error_log(
                '[AUTH_RESET_PASSWORD_ERROR] ' .
                $e->getMessage() . ' | ' .
                $e->getFile() . ':' . $e->getLine()
            );

            $this->json([
                'success' => false,
                'message' => 'No fue posible restablecer la contraseña.'
            ], 500);
        }
    }

    /**
     * POST /api/auth/register
     */
    public function register(): void
    {
        $input = $this->getJsonInput();

        if (
            empty($input['email']) ||
            empty($input['password']) ||
            empty($input['first_name'])
        ) {
            $this->json([
                'success' => false,
                'message' => 'Nombre, email y contraseña son obligatorios'
            ], 400);

            return;
        }

        $email = strtolower(trim($input['email']));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json([
                'success' => false,
                'message' => 'El correo electrónico no es válido'
            ], 400);

            return;
        }

        try {
            $userId = $this->authService->register($input);

            $this->json([
                'success' => true,
                'message' => 'Usuario registrado con éxito',
                'user_id' => $userId
            ], 201);

        } catch (DomainException $e) {
            $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);

        } catch (Throwable $e) {
            error_log(
                '[AUTH_REGISTER_ERROR] ' .
                $e->getMessage() .
                ' | ' .
                $e->getFile() .
                ':' .
                $e->getLine()
            );

            $this->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * POST /api/auth/login
     */
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'message' => 'Método HTTP no permitido'
            ], 405);

            return;
        }

        try {
            $input = $this->getJsonInput();

            $email = strtolower(
                trim($input['email'] ?? '')
            );

            $password = $input['password'] ?? '';

            $remember = !empty($input['remember']);

            if ($email === '' || $password === '') {
                $this->json([
                    'success' => false,
                    'message' => 'Todos los campos son obligatorios'
                ], 400);

                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->json([
                    'success' => false,
                    'message' => 'El correo electrónico no es válido'
                ], 400);

                return;
            }

            $userData = $this->authService->login(
                $email,
                $password,
                $remember
            );

            $this->json([
                'success' => true,
                'message' => 'Inicio de sesión exitoso',
                'data' => $userData
            ], 200);

        } catch (DomainException $e) {
            $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 401);

        } catch (Throwable $e) {
            error_log(
                '[AUTH_CRITICAL_ERROR] ' .
                $e->getMessage() .
                ' | ' .
                $e->getFile() .
                ':' .
                $e->getLine()
            );

            $this->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(): void
    {
        $this->authService->logout();

        $this->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }

    /**
     * Obtiene los datos enviados por POST o JSON.
     */
    private function getJsonInput(): array
    {
        $input = $_POST;

        if (!empty($input)) {
            return is_array($input) ? $input : [];
        }

        $rawInput = file_get_contents('php://input');

        if (!$rawInput) {
            return [];
        }

        $decoded = json_decode($rawInput, true);

        return is_array($decoded) ? $decoded : [];
    }
}