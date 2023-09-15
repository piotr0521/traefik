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
        new Get(uriTemplate: '/loans/{id}', normalizationContext: ['groups' => ['asset:item:read'], 'swagger_definition_name' => 'Item Read']),
        new GetCollection(uriTemplate: '/loans', normalizationContext: ['groups' => ['asset:collection:read'], 'swagger_definition_name' => 'Collection Read']), ],
    routePrefix: '/liability'
)]
#[Entity]
class LiabilityLoan extends Asset
{
    public function createConfig(): AssetConfig
    {
        return new AssetConfig(Privacy::PUBLIC, false);
    }
}
