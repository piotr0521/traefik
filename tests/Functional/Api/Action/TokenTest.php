<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Groshy\Tests\Helper\UsersAwareTrait;
use Talav\Component\Resource\Repository\RepositoryInterface;
use TomorrowIdeas\Plaid\Plaid;

class TokenTest extends ApiTestCase
{
    use UsersAwareTrait;

    private ?Client $client;
    private ?Plaid $plaid;

    private RepositoryInterface $plaidItemRepository;

    protected function setUp(): void
    {
        $this->markTestSkipped();
        $this->client = static::createClient();
        $this->setUpUsers($this->client->getContainer());
        $this->client->loginUser($this->getUser('user2'));
        $this->plaid = $this->client->getContainer()->get(Plaid::class);
        $this->plaidItemRepository = $this->client->getContainer()->get('app.repository.plaid_connection');
    }

    /**
     * @test
     */
    public function it_creates_new_plaid_token(): void
    {
        $this->client->request('POST', '/api/tokens', ['json' => []]);
        $this->assertResponseStatusCodeSame(201);
    }

    /**
     * @test
     */
    public function it_exchanges_public_plaid_token(): void
    {
        $link = $this->plaid->sandbox->createPublicToken('ins_56', ['transactions'])->public_token;
        $this->client->request('GET', '/api/tokens/exchange/'.$link);
        $this->assertResponseStatusCodeSame(200);
        $items = $this->plaidItemRepository->findBy(['createdBy' => $this->getUser('user2')]);
        self::assertGreaterThanOrEqual(1, count($items));
    }
}
