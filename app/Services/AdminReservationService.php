<?php

class AdminReservationService
{
    private AdminReservationRepository $repository;

    public function __construct()
    {
        $this->repository = new AdminReservationRepository();
    }

    public function getReservations(
        ?int $branchId = null,
        string $from = '',
        string $to = '',
        string $status = '',
        string $search = ''
    ): array {
        $allowedStatuses = [
            '',
            'pending',
            'confirmed',
            'checked_in',
            'completed',
            'cancelled',
            'no_show'
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            throw new InvalidArgumentException(
                'Estado de reserva no válido.'
            );
        }

        if ($from !== '' && !$this->isValidDate($from)) {
            throw new InvalidArgumentException(
                'La fecha inicial no es válida.'
            );
        }

        if ($to !== '' && !$this->isValidDate($to)) {
            throw new InvalidArgumentException(
                'La fecha final no es válida.'
            );
        }

        if ($from !== '' && $to !== '' && $from > $to) {
            throw new InvalidArgumentException(
                'La fecha inicial no puede ser mayor que la fecha final.'
            );
        }

        return $this->repository->getReservations(
            $branchId,
            $from,
            $to,
            $status,
            trim($search)
        );
    }

    public function getStats(?int $branchId = null): array
    {
        return $this->repository->getStats($branchId);
    }

    private function isValidDate(string $date): bool
    {
        $parsed = DateTime::createFromFormat('Y-m-d', $date);

        return $parsed !== false
            && $parsed->format('Y-m-d') === $date;
    }
}
