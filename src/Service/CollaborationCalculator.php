<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\EmployeePeriod;

final class CollaborationCalculator
{
    public function calculateOverlap(EmployeePeriod $firstPeriod, EmployeePeriod $secondPeriod): int
    {
        if ($firstPeriod->projectId !== $secondPeriod->projectId) {
            return 0;
        }

        $start = max($firstPeriod->startDate, $secondPeriod->startDate);
        $end = min($firstPeriod->endDate, $secondPeriod->endDate);

        if ($start > $end) {
            return 0;
        }

        return $start->diff($end)->days + 1;
    }
}
