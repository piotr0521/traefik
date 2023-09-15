<?php

declare(strict_types=1);

namespace Groshy\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Groshy\Domain\Enum\Privacy;
use Groshy\Domain\Enum\PropertyType;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(uriTemplate: '/properties/{id}', normalizationContext: ['groups' => ['asset:item:read'], 'swagger_definition_name' => 'Item Read']),
        new GetCollection(uriTemplate: '/properties', normalizationContext: ['groups' => ['asset:collection:read'], 'swagger_definition_name' => 'Collection Read']), ],
    routePrefix: '/asset'
)]
#[Entity]
class AssetProperty extends Asset
{
    #[Column(type: 'object', nullable: false)]
    protected AssetPropertyData $data;

    public function __construct()
    {
        $this->data = new AssetPropertyData();
        parent::__construct();
    }

    public function getData(): AssetPropertyData
    {
        return $this->data;
    }

    public function setData(AssetPropertyData $data): void
    {
        $this->data = $data;
    }

    #[Groups(['asset:item:read', 'asset:collection:read'])]
    public function getPropertyType(): ?PropertyType
    {
        return $this->data->getPropertyType();
    }

    #[Groups(['asset:item:read', 'asset:collection:read'])]
    public function getWebsite(): ?string
    {
        return $this->data->getWebsite();
    }

    #[Groups(['asset:item:read', 'asset:collection:read'])]
    public function getAddress(): ?string
    {
        return $this->data->getAddress();
    }

    #[Groups(['asset:item:read', 'asset:collection:read'])]
    public function getUnits(): ?int
    {
        return $this->data->getUnits();
    }

    public function createConfig(): AssetConfig
    {
        return new AssetConfig(Privacy::PRIVATE, false);
    }
}
