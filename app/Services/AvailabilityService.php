<?php

class AvailabilityService
{
    private BranchRepository $branchRepository;
    private RestaurantReservationRepository $reservationRepository;
    private RestaurantTableRepository $tableRepository;

    public function __construct()
    {
        $this->branchRepository = new BranchRepository();
        $this->reservationRepository = new RestaurantReservationRepository();
        $this->tableRepository = new RestaurantTableRepository();
    }

    /**
     * Obtiene los horarios disponibles
     * para una sede, fecha y cantidad de personas.
     */
    public function getAvailability(
        int $branchId,
        string $date,
        int $people
    ): array {

        $this->validateDate($date);

        if ($people < 1) {
            throw new Exception(
                'La cantidad de personas debe ser mayor a cero.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sede
        |--------------------------------------------------------------------------
        */

        $branch = $this->getBranch($branchId);

        if (!$branch) {
            throw new Exception('La sede no existe.');
        }

        if (!(int) $branch['is_active']) {
            throw new Exception('La sede no está activa.');
        }

        /*
        |--------------------------------------------------------------------------
        | Configuración
        |--------------------------------------------------------------------------
        */

        $settings = $this->getReservationSettings($branchId);

        if (!$settings) {
            throw new Exception(
                'La sede no tiene configuración de reservas.'
            );
        }

        if (!(int) $settings['is_enabled']) {
            throw new Exception(
                'Las reservas no están habilitadas para esta sede.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Cantidad de personas
        |--------------------------------------------------------------------------
        */

        $minPeople = (int) $settings['min_people'];
        $maxPeople = (int) $settings['max_people'];

        if (
            $people < $minPeople ||
            $people > $maxPeople
        ) {
            throw new Exception(
                "La reserva debe ser entre {$minPeople} y {$maxPeople} personas."
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Anticipación
        |--------------------------------------------------------------------------
        */

        $this->validateAdvanceBooking(
            $date,
            $settings
        );

        /*
        |--------------------------------------------------------------------------
        | Día de la semana
        |--------------------------------------------------------------------------
        */

        $dayOfWeek = (int) date(
            'N',
            strtotime($date)
        );

        /*
        |--------------------------------------------------------------------------
        | Horario de la sede
        |--------------------------------------------------------------------------
        */

        $businessHours = $this->getBusinessHours(
            $branchId,
            $dayOfWeek
        );

        if (
            !$businessHours ||
            (int) $businessHours['is_closed']
        ) {
            return [
                'branch' => $branch,
                'date' => $date,
                'people' => $people,
                'slots' => []
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Cierre de sede durante todo el día
        |--------------------------------------------------------------------------
        */

        if ($this->isBranchClosed($branchId, $date)) {
            return [
                'branch' => $branch,
                'date' => $date,
                'people' => $people,
                'slots' => []
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Generar slots
        |--------------------------------------------------------------------------
        */

        $slots = $this->generateSlots(
            $businessHours['open_time'],
            $businessHours['close_time'],
            (int) $settings['slot_interval_minutes'],
            (int) $settings['reservation_duration_minutes']
        );

        /*
        |--------------------------------------------------------------------------
        | Disponibilidad
        |--------------------------------------------------------------------------
        */

        $availableSlots = [];

        foreach ($slots as $slot) {

            /*
            |--------------------------------------------------------------------------
            | Validar anticipación para el día actual
            |--------------------------------------------------------------------------
            */

            if (
                !$this->isSlotAllowedToday(
                    $date,
                    $slot['start'],
                    $settings
                )
            ) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Validar cierres parciales
            |--------------------------------------------------------------------------
            */

            if (
                $this->isTimeClosed(
                    $branchId,
                    $date,
                    $slot['start'],
                    $slot['end']
                )
            ) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Obtener mesas realmente disponibles
            |--------------------------------------------------------------------------
            |
            | IMPORTANTE:
            | RestaurantTable::getAvailableTables()
            | recibe exactamente 4 parámetros.
            |
            */

            $tables = $this->tableRepository->getAvailableTables(
                $branchId,
                $date,
                $slot['start'],
                $slot['end']
            );

            /*
            |--------------------------------------------------------------------------
            | Buscar mejor combinación
            |--------------------------------------------------------------------------
            */

            $bestCombination = $this->findBestTableCombination(
                $tables,
                $people
            );

            $availableSlots[] = [
                'time' => substr($slot['start'], 0, 5),
                'start_time' => $slot['start'],
                'end_time' => $slot['end'],

                // Indica si existe una combinación de mesas
                // que permita atender la cantidad de personas solicitada.
                'available' => !empty($bestCombination),

                // Cantidad de mesas que requiere la mejor combinación
                // para esta reserva.
                'tables_required' => count($bestCombination),

                // NUEVO:
                // Cantidad TOTAL de mesas físicas disponibles
                // durante este horario.
                'available_tables_count' => count($tables),

                'tables' => array_map(
                    static function (array $table): array {
                        return [
                            'id' => (int) $table['id'],
                            'table_number' => $table['table_number'],
                            'name' => $table['name'],
                            'capacity' => (int) $table['capacity'],
                            'location' => $table['location']
                        ];
                    },
                    $bestCombination
                )
            ];
        }

        return [
            'branch' => $branch,
            'date' => $date,
            'people' => $people,
            'slots' => $availableSlots
        ];
    }

    /**
     * Obtiene una sede.
     */
    private function getBranch(int $branchId): ?array
    {
        return $this->branchRepository->findById($branchId);
    }

    /**
     * Obtiene configuración de reservas.
     */
    private function getReservationSettings(int $branchId): ?array
    {
        return $this->reservationRepository->getReservationSettings(
            $branchId
        );
    }

    /**
     * Obtiene horario según día.
     */
    public function getBusinessHours(
        int $branchId,
        int $dayOfWeek
    ): ?array {
        return $this->branchRepository->getBusinessHours(
            $branchId,
            $dayOfWeek
        );
    }

    /**
     * Comprueba si la sede está cerrada todo el día.
     */
    private function isBranchClosed(
        int $branchId,
        string $date
    ): bool {
        return $this->branchRepository->isBranchClosed(
            $branchId,
            $date
        );
    }

    /**
     * Comprueba si existe un cierre parcial
     * que se cruce con el horario solicitado.
     */
    public function isTimeClosed(
        int $branchId,
        string $date,
        string $startTime,
        string $endTime
    ): bool {
        return $this->branchRepository->isTimeClosed(
            $branchId,
            $date,
            $startTime,
            $endTime
        );
    }

    /**
     * Genera los slots de reserva.
     */
    public function generateSlots(
        string $openTime,
        string $closeTime,
        int $intervalMinutes,
        int $durationMinutes
    ): array {

        $slots = [];

        $openTimestamp = strtotime($openTime);
        $closeTimestamp = strtotime($closeTime);

        if ($openTimestamp === false || $closeTimestamp === false) {
            return [];
        }

        if ($intervalMinutes <= 0 || $durationMinutes <= 0) {
            return [];
        }

        $durationSeconds = $durationMinutes * 60;
        $intervalSeconds = $intervalMinutes * 60;

        $lastStartTimestamp =
            $closeTimestamp - $durationSeconds;

        for (
            $startTimestamp = $openTimestamp;
            $startTimestamp <= $lastStartTimestamp;
            $startTimestamp += $intervalSeconds
        ) {

            $endTimestamp =
                $startTimestamp + $durationSeconds;

            $slots[] = [
                'start' => date('H:i:s', $startTimestamp),
                'end'   => date('H:i:s', $endTimestamp)
            ];
        }

        return $slots;
    }

    /**
     * Valida formato de fecha.
     */
    private function validateDate(
        string $date
    ): void {

        $dateObject = DateTime::createFromFormat(
            'Y-m-d',
            $date
        );

        $errors = DateTime::getLastErrors();

        /*
        |--------------------------------------------------------------------------
        | PHP puede devolver false cuando no existen errores.
        |--------------------------------------------------------------------------
        */

        $hasErrors =
            is_array($errors) &&
            (
                $errors['warning_count'] > 0 ||
                $errors['error_count'] > 0
            );

        if (
            !$dateObject ||
            $hasErrors ||
            $dateObject->format('Y-m-d') !== $date
        ) {
            throw new Exception(
                'La fecha no tiene un formato válido. Use YYYY-MM-DD.'
            );
        }
    }

    /**
     * Valida la anticipación de la reserva.
     */
    private function validateAdvanceBooking(
        string $date,
        array $settings
    ): void {

        $today = new DateTime('today');

        $reservationDate = new DateTime($date);

        /*
        |--------------------------------------------------------------------------
        | No permitir fechas pasadas
        |--------------------------------------------------------------------------
        */

        if ($reservationDate < $today) {
            throw new Exception(
                'No puedes reservar una fecha pasada.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Máximo de días de anticipación
        |--------------------------------------------------------------------------
        */

        $maxAdvanceDays =
            (int) ($settings['max_advance_days'] ?? 60);

        $maxDate = clone $today;

        $maxDate->modify(
            '+' . $maxAdvanceDays . ' days'
        );

        if ($reservationDate > $maxDate) {
            throw new Exception(
                'La fecha supera el máximo permitido para reservas.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Reservas para el mismo día
        |--------------------------------------------------------------------------
        */

        $allowSameDay =
            (int) ($settings['allow_same_day'] ?? 1);

        if (
            !$allowSameDay &&
            $reservationDate->format('Y-m-d') ===
            $today->format('Y-m-d')
        ) {
            throw new Exception(
                'No se permiten reservas para el mismo día.'
            );
        }
    }

    /**
     * Determina si un horario de hoy todavía puede reservarse.
     */
    private function isSlotAllowedToday(
        string $date,
        string $startTime,
        array $settings
    ): bool {

        $timezone = new DateTimeZone('America/Bogota');

        $today = new DateTime('today', $timezone);

        $reservationDate = new DateTime(
            $date . ' 00:00:00',
            $timezone
        );

        /*
        |--------------------------------------------------------------------------
        | Si no es hoy, el horario está permitido.
        |--------------------------------------------------------------------------
        */

        if (
            $reservationDate->format('Y-m-d') !==
            $today->format('Y-m-d')
        ) {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | Hora actual de Colombia.
        |--------------------------------------------------------------------------
        */

        $now = new DateTime('now', $timezone);

        /*
        |--------------------------------------------------------------------------
        | Anticipación mínima configurada.
        |--------------------------------------------------------------------------
        */

        $minimumAdvanceMinutes =
            (int) ($settings['min_advance_minutes'] ?? 0);

        $minimumAdvance = clone $now;

        $minimumAdvance->modify(
            '+' . $minimumAdvanceMinutes . ' minutes'
        );

        /*
        |--------------------------------------------------------------------------
        | Hora de inicio del horario.
        |--------------------------------------------------------------------------
        */

        $slot = new DateTime(
            $date . ' ' . $startTime,
            $timezone
        );

        return $slot >= $minimumAdvance;
    }

    /**
     * Busca la mejor combinación de mesas.
     */
    public function findBestTableCombination(
        array $tables,
        int $people
    ): array {

        if (empty($tables)) {
            return [];
        }

        /*
        |--------------------------------------------------------------------------
        | Ordenar por capacidad ascendente.
        |--------------------------------------------------------------------------
        |
        | Esto ayuda a encontrar primero la mesa más pequeña
        | que pueda atender directamente al grupo.
        |
        */

        usort(
            $tables,
            static function (
                array $a,
                array $b
            ): int {

                $capacityCompare =
                    (int) $a['capacity']
                    <=>
                    (int) $b['capacity'];

                if ($capacityCompare !== 0) {
                    return $capacityCompare;
                }

                return (int) $a['id']
                    <=>
                    (int) $b['id'];
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Primero intentar una sola mesa.
        |--------------------------------------------------------------------------
        */

        foreach ($tables as $table) {

            if (
                (int) $table['capacity'] >=
                $people
            ) {
                return [$table];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Buscar combinación.
        |--------------------------------------------------------------------------
        */

        $best = null;

        $this->findCombinations(
            $tables,
            $people,
            0,
            [],
            0,
            $best
        );

        return $best ?? [];
    }

    /**
     * Algoritmo recursivo para encontrar combinaciones.
     */
    private function findCombinations(
        array $tables,
        int $people,
        int $index,
        array $current,
        int $capacity,
        ?array &$best
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Ya alcanzamos la capacidad necesaria.
        |--------------------------------------------------------------------------
        */

        if ($capacity >= $people) {

            if (
                $best === null ||
                $this->isBetterCombination(
                    $current,
                    $best,
                    $capacity,
                    $people
                )
            ) {
                $best = $current;
            }

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | No quedan mesas.
        |--------------------------------------------------------------------------
        */

        if ($index >= count($tables)) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Máximo 4 mesas combinadas.
        |--------------------------------------------------------------------------
        */

        if (count($current) >= 4) {
            return;
        }

        for (
            $i = $index;
            $i < count($tables);
            $i++
        ) {

            $table = $tables[$i];

            $newCapacity =
                $capacity +
                (int) $table['capacity'];

            $newCurrent = $current;

            $newCurrent[] = $table;

            $this->findCombinations(
                $tables,
                $people,
                $i + 1,
                $newCurrent,
                $newCapacity,
                $best
            );
        }
    }

    /**
     * Determina cuál combinación es mejor.
     *
     * Prioridad:
     *
     * 1. Menor desperdicio de capacidad.
     * 2. Menor cantidad de mesas.
     * 3. Menor ID de mesa como desempate.
     */
    private function isBetterCombination(
        array $current,
        array $best,
        int $currentCapacity,
        int $people
    ): bool {

        $bestCapacity = array_sum(
            array_map(
                static function (
                    array $table
                ): int {
                    return (int) $table['capacity'];
                },
                $best
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Menor desperdicio.
        |--------------------------------------------------------------------------
        */

        $currentWaste =
            $currentCapacity - $people;

        $bestWaste =
            $bestCapacity - $people;

        if (
            $currentWaste !==
            $bestWaste
        ) {
            return $currentWaste <
                $bestWaste;
        }

        /*
        |--------------------------------------------------------------------------
        | Si hay empate, usar menos mesas.
        |--------------------------------------------------------------------------
        */

        if (
            count($current) !==
            count($best)
        ) {
            return count($current) <
                count($best);
        }

        /*
        |--------------------------------------------------------------------------
        | Último desempate: IDs.
        |--------------------------------------------------------------------------
        */

        $currentIds = array_map(
            static function (
                array $table
            ): int {
                return (int) $table['id'];
            },
            $current
        );

        $bestIds = array_map(
            static function (
                array $table
            ): int {
                return (int) $table['id'];
            },
            $best
        );

        sort($currentIds);
        sort($bestIds);

        return $currentIds < $bestIds;
    }

    /**
     * Obtiene la configuración de reservas de una sede.
     *
     * Este método es público porque otros componentes
     * del sistema pueden necesitar consultar la configuración.
     */
    public function getSettings(int $branchId): ?array
    {
        return $this->reservationRepository->getReservationSettings(
            $branchId
        );
    }

    public function getAvailableTables(
        int $branchId,
        string $date,
        string $startTime,
        string $endTime
    ): array {
        return $this->tableRepository->getAvailableTables(
            $branchId,
            $date,
            $startTime,
            $endTime
        );
    }

}