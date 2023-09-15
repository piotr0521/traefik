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
use Groshy\Message\Command\PositionBusiness\CreatePositionBusinessCommand;
use Groshy\Message\Command\PositionBusiness\DeletePositionBusinessCommand;
use Groshy\Message\Command\PositionBusiness\UpdatePositionBusinessCommand;
use Groshy\Message\Dto\PositionBusiness\CreatePositionBusinessDto;
use Groshy\Message\Dto\PositionBusiness\UpdatePositionBusinessDto;
use Groshy\Presentation\Api\Doctrine\Orm\Filter\AssetTypeFilter;
use Groshy\Presentation\Api\Dto\PositionBusiness\ApiCreatePositionBusinessDto;
use Groshy\Presentation\Api\Dto\PositionBusiness\ApiUpdatePositionBusinessDto;
use Groshy\Presentation\Api\State\ResourceStateProcessor;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(uriTemplate: '/businesses/{id}.{_format}', normalizationContext: ['groups' => ['position:item:read'], 'swagger_definition_name' => 'Item Read']),
        new Patch(uriTemplate: '/businesses/{id}.{_format}', input: ['class' => ApiUpdatePositionBusinessDto::class, 'transform' => ['dto' => UpdatePositionBusinessDto::class, 'command' => UpdatePositionBusinessCommand::class]], processor: ResourceStateProcessor::class),
        new Delete(uriTemplate: '/businesses/{id}.{_format}', input: ['transform' => ['command' => DeletePositionBusinessCommand::class]], processor: ResourceStateProcessor::class),
        new Post(uriTemplate: '/businesses.{_format}', input: ['class' => ApiCreatePositionBusinessDto::class, 'transform' => ['dto' => CreatePositionBusinessDto::class, 'command' => CreatePositionBusinessCommand::class]], processor: ResourceStateProcessor::class),
        new GetCollection(uriTemplate: '/businesses.{_format}', normalizationContext: ['groups' => ['position:collection:read'], 'swagger_definition_name' => 'Collection Read']), ],
    routePrefix: '/position'
)]
#[Entity]
#[ApiFilter(filterClass: OrderFilter::class, properties: ['startDate' => 'DESC'], arguments: ['orderParameterName' => 'order'])]
#[ApiFilter(filterClass: AssetTypeFilter::class, properties: ['path' => 'asset.assetType'])]
class PositionBusiness extends Position
{
    #[Groups(['position:item:read', 'position:collection:read'])]
    public function getName(): ?string
    {
        return $this->asset ? $this->asset->getName() : '';
    }
}
