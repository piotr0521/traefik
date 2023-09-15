<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Faker\Factory as FakerFactory;
use Faker\Generator;
// use Groshy\Entity\TransactionType;
use Groshy\Entity\User;
use Groshy\Tests\Helper\DataBuilder;
use Groshy\Tests\Helper\UsersAwareTrait;
use Talav\Component\Resource\Repository\RepositoryInterface;

class TransactionTest extends ApiTestCase
{
    use UsersAwareTrait;
    use DataBuilder;

    private ?Generator $faker;

    private ?Client $client;

    private ?RepositoryInterface $transactionRepository;

    protected function setUp(): void
    {
        $this->faker = FakerFactory::create();
        $this->client = static::createClient();

        $this->transactionRepository = $this->client->getContainer()->get('app.repository.transaction');

        $this->setUpUsers($this->client->getContainer());
        $this->client->loginUser($this->getUser('user2'));
    }

    /**
     * @test
     */
    public function it_returns_404_for_transactions_for_positions_created_by_another_user(): void
    {
        foreach ($this->getUsers(['user1', 'user3', 'user4', 'user5']) as $user) {
            foreach ($this->getRandomTransactionCreatedBy($user, 2) as $transaction) {
                $this->client->request('GET', '/api/transactions/'.$transaction->getId());
                $this->assertResponseStatusCodeSame(404);
            }
        }
    }

    /**
     * @test
     */
    public function it_only_gets_transactions_for_positions_for_current_user(): void
    {
        $response = $this->client->request('GET', '/api/transactions');
        foreach ($response->toArray()['hydra:member'] as $trans) {
            self::assertStringContainsString($this->getUser('user2')->getId()->__toString(), $trans['position']['createdBy']);
        }
    }

    private function getRandomTransactionCreatedBy(User $user, int $counter = 3): array
    {
        $transactions = $this->transactionRepository->byUser($user);
        $counter = min($counter, count($transactions));

        return $this->faker->randomElements($transactions, $counter);
    }
}
