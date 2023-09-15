<?php

declare(strict_types=1);

namespace Groshy\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Groshy\Domain\Enum\Privacy;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(uriTemplate: '/businesses/{id}', normalizationContext: ['groups' => ['asset:item:read'], 'swagger_definition_name' => 'Item Read']),
        new GetCollection(uriTemplate: '/businesses', normalizationContext: ['groups' => ['asset:collection:read'], 'swagger_definition_name' => 'Collection Read']),
    ],
    routePrefix: '/asset'
)]
#[Entity]
class AssetBusiness extends Asset
{
    #[Column(type: 'object', nullable: false)]
    protected AssetBusinessData $data;

    public function __construct()
    {
        $this->data = new AssetBusinessData();
        parent::__construct();
    }

    public function getData(): AssetBusinessData
    {
        return $this->data;
    }

    public function setData(AssetBusinessData $data): void
    {
        $this->data = $data;
    }

    #[Groups(['asset:item:read', 'asset:collection:read'])]
    public function getWebsite(): ?string
    {
        return $this->data->getWebsite();
    }

    #[Groups(['asset:item:read', 'asset:collection:read'])]
    public function getDescription(): ?string
    {
        return $this->data->getDescription();
    }

    #[Groups(['asset:item:read', 'asset:collection:read'])]
    public function getOwnership(): ?float
    {
        return $this->data->getOwnership();
    }

    public function createConfig(): AssetConfig
    {
        return new AssetConfig(Privacy::PRIVATE, false);
    }
}
