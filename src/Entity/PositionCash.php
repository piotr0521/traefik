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
use Groshy\Message\Command\PositionCash\CreatePositionCashCommand;
use Groshy\Message\Command\PositionCash\DeletePositionCashCommand;
use Groshy\Message\Command\PositionCash\UpdatePositionCashCommand;
use Groshy\Message\Dto\PositionCash\CreatePositionCashDto;
use Groshy\Message\Dto\PositionCash\UpdatePositionCashDto;
use Groshy\Presentation\Api\Doctrine\Orm\Filter\AssetTypeFilter;
use Groshy\Presentation\Api\Dto\PositionCash\ApiCreatePositionCashDto;
use Groshy\Presentation\Api\Dto\PositionCash\ApiUpdatePositionCashDto;
use Groshy\Presentation\Api\State\ResourceStateProcessor;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(uriTemplate: '/cash/{id}.{_format}', normalizationContext: ['groups' => ['position:item:read'], 'swagger_definition_name' => 'Item Read']),
        new Patch(uriTemplate: '/cash/{id}.{_format}', input: ['class' => ApiUpdatePositionCashDto::class, 'transform' => ['dto' => UpdatePositionCashDto::class, 'command' => UpdatePositionCashCommand::class]], processor: ResourceStateProcessor::class),
        new Delete(uriTemplate: '/cash/{id}.{_format}', input: ['transform' => ['command' => DeletePositionCashCommand::class]], processor: ResourceStateProcessor::class),
        new Post(uriTemplate: '/cash.{_format}', input: ['class' => ApiCreatePositionCashDto::class, 'transform' => ['dto' => CreatePositionCashDto::class, 'command' => CreatePositionCashCommand::class]], processor: ResourceStateProcessor::class),
        new GetCollection(uriTemplate: '/cash.{_format}', normalizationContext: ['groups' => ['position:collection:read'], 'swagger_definition_name' => 'Collection Read']), ],
    routePrefix: '/position'
)]
#[Entity]
#[ApiFilter(filterClass: OrderFilter::class, properties: ['startDate' => 'DESC'], arguments: ['orderParameterName' => 'order'])]
#[ApiFilter(filterClass: AssetTypeFilter::class, properties: ['path' => 'asset.assetType'])]
class PositionCash extends Position
{
    #[Column(type: 'object')]
    protected PositionCashAccountData $data;

    public function __construct()
    {
        $this->data = new PositionCashAccountData();
        parent::__construct();
    }

    public function getData(): PositionCashAccountData
    {
        return $this->data;
    }

    public function setData(PositionCashAccountData $data): void
    {
        $this->data = $data;
    }

    #[Groups(['position:item:read', 'position:collection:read'])]
    public function getYield(): ?float
    {
        return $this->getData()?->getYield();
    }

    #[Groups(['position:item:read', 'position:collection:read'])]
    public function getName(): ?string
    {
        return $this->account?->getName();
    }
}
