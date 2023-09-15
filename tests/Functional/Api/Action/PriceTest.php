<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Groshy\Tests\Helper\UsersAwareTrait;
use Talav\Component\Resource\Repository\RepositoryInterface;

class PriceTest extends ApiTestCase
{
    use UsersAwareTrait;

    private ?Client $client;

    private ?RepositoryInterface $priceRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->setUpUsers($this->client->getContainer());
        $this->client->loginUser($this->getUser('user1'));

        $this->priceRepository = $this->client->getContainer()->get('app.repository.price');
    }

    /**
     * @test
     */
    public function it_returns_price(): void
    {
        $type = $this->priceRepository->findOneBy([]);
        $response = $this->client->request('GET', '/api/prices/'.$type->getId());
        $this->assertResponseIsSuccessful();
        $data = $response->toArray(false);
        self::assertArrayHasKey('id', $data);
        self::assertArrayHasKey('amount', $data);
        self::assertArrayHasKey('stripeId', $data);
        self::assertArrayHasKey('recurringInterval', $data);
        self::assertArrayHasKey('recurringIntervalCount', $data);
    }

    /**
     * @test
     */
    public function it_returns_all_prices(): void
    {
        $this->client->request('GET', '/api/prices');
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'hydra:totalItems' => 2,
        ]);
    }
}
