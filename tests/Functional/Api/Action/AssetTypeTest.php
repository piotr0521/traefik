<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Groshy\Tests\Helper\UsersAwareTrait;
use Talav\Component\Resource\Repository\RepositoryInterface;

class AssetTypeTest extends ApiTestCase
{
    use UsersAwareTrait;

    private ?Client $client;

    private ?RepositoryInterface $assetTypeRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->setUpUsers($this->client->getContainer());
        $this->client->loginUser($this->getUser('user1'));

        $this->assetTypeRepository = $this->client->getContainer()->get('app.repository.asset_type');
    }

    /**
     * @test
     */
    public function it_returns_active_asset_types(): void
    {
        $this->client->request('GET', '/api/asset_types');
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'hydra:totalItems' => $this->countActiveAssetTypes(),
        ]);
    }

    private function countActiveAssetTypes(): int
    {
        return (int) $this->assetTypeRepository
            ->createQueryBuilder('type')
            ->select('COUNT(DISTINCT type.id)')
            ->andWhere('type.isActive = :isActive')
            ->setParameter('isActive', true)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
