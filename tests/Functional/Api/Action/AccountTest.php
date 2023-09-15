<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Entity\User;
use Groshy\Tests\Helper\UsersAwareTrait;
use Talav\Component\Resource\Repository\RepositoryInterface;

class AccountTest extends ApiTestCase
{
    use UsersAwareTrait;

    private ?Client $client;
    private ?Generator $faker;

    private ?RepositoryInterface $accountRepository;

    protected function setUp(): void
    {
        $this->faker = FakerFactory::create();
        $this->client = static::createClient();
        $this->setUpUsers($this->client->getContainer());
        $this->client->loginUser($this->getUser('user2'));

        $this->accountRepository = $this->client->getContainer()->get('app.repository.account');
    }

    /**
     * @test
     */
    public function it_returns_404_for_accounts_created_by_another_user(): void
    {
        foreach ($this->getUsers(['user1', 'user3', 'user4', 'user5']) as $user) {
            foreach ($this->getRandomAccountCreatedBy($user, 2) as $account) {
                $this->client->request('GET', '/api/accounts/'.$account->getId());
                $this->assertResponseStatusCodeSame(404);
            }
        }
    }

    /**
     * @test
     */
    public function it_only_gets_accounts_for_current_user(): void
    {
        $response = $this->client->request('GET', '/api/accounts');
        foreach ($response->toArray()['hydra:member'] as $account) {
            self::assertArrayHasKey('createdBy', $account);
            self::assertStringContainsString($this->getUser('user2')->getId()->__toString(), $account['createdBy']);
        }
    }

    /**
     * @test
     */
    public function it_returns_account(): void
    {
        $account = $this->getRandomAccountCreatedBy($this->getUser('user2'), 1)[0];
        $response = $this->client->request('GET', '/api/accounts/'.$account->getId());
        $this->assertResponseIsSuccessful();
        $data = $response->toArray(false);
        self::assertArrayHasKey('id', $data);
        self::assertArrayHasKey('name', $data);
    }

    private function getRandomAccountCreatedBy(User $user, int $counter = 3): array
    {
        $accounts = $this->accountRepository->findBy(['createdBy' => $user]);
        $counter = min($counter, count($accounts));

        return $this->faker->randomElements($accounts, $counter);
    }
}
