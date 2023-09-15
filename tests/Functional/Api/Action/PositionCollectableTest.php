<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Entity\PositionCertificateDeposit;
use Groshy\Entity\PositionCollectable;
use Groshy\Entity\Tag;
use Groshy\Entity\User;
use Groshy\Tests\Helper\UsersAwareTrait;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

class PositionCollectableTest extends ApiTestCase
{
    use UsersAwareTrait;
    use PositionTestTrait;

    private ?Generator $faker;
    private ?Client $client;

    private ?ManagerInterface $positionInvestmentManager;
    private ?RepositoryInterface $positionCollectableRepository;
    private ?RepositoryInterface $tagRepository;

    protected function setUp(): void
    {
        $this->markTestSkipped();
        $this->client = static::createClient();
        $this->faker = FakerFactory::create();

        $this->setUpUsers($this->client->getContainer());
        $this->client->loginUser($this->getUser('user2'));

        $this->positionCollectableRepository = $this->client->getContainer()->get('app.repository.position_collectable');
        $this->tagRepository = $this->client->getContainer()->get('app.repository.tag');
    }

    /**
     * @test
     */
    public function it_shows_errors_for_empty_create_dto(): void
    {
        $this->client->request('POST', '/api/position/collectables', ['json' => []]);
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
    public function it_shows_error_for_long_name(): void
    {
        $this->client->request('POST', '/api/position/collectables', ['json' => [
            'name' => $this->faker->realTextBetween(300, 350),
        ]]);
        self::assertJsonContains([
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
    public function it_creates_new_position_collectable(): void
    {
        $data = [
            'name' => $this->faker->company,
            'notes' => $this->faker->text(200),
            'tags' => array_map(function ($el) {return static::findIriBy(Tag::class, ['id' => $el->getId()]); }, $this->getRandomTags($this->getUser('user2'), $this->faker->numberBetween(1, 3))),
        ];
        $response = $this->client->request('POST', '/api/position/collectables', ['json' => $data]);
        $this->assertResponseStatusCodeSame(201);
        /** @var PositionCollectable $position */
        $position = $this->positionCollectableRepository->find($response->toArray(false)['id']);
        self::assertEquals($data['name'], $position->getName());
        self::assertEquals($data['notes'], $position->getNotes());
        self::assertGreaterThan(0, count($position->getTags()));
    }

    /**
     * @test
     */
    public function it_only_returns_positions_collectable_created_by_the_current_user(): void
    {
        $this->trait_it_only_returns_positions_created_by_the_current_user('/api/position/collectables', $this->positionCollectableRepository);
    }

    /**
     * @test
     */
    public function it_allows_to_get_collectable_by_id(): void
    {
        $position = $this->positionCollectableRepository->findBy(['createdBy' => $this->getUser('user2')])[0];
        $this->client->request('GET', '/api/position/collectables/'.$position->getId());
        self::assertResponseIsSuccessful();
    }

    /**
     * @test
     */
    public function it_returns_404_for_property_created_by_another_user(): void
    {
        $position = $this->positionCollectableRepository->findBy(['createdBy' => $this->getUser('user8')])[0];
        $this->client->request('GET', '/api/position/collectables/'.$position->getId());
        self::assertResponseStatusCodeSame(404);
    }

    /**
     * @test
     */
    public function it_shows_errors_for_empty_name_in_update_dto(): void
    {
        $position = $this->positionCollectableRepository->findBy(['createdBy' => $this->getUser('user2')])[0];
        $this->client->request('PATCH', '/api/position/collectables/'.$position->getId(), [
            'json' => [
                'name' => '',
            ],
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);
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
    public function it_updates_position_collectable(): void
    {
        $data = [
            'name' => $this->faker->company,
            'notes' => $this->faker->text(200),
            'tags' => array_map(function ($el) {return static::findIriBy(Tag::class, ['id' => $el->getId()]); }, $this->getRandomTags($this->getUser('user2'), $this->faker->numberBetween(1, 3))),
        ];

        $position = $this->positionCollectableRepository->findBy(['createdBy' => $this->getUser('user2')])[0];
        $this->client->request('PATCH', '/api/position/collectables/'.$position->getId(), [
            'json' => $data,
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);
        self::assertResponseStatusCodeSame(200);
        /** @var PositionCertificateDeposit $position */
        $position = $this->positionCollectableRepository->find($position->getId());
        self::assertEquals($data['name'], $position->getName());
        self::assertEquals($data['notes'], $position->getNotes());
        self::assertGreaterThan(0, count($position->getTags()));
    }

    /**
     * @test
     */
    public function it_deletes_position_collectable(): void
    {
        $position = $this->positionCollectableRepository->findBy(['createdBy' => $this->getUser('user2')])[0];
        $id = $position->getId();
        $this->client->request('DELETE', '/api/position/collectables/'.$position->getId());
        $this->assertResponseStatusCodeSame(204);
        self::assertNull($this->positionCollectableRepository->find($id));
    }

    private function getRandomTags(User $user, int $count = 1): array
    {
        $tags = $this->tagRepository->findBy(['createdBy' => $user]);

        return $this->faker->randomElements($tags, $count);
    }

    private function getRandomPositionCollectable(User $user): PositionCollectable
    {
        $positions = $this->positionCollectableRepository->findBy(['createdBy' => $user]);

        return $positions[array_rand($positions)];
    }
}
