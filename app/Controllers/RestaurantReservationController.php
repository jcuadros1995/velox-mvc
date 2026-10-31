<?php
// app/Controllers/RestaurantReservationController.php

class RestaurantReservationController extends Controller
{
    private AvailabilityService $availabilityService;
    private ReservationService $reservationService;
    private BranchRepository $branchRepository;

    public function __construct()
    {
        $this->availabilityService = new AvailabilityService();
        $this->reservationService = new ReservationService();
        $this->branchRepository = new BranchRepository();
    }

    /**
     * Página pública de reservas.
     */
    public function page(): void
    {
        $this->view('layouts/public_layout', [
            'contentView' => __DIR__ . '/../Views/restaurant/reservations.php',
            'script'      => 'reservation'
        ]);
    }

    /**
     * Página pública para consultar reservas.
     */
    public function searchPage(): void
    {
        $this->view('layouts/public_layout', [
            'contentView' => __DIR__ . '/../Views/restaurant/reservation_search.php',
            'script'      => 'reservation-search'
        ]);
    }

    /**
     * GET /api/public/branches
     */
    public function branches(): void
    {
        try {
            $this->json([
                'success' => true,
                'data' => $this->branchRepository->getActiveBranches()
            ]);

        } catch (Throwable $e) {

            error_log(
                'Restaurant branches error: ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' => 'No fue posible obtener las sedes.'
            ], 500);
        }
    }

    /**
     * GET /api/public/availability
     */
    public function availability(): void
    {
        try {
            $availability = $this->availabilityService->getAvailability(
                (int) ($_GET['branch_id'] ?? 0),
                trim($_GET['date'] ?? ''),
                (int) ($_GET['people'] ?? 0)
            );

            $this->json([
                'success' => true,
                'data'    => $availability
            ]);

        } catch (InvalidArgumentException $e) {

            $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);

        } catch (Throwable $e) {

            error_log(
                'Restaurant availability error: ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' => 'Error interno al consultar disponibilidad.'
            ], 500);
        }
    }

    /**
     * POST /api/public/reservations
     */
    public function create(): void
    {
        try {
            $input = $this->getJsonInput();

            if (!is_array($input)) {
                throw new InvalidArgumentException(
                    'El cuerpo de la solicitud no es válido.'
                );
            }

            $result = $this->reservationService->createReservation($input);

            $this->json([
                'success' => true,
                'message' => 'Reserva creada correctamente.',
                'data'    => $result
            ]);

        } catch (InvalidArgumentException $e) {

            $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);

        } catch (Throwable $e) {

            error_log(
                'Restaurant reservation error: ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 409);
        }
    }

    /**
     * GET /api/public/reservations/search
     */
    public function search(): void
    {
        try {
            $reservationCode = trim(
                $_GET['reservation_code'] ?? ''
            );

            $email = trim(
                $_GET['email'] ?? ''
            );

            if (
                $reservationCode === '' &&
                $email === ''
            ) {
                throw new InvalidArgumentException(
                    'Ingresa un código de reserva o un correo electrónico.'
                );
            }

            $results = $this->reservationService->searchReservations(
                $reservationCode,
                $email
            );

            $this->json([
                'success' => true,
                'data'    => $results
            ]);

        } catch (InvalidArgumentException $e) {

            $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);

        } catch (Throwable $e) {

            error_log(
                'Restaurant reservation search error: ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' => 'No fue posible consultar la reserva.'
            ], 500);
        }
    }

    /**
     * POST /api/public/reservations/cancel
     *
     * El cliente debe proporcionar:
     * - código de reserva
     * - correo electrónico
     */
    public function cancel(): void
    {
        try {
            $input = $this->getJsonInput();

            $reservationCode = trim(
                $input['reservation_code'] ?? ''
            );

            $email = trim(
                $input['email'] ?? ''
            );

            if (
                $reservationCode === '' ||
                $email === ''
            ) {
                throw new InvalidArgumentException(
                    'El código de reserva y el correo electrónico son obligatorios.'
                );
            }

            $result =
                $this->reservationService->cancelPublicReservation(
                    $reservationCode,
                    $email
                );

            $this->json([
                'success' => true,
                'message' => 'La reserva fue cancelada correctamente.',
                'data'    => $result
            ]);

        } catch (InvalidArgumentException $e) {

            $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);

        } catch (Throwable $e) {

            error_log(
                'Restaurant reservation cancellation error: ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' => 'No fue posible cancelar la reserva.'
            ], 500);
        }
    }

    /**
     * Obtiene los datos enviados por JSON o formulario.
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