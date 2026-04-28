<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\EmployeePeriod;
use Exception;

final class CsvProcessor
{
    private DateFacade $dateFacade;
    private array $errors = [];

    public function __construct(DateFacade $dateFacade)
    {
        $this->dateFacade = $dateFacade;
    }

    public function processFile(string $filePath, string $dateFormat = 'EU'): array
    {
        $employeeData = [];
        $fileHandle = fopen($filePath, "r");

        if (!$fileHandle) {
            return [];
        }

        $lineCounter = 0;

        while (($row = fgetcsv($fileHandle, 1000, ",")) !== false) {
            $lineCounter++;
            $row = array_map('trim', $row);

            if (count($row) === 1 && $row[0] === '') {
                continue;
            }

            try {
                if (count($row) < 3) {
                    throw new Exception("Insufficient columns");
                }

                $employeeId = (int)$row[0];
                $projectId = (int)$row[1];

                $startDate = $this->dateFacade->parseDate($row[2], false, $dateFormat);
                $endDate = $this->dateFacade->parseDate($row[3] ?? null, true, $dateFormat);

                if ($startDate > $endDate) {
                    throw new Exception("Logical error: Start date cannot be later than the end date.");
                }

                $employeeData[$employeeId][] = new EmployeePeriod($employeeId, $projectId, $startDate, $endDate);
            } catch (Exception $e) {
                $this->errors[] = "Line $lineCounter: " . $e->getMessage();
            }
        }

        fclose($fileHandle);
        return $employeeData;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
