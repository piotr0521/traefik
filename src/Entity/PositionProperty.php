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
use Groshy\Message\Command\PositionProperty\CreatePositionPropertyCommand;
use Groshy\Message\Command\PositionProperty\DeletePositionPropertyCommand;
use Groshy\Message\Command\PositionProperty\UpdatePositionPropertyCommand;
use Groshy\Message\Dto\PositionProperty\CreatePositionPropertyDto;
use Groshy\Message\Dto\PositionProperty\UpdatePositionPropertyDto;
use Groshy\Presentation\Api\Doctrine\Orm\Filter\AssetTypeFilter;
use Groshy\Presentation\Api\Dto\PositionProperty\ApiCreatePositionPropertyDto;
use Groshy\Presentation\Api\Dto\PositionProperty\ApiUpdatePositionPropertyDto;
use Groshy\Presentation\Api\State\ResourceStateProcessor;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(uriTemplate: '/properties/{id}.{_format}', normalizationContext: ['groups' => ['position:item:read'], 'swagger_definition_name' => 'Item Read']),
        new Patch(uriTemplate: '/properties/{id}.{_format}', input: ['class' => ApiUpdatePositionPropertyDto::class, 'transform' => ['dto' => UpdatePositionPropertyDto::class, 'command' => UpdatePositionPropertyCommand::class]], processor: ResourceStateProcessor::class),
        new Delete(uriTemplate: '/properties/{id}.{_format}', input: ['transform' => ['command' => DeletePositionPropertyCommand::class]], processor: ResourceStateProcessor::class),
        new Post(uriTemplate: '/properties.{_format}', input: ['class' => ApiCreatePositionPropertyDto::class, 'transform' => ['dto' => CreatePositionPropertyDto::class, 'command' => CreatePositionPropertyCommand::class]], processor: ResourceStateProcessor::class),
        new GetCollection(uriTemplate: '/properties.{_format}', normalizationContext: ['groups' => ['position:collection:read'], 'swagger_definition_name' => 'Collection Read']), ],
    routePrefix: '/position'
)]
#[Entity]
#[ApiFilter(filterClass: OrderFilter::class, properties: ['startDate' => 'DESC'], arguments: ['orderParameterName' => 'order'])]
#[ApiFilter(filterClass: AssetTypeFilter::class, properties: ['path' => 'asset.assetType'])]
class PositionProperty extends Position
{
    #[Groups(['position:item:read', 'position:collection:read'])]
    public function getName(): ?string
    {
        return $this->asset ? $this->asset->getData()->getAddress() : '';
    }
}
