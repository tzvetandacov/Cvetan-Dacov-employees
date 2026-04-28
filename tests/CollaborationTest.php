<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Service\CollaborationCalculator;
use App\Model\EmployeePeriod;
use DateTimeImmutable;

class CollaborationTest extends TestCase
{
    private CollaborationCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new CollaborationCalculator();
    }

    public function testOverlapCalculationReturnsCorrectDays(): void
    {
        $periodOne = new EmployeePeriod(
            1, 12,
            new DateTimeImmutable('2023-01-01'),
            new DateTimeImmutable('2023-01-10')
        );

        $periodTwo = new EmployeePeriod(
            2, 12,
            new DateTimeImmutable('2023-01-05'),
            new DateTimeImmutable('2023-01-15')
        );

        $this->assertEquals(6, $this->calculator->calculateOverlap($periodOne, $periodTwo));
    }

    public function testOverlapWithOpenEndDate(): void
    {
        $today = (new DateTimeImmutable('today'))->modify('00:00:00');

        $periodOne = new EmployeePeriod(
            1, 1,
            $today->modify('-10 days'),
            $today
        );

        $periodTwo = new EmployeePeriod(
            2, 1,
            $today->modify('-5 days'),
            $today
        );

        $this->assertEquals(6, $this->calculator->calculateOverlap($periodOne, $periodTwo));
    }

    public function testLeapYearAccuracy(): void
    {
        $periodOne = new EmployeePeriod(
            1, 99,
            new DateTimeImmutable('2024-02-01'),
            new DateTimeImmutable('2024-03-01')
        );

        $periodTwo = new EmployeePeriod(
            2, 99,
            new DateTimeImmutable('2024-02-01'),
            new DateTimeImmutable('2024-03-01')
        );

        $this->assertEquals(30, $this->calculator->calculateOverlap($periodOne, $periodTwo));
    }

    public function testNoOverlapReturnsZero(): void
    {
        $periodOne = new EmployeePeriod(
            1, 10,
            new DateTimeImmutable('2023-01-01'),
            new DateTimeImmutable('2023-01-05')
        );

        $periodTwo = new EmployeePeriod(
            2, 10,
            new DateTimeImmutable('2023-01-06'),
            new DateTimeImmutable('2023-01-10')
        );

        $this->assertEquals(0, $this->calculator->calculateOverlap($periodOne, $periodTwo));
    }
}
