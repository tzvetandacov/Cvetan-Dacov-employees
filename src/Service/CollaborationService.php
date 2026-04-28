<?php

declare(strict_types=1);

namespace App\Service;

final class CollaborationService
{
    private CollaborationCalculator $calculator;

    public function __construct(CollaborationCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    public function findLongestCollaboration(array $employeesById): ?array
    {
        $projectGroups = $this->groupDataByProjectAndEmployee($employeesById);
        $collaborations = [];

        foreach ($projectGroups as $employeesInProject) {
            $cleanedProjectPeriods = [];
            foreach ($employeesInProject as $periods) {
                $mergedPeriods = $this->mergeOverlappingPeriods($periods);
                foreach ($mergedPeriods as $mergedPeriod) {
                    $cleanedProjectPeriods[] = $mergedPeriod;
                }
            }

            $overlaps = $this->extractProjectOverlaps($cleanedProjectPeriods);
            foreach ($overlaps as $overlap) {
                $collaborations = $this->accumulateCollaboration($collaborations, $overlap);
            }
        }

        if (empty($collaborations)) {
            return null;
        }

        usort($collaborations, function ($first, $second) {
            return $second['pair']['total'] <=> $first['pair']['total'];
        });

        return array_values($collaborations)[0];
    }

    private function groupDataByProjectAndEmployee(array $employeesById): array
    {
        $groupedData = [];
        foreach ($employeesById as $employeePeriods) {
            foreach ($employeePeriods as $period) {
                $groupedData[$period->projectId][$period->employeeId][] = $period;
            }
        }
        return $groupedData;
    }

    private function mergeOverlappingPeriods(array $periods): array
    {
        if (count($periods) <= 1) {
            return $periods;
        }

        usort($periods, function ($first, $second) {
            return $first->startDate <=> $second->startDate;
        });

        $merged = [];
        $current = $periods[0];

        for ($i = 1; $i < count($periods); $i++) {
            $next = $periods[$i];

            if ($next->startDate <= $current->endDate) {
                if ($next->endDate > $current->endDate) {
                    $current->endDate = $next->endDate;
                }
            } else {
                $merged[] = $current;
                $current = $next;
            }
        }
        $merged[] = $current;

        return $merged;
    }

    private function extractProjectOverlaps(array $periods): array
    {
        $overlaps = [];
        $totalPeriods = count($periods);

        /**
         * Sort periods by start date to optimize the comparison process.
         * This allows to use an early exit.
         */
        usort($periods, function ($first, $second) {
            return $first->startDate <=> $second->startDate;
        });

        for ($i = 0; $i < $totalPeriods; $i++) {
            for ($j = $i + 1; $j < $totalPeriods; $j++) {
                $firstEmployee = $periods[$i];
                $secondEmployee = $periods[$j];

                /**
                 *  Since periods are sorted by start date, if the next period
                 *  starts after the current one has ended, no further overlaps are possible
                 *  for current outerIndex period.
                 */
                if ($secondEmployee->startDate > $firstEmployee->endDate) {
                    break;
                }

                if ($firstEmployee->employeeId === $secondEmployee->employeeId) {
                    continue;
                }

                $days = $this->calculator->calculateOverlap($firstEmployee, $secondEmployee);
                if ($days > 0) {
                    $overlaps[] = [
                        'firstEmployee' => $firstEmployee,
                        'secondEmployee' => $secondEmployee,
                        'days' => $days
                    ];
                }
            }
        }
        return $overlaps;
    }

    private function accumulateCollaboration(array $collaborations, array $overlap): array
    {
        $first = $overlap['firstEmployee'];
        $second = $overlap['secondEmployee'];

        $ids = [$first->employeeId, $second->employeeId];
        sort($ids);
        $pairKey = implode('_', $ids);

        if (!isset($collaborations[$pairKey])) {
            $collaborations[$pairKey] = [
                'pair' => [
                    'employeeId1' => $ids[0],
                    'employeeId2' => $ids[1],
                    'total' => 0
                ],
                'projects' => []
            ];
        }

        $collaborations[$pairKey]['pair']['total'] += $overlap['days'];
        $collaborations[$pairKey]['projects'][] = [
            'employeeId1' => $ids[0],
            'employeeId2' => $ids[1],
            'projectId' => $first->projectId,
            'days' => $overlap['days']
        ];

        return $collaborations;
    }
}
