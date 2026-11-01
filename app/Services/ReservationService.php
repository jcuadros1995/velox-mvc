<?php
// app/Services/ReservationService.php

class ReservationService
{
    private AvailabilityService $availabilityService;
    private CustomerRepository $customerRepository;
    private RestaurantReservationRepository $reservationRepository;
    private UserRepository $userRepository;
    private NotificationService $notificationService;
    private BranchRepository $branchRepository;
    private EmailService $emailService;

    public function __construct()
    {
        $this->availabilityService =
            new AvailabilityService();

        $this->customerRepository =
            new CustomerRepository();

        $this->reservationRepository =
            new RestaurantReservationRepository();

        $this->notificationService =
            new NotificationService();

        $this->userRepository =
            new UserRepository();

        $this->branchRepository =
            new BranchRepository();

        $this->emailService =
            new EmailService();
    }

    public function createReservation(array $input): array
    {
        try {

            /*
             * =========================================================
             * 1. VALIDAR ESTRUCTURA DE LA SOLICITUD
             * =========================================================
             */

            $branchId = (int) ($input['branch_id'] ?? 0);

            $date = trim(
                $input['reservation_date'] ?? ''
            );

            $startTime = trim(
                $input['start_time'] ?? ''
            );

            $people = (int) (
                $input['people_count'] ?? 0
            );

            $customerData = $input['customer'] ?? [];

            if (!is_array($customerData)) {
                throw new InvalidArgumentException(
                    'Los datos del cliente no son válidos.'
                );
            }


            /*
             * =========================================================
             * 2. VALIDACIONES BÁSICAS
             * =========================================================
             */

            if ($branchId <= 0) {
                throw new InvalidArgumentException(
                    'La sede es obligatoria.'
                );
            }

            if (!$this->isValidDate($date)) {
                throw new InvalidArgumentException(
                    'La fecha no es válida.'
                );
            }

            if (!$this->isValidTime($startTime)) {
                throw new InvalidArgumentException(
                    'La hora de inicio no es válida.'
                );
            }

            if ($people <= 0) {
                throw new InvalidArgumentException(
                    'La cantidad de personas debe ser mayor a cero.'
                );
            }


            /*
             * =========================================================
             * 3. DATOS DEL CLIENTE
             * =========================================================
             */

            $customerName = trim(
                $customerData['name'] ?? ''
            );

            $customerPhone = trim(
                $customerData['phone'] ?? ''
            );

            $customerEmail = trim(
                $customerData['email'] ?? ''
            );

            $notes = trim(
                $input['notes'] ?? ''
            );


            if ($customerName === '') {
                throw new InvalidArgumentException(
                    'El nombre es obligatorio.'
                );
            }

            if ($customerPhone === '') {
                throw new InvalidArgumentException(
                    'El teléfono es obligatorio.'
                );
            }

            if (
                $customerEmail !== '' &&
                !filter_var(
                    $customerEmail,
                    FILTER_VALIDATE_EMAIL
                )
            ) {
                throw new InvalidArgumentException(
                    'El correo electrónico no es válido.'
                );
            }


            /*
             * =========================================================
             * 4. OBTENER CONFIGURACIÓN DE RESERVAS
             * =========================================================
             */

            $settings =
                $this->availabilityService->getSettings(
                    $branchId
                );

            if (
                !$settings ||
                !(int) $settings['is_enabled']
            ) {
                throw new InvalidArgumentException(
                    'Las reservas no están habilitadas.'
                );
            }


            /*
             * =========================================================
             * 5. VALIDAR CANTIDAD DE PERSONAS
             * =========================================================
             */

            $minPeople =
                (int) $settings['min_people'];

            $maxPeople =
                (int) $settings['max_people'];

            if (
                $people < $minPeople ||
                $people > $maxPeople
            ) {
                throw new InvalidArgumentException(
                    "La cantidad de personas debe estar entre {$minPeople} y {$maxPeople}."
                );
            }


            /*
             * =========================================================
             * 6. CALCULAR DURACIÓN DE LA RESERVA
             * =========================================================
             */

            $duration =
                (int) $settings[
                'reservation_duration_minutes'
                ];

            if ($duration <= 0) {
                throw new RuntimeException(
                    'La duración de la reserva no está configurada correctamente.'
                );
            }


            /*
             * =========================================================
             * 7. CALCULAR HORA DE FINALIZACIÓN
             * =========================================================
             */

            $startTimestamp = strtotime(
                $date . ' ' . $startTime
            );

            if ($startTimestamp === false) {
                throw new InvalidArgumentException(
                    'La fecha y hora no son válidas.'
                );
            }

            $endTimestamp =
                $startTimestamp + ($duration * 60);

            $normalizedStartTime =
                date(
                    'H:i:s',
                    $startTimestamp
                );

            $endTime =
                date(
                    'H:i:s',
                    $endTimestamp
                );


            /*
             * =========================================================
             * 8. VALIDAR HORARIO COMERCIAL
             * =========================================================
             */

            $dateTimestamp = strtotime($date);

            if ($dateTimestamp === false) {
                throw new InvalidArgumentException(
                    'La fecha no es válida.'
                );
            }

            $dayOfWeek = (int) date(
                'N',
                $dateTimestamp
            );

            $hours =
                $this->availabilityService->getBusinessHours(
                    $branchId,
                    $dayOfWeek
                );

            if (
                !$hours ||
                (int) $hours['is_closed']
            ) {
                throw new InvalidArgumentException(
                    'La sede está cerrada ese día.'
                );
            }


            /*
             * =========================================================
             * 9. VALIDAR APERTURA Y CIERRE
             * =========================================================
             */

            if (
                $normalizedStartTime < $hours['open_time'] ||
                $endTime > $hours['close_time']
            ) {
                throw new InvalidArgumentException(
                    'El horario seleccionado está fuera del horario de atención.'
                );
            }


            /*
             * =========================================================
             * 10. VALIDAR CIERRES ESPECIALES
             * =========================================================
             */

            if (
                $this->availabilityService->isTimeClosed(
                    $branchId,
                    $date,
                    $normalizedStartTime,
                    $endTime
                )
            ) {
                throw new InvalidArgumentException(
                    'El horario seleccionado no está disponible.'
                );
            }


            /*
             * =========================================================
             * 11. OBTENER MESAS DISPONIBLES
             * =========================================================
             */

            $availableTables =
                $this->availabilityService->getAvailableTables(
                    $branchId,
                    $date,
                    $normalizedStartTime,
                    $endTime
                );

            if (empty($availableTables)) {
                throw new InvalidArgumentException(
                    'No hay mesas disponibles para ese horario.'
                );
            }


            /*
             * =========================================================
             * 12. ENCONTRAR MEJOR COMBINACIÓN DE MESAS
             * =========================================================
             */

            $combinationTables =
                $this->availabilityService->findBestTableCombination(
                    $availableTables,
                    $people
                );

            if (empty($combinationTables)) {
                throw new InvalidArgumentException(
                    'No hay una combinación de mesas disponible para ese número de personas.'
                );
            }


            /*
             * =========================================================
             * 13. CALCULAR CAPACIDAD TOTAL
             * =========================================================
             */

            $totalCapacity = array_sum(
                array_map(
                    static function (array $table): int {
                        return (int) $table['capacity'];
                    },
                    $combinationTables
                )
            );

            $combination = [
                'tables' => $combinationTables,
                'total_capacity' => $totalCapacity
            ];


            /*
             * =========================================================
             * 14. OBTENER IDS DE LAS MESAS
             * =========================================================
             */

            $tableIds = array_map(
                static function ($table): int {
                    return (int) $table['id'];
                },
                $combination['tables']
            );

            sort($tableIds);

            if (empty($tableIds)) {
                throw new RuntimeException(
                    'No fue posible determinar las mesas de la reserva.'
                );
            }


            /*
             * =========================================================
             * 15. INICIAR TRANSACCIÓN
             * =========================================================
             */

            $this->reservationRepository->beginTransaction();


            /*
             * =========================================================
             * 16. BLOQUEAR MESAS
             *
             * Volvemos a consultar las mesas dentro de la
             * transacción para evitar carreras de concurrencia.
             * =========================================================
             */


            $lockedTables =
                $this->reservationRepository->lockTables(
                    $tableIds,
                    $branchId
                );

            if (
                count($lockedTables) !== count($tableIds)
            ) {
                throw new RuntimeException(
                    'Las mesas seleccionadas ya no están disponibles.'
                );
            }


            /*
             * =========================================================
             * 17. VALIDAR NUEVAMENTE CONFLICTOS
             * =========================================================
             */

            $conflicts =
                $this->reservationRepository
                    ->countTableConflicts(
                        $tableIds,
                        $date,
                        $normalizedStartTime,
                        $endTime
                    );

            if ($conflicts > 0) {
                throw new RuntimeException(
                    'Las mesas seleccionadas acaban de ser reservadas.'
                );
            }


            /*
             * =========================================================
             * 18. BUSCAR CLIENTE POR EMAIL
             * =========================================================
             */

            $customerId = null;

            if ($customerEmail !== '') {

                $customer =
                    $this->customerRepository->findByEmail(
                        $customerEmail
                    );

                if ($customer) {
                    $customerId =
                        (int) $customer['id'];
                }
            }


            /*
             * =========================================================
             * 19. BUSCAR CLIENTE POR TELÉFONO
             * =========================================================
             */

            if (
                !$customerId &&
                $customerPhone !== ''
            ) {

                $customer =
                    $this->customerRepository->findByPhone(
                        $customerPhone
                    );

                if ($customer) {
                    $customerId =
                        (int) $customer['id'];
                }
            }


            /*
             * =========================================================
             * 20. SEPARAR NOMBRE Y APELLIDO
             * =========================================================
             */

            $nameParts = preg_split(
                '/\s+/',
                $customerName,
                2
            );

            $firstName =
                trim($nameParts[0] ?? '');

            $lastName =
                trim($nameParts[1] ?? '');


            /*
             * =========================================================
             * 21. CREAR O ACTUALIZAR CLIENTE
             * =========================================================
             */

            if (!$customerId) {

                $customerId =
                    $this->customerRepository->create([
                        'first_name' => $firstName,
                        'last_name' =>
                            $lastName !== ''
                                ? $lastName
                                : null,
                        'phone' => $customerPhone,
                        'email' =>
                            $customerEmail !== ''
                                ? $customerEmail
                                : null,
                        'notes' => null
                    ]);

            } else {

                $this->customerRepository->update(
                    $customerId,
                    [
                        'first_name' => $firstName,
                        'last_name' =>
                            $lastName !== ''
                                ? $lastName
                                : null,
                        'phone' => $customerPhone
                    ]
                );
            }


            /*
             * =========================================================
             * 22. GENERAR CÓDIGO ÚNICO DE RESERVA
             * =========================================================
             */

            $reservationCode =
                $this->reservationRepository
                    ->generateUniqueReservationCode();


            /*
             * =========================================================
             * 23. DETERMINAR ESTADO INICIAL
             * =========================================================
             */

            $status =
                (int) $settings['auto_confirm'] === 1
                    ? 'confirmed'
                    : 'pending';


            /*
             * =========================================================
             * 24. FECHA DE CONFIRMACIÓN
             * =========================================================
             */

            $confirmedAt =
                $status === 'confirmed'
                    ? date('Y-m-d H:i:s')
                    : null;


            /*
             * =========================================================
             * 25. CREAR RESERVA
             * =========================================================
             */

            $reservationId =
                $this->reservationRepository->create([
                    'reservation_code' =>
                        $reservationCode,

                    'branch_id' =>
                        $branchId,

                    'customer_id' =>
                        $customerId,

                    'reservation_date' =>
                        $date,

                    'start_time' =>
                        $normalizedStartTime,

                    'end_time' =>
                        $endTime,

                    'people_count' =>
                        $people,

                    'status' =>
                        $status,

                    'source' =>
                        'web',

                    'notes' =>
                        $notes !== ''
                            ? $notes
                            : null,

                    'confirmed_at' =>
                        $confirmedAt
                ]);


            /*
             * =========================================================
             * 26. ASIGNAR MESAS
             * =========================================================
             */

            $this->reservationRepository->assignTables(
                $reservationId,
                $tableIds
            );


            /*
             * =========================================================
             * 27. CONFIRMAR TRANSACCIÓN
             * =========================================================
             */

            $this->reservationRepository->commit();




            /*
             * =========================================================
             * 28. RESPUESTA
             * =========================================================
             */

            $reservationResult = [
                'customer_name' =>
                    $customerName,

                'customer_email' =>
                    $customerEmail,

                'reservation_id' =>
                    $reservationId,

                'reservation_code' =>
                    $reservationCode,

                'branch_id' =>
                    $branchId,

                'reservation_date' =>
                    $date,

                'start_time' =>
                    $normalizedStartTime,

                'end_time' =>
                    $endTime,

                'people_count' =>
                    $people,

                'status' =>
                    $status,

                'tables' =>
                    $combination['tables'],

                'total_capacity' =>
                    $combination['total_capacity']
            ];

            $this->notifyReservationCreated(
                $reservationResult
            );

            /*
             * Emails.
             */
            $this->sendReservationCreatedEmails(
                $reservationResult
            );

            return $reservationResult;

        } catch (Throwable $e) {

            /*
             * =========================================================
             * ROLLBACK
             * =========================================================
             */

            if ($this->reservationRepository->inTransaction()) {
                $this->reservationRepository->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Valida fecha en formato YYYY-MM-DD.
     */
    private function isValidDate(string $date): bool
    {
        $d = DateTime::createFromFormat(
            'Y-m-d',
            $date
        );

        return $d !== false
            && $d->format('Y-m-d') === $date;
    }

    /**
     * Valida hora en formato HH:MM o HH:MM:SS.
     */
    private function isValidTime(string $time): bool
    {
        return preg_match(
                '/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/',
                $time
            ) === 1;
    }

    /**
     * Consulta reservas por código y/o correo electrónico.
     */

    public function searchReservations(
        string $reservationCode = '',
        string $email = ''
    ): array {
        $reservationCode = strtoupper(trim($reservationCode));
        $email = strtolower(trim($email));

        // La búsqueda pública siempre exige dos factores de conocimiento.
        if (
            $reservationCode === '' ||
            $email === '' ||
            !preg_match('/^RSV-[A-Z0-9]{6}$/', $reservationCode) ||
            !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            throw new InvalidArgumentException(
                'Ingresa un código de reserva y un correo electrónico válidos.'
            );
        }

        return $this->reservationRepository->searchPublicReservations(
            $reservationCode,
            $email
        );
    }

    /**
     * Cancela una reserva desde el portal público.
     *
     * Seguridad:
     *
     * 1. Valida código.
     * 2. Valida correo.
     * 3. Verifica que código + correo pertenezcan
     *    a la misma reserva.
     * 4. Verifica que la reserva permita cancelación.
     * 5. Verifica el límite de tiempo configurado
     *    por la sede.
     * 6. Actualiza únicamente la reserva correspondiente.
     */
    public function cancelPublicReservation(
        string $reservationCode,
        string $email
    ): array {

        $reservationCode =
            strtoupper(trim($reservationCode));

        $email =
            trim($email);


        /*
         * =====================================================
         * VALIDACIÓN DEL CÓDIGO
         * =====================================================
         */

        if (
            !preg_match(
                '/^RSV-[A-Z0-9]{6}$/',
                $reservationCode
            )
        ) {

            throw new InvalidArgumentException(
                'El código de reserva no tiene un formato válido.'
            );
        }


        /*
         * =====================================================
         * VALIDACIÓN DEL CORREO
         * =====================================================
         */

        if (
            !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {

            throw new InvalidArgumentException(
                'El correo electrónico no tiene un formato válido.'
            );
        }


        /*
         * =====================================================
         * BUSCAR RESERVA
         * =====================================================
         *
         * La combinación código + correo debe coincidir.
         */

        $reservation =
            $this->reservationRepository
                ->findPublicReservationForCancellation(
                    $reservationCode,
                    $email
                );


        if (!$reservation) {

            throw new InvalidArgumentException(
                'No encontramos una reserva asociada al código y correo ingresados.'
            );
        }


        /*
         * =====================================================
         * VALIDAR ESTADO
         * =====================================================
         */

        $allowedStatuses = [
            'pending',
            'confirmed'
        ];


        if (
            !in_array(
                $reservation['status'],
                $allowedStatuses,
                true
            )
        ) {

            if (
                $reservation['status'] === 'cancelled'
            ) {

                throw new InvalidArgumentException(
                    'Esta reserva ya se encuentra cancelada.'
                );
            }


            throw new InvalidArgumentException(
                'Esta reserva ya no puede ser cancelada.'
            );
        }


        /*
         * =====================================================
         * CONFIGURACIÓN DE LA SEDE
         * =====================================================
         */

        $settings =
            $this->reservationRepository
                ->getReservationSettings(
                    (int) $reservation['branch_id']
                );


        /*
         * Si no existe configuración,
         * por seguridad NO permitimos cancelación pública.
         */
        if (!$settings) {

            throw new InvalidArgumentException(
                'La cancelación pública no está configurada para esta sede.'
            );
        }


        /*
         * =====================================================
         * ¿ESTÁ PERMITIDA LA CANCELACIÓN?
         * =====================================================
         */

        if (
            (int) $settings['allow_customer_cancellation'] !== 1
        ) {

            throw new InvalidArgumentException(
                'Esta sede no permite cancelar reservas desde el portal público.'
            );
        }


        /*
         * =====================================================
         * FECHA/HORA DE LA RESERVA
         * =====================================================
         */

        $reservationDateTime =
            new DateTime(
                $reservation['reservation_date'] .
                ' ' .
                $reservation['start_time']
            );


        $now =
            new DateTime();


        /*
         * =====================================================
         * RESERVA YA INICIADA
         * =====================================================
         */

        if (
            $reservationDateTime <= $now
        ) {

            throw new InvalidArgumentException(
                'La reserva ya inició o la hora de la reserva ya pasó.'
            );
        }


        /*
         * =====================================================
         * LÍMITE DE CANCELACIÓN
         * =====================================================
         *
         * Ejemplo:
         *
         * cancellation_limit_minutes = 120
         *
         * Reserva 19:00
         * Se puede cancelar hasta las 17:00.
         */

        $cancellationLimitMinutes =
            (int) (
                $settings['cancellation_limit_minutes'] ?? 0
            );


        if (
            $cancellationLimitMinutes > 0
        ) {

            $limitDateTime =
                clone $reservationDateTime;


            $limitDateTime->modify(
                '-' .
                $cancellationLimitMinutes .
                ' minutes'
            );


            if (
                $now >= $limitDateTime
            ) {

                throw new InvalidArgumentException(
                    'La reserva ya se encuentra fuera del tiempo permitido para cancelación.'
                );
            }
        }


        /*
         * =====================================================
         * CANCELAR
         * =====================================================
         */

        $cancelled =
            $this->reservationRepository
                ->cancelReservation(
                    (int) $reservation['id'],
                    'Cancelación realizada por el cliente desde el portal público'
                );


        if (!$cancelled) {

            throw new RuntimeException(
                'La reserva no pudo ser cancelada. Es posible que haya cambiado de estado.'
            );
        }


        /*
         * =====================================================
         * RESPUESTA
         * =====================================================
         */

        return [

            'id' =>
                (int) $reservation['id'],

            'reservation_code' =>
                $reservation['reservation_code'],

            'status' =>
                'cancelled',

            'reservation_date' =>
                $reservation['reservation_date'],

            'start_time' =>
                substr(
                    $reservation['start_time'],
                    0,
                    5
                ),

            'end_time' =>
                substr(
                    $reservation['end_time'],
                    0,
                    5
                ),

            'message' =>
                'La reserva fue cancelada correctamente.'
        ];
    }

    /**
     * Notifica la creación de una reserva
     * a los administradores correspondientes.
     */
    private function notifyReservationCreated(
        array $reservation
    ): void {

        $branchId =
            (int) $reservation['branch_id'];

        $recipients =
            $this->userRepository
                ->getReservationNotificationRecipients(
                    $branchId
                );

        if (empty($recipients)) {
            return;
        }

        $reservationCode =
            (string) $reservation['reservation_code'];

        $date =
            (string) $reservation['reservation_date'];

        $startTime =
            (string) $reservation['start_time'];

        $endTime =
            (string) $reservation['end_time'];

        $people =
            (int) $reservation['people_count'];

        $message =
            sprintf(
                'Se creó la reserva %s para el %s de %s a %s. Personas: %d.',
                $reservationCode,
                $date,
                $startTime,
                $endTime,
                $people
            );

        foreach ($recipients as $recipient) {

            try {

                $this->notificationService->create(
                    (int) $recipient['id'],
                    $branchId,
                    'reservation_created',
                    'Nueva reserva',
                    $message,
                    'reservation',
                    (int) $reservation['reservation_id']
                );

            } catch (Throwable $e) {

                /*
                 * La reserva ya fue confirmada.
                 * Una falla en una notificación no debe
                 * invalidar la reserva.
                 */
                error_log(
                    'Burger Town Notification Error: ' .
                    $e->getMessage()
                );
            }
        }
    }


    private function sendReservationCreatedEmails(
        array $reservation
    ): void {

        /*
         * ---------------------------------------------------------
         * DATOS DE LA SEDE
         * ---------------------------------------------------------
         */

        $branch =
            $this->branchRepository->findById(
                (int) $reservation['branch_id']
            );

        if (!$branch) {
            return;
        }


        /*
         * ---------------------------------------------------------
         * DATOS COMPLETOS PARA EL EMAIL
         * ---------------------------------------------------------
         */

        $reservation['branch_name'] =
            $branch['name'] ?? '';

        $reservation['branch_phone'] =
            $branch['phone'] ?? '';

        $reservation['branch_email'] =
            $branch['email'] ?? '';

        $reservation['customer_name'] =
            trim(
                (string) (
                    $reservation['customer_name'] ?? ''
                )
            );

        $reservation['customer_email'] =
            trim(
                (string) (
                    $reservation['customer_email'] ?? ''
                )
            );


        /*
         * ---------------------------------------------------------
         * EMAIL DEL CLIENTE
         * ---------------------------------------------------------
         */

        try {

            $this->emailService
                ->sendReservationCreatedCustomer(
                    $reservation
                );

        } catch (Throwable $e) {

            error_log(
                '[RESERVATION_EMAIL_CUSTOMER] ' .
                $e->getMessage()
            );
        }


        /*
         * ---------------------------------------------------------
         * EMAILS ADMINISTRATIVOS
         * ---------------------------------------------------------
         */

        $branchEmail = trim(
            (string) ($branch['email'] ?? '')
        );

        if (
            $branchEmail !== '' &&
            filter_var($branchEmail, FILTER_VALIDATE_EMAIL)
        ) {
            $recipient = [
                'email' => $branchEmail,
                'first_name' => $branch['name'] ?? '',
                'last_name' => ''
            ];

            try {
                $this->emailService
                    ->sendReservationCreatedAdmin(
                        $reservation,
                        $recipient
                    );

            } catch (Throwable $e) {
                error_log(
                    '[RESERVATION_EMAIL_ADMIN] ' .
                    $e->getMessage()
                );
            }
        }
    }


}