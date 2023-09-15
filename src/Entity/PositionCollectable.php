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
use Doctrine\ORM\Mapping\Entity;
use Groshy\Message\Command\PositionCollectable\CreatePositionCollectableCommand;
use Groshy\Message\Command\PositionCollectable\DeletePositionCollectableCommand;
use Groshy\Message\Command\PositionCollectable\UpdatePositionCollectableCommand;
use Groshy\Message\Dto\PositionCollectable\CreatePositionCollectableDto;
use Groshy\Message\Dto\PositionCollectable\UpdatePositionCollectableDto;
use Groshy\Presentation\Api\Doctrine\Orm\Filter\AssetTypeFilter;
use Groshy\Presentation\Api\Dto\PositionCollectable\ApiCreatePositionCollectableDto;
use Groshy\Presentation\Api\Dto\PositionCollectable\ApiUpdatePositionCollectableDto;
use Groshy\Presentation\Api\State\ResourceStateProcessor;

#[ApiResource(
    operations: [
        new Get(uriTemplate: '/collectables/{id}.{_format}', normalizationContext: ['groups' => ['position:item:read'], 'swagger_definition_name' => 'Item Read']),
        new Patch(uriTemplate: '/collectables/{id}.{_format}', input: ['class' => ApiUpdatePositionCollectableDto::class, 'transform' => ['dto' => UpdatePositionCollectableDto::class, 'command' => UpdatePositionCollectableCommand::class]], processor: ResourceStateProcessor::class),
        new Delete(uriTemplate: '/collectables/{id}.{_format}', input: ['transform' => ['command' => DeletePositionCollectableCommand::class]], processor: ResourceStateProcessor::class),
        new Post(uriTemplate: '/collectables.{_format}', input: ['class' => ApiCreatePositionCollectableDto::class, 'transform' => ['dto' => CreatePositionCollectableDto::class, 'command' => CreatePositionCollectableCommand::class]], processor: ResourceStateProcessor::class),
        new GetCollection(uriTemplate: '/collectables.{_format}', normalizationContext: ['groups' => ['position:collection:read'], 'swagger_definition_name' => 'Collection Read']), ],
    routePrefix: '/position'
)]
#[Entity]
#[ApiFilter(filterClass: OrderFilter::class, properties: ['startDate' => 'DESC'], arguments: ['orderParameterName' => 'order'])]
#[ApiFilter(filterClass: AssetTypeFilter::class, properties: ['path' => 'asset.assetType'])]
class PositionCollectable extends Position
{
}
