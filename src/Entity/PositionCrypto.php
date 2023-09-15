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
use Groshy\Message\Command\PositionCrypto\CreatePositionCryptoCommand;
use Groshy\Message\Command\PositionCrypto\DeletePositionCryptoCommand;
use Groshy\Message\Command\PositionCrypto\UpdatePositionCryptoCommand;
use Groshy\Message\Dto\PositionCrypto\CreatePositionCryptoDto;
use Groshy\Message\Dto\PositionCrypto\UpdatePositionCryptoDto;
use Groshy\Presentation\Api\Doctrine\Orm\Filter\AssetTypeFilter;
use Groshy\Presentation\Api\Dto\PositionCrypto\ApiCreatePositionCryptoDto;
use Groshy\Presentation\Api\Dto\PositionCrypto\ApiUpdatePositionCryptoDto;
use Groshy\Presentation\Api\State\ResourceStateProcessor;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(uriTemplate: '/crypto/{id}.{_format}', normalizationContext: ['groups' => ['position:item:read'], 'swagger_definition_name' => 'Item Read']),
        new Patch(uriTemplate: '/crypto/{id}.{_format}', input: ['class' => ApiUpdatePositionCryptoDto::class, 'transform' => ['dto' => UpdatePositionCryptoDto::class, 'command' => UpdatePositionCryptoCommand::class]], processor: ResourceStateProcessor::class),
        new Delete(uriTemplate: '/crypto/{id}.{_format}', input: ['transform' => ['command' => DeletePositionCryptoCommand::class]], processor: ResourceStateProcessor::class),
        new Post(uriTemplate: '/crypto.{_format}', input: ['class' => ApiCreatePositionCryptoDto::class, 'transform' => ['dto' => CreatePositionCryptoDto::class, 'command' => CreatePositionCryptoCommand::class]], processor: ResourceStateProcessor::class),
        new GetCollection(uriTemplate: '/crypto.{_format}', normalizationContext: ['groups' => ['position:collection:read'], 'swagger_definition_name' => 'Collection Read']), ],
    routePrefix: '/position'
)]
#[Entity]
#[ApiFilter(filterClass: OrderFilter::class, properties: ['startDate' => 'DESC'], arguments: ['orderParameterName' => 'order'])]
#[ApiFilter(filterClass: AssetTypeFilter::class, properties: ['path' => 'asset.assetType'])]
class PositionCrypto extends Position
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
