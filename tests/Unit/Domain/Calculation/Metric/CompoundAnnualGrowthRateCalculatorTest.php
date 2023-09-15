<?php

declare(strict_types=1);

namespace Groshy\Tests\Unit\Domain\Calculation\Metric;

use DateTime;
use Groshy\Domain\Calculation\Metric\CalculationLineCollection;
use Groshy\Domain\Calculation\Metric\CalculationLineEvent;
use Groshy\Domain\Calculation\Metric\CompoundAnnualGrowthRateCalculator;
use Groshy\Domain\Calculation\Metric\TimeWeightedReturnCalculator;
use Groshy\Model\DateRange;
use Money\Money;
use PHPUnit\Framework\TestCase;

final class CompoundAnnualGrowthRateCalculatorTest extends TestCase
{
    /**
     * @test
     * @dataProvider provide
     */
    public function it_calculates_annualized_time_weighted_return(array $lines, DateRange $range, string $expected)
    {
        $collection = new CalculationLineCollection();
        $collection->addArray($lines);
        $calculator = new CompoundAnnualGrowthRateCalculator(new TimeWeightedReturnCalculator($collection->toArray()), $range);
        self::assertEquals($expected, $calculator->result());
    }

    public function provide(): array
    {
        return [
            [
                [
                    [new DateTime(), Money::USD(0), Money::USD(200), CalculationLineEvent::START],
                    [new DateTime(), Money::USD(200), Money::USD(200), CalculationLineEvent::END],
                ],
                new DateRange(new DateTime('- 365 days'), new DateTime('now')),
                '0.00000000000000',
            ],
            [
                [
                    [new DateTime(), Money::USD(0), Money::USD(200), CalculationLineEvent::START],
                    [new DateTime(), Money::USD(300), Money::USD(300), CalculationLineEvent::END],
                ],
                new DateRange(new DateTime('- 2 years'), new DateTime('now')),
                '0.22474487139159',
            ],
        ];
    }
}
