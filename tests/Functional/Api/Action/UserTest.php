<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Entity\User;
use Groshy\Tests\Helper\UsersAwareTrait;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

class UserTest extends ApiTestCase
{
    use UsersAwareTrait;

    private ?Client $client;
    private ?Generator $faker;

    private RepositoryInterface $positionRepository;
    private RepositoryInterface $userRepository;
    private UserPasswordHasherInterface $hasher;

    protected function setUp(): void
    {
        $this->faker = FakerFactory::create();
        $this->client = static::createClient();
        $this->setUpUsers($this->client->getContainer());
        $this->client->loginUser($this->getUser('user2'));

        $this->positionRepository = $this->client->getContainer()->get('app.repository.position');
        $this->userRepository = $this->client->getContainer()->get('app.repository.user');
        $this->hasher = $this->client->getContainer()->get(UserPasswordHasherInterface::class);
    }

    /**
     * @test
     */
    public function it_returns_current_user_data(): void
    {
        $response = $this->client->request('GET', '/api/users/'.$this->getUser('user2')->getId());
        self::assertResponseIsSuccessful();
        self::assertCount(7, $response->toArray());
        self::assertArrayHasKey('id', $response->toArray());
        self::assertArrayHasKey('firstName', $response->toArray());
        self::assertArrayHasKey('lastName', $response->toArray());
        self::assertArrayHasKey('username', $response->toArray());
    }

    /**
     * @test
     */
    public function it_returns_returns_403_for_another_user(): void
    {
        $this->client->request('GET', '/api/users/'.$this->getUser('user1')->getId());
        self::assertResponseStatusCodeSame(403);
    }

    /**
     * @test
     */
    public function it_returns_dashboard_stats_for_current_user(): void
    {
        $this->client->request('GET', '/api/users/'.$this->getUser('user2')->getId().'/stats');
        self::assertResponseIsSuccessful();
    }

    /**
     * @test
     */
    public function it_returns_dashboard_stats_for_current_user_without_any_investments(): void
    {
        $this->client->loginUser($this->getUser('user0'));
        $this->client->request('GET', '/api/users/'.$this->getUser('user0')->getId().'/stats');
        self::assertResponseIsSuccessful();
    }

    /**
     * @test
     */
    public function it_returns_dashboard_stats_for_current_user_and_position(): void
    {
        $user = $this->getUser('user2');
        $position = $this->positionRepository->findBy(['createdBy' => $user])[0];
        $this->client->request('GET', '/api/users/'.$user->getId().'/stats?position='.$position->getId());
        self::assertResponseIsSuccessful();
    }

    /**
     * @test
     */
    public function it_returns_returns_403_for_another_user_dashboard_stats(): void
    {
        $this->client->request('GET', '/api/users/'.$this->getUser('user3')->getId().'/stats');
        self::assertResponseStatusCodeSame(403);
    }

    /**
     * @test
     */
    public function it_returns_returns_403_for_another_user_reset_tag(): void
    {
        $this->client->request('PUT', '/api/users/'.$this->getUser('user3')->getId().'/reset_tags', ['json' => []]);
        self::assertResponseStatusCodeSame(403);
    }

    /**
     * @test
     */
    public function it_resets_user_tags(): void
    {
        $this->client->request('PUT', '/api/users/'.$this->getUser('user2')->getId().'/reset_tags', ['json' => []]);
        self::assertResponseStatusCodeSame(202);
    }

    /**
     * @test
     */
    public function it_updates_user_profile_data(): void
    {
        $data = [
            'firstName' => $this->faker->firstName(),
            'lastName' => $this->faker->lastName(),
            'username' => $this->faker->userName(),
        ];
        $this->client->request('PATCH', '/api/users/'.$this->getUser('user2')->getId(), [
            'json' => $data,
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);
        self::assertResponseStatusCodeSame(200);
        /** @var User $user */
        $user = $this->userRepository->find($this->getUser('user2')->getId());
        self::assertEquals($data['firstName'], $user->getFirstName());
        self::assertEquals($data['lastName'], $user->getLastName());
        self::assertEquals($data['username'], $user->getUsername());
    }

    /**
     * @test
     */
    public function it_returns_returns_403_for_another_user_profile_update(): void
    {
        $data = [
            'firstName' => $this->faker->firstName(),
        ];
        $this->client->request('PATCH', '/api/users/'.$this->getUser('user3')->getId(), [
            'json' => $data,
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);
        self::assertResponseStatusCodeSame(403);
    }

    /**
     * @test
     */
    public function it_updates_user_password(): void
    {
        $data = [
            'currentPassword' => 'user2',
            'newPassword' => 'user22',
        ];
        $this->client->request('PUT', '/api/users/'.$this->getUser('user2')->getId().'/password', ['json' => $data]);
        self::assertResponseStatusCodeSame(200);
        self::assertFalse($this->hasher->isPasswordValid($this->getUser('user2'), $data['currentPassword']));
        self::assertTrue($this->hasher->isPasswordValid($this->getUser('user2'), $data['newPassword']));
    }

    /**
     * @test
     */
    public function it_requires_current_password_to_be_valid_and_new_password_not_empty(): void
    {
        $data = [
            'currentPassword' => 'not valid password',
        ];
        $this->client->request('PUT', '/api/users/'.$this->getUser('user2')->getId().'/password', ['json' => $data]);
        $this->assertResponseStatusCodeSame(422);
        self::assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => 'currentPassword',
                    'message' => 'This value should be the user\'s current password.',
                ],
                1 => [
                    'propertyPath' => 'newPassword',
                    'message' => 'Please enter a password.',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_returns_returns_403_for_another_user_password_update(): void
    {
        $data = [
            'currentPassword' => 'not valid password',
        ];
        $this->client->request('PUT', '/api/users/'.$this->getUser('user3')->getId().'/password', [
            'json' => $data,
        ]);
        self::assertResponseStatusCodeSame(403);
    }

    /**
     * @test
     */
    public function it_returns_graph_for_current_user(): void
    {
        $user = $this->getUser('user2');
        $response = $this->client->request('GET', '/api/users/'.$user->getId().'/graph');
        self::assertResponseIsSuccessful();
    }

    /**
     * @test
     */
    public function it_returns_returns_403_for_another_user_graph(): void
    {
        $this->client->request('GET', '/api/users/'.$this->getUser('user3')->getId().'/graph');
        self::assertResponseStatusCodeSame(403);
    }
}
