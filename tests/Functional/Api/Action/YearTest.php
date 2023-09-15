<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Groshy\Tests\Helper\UsersAwareTrait;

class YearTest extends ApiTestCase
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
    public function it_returns_stats_for_years(): void
    {
        $this->client->request('GET', '/api/years/stats');
        $this->assertResponseStatusCodeSame(200);
    }
}
