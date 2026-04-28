<?php

declare(strict_types=1);

namespace App\Service;

use Carbon\Carbon;
use DateTimeImmutable;
use Exception;
use Throwable;

final class DateFacade
{
    public function parseDate(?string $dateValue, bool $isEndDate = false, string $format = 'EU'): DateTimeImmutable
    {
        $input = (string)$dateValue;
        $cleanedValue = trim($input, " \t\n\r\0\x0B\"'");

        if ($cleanedValue === '' || strtolower($cleanedValue) === 'null') {
            if ($isEndDate) {
                return (new DateTimeImmutable('today'));
            }
            throw new Exception("Start date is mandatory and cannot be empty.");
        }

        $normalizedValue = str_replace(['/', '.'], '-', $cleanedValue);
        $dateFormat = ($format === 'US') ? 'm-d-Y' : 'd-m-Y';

        if (class_exists(Carbon::class)) {
            try {
                return Carbon::createFromFormat($dateFormat, $normalizedValue)->startOfDay()->toDateTimeImmutable();
            } catch (Throwable) {
                try {
                    return Carbon::parse($normalizedValue)->startOfDay()->toDateTimeImmutable();
                } catch (Throwable) {
                }
            }
        }

        try {
            $parsedDate = DateTimeImmutable::createFromFormat($dateFormat, $normalizedValue);

            if (!$parsedDate) {
                $parsedDate = new DateTimeImmutable($normalizedValue);
            }

            return $parsedDate->modify('today');
        } catch (Throwable) {
            throw new Exception("Invalid date format: " . $cleanedValue);
        }
    }

    public function isCarbonAvailable(): bool
    {
        return class_exists(Carbon::class);
    }
}
