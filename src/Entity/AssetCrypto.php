<?php

declare(strict_types=1);

namespace Groshy\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Index;
use Groshy\Domain\Enum\Privacy;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(uriTemplate: '/crypto/{id}', normalizationContext: ['groups' => ['asset:item:read'], 'swagger_definition_name' => 'Item Read']),
        new GetCollection(uriTemplate: '/crypto', normalizationContext: ['groups' => ['asset:collection:read'], 'swagger_definition_name' => 'Collection Read']), ],
    routePrefix: '/asset',
    order: ['symbol' => 'ASC']
)]
#[Entity]
#[Index(columns: ['symbol'])]
#[ApiFilter(filterClass: SearchFilter::class, properties: ['symbol' => 'partial'])]
class AssetCrypto extends Asset
{
    #[Column(type: 'string', length: 10, nullable: false)]
    #[Groups(['asset:item:read', 'asset:collection:read'])]
    protected ?string $symbol = null;

    public function createConfig(): AssetConfig
    {
        return new AssetConfig(Privacy::PUBLIC, false);
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): void
    {
        $this->symbol = $symbol;
    }
}
