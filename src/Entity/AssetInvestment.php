<?php

declare(strict_types=1);

namespace Groshy\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Groshy\Domain\Enum\Privacy;
use Groshy\Message\Command\AssetInvestment\CreateAssetInvestmentCommand;
use Groshy\Message\Command\AssetInvestment\UpdateAssetInvestmentCommand;
use Groshy\Message\Dto\AssetInvestment\CreateAssetInvestmentDto;
use Groshy\Message\Dto\AssetInvestment\UpdateAssetInvestmentDto;
use Groshy\Presentation\Api\Dto\AssetInvestment\ApiCreateAssetInvestmentDto;
use Groshy\Presentation\Api\Dto\AssetInvestment\ApiUpdateAssetInvestmentDto;
use Groshy\Presentation\Api\State\ResourceStateProcessor;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(uriTemplate: '/investments/{id}', normalizationContext: ['groups' => ['asset:item:read'], 'swagger_definition_name' => 'Item Read']),
        new Patch(uriTemplate: '/investments/{id}', input: ['class' => ApiUpdateAssetInvestmentDto::class, 'transform' => ['dto' => UpdateAssetInvestmentDto::class, 'command' => UpdateAssetInvestmentCommand::class]], processor: ResourceStateProcessor::class),
        new Post(uriTemplate: '/investments', input: ['class' => ApiCreateAssetInvestmentDto::class, 'transform' => ['dto' => CreateAssetInvestmentDto::class, 'command' => CreateAssetInvestmentCommand::class]], processor: ResourceStateProcessor::class),
        new GetCollection(uriTemplate: '/investments', normalizationContext: ['groups' => ['asset:collection:read'], 'swagger_definition_name' => 'Collection Read']), ],
    routePrefix: '/asset'
)]
#[Entity]
#[ApiFilter(filterClass: SearchFilter::class, properties: ['sponsor' => 'exact'])]
class AssetInvestment extends Asset
{
    #[Column(type: 'object', nullable: false)]
    protected AssetInvestmentData $data;

    public function __construct()
    {
        $this->data = new AssetInvestmentData();
        parent::__construct();
    }

    public function getData(): AssetInvestmentData
    {
        return $this->data;
    }

    public function setData(AssetInvestmentData $data): void
    {
        $this->data = $data;
    }

    #[Groups(['asset:item:read', 'asset:collection:read'])]
    public function getWebsite(): ?string
    {
        return $this->data->getWebsite();
    }

    #[Groups(['asset:item:read', 'asset:collection:read'])]
    public function isEvergreen(): bool
    {
        return $this->data->isEvergreen();
    }

    #[Groups(['asset:item:read', 'asset:collection:read'])]
    public function getTerm(): ?string
    {
        return $this->data->getTerm();
    }

    #[Groups(['asset:item:read', 'asset:collection:read'])]
    public function getIrr(): ?string
    {
        return $this->data->getIrr();
    }

    #[Groups(['asset:item:read', 'asset:collection:read'])]
    public function getMultiple(): ?string
    {
        return $this->data->getMultiple();
    }

    public function createConfig(): AssetConfig
    {
        return new AssetConfig(Privacy::PRIVATE, true);
    }
}
