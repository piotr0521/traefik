<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Tests\Helper\UsersAwareTrait;
use Talav\Component\Resource\Repository\RepositoryInterface;

class AssetBusinessTest extends ApiTestCase
{
    use UsersAwareTrait;

    private ?Generator $faker;
    private ?Client $client;

    private RepositoryInterface $assetBusinessRepository;

    protected function setUp(): void
    {
        $this->markTestSkipped();
        $this->client = static::createClient();
        $this->faker = FakerFactory::create();
        $this->setUpUsers($this->client->getContainer());
        $this->client->loginUser($this->getUser('user2'));

        $this->assetBusinessRepository = $this->client->getContainer()->get('app.repository.asset_business');
    }

    /**
     * @test
     */
    public function it_only_returns_businesses_created_by_the_current_user(): void
    {
        $result = $this->client->request('GET', '/api/asset/businesses');
        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            'hydra:totalItems' => count($this->assetBusinessRepository->findBy(['createdBy' => $this->getUser('user2')])),
        ]);
        foreach ($result->toArray()['hydra:member'] as $business) {
            $asset = $this->assetBusinessRepository->find($business['id']);
            self::assertEquals($asset->getCreatedBy(), $this->getUser('user2'));
        }
    }

    /**
     * @test
     */
    public function it_allows_to_get_business_by_id(): void
    {
        $business = $this->assetBusinessRepository->findBy(['createdBy' => $this->getUser('user2')])[0];
        $this->client->request('GET', '/api/asset/businesses/'.$business->getId());
        self::assertResponseIsSuccessful();
    }

    /**
     * @test
     */
    public function it_returns_404_for_business_created_by_another_user(): void
    {
        $business = $this->assetBusinessRepository->findBy(['createdBy' => $this->getUser('user3')])[0];
        $this->client->request('GET', '/api/asset/businesses/'.$business->getId());
        self::assertResponseStatusCodeSame(404);
    }
}
