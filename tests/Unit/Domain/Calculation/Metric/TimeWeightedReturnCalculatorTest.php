<?php

declare(strict_types=1);

namespace Groshy\Tests\Unit\Domain\Calculation\Metric;

use DateTime;
use Groshy\Domain\Calculation\Metric\CalculationLineCollection;
use Groshy\Domain\Calculation\Metric\CalculationLineEvent;
use Groshy\Domain\Calculation\Metric\TimeWeightedReturnCalculator;
use Money\Money;
use PHPUnit\Framework\TestCase;

final class TimeWeightedReturnCalculatorTest extends TestCase
{
    /**
     * @test
     * @dataProvider provide
     */
    public function it_calculates_time_weighted_return(array $lines, string $expected)
    {
        $collection = new CalculationLineCollection();
        $collection->addArray($lines);
        $calculator = new TimeWeightedReturnCalculator($collection->toArray());
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
                '0.00000000000000',
            ],
            [
                [
                    [new DateTime(), Money::USD(0), Money::USD(200), CalculationLineEvent::START],
                    [new DateTime(), Money::USD(300), Money::USD(300), CalculationLineEvent::END],
                ],
                '0.50000000000000',
            ],
            [
                [
                    [new DateTime(), Money::USD(0), Money::USD(200), CalculationLineEvent::START],
                    [new DateTime(), Money::USD(100), Money::USD(100), CalculationLineEvent::END],
                ],
                '-0.50000000000000',
            ],
            // example from https://www.fool.com/about/how-to-calculate-investment-returns/
            [
                [
                    [new DateTime('-5 months'), Money::USD(0), Money::USD(20300), CalculationLineEvent::START],
                    [new DateTime('-4 months'), Money::USD(21773), Money::USD(22273), CalculationLineEvent::MOVEMENT],
                    [new DateTime('-3 months'), Money::USD(23937), Money::USD(24437), CalculationLineEvent::MOVEMENT],
                    [new DateTime('-2 months'), Money::USD(22823), Money::USD(22573), CalculationLineEvent::MOVEMENT],
                    [new DateTime('-1 months'), Money::USD(24518), Money::USD(25018), CalculationLineEvent::MOVEMENT],
                    [new DateTime(), Money::USD(25992), Money::USD(25992), CalculationLineEvent::END],
                ],
                '0.21484521684790',
            ],
            // example from https://static.twentyoverten.com/59b00a0441a46f312d08c93d/dxSbxcj20w-/TWRR-Overview.pdf
            [
                [
                    [new DateTime('-5 months'), Money::USD(0), Money::USD(200000), CalculationLineEvent::START],
                    [new DateTime('-4 months'), Money::USD(205000), Money::USD(255000), CalculationLineEvent::MOVEMENT],
                    [new DateTime(), Money::USD(274150), Money::USD(274150), CalculationLineEvent::END],
                ],
                '0.10197549019607',
            ],
            // example from https://static.twentyoverten.com/59b00a0441a46f312d08c93d/dxSbxcj20w-/TWRR-Overview.pdf
            [
                [
                    [new DateTime('-5 months'), Money::USD(0), Money::USD(200000), CalculationLineEvent::START],
                    [new DateTime('-4 months'), Money::USD(190000), Money::USD(290000), CalculationLineEvent::MOVEMENT],
                    [new DateTime(), Money::USD(301600), Money::USD(301600), CalculationLineEvent::END],
                ],
                '-0.01200000000000',
            ],
        ];
    }
}
