<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Domain\Enum\PropertyType;
use Groshy\Entity\PositionProperty;
use Groshy\Entity\Tag;
use Groshy\Entity\User;
use Groshy\Tests\Helper\UsersAwareTrait;
use Talav\Component\Resource\Repository\RepositoryInterface;

class PositionPropertyTest extends ApiTestCase
{
    use UsersAwareTrait;
    use PositionTestTrait;

    private ?Generator $faker;
    private ?Client $client;

    private ?RepositoryInterface $assetTypeRepository;
    private ?RepositoryInterface $positionPropertyRepository;
    private ?RepositoryInterface $assetPropertyRepository;
    private ?RepositoryInterface $tagRepository;

    protected function setUp(): void
    {
        $this->markTestSkipped();
        $this->client = static::createClient();
        $this->faker = FakerFactory::create();

        $this->setUpUsers($this->client->getContainer());
        $this->client->loginUser($this->getUser('user2'));

        $this->assetTypeRepository = $this->client->getContainer()->get('app.repository.asset_type');
        $this->positionPropertyRepository = $this->client->getContainer()->get('app.repository.position_property');
        $this->assetPropertyRepository = $this->client->getContainer()->get('app.repository.asset_property');
        $this->tagRepository = $this->client->getContainer()->get('app.repository.tag');
    }

    /**
     * @test
     */
    public function it_only_returns_positions_property_created_by_the_current_user(): void
    {
        $this->trait_it_only_returns_positions_created_by_the_current_user('/api/position/properties', $this->positionPropertyRepository);
    }

    /**
     * @test
     */
    public function it_allows_to_get_property_by_id(): void
    {
        $property = $this->positionPropertyRepository->findBy(['createdBy' => $this->getUser('user2')])[0];
        $this->client->request('GET', '/api/position/properties/'.$property->getId());
        self::assertResponseIsSuccessful();
    }

    /**
     * @test
     */
    public function it_returns_404_for_property_created_by_another_user(): void
    {
        $property = $this->assetPropertyRepository->findBy(['createdBy' => $this->getUser('user3')])[0];
        $this->client->request('GET', '/api/position/properties/'.$property->getId());
        self::assertResponseStatusCodeSame(404);
    }

    /**
     * @test
     */
    public function it_shows_errors_for_empty_create_dto(): void
    {
        $this->client->request('POST', '/api/position/properties', ['json' => []]);
        self::assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => 'name',
                    'message' => 'This value should not be blank.',
                ],
                1 => [
                    'propertyPath' => 'propertyType',
                    'message' => 'This value should not be blank.',
                ],
                2 => [
                    'propertyPath' => 'purchaseDate',
                    'message' => 'This value should not be blank.',
                ],
                3 => [
                    'propertyPath' => 'purchaseValue',
                    'message' => 'This value should not be blank.',
                ],
                4 => [
                    'propertyPath' => 'currentValue',
                    'message' => 'This value should not be blank.',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_creates_new_position_and_asset(): void
    {
        $tags = $this->getRandomTags($this->getUser('user2'), $this->faker->numberBetween(1, 3));

        $data = [
            'tags' => array_map(function ($el) {return static::findIriBy(Tag::class, ['id' => $el->getId()]); }, $tags),
            'notes' => $this->faker->text(200),
            'currentValue' => strval($this->faker->numberBetween(400, 500) * 1000),
            'purchaseDate' => $this->faker->dateTimeBetween('-5 years', '-1 year')->format('Y-m-d'),
            'name' => $this->faker->realTextBetween(10, 150),
            'propertyType' => $this->faker->randomElement(PropertyType::cases()),
            'address' => str_replace("\n", ' ', $this->faker->address()),
            'website' => $this->faker->url(),
            'units' => $this->faker->numberBetween(2, 450),
        ];
        $data['purchaseValue'] = strval($data['currentValue'] * (1 - $this->faker->numberBetween(10, 30) / 100));

        $response = $this->client->request('POST', '/api/position/properties', ['json' => $data]);
        self::assertResponseStatusCodeSame(201);
        /** @var PositionProperty $position */
        $position = $this->positionPropertyRepository->find($response->toArray()['id']);
        $asset = $position->getAsset();
        self::assertGreaterThanOrEqual(1, count($position->getTags()));
        self::assertEquals($data['notes'], $position->getNotes());
        self::assertEquals($data['units'], $asset->getUnits());
        self::assertEquals($data['website'], $asset->getWebsite());
        self::assertEquals($data['address'], $asset->getAddress());
        self::assertEquals($data['propertyType'], $asset->getPropertyType());
        self::assertEquals($data['name'], $asset->getName());
        self::assertEquals('Investment Property', $asset->getAssetType()->getName());
    }

    /**
     * @test
     */
    public function it_shows_errors_for_empty_name_in_update_dto(): void
    {
        $position = $this->getRandomPosition($this->getUser('user2'));
        $this->client->request('PATCH', '/api/position/properties/'.$position->getId(), [
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
    public function it_updates_position_and_asset(): void
    {
        $position = $this->getRandomPosition($this->getUser('user2'));
        $tags = $this->getRandomTags($this->getUser('user2'), $this->faker->numberBetween(1, 3));
        $data = [
            'tags' => array_map(function ($el) {return static::findIriBy(Tag::class, ['id' => $el->getId()]); }, $tags),
            'notes' => $this->faker->text(200),
            'name' => $this->faker->realTextBetween(10, 150),
            'propertyType' => $this->faker->randomElement(PropertyType::cases()),
            'address' => str_replace("\n", ' ', $this->faker->address()),
            'website' => $this->faker->url(),
            'units' => $this->faker->numberBetween(2, 450),
        ];
        $this->client->request('PATCH', '/api/position/properties/'.$position->getId(), [
            'json' => $data,
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);
        $this->assertResponseStatusCodeSame(200);
        $position = $this->positionPropertyRepository->find($position->getId());
        $asset = $position->getAsset();
        self::assertEquals($data['notes'], $position->getNotes());
        self::assertEquals($data['units'], $asset->getUnits());
        self::assertEquals($data['website'], $asset->getWebsite());
        self::assertEquals($data['address'], $asset->getAddress());
        self::assertEquals($data['propertyType'], $asset->getPropertyType());
        self::assertEquals($data['name'], $asset->getName());
    }

    /**
     * @test
     */
    public function it_deletes_position_and_asset(): void
    {
        $position = $this->getRandomPosition($this->getUser('user2'));
        $id = $position->getId();
        $assetId = $position->getAsset()->getId();
        self::assertNotNull($this->assetPropertyRepository->find($assetId));
        $this->client->request('DELETE', '/api/position/properties/'.$position->getId());
        $this->assertResponseStatusCodeSame(204);
        self::assertNull($this->positionPropertyRepository->find($id));
        self::assertNull($this->assetPropertyRepository->find($assetId));
    }

    private function getRandomPosition(User $user): PositionProperty
    {
        $positions = $this->positionPropertyRepository->findBy(['createdBy' => $user]);

        return $positions[array_rand($positions)];
    }

    private function getRandomTags(User $user, int $count = 1): array
    {
        return $this->faker->randomElements($this->tagRepository->findBy(['createdBy' => $user]), $count);
    }
}
