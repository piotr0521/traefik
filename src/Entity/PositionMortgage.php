<?php

declare(strict_types=1);

namespace Groshy\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Groshy\Message\Command\PositionMortgage\CreatePositionMortgageCommand;
use Groshy\Message\Command\PositionMortgage\DeletePositionMortgageCommand;
use Groshy\Message\Command\PositionMortgage\UpdatePositionMortgageCommand;
use Groshy\Message\Dto\PositionMortgage\CreatePositionMortgageDto;
use Groshy\Message\Dto\PositionMortgage\UpdatePositionMortgageDto;
use Groshy\Presentation\Api\Doctrine\Orm\Filter\AssetTypeFilter;
use Groshy\Presentation\Api\Dto\PositionMortgage\ApiCreatePositionMortgageDto;
use Groshy\Presentation\Api\Dto\PositionMortgage\ApiUpdatePositionMortgageDto;
use Groshy\Presentation\Api\State\ResourceStateProcessor;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(uriTemplate: '/mortgages/{id}.{_format}', normalizationContext: ['groups' => ['position:item:read'], 'swagger_definition_name' => 'Item Read']),
        new Patch(uriTemplate: '/mortgages/{id}.{_format}', input: ['class' => ApiUpdatePositionMortgageDto::class, 'transform' => ['dto' => UpdatePositionMortgageDto::class, 'command' => UpdatePositionMortgageCommand::class]], processor: ResourceStateProcessor::class),
        new Delete(uriTemplate: '/mortgages/{id}.{_format}', input: ['transform' => ['command' => DeletePositionMortgageCommand::class]], processor: ResourceStateProcessor::class),
        new Post(uriTemplate: '/mortgages.{_format}', input: ['class' => ApiCreatePositionMortgageDto::class, 'transform' => ['dto' => CreatePositionMortgageDto::class, 'command' => CreatePositionMortgageCommand::class]], processor: ResourceStateProcessor::class),
        new GetCollection(uriTemplate: '/mortgages.{_format}', normalizationContext: ['groups' => ['position:collection:read'], 'swagger_definition_name' => 'Collection Read']), ],
    routePrefix: '/position'
)]
#[Entity]
#[ApiFilter(filterClass: OrderFilter::class, properties: ['startDate' => 'DESC'], arguments: ['orderParameterName' => 'order'])]
#[ApiFilter(filterClass: AssetTypeFilter::class, properties: ['path' => 'asset.assetType'])]
class PositionMortgage extends Position
{
    #[Column(type: 'object')]
    protected PositionMortgageData $data;

    public function __construct()
    {
        $this->data = new PositionMortgageData();
        parent::__construct();
    }

    public function getData(): PositionMortgageData
    {
        return $this->data;
    }

    public function setData(PositionMortgageData $data): void
    {
        $this->data = $data;
    }

    #[Groups(['position:item:read'])]
    public function getTerms(): ?int
    {
        return $this->data->getTerms();
    }

    #[Groups(['position:item:read'])]
    public function getInterest(): ?float
    {
        return $this->data->getInterest();
    }
}
