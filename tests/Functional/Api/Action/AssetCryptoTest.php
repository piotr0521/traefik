<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Groshy\Tests\Helper\UsersAwareTrait;
use Talav\Component\Resource\Repository\RepositoryInterface;

class AssetCryptoTest extends ApiTestCase
{
    use UsersAwareTrait;

    private ?Client $client;

    private RepositoryInterface $assetCryptoRepository;

    protected function setUp(): void
    {
        $this->markTestSkipped();
        $this->client = static::createClient();

        $this->setUpUsers($this->client->getContainer());
        $this->client->loginUser($this->getUser('user2'));

        $this->assetCryptoRepository = $this->client->getContainer()->get('app.repository.asset_crypto');
    }

    /**
     * @test
     */
    public function it_returns_all_assets(): void
    {
        $response = $this->client->request('GET', '/api/asset/crypto');
        self::assertEquals($this->assetCryptoRepository->createPaginator()->count(), $response->toArray()['hydra:totalItems']);
    }

    /**
     * @test
     */
    public function it_returns_asset_crypto_details(): void
    {
        $response = $this->client->request('GET', '/api/asset/crypto');
        $this->client->request('GET', $response->toArray()['hydra:member'][0]['@id']);
        $this->assertJsonContains([
            'name' => '2GIVE',
            'symbol' => '2GIVE',
        ]);
    }
}
