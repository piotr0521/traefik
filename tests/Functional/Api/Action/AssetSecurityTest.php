<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Groshy\Tests\Helper\UsersAwareTrait;
use Talav\Component\Resource\Repository\RepositoryInterface;

class AssetSecurityTest extends ApiTestCase
{
    use UsersAwareTrait;

    private ?Client $client;

    private RepositoryInterface $assetSecurityRepository;

    protected function setUp(): void
    {
        $this->markTestSkipped();
        $this->client = static::createClient();

        $this->setUpUsers($this->client->getContainer());
        $this->client->loginUser($this->getUser('user2'));

        $this->assetSecurityRepository = $this->client->getContainer()->get('app.repository.asset_security');
    }

    /**
     * @test
     */
    public function it_returns_all_assets(): void
    {
        $response = $this->client->request('GET', '/api/asset/securities');
        self::assertEquals($this->assetSecurityRepository->createPaginator()->count(), $response->toArray()['hydra:totalItems']);
    }

    /**
     * @test
     */
    public function it_returns_asset_security_details(): void
    {
        $response = $this->client->request('GET', '/api/asset/securities');
        $this->client->request('GET', $response->toArray()['hydra:member'][0]['@id']);
        $this->assertJsonContains([
            'name' => 'Agilent Technologies Inc. Common Stock',
            'symbol' => 'A',
        ]);
    }
}
