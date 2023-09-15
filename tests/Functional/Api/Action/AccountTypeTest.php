<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Groshy\Tests\Helper\UsersAwareTrait;
use Talav\Component\Resource\Repository\RepositoryInterface;

class AccountTypeTest extends ApiTestCase
{
    use UsersAwareTrait;

    private ?Client $client;

    private ?RepositoryInterface $accountTypeRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->setUpUsers($this->client->getContainer());
        $this->client->loginUser($this->getUser('user1'));

        $this->accountTypeRepository = $this->client->getContainer()->get('app.repository.account_type');
    }

    /**
     * @test
     */
    public function it_returns_account_type(): void
    {
        $type = $this->accountTypeRepository->findOneBy(['name' => 'Mortgage']);
        $response = $this->client->request('GET', '/api/account_types/'.$type->getId());
        $this->assertResponseIsSuccessful();
        $data = $response->toArray(false);
        self::assertArrayHasKey('id', $data);
        self::assertArrayHasKey('name', $data);
        self::assertArrayHasKey('description', $data);
        self::assertArrayHasKey('slug', $data);
        self::assertArrayHasKey('parent', $data);
    }

    /**
     * @test
     */
    public function it_returns_all_account_types(): void
    {
        $this->client->request('GET', '/api/account_types');
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'hydra:totalItems' => $this->countAccountTypes(),
        ]);
    }

    private function countAccountTypes(): int
    {
        return (int) $this->accountTypeRepository
            ->createQueryBuilder('type')
            ->select('COUNT(DISTINCT type.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
