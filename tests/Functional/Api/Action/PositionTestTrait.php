<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use Talav\Component\Resource\Repository\RepositoryInterface;

trait PositionTestTrait
{
    public function trait_it_only_returns_positions_created_by_the_current_user(string $endpoint, RepositoryInterface $repository): void
    {
        $result = $this->client->request('GET', $endpoint);
        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            'hydra:totalItems' => count($repository->findBy(['createdBy' => $this->getUser('user2')])),
        ]);
        foreach ($result->toArray()['hydra:member'] as $property) {
            $asset = $repository->find($property['id']);
            self::assertEquals($asset->getCreatedBy(), $this->getUser('user2'));
        }
    }
}
