<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Entity\PositionBusiness;
use Groshy\Entity\Tag;
use Groshy\Entity\User;
use Groshy\Model\MoneyAwareTrait;
use Groshy\Tests\Helper\UsersAwareTrait;
use Talav\Component\Resource\Repository\RepositoryInterface;

class PositionBusinessTest extends ApiTestCase
{
    use UsersAwareTrait;
    use PositionTestTrait;
    use MoneyAwareTrait;

    private ?Generator $faker;
    private ?Client $client;

    private ?RepositoryInterface $assetTypeRepository;
    private ?RepositoryInterface $positionBusinessRepository;
    private ?RepositoryInterface $assetBusinessRepository;
    private ?RepositoryInterface $tagRepository;

    protected function setUp(): void
    {
        $this->markTestSkipped();
        $this->client = static::createClient();
        $this->faker = FakerFactory::create();

        $this->setUpUsers($this->client->getContainer());
        $this->client->loginUser($this->getUser('user2'));

        $this->assetTypeRepository = $this->client->getContainer()->get('app.repository.asset_type');
        $this->positionBusinessRepository = $this->client->getContainer()->get('app.repository.position_business');
        $this->assetBusinessRepository = $this->client->getContainer()->get('app.repository.asset_business');
        $this->tagRepository = $this->client->getContainer()->get('app.repository.tag');
    }

    /**
     * @test
     */
    public function it_only_returns_positions_business_created_by_the_current_user(): void
    {
        $this->trait_it_only_returns_positions_created_by_the_current_user('/api/position/businesses', $this->positionBusinessRepository);
    }

    /**
     * @test
     */
    public function it_allows_to_get_business_by_id(): void
    {
        $business = $this->positionBusinessRepository->findBy(['createdBy' => $this->getUser('user2')])[0];
        $this->client->request('GET', '/api/position/businesses/'.$business->getId());
        self::assertResponseIsSuccessful();
    }

    /**
     * @test
     */
    public function it_returns_404_for_business_created_by_another_user(): void
    {
        $business = $this->assetBusinessRepository->findBy(['createdBy' => $this->getUser('user3')])[0];
        $this->client->request('GET', '/api/position/businesses/'.$business->getId());
        self::assertResponseStatusCodeSame(404);
    }

    /**
     * @test
     */
    public function it_shows_errors_for_empty_create_dto(): void
    {
        $this->client->request('POST', '/api/position/businesses', ['json' => []]);
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
                    'propertyPath' => 'ownership',
                    'message' => 'This value should not be blank.',
                ],
                2 => [
                    'propertyPath' => 'originalDate',
                    'message' => 'This value should not be blank.',
                ],
                3 => [
                    'propertyPath' => 'originalValue',
                    'message' => 'This value should not be blank.',
                ],
                4 => [
                    'propertyPath' => 'currentValue',
                    'message' => 'This value should not be blank.',
                ],
                5 => [
                    'propertyPath' => 'valueDate',
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
            'name' => $this->faker->realTextBetween(10, 150),
            'description' => $this->faker->realTextBetween(10, 150),
            'website' => $this->faker->url(),
            'ownership' => $this->faker->randomFloat(1, 80, 100),
            'originalValue' => strval($this->faker->numberBetween(40, 100) * 1000),
            'originalDate' => $this->faker->dateTimeBetween('-5 years', '-1 year')->format('Y-m-d'),
            'valueDate' => $this->faker->dateTimeBetween('-2 months', '-1 day')->format('Y-m-d'),
        ];
        $data['currentValue'] = strval($data['originalValue'] * $this->faker->randomFloat(2, 1.10, 1.30));

        $response = $this->client->request('POST', '/api/position/businesses', ['json' => $data]);
        self::assertResponseStatusCodeSame(201);
        /** @var PositionBusiness $position */
        $position = $this->positionBusinessRepository->find($response->toArray()['id']);
        $asset = $position->getAsset();
        self::assertGreaterThanOrEqual(1, count($position->getTags()));
        self::assertEquals($data['notes'], $position->getNotes());
        self::assertEquals($data['website'], $asset->getWebsite());
        self::assertEquals($data['name'], $asset->getName());
        self::assertEquals($data['description'], $asset->getDescription());
        self::assertEquals('Private Business', $asset->getAssetType()->getName());
    }

    /**
     * @test
     */
    public function it_shows_errors_for_empty_name_in_update_dto(): void
    {
        $position = $this->getRandomPosition($this->getUser('user2'));
        $this->client->request('PATCH', '/api/position/businesses/'.$position->getId(), [
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
            'description' => $this->faker->realTextBetween(10, 150),
            'website' => $this->faker->url(),
            'ownership' => $this->faker->randomFloat(1, 80, 100),
        ];
        $this->client->request('PATCH', '/api/position/businesses/'.$position->getId(), [
            'json' => $data,
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);
        $this->assertResponseStatusCodeSame(200);
        $position = $this->positionBusinessRepository->find($position->getId());
        $asset = $position->getAsset();
        self::assertEquals($data['notes'], $position->getNotes());
        self::assertEquals($data['website'], $asset->getWebsite());
        self::assertEquals($data['name'], $asset->getName());
        self::assertEquals($data['description'], $asset->getDescription());
    }

    /**
     * @test
     */
    public function it_deletes_position_and_asset(): void
    {
        $position = $this->getRandomPosition($this->getUser('user2'));
        $id = $position->getId();
        $assetId = $position->getAsset()->getId();
        self::assertNotNull($this->assetBusinessRepository->find($assetId));
        $this->client->request('DELETE', '/api/position/businesses/'.$position->getId());
        $this->assertResponseStatusCodeSame(204);
        self::assertNull($this->positionBusinessRepository->find($id));
        self::assertNull($this->assetBusinessRepository->find($assetId));
    }

    private function getRandomPosition(User $user): PositionBusiness
    {
        $positions = $this->positionBusinessRepository->findBy(['createdBy' => $user]);

        return $positions[array_rand($positions)];
    }

    private function getRandomTags(User $user, int $count = 1): array
    {
        return $this->faker->randomElements($this->tagRepository->findBy(['createdBy' => $user]), $count);
    }
}
