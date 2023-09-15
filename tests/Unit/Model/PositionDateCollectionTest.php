<?php

declare(strict_types=1);

namespace Groshy\Tests\Unit\Model;

use DateTime;
use Groshy\Domain\Calculation\Metric\CalculationLineCollection;
use Groshy\Domain\Calculation\Metric\CalculationLineEvent;
use Groshy\Model\PositionDateCollection;
use Groshy\Model\PositionDateSet;
use Groshy\Model\PositionDateValue;
use Groshy\Tests\Helper\ModelBuilder;
use Money\Money;
use PHPUnit\Framework\TestCase;

final class PositionDateCollectionTest extends TestCase
{
    use ModelBuilder;

    /**
     * @test
     */
    public function it_returns_position_date_iterator_for_one_record()
    {
        $positionDate = $this->buildPositionDate();
        $set = new PositionDateSet([$positionDate]);
        $collection = new PositionDateCollection([$set]);
        self::assertCount(1, iterator_to_array($collection->getPositionDates(), false));
    }

    /**
     * @test
     */
    public function it_returns_position_date_iterator_for_two_sets()
    {
        $positionDate1 = $this->buildPositionDate(date: new DateTime('-5 days'));
        $positionDate2 = $this->buildPositionDate(date: new DateTime('-2 days'));
        $set1 = new PositionDateSet([$positionDate1]);
        $set2 = new PositionDateSet([$positionDate2]);
        $collection = new PositionDateCollection([$set1, $set2]);
        self::assertCount(2, iterator_to_array($collection->getPositionDates(), false));
    }

    /**
     * @test
     */
    public function it_returns_position_date_iterator_for_one_set_with_two_positions()
    {
        $date1 = new DateTime('-1 day');
        $positionDate1 = $this->buildPositionDate(date: $date1);
        $positionDate2 = $this->buildPositionDate(date: $date1);
        $set = new PositionDateSet([$positionDate1, $positionDate2]);
        $collection = new PositionDateCollection([$set]);
        self::assertCount(2, iterator_to_array($collection->getPositionDates(), false));
    }

    /**
     * @test
     */
    public function it_generates_twr_calculation_lines_for_position_date_with_one_transaction()
    {
        $date1 = new DateTime('-1 day');
        $position1 = $this->buildPosition();
        $tr1 = $this->buildTransaction($position1, Money::USD(200)->multiply(-1), $date1);
        $positionDate1 = $this->buildPositionDate(position: $position1, date: $date1, transactions: [$tr1], value: new PositionDateValue(amount: Money::USD(200)));

        $set = new PositionDateSet([$positionDate1]);
        $collection = new PositionDateCollection([$set]);

        $expected = new CalculationLineCollection(
            [
                [$date1, Money::USD(0), Money::USD(200), CalculationLineEvent::START],
                [$date1, Money::USD(200), Money::USD(200), CalculationLineEvent::END],
            ]
        );
        self::assertTrue($expected->equals($collection->getLineCollection()));
    }

    /**
     * @test
     */
    public function it_generates_twr_calculation_lines_for_position_with_initial_contribution_and_growth()
    {
        $date1 = new DateTime('-5 days');
        $position1 = $this->buildPosition();

        $tr1 = $this->buildTransaction(position: $position1, amount: Money::USD(200)->multiply(-1), date: $date1);
        $positionDate1 = $this->buildPositionDate(position: $position1, date: $date1, transactions: [$tr1], value: new PositionDateValue(amount: Money::USD(200)));

        $date2 = new DateTime('-3 days');
        $positionDate2 = $this->buildPositionDate(position: $position1, date: $date2, value: new PositionDateValue(amount: Money::USD(300)));

        $set1 = new PositionDateSet([$positionDate1]);
        $set2 = new PositionDateSet([$positionDate2]);
        $collection = new PositionDateCollection([$set1, $set2]);

        $expected = new CalculationLineCollection(
            [
                [$date1, Money::USD(0), Money::USD(200), CalculationLineEvent::START],
                [$date2, Money::USD(300), Money::USD(300), CalculationLineEvent::END],
            ]
        );
        self::assertTrue($expected->equals($collection->getLineCollection()));
    }

    /**
     * @test
     */
    public function it_generates_twr_calculation_lines_for_position_with_initial_contribution_and_dividends_and_growth()
    {
        $date1 = new DateTime('-5 days');
        $position1 = $this->buildPosition();
        $tr1 = $this->buildTransaction(position: $position1, amount: Money::USD(200)->multiply(-1), date: $date1);
        $positionDate1 = $this->buildPositionDate(position: $position1, date: $date1, transactions: [$tr1], value: new PositionDateValue(amount: Money::USD(200)));

        $date2 = new DateTime('-3 days');
        $tr2 = $this->buildTransaction(position: $position1, amount: Money::USD(100), date: $date2);
        $positionDate2 = $this->buildPositionDate(position: $position1, date: $date2, transactions: [$tr2], value: new PositionDateValue(amount: Money::USD(300)));

        $set1 = new PositionDateSet([$positionDate1]);
        $set2 = new PositionDateSet([$positionDate2]);
        $collection = new PositionDateCollection([$set1, $set2]);

        $expected = new CalculationLineCollection(
            [
                [$date1, Money::USD(0), Money::USD(200), CalculationLineEvent::START],
                [$date2, Money::USD(400), Money::USD(300), CalculationLineEvent::MOVEMENT],
                [$date2, Money::USD(300), Money::USD(300), CalculationLineEvent::END],
            ]
        );
        self::assertTrue($expected->equals($collection->getLineCollection()));
    }

    /**
     * @test
     */
    public function it_generates_twr_calculation_lines_for_position_with_two_diividends_and_one_reinvest()
    {
        $date1 = new DateTime('-5 days');
        $position1 = $this->buildPosition();
        $tr1 = $this->buildTransaction(position: $position1, amount: Money::USD(24180264)->multiply(-1), date: $date1);
        $positionDate1 = $this->buildPositionDate(position: $position1, date: $date1, transactions: [$tr1], value: new PositionDateValue(amount: Money::USD(24180264)));

        $date2 = new DateTime('-4 days');
        $tr2 = $this->buildTransaction(position: $position1, amount: Money::USD(228497), date: $date2);
        $positionDate2 = $this->buildPositionDate(position: $position1, date: $date2, transactions: [$tr2], value: new PositionDateValue(amount: Money::USD(24180264)));

        $date3 = new DateTime('-3 days');
        $tr3 = $this->buildTransaction(position: $position1, amount: Money::USD(189188), date: $date3);
        $positionDate3 = $this->buildPositionDate(position: $position1, date: $date3, transactions: [$tr3], value: new PositionDateValue(amount: Money::USD(24180264)));

        $date4 = new DateTime('-2 days');
        $tr4 = $this->buildTransaction(position: $position1, amount: Money::USD(233029), date: $date4);
        $tr5 = $this->buildTransaction(position: $position1, amount: Money::USD(233029)->multiply(-1), date: $date4);
        $positionDate4 = $this->buildPositionDate(position: $position1, date: $date4, transactions: [$tr4, $tr5], value: new PositionDateValue(amount: Money::USD(25045376)));

        $set1 = new PositionDateSet([$positionDate1]);
        $set2 = new PositionDateSet([$positionDate2]);
        $set3 = new PositionDateSet([$positionDate3]);
        $set4 = new PositionDateSet([$positionDate4]);
        $collection = new PositionDateCollection([$set1, $set2, $set3, $set4]);

        $expected = new CalculationLineCollection(
            [
                [$date1, Money::USD(0), Money::USD(24180264), CalculationLineEvent::START],
                [$date2, Money::USD(24408761), Money::USD(24180264), CalculationLineEvent::MOVEMENT], // 1.009449731400782
                [$date3, Money::USD(24369452), Money::USD(24180264), CalculationLineEvent::MOVEMENT], // 1.00782406676784
                [$date4, Money::USD(25045376), Money::USD(25045376), CalculationLineEvent::END], // 1.035777607721735
            ]
        );
        self::assertTrue($expected->equals($collection->getLineCollection()));
    }

    /**
     * @test
     */
    public function it_generates_twr_calculation_lines_for_position_with_no_values()
    {
        $date1 = new DateTime('-5 days');
        $position1 = $this->buildPosition();
        $positionDate1 = $this->buildPositionDate(position: $position1, date: $date1, value: new PositionDateValue(amount: Money::USD(0)));

        $date2 = new DateTime('-3 days');
        $positionDate2 = $this->buildPositionDate(position: $position1, date: $date2, value: new PositionDateValue(amount: Money::USD(0)));

        $set1 = new PositionDateSet([$positionDate1]);
        $set2 = new PositionDateSet([$positionDate2]);
        $collection = new PositionDateCollection([$set1, $set2]);

        $expected = new CalculationLineCollection(
            [
                [$date1, Money::USD(0), Money::USD(0), CalculationLineEvent::START],
                [$date2, Money::USD(0), Money::USD(0), CalculationLineEvent::END],
            ]
        );
        self::assertTrue($expected->equals($collection->getLineCollection()));
    }
}
