<?php

declare(strict_types=1);

namespace Groshy\Tests\Unit\Domain\Calculation\Metric;

use DateTime;
use Groshy\Domain\Calculation\Metric\CalculationLine;
use Groshy\Domain\Calculation\Metric\CalculationLineEvent;
use Money\Money;
use PHPUnit\Framework\TestCase;

final class CalculationLineTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_new_start_calculation_line()
    {
        new CalculationLine(new DateTime(), Money::USD(0), Money::USD(10), CalculationLineEvent::START);
        self::addToAssertionCount(2);
    }

    /**
     * @test
     */
    public function it_creates_new_end_calculation_line()
    {
        $line1 = new CalculationLine(new DateTime(), Money::USD(0), Money::USD(10), CalculationLineEvent::START);
        new CalculationLine(new DateTime(), Money::USD(10), Money::USD(10), CalculationLineEvent::END, $line1);
        self::addToAssertionCount(1);
    }

    /**
     * @test
     */
    public function it_calculates_rate_of_return()
    {
        $line1 = new CalculationLine(new DateTime(), Money::USD(0), Money::USD(10), CalculationLineEvent::START);
        $line2 = new CalculationLine(new DateTime(), Money::USD(20), Money::USD(20), CalculationLineEvent::END, $line1);
        self::assertNull($line1->getRateOfReturn());
        self::assertEquals('2.00000000000000', $line2->getRateOfReturn());
    }
}
