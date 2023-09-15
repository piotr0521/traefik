<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Entity\AccountHolder;
use Groshy\Entity\User;
use Groshy\Tests\Helper\UsersAwareTrait;
use Talav\Component\Resource\Repository\RepositoryInterface;

class AccountHolderTest extends ApiTestCase
{
    use UsersAwareTrait;

    private ?Client $client;
    private ?Generator $faker;

    private ?RepositoryInterface $accountHolderRepository;

    protected function setUp(): void
    {
        $this->faker = FakerFactory::create();
        $this->client = static::createClient();
        $this->setUpUsers($this->client->getContainer());
        $this->client->loginUser($this->getUser('user2'));

        $this->accountHolderRepository = $this->client->getContainer()->get('app.repository.account_holder');
    }

    /**
     * @test
     */
    public function it_returns_404_for_account_holders_created_by_another_user(): void
    {
        foreach ($this->getUsers(['user1', 'user3', 'user4', 'user5']) as $user) {
            foreach ($this->getRandomAccountHolderCreatedBy($user, 2) as $accountHolder) {
                $this->client->request('GET', '/api/account_holders/'.$accountHolder->getId());
                $this->assertResponseStatusCodeSame(404);
            }
        }
    }

    /**
     * @test
     */
    public function it_only_gets_account_holders_for_current_user(): void
    {
        $response = $this->client->request('GET', '/api/accounts');
        foreach ($response->toArray()['hydra:member'] as $accountHolder) {
            self::assertArrayHasKey('createdBy', $accountHolder);
            self::assertStringContainsString($this->getUser('user2')->getId()->__toString(), $accountHolder['createdBy']);
        }
    }

    /**
     * @test
     */
    public function it_returns_account_holder(): void
    {
        $accountHolder = $this->getRandomAccountHolderCreatedBy($this->getUser('user2'), 1)[0];
        $response = $this->client->request('GET', '/api/account_holders/'.$accountHolder->getId());
        $this->assertResponseIsSuccessful();
        $data = $response->toArray(false);
        self::assertArrayHasKey('id', $data);
        self::assertArrayHasKey('name', $data);
    }

    /**
     * @test
     */
    public function it_shows_errors_for_empty_create_dto(): void
    {
        $this->client->request('POST', '/api/account_holders', ['json' => []]);
        self::assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => 'name',
                    'message' => 'This value should not be blank.',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_shows_errors_for_long_name_in_create_dto(): void
    {
        $this->client->request('POST', '/api/account_holders', ['json' => [
            'name' => $this->faker->realTextBetween(300, 350),
        ]]);
        self::assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => 'name',
                    'message' => 'This value is too long. It should have 250 characters or less.',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_creates_new_account_holder(): void
    {
        $data = [
            'name' => $this->faker->realTextBetween(100, 150),
        ];
        $response = $this->client->request('POST', '/api/account_holders', ['json' => $data]);
        self::assertResponseStatusCodeSame(201);
        /** @var AccountHolder $accountHolder */
        $accountHolder = $this->accountHolderRepository->find($response->toArray(false)['id']);
        self::assertEquals($data['name'], $accountHolder->getName());
    }

    /**
     * @test
     */
    public function it_updates_account_holder(): void
    {
        $data = [
            'name' => $this->faker->realTextBetween(100, 150),
        ];
        $response = $this->client->request('POST', '/api/account_holders', ['json' => $data]);
        self::assertResponseStatusCodeSame(201);
        $data = [
            'name' => $this->faker->realTextBetween(100, 150),
        ];
        $id = $response->toArray(false)['id'];
        $response = $this->client->request('PATCH', '/api/account_holders/'.$id, [
            'json' => $data,
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);
        self::assertResponseStatusCodeSame(200);

        /** @var AccountHolder $accountHolder */
        $accountHolder = $this->accountHolderRepository->find($id);
        self::assertEquals($data['name'], $accountHolder->getName());
    }

    /**
     * @test
     */
    public function it_deletes_account_holder(): void
    {
        $data = [
            'name' => $this->faker->realTextBetween(100, 150),
        ];
        $response = $this->client->request('POST', '/api/account_holders', ['json' => $data]);
        self::assertResponseStatusCodeSame(201);
        $id = $response->toArray(false)['id'];

        $this->client->request('DELETE', '/api/account_holders/'.$id);
        $this->assertResponseStatusCodeSame(204);
        self::assertNull($this->accountHolderRepository->find($id));
    }

    private function getRandomAccountHolderCreatedBy(User $user, int $counter = 3): array
    {
        $accountHolders = $this->accountHolderRepository->findBy(['createdBy' => $user]);
        $counter = min($counter, count($accountHolders));

        return $this->faker->randomElements($accountHolders, $counter);
    }
}
