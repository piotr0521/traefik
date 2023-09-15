<?php

declare(strict_types=1);

namespace Groshy\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping\Entity;
use Groshy\Domain\Enum\Privacy;

#[ApiResource(
    operations: [
        new Get(uriTemplate: '/collectables/{id}', normalizationContext: ['groups' => ['asset:item:read'], 'swagger_definition_name' => 'Item Read']),
        new GetCollection(uriTemplate: '/collectables', normalizationContext: ['groups' => ['asset:collection:read'], 'swagger_definition_name' => 'Collection Read']), ],
    routePrefix: '/asset'
)]
#[Entity]
class AssetCollectable extends Asset
{
    public function createConfig(): AssetConfig
    {
        return new AssetConfig(Privacy::PUBLIC, false);
    }
}
