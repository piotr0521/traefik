<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Groshy\Tests\Helper\UsersAwareTrait;

class LiabilityLoanTest extends ApiTestCase
{
    use UsersAwareTrait;

    private ?Client $client;

    protected function setUp(): void
    {
        $this->markTestSkipped();
        $this->client = static::createClient();

        $this->setUpUsers($this->client->getContainer());
        $this->client->loginUser($this->getUser('user2'));
    }

    /**
     * @test
     */
    public function it_only_returns_one_mortgage_liability_for_user(): void
    {
        $response = $this->client->request('GET', '/api/liability/loans');
        self::assertCount(1, $response->toArray()['hydra:member']);
    }

    /**
     * @test
     */
    public function it_returns_mortgage_liability_details(): void
    {
        $response = $this->client->request('GET', '/api/liability/loans');
        $this->client->request('GET', $response->toArray()['hydra:member'][0]['@id']);
        $this->assertJsonContains([
            'name' => 'Loan',
        ]);
    }
}
