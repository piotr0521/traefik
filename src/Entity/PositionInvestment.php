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
use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Groshy\Message\Command\PositionInvestment\CreatePositionInvestmentCommand;
use Groshy\Message\Command\PositionInvestment\DeletePositionInvestmentCommand;
use Groshy\Message\Command\PositionInvestment\UpdatePositionInvestmentCommand;
use Groshy\Message\Dto\PositionInvestment\CreatePositionInvestmentDto;
use Groshy\Message\Dto\PositionInvestment\UpdatePositionInvestmentDto;
use Groshy\Model\MoneyAwareTrait;
use Groshy\Presentation\Api\Doctrine\Orm\Filter\AssetTypeFilter;
use Groshy\Presentation\Api\Dto\PositionInvestment\ApiCreatePositionInvestmentDto;
use Groshy\Presentation\Api\Dto\PositionInvestment\ApiUpdatePositionInvestmentDto;
use Groshy\Presentation\Api\State\ResourceStateProcessor;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(uriTemplate: '/investments/{id}.{_format}', normalizationContext: ['groups' => ['position:item:read'], 'swagger_definition_name' => 'Item Read']),
        new Patch(uriTemplate: '/investments/{id}.{_format}', input: ['class' => ApiUpdatePositionInvestmentDto::class, 'transform' => ['dto' => UpdatePositionInvestmentDto::class, 'command' => UpdatePositionInvestmentCommand::class]], processor: ResourceStateProcessor::class),
        new Delete(uriTemplate: '/investments/{id}.{_format}', input: ['transform' => ['command' => DeletePositionInvestmentCommand::class]], processor: ResourceStateProcessor::class),
        new Post(uriTemplate: '/investments.{_format}', input: ['class' => ApiCreatePositionInvestmentDto::class, 'transform' => ['dto' => CreatePositionInvestmentDto::class, 'command' => CreatePositionInvestmentCommand::class]], processor: ResourceStateProcessor::class),
        new GetCollection(uriTemplate: '/investments.{_format}', normalizationContext: ['groups' => ['position:collection:read'], 'swagger_definition_name' => 'Collection Read']), ],
    routePrefix: '/position',
    paginationClientItemsPerPage: true
)]
#[Entity]
#[ApiFilter(filterClass: OrderFilter::class, properties: ['startDate' => 'DESC'], arguments: ['orderParameterName' => 'order'])]
#[ApiFilter(filterClass: AssetTypeFilter::class, properties: ['path' => 'asset.assetType'])]
class PositionInvestment extends Position
{
    use MoneyAwareTrait;

    #[Column(type: 'object')]
    protected PositionInvestmentData $data;

    public function __construct()
    {
        $this->data = new PositionInvestmentData();
        parent::__construct();
    }

    #[Groups(['position:item:read', 'position:collection:read'])]
    public function getName(): ?string
    {
        return $this->asset ? $this->asset->getName() : '';
    }

    public function getData(): PositionInvestmentData
    {
        return $this->data;
    }

    public function setData(PositionInvestmentData $data): void
    {
        $this->data = $data;
    }

    #[Groups(['position:item:read', 'position:collection:read'])]
    public function getCapitalCommitment(): ?array
    {
        return $this->formatMoney($this->data->getCapitalCommitment());
    }

    #[Groups(['position:item:read'])]
    public function isDirect(): bool
    {
        return $this->data->isDirect();
    }

    #[Groups(['position:item:read', 'position:collection:read'])]
    public function isFullyCommitted(): bool
    {
        if (is_null($this->getLastValue()) || is_null($this->getLastValue()->getAmount()) || is_null($this->getCapitalCommitment()) || $this->isCompleted()) {
            return true;
        }

        return $this->getLastValue()->getAmount()->greaterThanOrEqual($this->getContributions());
    }

    public function getStatsData(DateTime $from, DateTime $to): PositionStatsData
    {
        return parent::getStatsData($from, $to)->merge(new PositionStatsData(capitalCommitted: $this->getContributions()));
    }
}
