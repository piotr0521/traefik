<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Entity\Price;
use Groshy\Tests\Helper\UsersAwareTrait;
use Talav\Component\Resource\Repository\RepositoryInterface;

class SubscriptionTest extends ApiTestCase
{
    use UsersAwareTrait;

    private ?Generator $faker;

    private ?Client $client;

    private ?RepositoryInterface $priceRepository;

    protected function setUp(): void
    {
        $this->faker = FakerFactory::create();
        $this->client = static::createClient();
        $this->setUpUsers($this->client->getContainer());

        $this->priceRepository = $this->client->getContainer()->get('app.repository.price');
        $this->client->loginUser($this->getUser('user2'));
    }

    /**
     * @test
     */
    public function it_creates_new_subscription(): void
    {
        $data = [
            'price' => static::findIriBy(Price::class, []),
        ];

        $response = $this->client->request('POST', '/api/subscriptions', ['json' => $data]);
        self::assertResponseStatusCodeSame(201);
        $data = $response->toArray(false);
        self::assertArrayHasKey('id', $data);
        self::assertArrayHasKey('clientSecret', $data);
    }
}
