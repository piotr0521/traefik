<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Message\CommandHandler\PositionEvent;

use DateTime;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Domain\Enum\PositionEventType;
use Groshy\Entity\PositionEvent;
use Groshy\Message\Dto\PositionEvent\CreateTransactionDto;
use Groshy\Tests\Helper\DataBuilder;
use Groshy\Tests\Helper\UsersAwareTrait;
use Money\Money;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Talav\Component\Resource\Repository\RepositoryInterface;

class CreatePositionEventHandlerTest extends KernelTestCase
{
    use UsersAwareTrait;
    use DataBuilder;

    private ?Generator $faker;

    private ?RepositoryInterface $positionEventRepository;

    protected function setUp(): void
    {
        $this->faker = FakerFactory::create();

        $this->positionEventRepository = static::getContainer()->get('app.repository.position_event');
        $this->setUpUsers(static::getContainer());
    }

    /**
     * @test
     */
    public function it_creates_position_event_with_value_and_two_transactions(): void
    {
        $date = new DateTime();
        $amountValue = Money::USD(200);
        $transactionAmount = Money::USD(100);
        $notes = $this->faker->text(40);
        $position = $this->createInvestmentPosition(user: $this->getUser('user2'));
        $this->createPositionEvent(
            position: $position,
            date: $date,
            valueAmount: $amountValue,
            type: PositionEventType::VALUE_UPDATE,
            notes: $notes,
            transactions: [
                CreateTransactionDto::factory($transactionAmount),
                CreateTransactionDto::factory($transactionAmount->multiply(-1)),
            ]
        );
        /** @var PositionEvent $positionEvent */
        $positionEvents = $this->positionEventRepository->findBy(['position' => $position]);
        self::assertCount(1, $positionEvents);
        $positionEvent = $positionEvents[0];
        self::assertEquals($date->format('Y-m-d'), $positionEvent->getDate()->format('Y-m-d'));
        self::assertEquals($notes, $positionEvent->getNotes());
        self::assertNotNull($positionEvent->getValue());
        self::assertTrue($positionEvent->getValue()->getAmount()->equals($amountValue));
        self::assertCount(2, $positionEvent->getTransactions());
        self::assertCount(1, $positionEvent->getPositiveTransactions());
        self::assertCount(1, $positionEvent->getNegativeTransactions());

        $positiveTransaction = $positionEvent->getPositiveTransactions()->first();
        self::assertNotNull($positiveTransaction->getAmount());
        self::assertTrue($positiveTransaction->getAmount()->equals($transactionAmount));
        self::assertNotNull($positiveTransaction->getTransactionDate());
        self::assertEquals($date->format('Y-m-d'), $positiveTransaction->getTransactionDate()->format('Y-m-d'));

        $negativeTransaction = $positionEvent->getNegativeTransactions()->first();
        self::assertNotNull($negativeTransaction->getAmount());
        self::assertTrue($negativeTransaction->getAmount()->equals($transactionAmount->multiply(-1)));
        self::assertNotNull($negativeTransaction->getTransactionDate());
        self::assertEquals($date->format('Y-m-d'), $negativeTransaction->getTransactionDate()->format('Y-m-d'));
    }

    /**
     * @test
     */
    public function it_ignores_transactions_with_zero_value(): void
    {
        $position = $this->createInvestmentPosition(user: $this->getUser('user2'));
        $this->createPositionEvent(
            position: $position,
            type: PositionEventType::DISTRIBUTION,
            transactions: [
                CreateTransactionDto::factory(Money::USD(0)),
            ]
        );

        /** @var PositionEvent $positionEvent */
        $positionEvent = $this->positionEventRepository->findOneBy(['position' => $position]);
        self::assertCount(0, $positionEvent->getTransactions());
    }

    /**
     * @test
     */
    public function it_creates_complete_event_with_zero_value(): void
    {
        $position = $this->createInvestmentPosition(user: $this->getUser('user2'));
        $this->createPositionEvent(
            position: $position,
            type: PositionEventType::COMPLETE,
            transactions: [
                CreateTransactionDto::factory(Money::USD(100)),
            ]
        );

        /** @var PositionEvent $positionEvent */
        $positionEvent = $this->positionEventRepository->findOneBy(['position' => $position]);
        self::assertCount(1, $positionEvent->getTransactions());
        self::assertNotNull($positionEvent->getValue());
        self::assertTrue($positionEvent->getValue()->getAmount()->isZero());
    }
}
