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
use Groshy\Message\Command\PositionSecurity\CreatePositionSecurityCommand;
use Groshy\Message\Command\PositionSecurity\DeletePositionSecurityCommand;
use Groshy\Message\Command\PositionSecurity\UpdatePositionSecurityCommand;
use Groshy\Message\Dto\PositionSecurity\CreatePositionSecurityDto;
use Groshy\Message\Dto\PositionSecurity\UpdatePositionSecurityDto;
use Groshy\Presentation\Api\Doctrine\Orm\Filter\AssetTypeFilter;
use Groshy\Presentation\Api\Dto\PositionSecurity\ApiCreatePositionSecurityDto;
use Groshy\Presentation\Api\Dto\PositionSecurity\ApiUpdatePositionSecurityDto;
use Groshy\Presentation\Api\State\ResourceStateProcessor;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(uriTemplate: '/securities/{id}.{_format}', normalizationContext: ['groups' => ['position:item:read'], 'swagger_definition_name' => 'Item Read']),
        new Patch(uriTemplate: '/securities/{id}.{_format}', input: ['class' => ApiUpdatePositionSecurityDto::class, 'transform' => ['dto' => UpdatePositionSecurityDto::class, 'command' => UpdatePositionSecurityCommand::class]], processor: ResourceStateProcessor::class),
        new Delete(uriTemplate: '/securities/{id}.{_format}', input: ['transform' => ['command' => DeletePositionSecurityCommand::class]], processor: ResourceStateProcessor::class),
        new Post(uriTemplate: '/securities.{_format}', input: ['class' => ApiCreatePositionSecurityDto::class, 'transform' => ['dto' => CreatePositionSecurityDto::class, 'command' => CreatePositionSecurityCommand::class]], processor: ResourceStateProcessor::class),
        new GetCollection(uriTemplate: '/securities.{_format}', normalizationContext: ['groups' => ['position:collection:read'], 'swagger_definition_name' => 'Collection Read']), ],
    routePrefix: '/position'
)]
#[Entity]
#[ApiFilter(filterClass: OrderFilter::class, properties: ['startDate' => 'DESC'], arguments: ['orderParameterName' => 'order'])]
#[ApiFilter(filterClass: AssetTypeFilter::class, properties: ['path' => 'asset.assetType'])]
class PositionSecurity extends Position
{
    #[Groups(['position:item:read', 'position:collection:read'])]
    public function getName(): ?string
    {
        return $this->asset ? $this->asset->getName() : '';
    }

    #[Groups(['position:item:read', 'position:collection:read'])]
    public function getSymbol(): ?string
    {
        return $this->asset ? $this->asset->getSymbol() : '';
    }
}
