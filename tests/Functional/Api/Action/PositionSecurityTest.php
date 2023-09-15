<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Entity\AssetSecurity;
use Groshy\Entity\PositionSecurity;
use Groshy\Entity\Tag;
use Groshy\Entity\User;
use Groshy\Model\MoneyAwareTrait;
use Groshy\Tests\Helper\UsersAwareTrait;
use Talav\Component\Resource\Repository\RepositoryInterface;

class PositionSecurityTest extends ApiTestCase
{
    use UsersAwareTrait;
    use PositionTestTrait;
    use MoneyAwareTrait;

    private ?Generator $faker;
    private ?Client $client;

    private ?RepositoryInterface $assetTypeRepository;
    private ?RepositoryInterface $positionSecurityRepository;
    private ?RepositoryInterface $assetSecurityRepository;
    private ?RepositoryInterface $tagRepository;

    protected function setUp(): void
    {
        $this->markTestSkipped();
        $this->client = static::createClient();
        $this->faker = FakerFactory::create();

        $this->setUpUsers($this->client->getContainer());
        $this->client->loginUser($this->getUser('user2'));

        $this->assetTypeRepository = $this->client->getContainer()->get('app.repository.asset_type');
        $this->positionSecurityRepository = $this->client->getContainer()->get('app.repository.position_security');
        $this->assetSecurityRepository = $this->client->getContainer()->get('app.repository.asset_security');
        $this->tagRepository = $this->client->getContainer()->get('app.repository.tag');
    }

    /**
     * @test
     */
    public function it_shows_errors_for_empty_create_dto(): void
    {
        $response = $this->client->request('POST', '/api/position/securities', ['json' => []]);
        self::assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => 'purchaseDate',
                    'message' => 'This value should not be blank.',
                ],
                1 => [
                    'propertyPath' => 'quantity',
                    'message' => 'This value should not be blank.',
                ],
                2 => [
                    'propertyPath' => 'averagePrice',
                    'message' => 'This value should not be blank.',
                ],
                3 => [
                    'propertyPath' => 'asset',
                    'message' => 'This value should not be blank.',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_creates_new_position(): void
    {
        $tags = $this->getRandomTags($this->getUser('user2'), $this->faker->numberBetween(1, 3));
        $symbol = 'AAPL';
        $data = [
            'asset' => static::findIriBy(AssetSecurity::class, ['symbol' => $symbol]),
            'tags' => array_map(function ($el) {return static::findIriBy(Tag::class, ['id' => $el->getId()]); }, $tags),
            'notes' => $this->faker->text(200),
            'purchaseDate' => $this->faker->dateTimeBetween('-5 years', '-1 year')->format('Y-m-d'),
            'quantity' => $this->faker->numberBetween(5, 10),
            'averagePrice' => strval($this->faker->randomFloat(2, '100', '300')),
        ];

        $response = $this->client->request('POST', '/api/position/securities', ['json' => $data]);
        self::assertResponseStatusCodeSame(201);
        /** @var PositionSecurity $position */
        $position = $this->positionSecurityRepository->find($response->toArray()['id']);
        self::assertGreaterThanOrEqual(1, count($position->getTags()));
        self::assertEquals($symbol, $position->getAsset()->getSymbol());
        self::assertEquals($data['notes'], $position->getNotes());
        self::assertEquals($data['quantity'], $position->getLastValue()->getQuantity());
        self::assertEquals($data['quantity'] * $data['averagePrice'] * 100, $position->getLastValue()->getValue()->getAmount());
    }

    /**
     * @test
     */
    public function it_only_returns_positions_security_created_by_the_current_user(): void
    {
        $this->trait_it_only_returns_positions_created_by_the_current_user('/api/position/securities', $this->positionSecurityRepository);
    }

    /**
     * @test
     */
    public function it_allows_to_get_position_security_by_id(): void
    {
        $security = $this->positionSecurityRepository->findBy(['createdBy' => $this->getUser('user2')])[0];
        $this->client->request('GET', '/api/position/securities/'.$security->getId());
        self::assertResponseIsSuccessful();
    }

    /**
     * @test
     */
    public function it_returns_404_for_security_created_by_another_user(): void
    {
        $security = $this->positionSecurityRepository->findBy(['createdBy' => $this->getUser('user1')])[0];
        $this->client->request('GET', '/api/position/securities/'.$security->getId());
        self::assertResponseStatusCodeSame(404);
    }

    /**
     * @test
     */
    public function it_updates_position(): void
    {
        $position = $this->getRandomPosition($this->getUser('user2'));
        $tags = $this->getRandomTags($this->getUser('user2'), $this->faker->numberBetween(1, 3));
        $data = [
            'tags' => array_map(function ($el) {return static::findIriBy(Tag::class, ['id' => $el->getId()]); }, $tags),
            'notes' => $this->faker->text(200),
        ];
        $this->client->request('PATCH', '/api/position/securities/'.$position->getId(), [
            'json' => $data,
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);
        $this->assertResponseStatusCodeSame(200);
        $position = $this->positionSecurityRepository->find($position->getId());
        self::assertEquals($data['notes'], $position->getNotes());
    }

    /**
     * @test
     */
    public function it_deletes_position(): void
    {
        $position = $this->getRandomPosition($this->getUser('user2'));
        $id = $position->getId();
        $this->client->request('DELETE', '/api/position/securities/'.$position->getId());
        $this->assertResponseStatusCodeSame(204);
        self::assertNull($this->positionSecurityRepository->find($id));
    }

    private function getRandomPosition(User $user): PositionSecurity
    {
        $positions = $this->positionSecurityRepository->findBy(['createdBy' => $user]);

        return $positions[array_rand($positions)];
    }

    private function getRandomTags(User $user, int $count = 1): array
    {
        return $this->faker->randomElements($this->tagRepository->findBy(['createdBy' => $user]), $count);
    }
}
