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
use Groshy\Domain\Enum\SecurityType;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(uriTemplate: '/securities/{id}', normalizationContext: ['groups' => ['asset:item:read'], 'swagger_definition_name' => 'Item Read']),
        new GetCollection(uriTemplate: '/securities', normalizationContext: ['groups' => ['asset:collection:read'], 'swagger_definition_name' => 'Collection Read']), ],
    routePrefix: '/asset',
    order: ['symbol' => 'ASC']
)]
#[Entity]
#[Index(columns: ['symbol'])]
#[Index(columns: ['security_type'])]
#[ApiFilter(filterClass: SearchFilter::class, properties: ['symbol' => 'partial'])]
class AssetSecurity extends Asset
{
    #[Column(type: 'string', length: 20, nullable: false, enumType: SecurityType::class)]
    #[Groups(['asset:item:read', 'asset:collection:read'])]
    protected ?SecurityType $securityType = null;

    #[Column(type: 'string', length: 10, nullable: false)]
    #[Groups(['asset:item:read', 'asset:collection:read'])]
    protected ?string $symbol = null;

    public function createConfig(): AssetConfig
    {
        return new AssetConfig(Privacy::PUBLIC, false);
    }

    public function getSecurityType(): ?SecurityType
    {
        return $this->securityType;
    }

    public function setSecurityType(?SecurityType $securityType): void
    {
        $this->securityType = $securityType;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(?string $symbol): void
    {
        $this->symbol = $symbol;
    }
}
