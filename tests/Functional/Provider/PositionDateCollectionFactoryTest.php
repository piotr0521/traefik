<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Provider;

use DateTime;
use Groshy\Domain\Enum\PositionEventType;
use Groshy\Message\Dto\PositionEvent\CreateTransactionDto;
use Groshy\Model\PositionDate;
use Groshy\Provider\PositionDateCollectionFactory;
use Groshy\Tests\Helper\DataBuilder;
use Groshy\Tests\Helper\UsersAwareTrait;
use Money\Money;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PositionDateCollectionFactoryTest extends KernelTestCase
{
    use UsersAwareTrait;
    use DataBuilder;

    private PositionDateCollectionFactory $factory;

    protected function setUp(): void
    {
        $this->markTestSkipped('Deprecated class');
        $this->setUpUsers(static::getContainer());
        $this->factory = static::getContainer()->get(PositionDateCollectionFactory::class);
    }

    /**
     * @test
     */
    public function it_creates_list_with_initial_zero_value(): void
    {
        $position = $this->createCashPosition($this->getUser('user0'));
        $this->createPositionEvent(position: $position, date: new DateTime('now'), valueAmount: Money::USD(1000), type: PositionEventType::VALUE_UPDATE);

        $collection = $this->factory->build([$position], new DateTime('now - 5 days'), new DateTime('now'));

        $dateIterator = $collection->getPositionDates();
        self::assertCount(2, $dateIterator);
        /** @var array<PositionDate> $dates */
        $dates = iterator_to_array($dateIterator, false);
        self::assertEquals((new DateTime('now - 5 days'))->format('Y-m-d'), $dates[0]->getDate()->format('Y-m-d'));
        self::assertEquals('0', $dates[0]->getValue()->amount->getAmount());
        self::assertEquals((new DateTime('now'))->format('Y-m-d'), $dates[1]->getDate()->format('Y-m-d'));
        self::assertEquals('1000', $dates[1]->getValue()->amount->getAmount());
    }

    /**
     * @test
     */
    public function it_creates_list_with_all_points_including_zero_and_last(): void
    {
        $position = $this->createCashPosition($this->getUser('user0'));
        $this->createPositionEvent(position: $position, date: new DateTime('now - 3 days'), valueAmount: Money::USD(1000), type: PositionEventType::VALUE_UPDATE);
        $this->createPositionEvent(position: $position, date: new DateTime('now - 2 days'), valueAmount: Money::USD(2000), type: PositionEventType::VALUE_UPDATE);
        $this->createPositionEvent(position: $position, date: new DateTime('now - 1 day'), valueAmount: Money::USD(3000), type: PositionEventType::VALUE_UPDATE);

        $collection = $this->factory->build([$position], new DateTime('now - 5 days'), new DateTime('now'));

        $dates = iterator_to_array($collection->getPositionDates(), false);
        self::assertCount(5, $dates);
        self::assertEquals((new DateTime('now - 5 days'))->format('Y-m-d'), $dates[0]->getDate()->format('Y-m-d'));
        self::assertEquals('0', $dates[0]->getValue()->amount->getAmount());
        self::assertEquals((new DateTime('now - 3 days'))->format('Y-m-d'), $dates[1]->getDate()->format('Y-m-d'));
        self::assertEquals('1000', $dates[1]->getValue()->amount->getAmount());
        self::assertEquals('0', $dates[1]->getPreviousValue()->amount->getAmount());
        self::assertEquals((new DateTime('now - 2 days'))->format('Y-m-d'), $dates[2]->getDate()->format('Y-m-d'));
        self::assertEquals('2000', $dates[2]->getValue()->amount->getAmount());
        self::assertEquals((new DateTime('now - 1 day'))->format('Y-m-d'), $dates[3]->getDate()->format('Y-m-d'));
        self::assertEquals('3000', $dates[3]->getValue()->amount->getAmount());
        self::assertEquals('2000', $dates[3]->getPreviousValue()->amount->getAmount());
        self::assertEquals((new DateTime('now'))->format('Y-m-d'), $dates[4]->getDate()->format('Y-m-d'));
        self::assertEquals('3000', $dates[4]->getValue()->amount->getAmount());
        self::assertEquals('3000', $dates[4]->getPreviousValue()->amount->getAmount());
    }

    /**
     * @test
     */
    public function it_creates_collection_for_two_positions_without_date_correlation(): void
    {
        $position1 = $this->createCashPosition($this->getUser('user0'));
        $this->createPositionEvent(position: $position1, date: new DateTime('-1 month'), valueAmount: Money::USD(1000), type: PositionEventType::VALUE_UPDATE);
        $position2 = $this->createCashPosition($this->getUser('user0'));
        $this->createPositionEvent(position: $position2, date: new DateTime('-2 months'), valueAmount: Money::USD(1000), type: PositionEventType::VALUE_UPDATE);

        $collection = $this->factory->build([$position1, $position2], new DateTime('now - 3 months'), new DateTime('now'));

        self::assertCount(8, $collection->getPositionDates());
    }

    /**
     * @test
     */
    public function it_creates_collection_when_event_does_not_have_current_value(): void
    {
        $position = $this->createInvestmentPosition(user: $this->getUser('user0'), typeName: 'Hard Money Loan Fund');
        $this->createPositionEvent(
            position: $position,
            date: new DateTime('April 1 2022'),
            valueAmount: Money::USD(10981100),
            type: PositionEventType::VALUE_UPDATE
        );
        $this->createPositionEvent(
            position: $position,
            date: new DateTime('May 1 2022'),
            type: PositionEventType::DISTRIBUTION,
            transactions: [
                CreateTransactionDto::factory(amount: Money::USD(83200)),
            ]
        );
        $this->createPositionEvent(
            position: $position,
            date: new DateTime('June 1 2022'),
            valueAmount: Money::USD(11012000),
            type: PositionEventType::DISTRIBUTION,
            transactions: [
                CreateTransactionDto::factory(amount: Money::USD(83200)),
                CreateTransactionDto::factory(amount: Money::USD(-83200)),
            ]
        );
        $this->createPositionEvent(
            position: $position,
            date: new DateTime('July 1 2022'),
            valueAmount: Money::USD(11012000),
            type: PositionEventType::DISTRIBUTION,
            transactions: [
                CreateTransactionDto::factory(amount: Money::USD(81900)),
                CreateTransactionDto::factory(amount: Money::USD(-81900)),
            ]
        );

        $collection = $this->factory->build([$position], new DateTime('now - 12 months'), new DateTime('now'));
        $positionDates = iterator_to_array($collection->getPositionDates(), false);
        self::assertCount(6, $positionDates);
        self::assertNotNull($positionDates[0]->getValue());
        self::assertTrue(Money::USD(0)->equals($positionDates[0]->getValue()->getAmount()));
        self::assertNotNull($positionDates[1]->getValue());
        self::assertTrue(Money::USD(10981100)->equals($positionDates[1]->getValue()->getAmount()));
        self::assertNotNull($positionDates[2]->getValue());
        self::assertTrue(Money::USD(10981100)->equals($positionDates[2]->getValue()->getAmount()));

        self::assertNotNull($positionDates[3]->getValue());
        self::assertTrue(Money::USD(11012000)->equals($positionDates[3]->getValue()->getAmount()));

        self::assertNotNull($positionDates[4]->getValue());
        self::assertTrue(Money::USD(11012000)->equals($positionDates[4]->getValue()->getAmount()));

        self::assertNotNull($positionDates[5]->getValue());
        self::assertTrue(Money::USD(11012000)->equals($positionDates[5]->getValue()->getAmount()));
    }

    /**
     * @test
     */
    public function it_creates_position_dates_for_dates_when_another_position_changes(): void
    {
        $position1 = $this->createInvestmentPosition(user: $this->getUser('user0'), typeName: 'Hard Money Loan Fund');
        $position2 = $this->createInvestmentPosition(user: $this->getUser('user0'), typeName: 'Hard Money Loan Fund');
        $this->createPositionEvent(
            position: $position1,
            date: new DateTime('April 1 2022'),
            valueAmount: Money::USD(1000),
            type: PositionEventType::VALUE_UPDATE,
        );
        $this->createPositionEvent(
            position: $position2,
            date: new DateTime('April 1 2022'),
            valueAmount: Money::USD(2000),
            type: PositionEventType::VALUE_UPDATE,
        );
        $this->createPositionEvent(
            position: $position1,
            date: new DateTime('May 1 2022'),
            type: PositionEventType::DISTRIBUTION,
            transactions: [
                CreateTransactionDto::factory(amount: Money::USD(100)),
            ]
        );
        $this->createPositionEvent(
            position: $position2,
            date: new DateTime('June 1 2022'),
            type: PositionEventType::DISTRIBUTION,
            transactions: [
                CreateTransactionDto::factory(amount: Money::USD(200)),
            ]
        );

        $collection = $this->factory->build([$position1, $position2], new DateTime('now - 12 months'), new DateTime('now'));
        self::assertCount(10, iterator_to_array($collection->getPositionDates(), false));
    }
}
