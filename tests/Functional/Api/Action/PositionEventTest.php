<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use DateTime;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Domain\Enum\PositionEventType;
use Groshy\Entity\Position;
use Groshy\Entity\PositionEvent;
use Groshy\Entity\Transaction;
use Groshy\Entity\User;
use Groshy\Message\Dto\PositionEvent\CreateTransactionDto;
use Groshy\Model\MoneyAwareTrait;
use Groshy\Tests\Helper\DataBuilder;
use Groshy\Tests\Helper\UsersAwareTrait;
use Money\Money;
use Talav\Component\Resource\Repository\RepositoryInterface;

class PositionEventTest extends ApiTestCase
{
    use UsersAwareTrait;
    use MoneyAwareTrait;
    use DataBuilder;

    private ?Generator $faker;
    private ?Client $client;

    private ?RepositoryInterface $positionInvestmentRepository;
    private ?RepositoryInterface $positionEventRepository;
    private ?RepositoryInterface $assetInvestmentRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->faker = FakerFactory::create();

        $this->setUpUsers($this->client->getContainer());
        $this->client->loginUser($this->getUser('user2'));

        $this->positionInvestmentRepository = $this->client->getContainer()->get('app.repository.position_investment');
        $this->positionEventRepository = $this->client->getContainer()->get('app.repository.position_event');
        $this->assetInvestmentRepository = $this->client->getContainer()->get('app.repository.asset_investment');
    }

    /**
     * @test
     */
    public function it_returns_404_for_position_events_for_positions_created_by_another_user(): void
    {
        foreach ($this->getUsers(['user1', 'user3', 'user4', 'user5']) as $user) {
            foreach ($this->getRandomPositionCreatedBy($user, 2) as $positionEvent) {
                $this->client->request('GET', '/api/position_events/'.$positionEvent->getId());
                $this->assertResponseStatusCodeSame(404);
            }
        }
    }

    /**
     * @test
     */
    public function it_only_gets_position_events_for_positions_for_current_user(): void
    {
        $response = $this->client->request('GET', '/api/position_events');
        foreach ($response->toArray()['hydra:member'] as $trans) {
            self::assertStringContainsString($this->getUser('user2')->getId()->__toString(), $trans['position']['createdBy']);
        }
    }

    /**
     * @test
     */
    public function it_returns_position_event_data(): void
    {
        $notes = $this->faker->text(100);
        $position = $this->createInvestmentPosition(user: $this->getUser('user2'));
        $positionEvent = $this->createPositionEvent(
            position: $position,
            valueAmount: Money::USD(100),
            type: PositionEventType::REINVEST,
            notes: $notes,
            transactions: [
                CreateTransactionDto::factory(Money::USD(100)),
                CreateTransactionDto::factory(Money::USD(-100)),
            ]
        );
        $response = $this->client->request('GET', '/api/position_events/'.$positionEvent->getId());
        $data = $response->toArray(false);
        self::assertArrayHasKey('notes', $data);
        self::assertEquals($notes, $data['notes']);
        self::assertArrayHasKey('transactions', $data);
        self::assertIsArray($data['transactions']);
        self::assertCount(2, $data['transactions']);
        self::assertArrayHasKey(0, $data['transactions']);
        self::assertArrayHasKey(1, $data['transactions']);
        self::assertArrayHasKey('amount', $data['transactions'][0]);
        self::assertArrayHasKey('amount', $data['transactions'][1]);
    }

    /**
     * @test
     */
    public function it_requires_value_for_value_update_events(): void
    {
        $position = $this->getRandomPosition();
        $data = [
            'date' => (new DateTime('now'))->format('Y-m-d'),
            'type' => PositionEventType::VALUE_UPDATE->value,
            'position' => static::findIriBy(Position::class, ['id' => $position->getId()]),
        ];
        $response = $this->client->request('POST', '/api/position_events', ['json' => $data]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => 'value',
                    'message' => 'Value is required for value update event type',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_requires_correct_date(): void
    {
        $data = [
            'date' => 'random line',
        ];
        $response = $this->client->request('POST', '/api/position_events', ['json' => $data]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => 'date',
                    'message' => 'This value should be of type string.',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_requires_date(): void
    {
        $data = [
            'date' => null,
        ];
        $response = $this->client->request('POST', '/api/position_events', ['json' => $data]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => 'date',
                    'message' => 'This value should not be blank.',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_requires_no_transactions_for_value_update_event(): void
    {
        $position = $this->getRandomPosition();
        $data = [
            'date' => (new DateTime('now'))->format('Y-m-d'),
            'type' => PositionEventType::VALUE_UPDATE->value,
            'position' => static::findIriBy(Position::class, ['id' => $position->getId()]),
            'value' => ['amount' => '100.99'],
            'transactions' => [
                [
                    'amount' => '10.99',
                ],
            ],
        ];
        $response = $this->client->request('POST', '/api/position_events', ['json' => $data]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => 'type',
                    'message' => 'Value update event should not have any transactions',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_requires_one_positive_transaction_for_distributions_but_provided_zero_transactions(): void
    {
        $position = $this->getRandomPosition();
        $data = [
            'date' => (new DateTime('now'))->format('Y-m-d'),
            'type' => PositionEventType::DISTRIBUTION->value,
            'position' => static::findIriBy(Position::class, ['id' => $position->getId()]),
        ];
        $response = $this->client->request('POST', '/api/position_events', ['json' => $data]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => 'transactions',
                    'message' => 'Distribution event should have 1 positive transaction',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_requires_one_positive_transaction_for_distributions_but_provided_negative_transaction(): void
    {
        $position = $this->getRandomPosition();
        $data = [
            'date' => (new DateTime('now'))->format('Y-m-d'),
            'type' => PositionEventType::DISTRIBUTION->value,
            'position' => static::findIriBy(Position::class, ['id' => $position->getId()]),
            'transactions' => [
                [
                    'amount' => '-10.99',
                ],
            ],
        ];
        $response = $this->client->request('POST', '/api/position_events', ['json' => $data]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => 'transactions',
                    'message' => 'Distribution event should have 1 positive transaction',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_requires_one_negative_transaction_for_contributions_but_provided_positive_transaction(): void
    {
        $position = $this->getRandomPosition();
        $data = [
            'date' => (new DateTime('now'))->format('Y-m-d'),
            'type' => PositionEventType::CONTRIBUTION->value,
            'position' => static::findIriBy(Position::class, ['id' => $position->getId()]),
            'transactions' => [
                [
                    'amount' => '10.99',
                ],
            ],
        ];
        $response = $this->client->request('POST', '/api/position_events', ['json' => $data]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => 'transactions',
                    'message' => 'Contribution event should have 1 negative transaction',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_requires_at_least_one_transaction_for_distributions(): void
    {
        $position = $this->getRandomPosition();
        $data = [
            'date' => (new DateTime('now'))->format('Y-m-d'),
            'type' => PositionEventType::DISTRIBUTION->value,
            'position' => static::findIriBy(Position::class, ['id' => $position->getId()]),
        ];
        $response = $this->client->request('POST', '/api/position_events', ['json' => $data]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => 'transactions',
                    'message' => 'Distribution event should have 1 positive transaction',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_does_not_allow_to_create_position_event_with_type_not_allowed_for_asset_type(): void
    {
        $position = $this->getRandomPosition();
        $data = [
            'date' => (new DateTime('now'))->format('Y-m-d'),
            'type' => PositionEventType::SELL->value,
            'position' => static::findIriBy(Position::class, ['id' => $position->getId()]),
        ];
        $response = $this->client->request('POST', '/api/position_events', ['json' => $data]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => 'type',
                    'message' => 'This event cannot be added to the position asset type',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_requires_required_fields_for_create_endpoint(): void
    {
        $response = $this->client->request('POST', '/api/position_events', ['json' => []]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            'violations' => [
                0 => [
                    'propertyPath' => 'date',
                    'message' => 'This value should not be blank.',
                ],
                1 => [
                    'propertyPath' => 'type',
                    'message' => 'This value should not be blank.',
                ],
                2 => [
                    'propertyPath' => 'position',
                    'message' => 'This value should not be blank.',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_requires_float_value_for_value_update_amount(): void
    {
        $data = [
            'value' => ['amount' => 'not a float'],
        ];
        $response = $this->client->request('POST', '/api/position_events', ['json' => $data]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            'violations' => [
                3 => [
                    'propertyPath' => 'value.amount',
                    'message' => 'This value is not a correct amount.',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_requires_amount_to_be_less_defined_value(): void
    {
        $data = [
            'value' => ['amount' => 20000000],
        ];
        $this->client->request('POST', '/api/position_events', ['json' => $data]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            'violations' => [
                3 => [
                    'propertyPath' => 'value.amount',
                    'message' => 'Amount is too large.',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_requires_float_value_for_transaction_amount(): void
    {
        $data = [
            'transactions' => [
                ['amount' => 'not a float'],
                ['amount' => 'not a float'],
            ],
        ];
        $response = $this->client->request('POST', '/api/position_events', ['json' => $data]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            'violations' => [
                3 => [
                    'propertyPath' => 'transactions[0].amount',
                    'message' => 'This value is not a correct amount.',
                ],
                4 => [
                    'propertyPath' => 'transactions[1].amount',
                    'message' => 'This value is not a correct amount.',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_creates_position_event_with_value_update_and_two_transactions(): void
    {
        $position = $this->getRandomPosition();
        $data = [
            'date' => (new DateTime('now'))->format('Y-m-d'),
            'type' => PositionEventType::REINVEST->value,
            'position' => static::findIriBy(Position::class, ['id' => $position->getId()]),
            'value' => ['amount' => '100.99'],
            'notes' => $this->faker->text(100),
            'transactions' => [
                [
                    'amount' => '10.99',
                ],
                [
                    'amount' => '-10.99',
                ],
            ],
        ];
        $response = $this->client->request('POST', '/api/position_events', ['json' => $data]);

        /** @var PositionEvent $positionsEvent */
        $positionsEvent = $this->positionEventRepository->find($response->toArray()['id']);
        self::assertEquals($data['date'], $positionsEvent->getDate()->format('Y-m-d'));
        self::assertEquals($position, $positionsEvent->getPosition());
        self::assertEquals($data['type'], $positionsEvent->getType()->value);
        self::assertEquals($data['notes'], $positionsEvent->getNotes());
        self::assertTrue($positionsEvent->getValue()->getAmount()->equals(Money::USD('10099')));
        self::assertCount(2, $positionsEvent->getTransactions());
        self::assertTrue(Money::USD('1099')->equals($positionsEvent->getTransactions()->get(0)->getAmount()));
        self::assertTrue(Money::USD('-1099')->equals($positionsEvent->getTransactions()->get(1)->getAmount()));
    }

    /**
     * @test
     */
    public function it_requires_empty_value_and_or_positive_transaction_to_create_complete_event(): void
    {
        $position = $this->createInvestmentPosition(user: $this->getUser('user2'));
        $data = [
            'date' => (new DateTime('now'))->format('Y-m-d'),
            'type' => PositionEventType::COMPLETE->value,
            'position' => static::findIriBy(Position::class, ['id' => $position->getId()]),
            'value' => ['amount' => '100.99'],
            'transactions' => [
                [
                    'amount' => '-10.99',
                ],
            ],
        ];
        $response = $this->client->request('POST', '/api/position_events', ['json' => $data]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => 'type',
                    'message' => 'Complete event can only have one positive transaction',
                ],
                1 => [
                    'propertyPath' => 'value',
                    'message' => 'After completion investment should have zero value',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_creates_complete_event_with_one_positive_transactions(): void
    {
        $position = $this->createInvestmentPosition(user: $this->getUser('user2'));
        $data = [
            'date' => (new DateTime('now'))->format('Y-m-d'),
            'type' => PositionEventType::COMPLETE->value,
            'position' => static::findIriBy(Position::class, ['id' => $position->getId()]),
            'transactions' => [
                [
                    'amount' => '10.99',
                ],
            ],
        ];
        $response = $this->client->request('POST', '/api/position_events', ['json' => $data]);

        /** @var PositionEvent $positionsEvent */
        $positionsEvent = $this->positionEventRepository->find($response->toArray()['id']);
        self::assertEquals($data['date'], $positionsEvent->getDate()->format('Y-m-d'));
        self::assertEquals($position, $positionsEvent->getPosition());
        self::assertEquals($data['type'], $positionsEvent->getType()->value);
        self::assertCount(1, $positionsEvent->getTransactions());
        self::assertTrue(Money::USD('1099')->equals($positionsEvent->getTransactions()->get(0)->getAmount()));
    }

    /**
     * @test
     */
    public function it_updates_position_event_and_updates_transactions(): void
    {
        $position = $this->getRandomPosition();
        $data = [
            'date' => (new DateTime('now'))->format('Y-m-d'),
            'type' => PositionEventType::REINVEST->value,
            'position' => static::findIriBy(Position::class, ['id' => $position->getId()]),
            'value' => ['amount' => '100.99'],
            'notes' => $this->faker->text(100),
            'transactions' => [
                [
                    'amount' => '10.99',
                ],
                [
                    'amount' => '-10.99',
                ],
            ],
        ];
        $response = $this->client->request('POST', '/api/position_events', ['json' => $data]);
        /** @var PositionEvent $positionsEvent */
        $positionsEvent = $this->positionEventRepository->find($response->toArray()['id']);
        /** @var Transaction $transaction1 */
        $transaction1 = $positionsEvent->getTransactions()->get(0);
        /** @var Transaction $transaction2 */
        $transaction2 = $positionsEvent->getTransactions()->get(1);
        $data = [
            'type' => PositionEventType::REINVEST->value,
            'transactions' => [
                [
                    'transaction' => static::findIriBy(Transaction::class, ['id' => $transaction1->getId()]),
                    'amount' => $transaction1->getAmount()->add(Money::USD(100))->getAmount() / 100,
                ],
                [
                    'transaction' => static::findIriBy(Transaction::class, ['id' => $transaction2->getId()]),
                    'amount' => $transaction2->getAmount()->subtract(Money::USD(100))->getAmount() / 100,
                ],
            ],
        ];
        $this->client->request('PATCH', '/api/position_events/'.$positionsEvent->getId(), [
            'json' => $data,
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);
        $this->assertResponseStatusCodeSame(200);
        /** @var PositionEvent $positionsEvent */
        $positionsEvent = $this->positionEventRepository->find($positionsEvent->getId());
        self::assertCount(2, $positionsEvent->getTransactions());
        self::assertCount(1, $positionsEvent->getPositiveTransactions());
        self::assertCount(1, $positionsEvent->getNegativeTransactions());
        self::assertTrue(Money::USD('1199')->equals($positionsEvent->getPositiveTransactions()->getValues()[0]->getAmount()));
        self::assertTrue(Money::USD('-1199')->equals($positionsEvent->getNegativeTransactions()->getValues()[0]->getAmount()));
    }

    /**
     * @test
     */
    public function it_does_not_allow_to_update_type_to_not_allowed_for_asset_type(): void
    {
        $position = $this->getRandomPosition();

        $data = [
            'date' => (new DateTime('now'))->format('Y-m-d'),
            'type' => PositionEventType::VALUE_UPDATE->value,
            'position' => static::findIriBy(Position::class, ['id' => $position->getId()]),
            'value' => ['amount' => '100.99'],
        ];
        $response = $this->client->request('POST', '/api/position_events', ['json' => $data]);
        /** @var PositionEvent $positionsEvent */
        $positionsEvent = $this->positionEventRepository->find($response->toArray()['id']);

        $updateData = [
            'type' => PositionEventType::SELL->value,
        ];
        $this->client->request('PATCH', '/api/position_events/'.$positionsEvent->getId(), [
            'json' => $updateData,
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => 'type',
                    'message' => 'This event cannot be added to the position asset type',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_deletes_position_event(): void
    {
        $position = $this->createInvestmentPosition(user: $this->getUser('user2'));
        $positionEvent = $this->createPositionEvent(position: $position, valueAmount: Money::USD(100), type: PositionEventType::VALUE_UPDATE);

        $id = $positionEvent->getId();
        $this->client->request('DELETE', '/api/position_events/'.$id);
        $this->assertResponseStatusCodeSame(204);
        self::assertNull($this->positionEventRepository->find($id));
    }

    private function getRandomPosition(): Position
    {
        $positions = $this->positionInvestmentRepository->findBy(['createdBy' => $this->getUser('user2')]);

        return $this->faker->randomElement($positions);
    }

    private function getRandomPositionCreatedBy(User $user, int $counter = 3): array
    {
        $transactions = $this->positionInvestmentRepository->findBy(['createdBy' => $user]);
        $counter = min($counter, count($transactions));

        return $this->faker->randomElements($transactions, $counter);
    }
}
