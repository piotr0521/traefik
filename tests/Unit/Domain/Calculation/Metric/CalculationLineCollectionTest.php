<?php

declare(strict_types=1);

namespace Groshy\Tests\Unit\Domain\Calculation\Metric;

use DateTime;
use Groshy\Domain\Calculation\Metric\CalculationLineCollection;
use Groshy\Domain\Calculation\Metric\CalculationLineEvent;
use Money\Money;
use PHPUnit\Framework\TestCase;

final class CalculationLineCollectionTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_collection_of_calculation_lines_added_individually()
    {
        $collection = new CalculationLineCollection();
        $collection->add(new DateTime(), Money::USD(0), Money::USD(10), CalculationLineEvent::START);
        $collection->add(new DateTime(), Money::USD(20), Money::USD(20), CalculationLineEvent::END);
        $data = $collection->toArray();
        self::assertCount(2, $data);
        self::assertTrue($data[0]->getAfter()->equals(Money::USD(10)));
        self::assertTrue($data[1]->getAfter()->equals(Money::USD(20)));
    }

    /**
     * @test
     */
    public function it_creates_collection_of_calculation_lines_added_from_array()
    {
        $collection = new CalculationLineCollection();
        $collection->addArray([
            [new DateTime(), Money::USD(0), Money::USD(10), CalculationLineEvent::START],
            [new DateTime(), Money::USD(20), Money::USD(20), CalculationLineEvent::END],
        ]);
        $data = $collection->toArray();
        self::assertCount(2, $data);
        self::assertTrue($data[0]->getAfter()->equals(Money::USD(10)));
        self::assertTrue($data[1]->getAfter()->equals(Money::USD(20)));
    }
}
