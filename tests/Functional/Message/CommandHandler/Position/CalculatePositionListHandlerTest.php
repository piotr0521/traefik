<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Message\CommandHandler\Position;

use DateTime;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Domain\Enum\PositionEventType;
use Groshy\Entity\Position;
use Groshy\Message\Dto\PositionEvent\CreateTransactionDto;
use Groshy\Tests\Helper\DataBuilder;
use Groshy\Tests\Helper\UsersAwareTrait;
use Money\Money;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Talav\Component\Resource\Repository\RepositoryInterface;

class CalculatePositionListHandlerTest extends KernelTestCase
{
    use UsersAwareTrait;
    use DataBuilder;

    private ?Generator $faker;

    private ?RepositoryInterface $positionRepository;

    protected function setUp(): void
    {
        $this->faker = FakerFactory::create();

        $this->positionRepository = static::getContainer()->get('app.repository.position');
        $this->setUpUsers(static::getContainer());
    }

    /**
     * @test
     */
    public function it_updates_start_date_and_last_value_for_one_position_based_on_the_value_update(): void
    {
        $date = new DateTime('-1 year');
        $amount = Money::USD(100);
        $position = $this->createInvestmentPosition(user: $this->getUser('user2'));
        $this->createPositionEvent(position: $position, date: $date, valueAmount: $amount, type: PositionEventType::VALUE_UPDATE);

        /** @var Position $position */
        $position = $this->positionRepository->find($position->getId());
        self::assertEquals($date->format('Y-m-d'), $position->getStartDate()->format('Y-m-d'));
        self::assertNotNull($position->getLastValue());
        self::assertNotNull($position->getLastValue()->getAmount()->equals($amount));

        $date = new DateTime('-2 year');
        $amount = Money::USD(200);
        $this->createPositionEvent(position: $position, date: $date, valueAmount: $amount, type: PositionEventType::VALUE_UPDATE);
        $position = $this->positionRepository->find($position->getId());
        self::assertEquals($date->format('Y-m-d'), $position->getStartDate()->format('Y-m-d'));
        self::assertNotNull($position->getLastValue());
        self::assertNotNull($position->getLastValue()->getAmount()->equals($amount));
    }

    /**
     * @test
     */
    public function it_updates_complete_date_for_one_position(): void
    {
        $position = $this->createInvestmentPosition(user: $this->getUser('user2'));
        $positionEvent = $this->createPositionEvent(
            position: $position,
            type: PositionEventType::COMPLETE,
            transactions: [
                CreateTransactionDto::factory(Money::USD(100)),
            ]
        );

        /** @var Position $position */
        $position = $this->positionRepository->find($position->getId());
        self::assertEquals($positionEvent->getDate()->format('Y-m-d'), $position->getCompleteDate()->format('Y-m-d'));

        $this->deletePositionEvent($positionEvent);
        /** @var Position $position */
        $position = $this->positionRepository->find($position->getId());
        self::assertNull($position->getCompleteDate());
    }

    /**
     * @test
     */
    public function it_updates_contributions_based_on_transactions(): void
    {
        $tr1Amount = Money::USD(-100);
        $position = $this->createInvestmentPosition(user: $this->getUser('user2'));
        $this->createPositionEvent(
            position: $position,
            date: new DateTime('-12 months'),
            valueAmount: Money::USD(100),
            type: PositionEventType::VALUE_UPDATE,
            transactions: [CreateTransactionDto::factory($tr1Amount)]
        );

        /** @var Position $position */
        $position = $this->positionRepository->find($position->getId());
        self::assertNotNull($position->getContributions());
        self::assertTrue($position->getContributions()->equals($tr1Amount->multiply(-1)));

        $tr2Amount = Money::USD(-10);
        $this->createPositionEvent(
            position: $position,
            date: new DateTime('-11 months'),
            valueAmount: Money::USD(110),
            type: PositionEventType::CONTRIBUTION,
            transactions: [CreateTransactionDto::factory($tr2Amount)]
        );

        /** @var Position $position */
        $position = $this->positionRepository->find($position->getId());
        self::assertTrue($position->getContributions()->equals(Money::USD(110)));
    }

    /**
     * @test
     */
    public function it_updates_distributions_based_on_transactions(): void
    {
        $tr1Amount = Money::USD(-100);
        $position = $this->createInvestmentPosition(user: $this->getUser('user2'));
        $this->createPositionEvent(
            position: $position,
            date: new DateTime('-12 months'),
            valueAmount: Money::USD(100),
            type: PositionEventType::VALUE_UPDATE,
            transactions: [CreateTransactionDto::factory($tr1Amount)]
        );

        /** @var Position $position */
        $position = $this->positionRepository->find($position->getId());
        self::assertNotNull($position->getDistributions());
        self::assertTrue($position->getDistributions()->isZero());

        $tr2Amount = Money::USD(10);
        $this->createPositionEvent(
            position: $position,
            date: new DateTime('-11 months'),
            type: PositionEventType::DISTRIBUTION,
            transactions: [CreateTransactionDto::factory($tr2Amount)]
        );

        /** @var Position $position */
        $position = $this->positionRepository->find($position->getId());
        self::assertTrue($position->getDistributions()->equals(Money::USD(10)));
    }

    /**
     * @test
     */
    public function it_updates_irr_and_multiplier(): void
    {
        $tr1Amount = Money::USD(-100);
        $position = $this->createInvestmentPosition(user: $this->getUser('user2'));
        $this->createPositionEvent(
            position: $position,
            date: new DateTime('-365 days'),
            valueAmount: Money::USD(100),
            type: PositionEventType::VALUE_UPDATE,
            transactions: [CreateTransactionDto::factory($tr1Amount)]
        );
        $this->createPositionEvent(
            position: $position,
            date: new DateTime(),
            valueAmount: Money::USD(150),
            type: PositionEventType::VALUE_UPDATE,
        );

        /** @var Position $position */
        $position = $this->positionRepository->find($position->getId());
        self::assertEquals('0.5', $position->getIrr());
        self::assertEquals('1.50000000000000', $position->getMultiplier());
    }
}
