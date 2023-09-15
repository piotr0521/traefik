<?php

declare(strict_types=1);

namespace Groshy\Tests\Unit\Domain\Calculation\Metric;

use DateTime;
use Groshy\Domain\Calculation\Metric\CalculationLineCollection;
use Groshy\Domain\Calculation\Metric\CalculationLineEvent;
use Groshy\Domain\Calculation\Metric\XirrCalculator;
use Money\Money;
use PHPUnit\Framework\TestCase;

final class XirrCalculatorTest extends TestCase
{
    /**
     * @test
     * @dataProvider provide
     */
    public function it_calculates_annualized_time_weighted_return(array $lines, string $expected)
    {
        $collection = new CalculationLineCollection();
        $collection->addArray($lines);
        $calculator = new XirrCalculator($collection->toArray());
        self::assertEquals($expected, $calculator->result());
    }

    public function provide(): array
    {
        return [
            [
                [
                    [new DateTime('- 365 days'), Money::USD(0), Money::USD(200), CalculationLineEvent::START],
                    [new DateTime(), Money::USD(300), Money::USD(300), CalculationLineEvent::END],
                ],
                '0.5',
            ],
            [
                [
                    [new DateTime('- 366 days'), Money::USD(0), Money::USD(200), CalculationLineEvent::START],
                    [new DateTime(), Money::USD(300), Money::USD(300), CalculationLineEvent::END],
                ],
                '0.49833917787628',
            ],
            [
                [
                    [new DateTime('30 January 2017'), Money::USD(0), Money::USD(140000), CalculationLineEvent::START],
                    [new DateTime('28 February 2017'), Money::USD(140000), Money::USD(140000 - 2457), CalculationLineEvent::MOVEMENT],
                    [new DateTime('11 October 2017'), Money::USD(140000 - 2457), Money::USD(140000 - 2457 - 51000), CalculationLineEvent::MOVEMENT],
                    [new DateTime('10 January 2018'), Money::USD(140000 - 2457 - 51000), Money::USD(140000 - 2457 - 51000 - 40000), CalculationLineEvent::MOVEMENT],
                    [new DateTime('10 February 2019'), Money::USD(64000), Money::USD(0), CalculationLineEvent::MOVEMENT],
                    [new DateTime('10 February 2019'), Money::USD(0), Money::USD(0), CalculationLineEvent::END],
                ],
                '0.096622731681047',
            ],
            [
                [
                    [new DateTime('12 December 2022'), Money::USD(0), Money::USD(20375755), CalculationLineEvent::START],
                    [new DateTime('23 December 2022'), Money::USD(20510512), Money::USD(20375755), CalculationLineEvent::MOVEMENT],
                    [new DateTime('12 January 2023'), Money::USD(20375755), Money::USD(20375755), CalculationLineEvent::END],
                ],
                '0.081062631929422',
            ],
        ];
    }
}
