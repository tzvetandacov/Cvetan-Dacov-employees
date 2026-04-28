<?php

declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;

final readonly class EmployeePeriod
{
    public function __construct(
        public int $employeeId,
        public int $projectId,
        public DateTimeImmutable $startDate,
        public DateTimeImmutable $endDate
    ) {
    }
}
