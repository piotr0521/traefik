<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Groshy\Tests\Helper\UsersAwareTrait;

class AssetCashTest extends ApiTestCase
{
    use UsersAwareTrait;

    private ?Client $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->setUpUsers($this->client->getContainer());
        $this->client->loginUser($this->getUser('user2'));
    }

    /**
     * @test
     */
    public function it_only_returns_one_asset_cash_for_user(): void
    {
        $response = $this->client->request('GET', '/api/asset/cash');
        self::assertCount(1, $response->toArray()['hydra:member']);
    }

    /**
     * @test
     */
    public function it_returns_asset_cash_details(): void
    {
        $response = $this->client->request('GET', '/api/asset/cash');
        $this->client->request('GET', $response->toArray()['hydra:member'][0]['@id']);
        $this->assertJsonContains([
            'name' => 'Cash',
            'assetType' => [
                'slug' => 'cash',
            ],
        ]);
    }
}
