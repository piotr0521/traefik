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
use Groshy\Message\Command\PositionCreditCard\CreatePositionCreditCardCommand;
use Groshy\Message\Command\PositionCreditCard\DeletePositionCreditCardCommand;
use Groshy\Message\Command\PositionCreditCard\UpdatePositionCreditCardCommand;
use Groshy\Message\Dto\PositionCreditCard\CreatePositionCreditCardDto;
use Groshy\Message\Dto\PositionCreditCard\UpdatePositionCreditCardDto;
use Groshy\Model\MoneyAwareTrait;
use Groshy\Presentation\Api\Doctrine\Orm\Filter\AssetTypeFilter;
use Groshy\Presentation\Api\Dto\PositionCreditCard\ApiCreatePositionCreditCardDto;
use Groshy\Presentation\Api\Dto\PositionCreditCard\ApiUpdatePositionCreditCardDto;
use Groshy\Presentation\Api\State\ResourceStateProcessor;
use Money\Money;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ApiResource(
    operations: [
        new Get(uriTemplate: '/credit_cards/{id}.{_format}', normalizationContext: ['groups' => ['position:item:read'], 'swagger_definition_name' => 'Item Read']),
        new Patch(uriTemplate: '/credit_cards/{id}.{_format}', input: ['class' => ApiUpdatePositionCreditCardDto::class, 'transform' => ['dto' => UpdatePositionCreditCardDto::class, 'command' => UpdatePositionCreditCardCommand::class]], processor: ResourceStateProcessor::class),
        new Delete(uriTemplate: '/credit_cards/{id}.{_format}', input: ['transform' => ['command' => DeletePositionCreditCardCommand::class]], processor: ResourceStateProcessor::class),
        new Post(uriTemplate: '/credit_cards.{_format}', input: ['class' => ApiCreatePositionCreditCardDto::class, 'transform' => ['dto' => CreatePositionCreditCardDto::class, 'command' => CreatePositionCreditCardCommand::class]], processor: ResourceStateProcessor::class),
        new GetCollection(uriTemplate: '/credit_cards.{_format}', normalizationContext: ['groups' => ['position:collection:read'], 'swagger_definition_name' => 'Collection Read']), ],
    routePrefix: '/position'
)]
#[Entity]
#[ApiFilter(filterClass: OrderFilter::class, properties: ['startDate' => 'DESC'], arguments: ['orderParameterName' => 'order'])]
#[ApiFilter(filterClass: AssetTypeFilter::class, properties: ['path' => 'asset.assetType'])]
class PositionCreditCard extends Position
{
    use MoneyAwareTrait;
    #[Column(type: 'object')]
    protected PositionCreditCardData $data;

    public function __construct()
    {
        $this->data = new PositionCreditCardData();
        parent::__construct();
    }

    public function getData(): PositionCreditCardData
    {
        return $this->data;
    }

    public function setData(PositionCreditCardData $data): void
    {
        $this->data = $data;
    }

    #[Groups(['position:item:read'])]
    public function getUtilization(): ?float
    {
        if (is_null($this->data->getCardLimit())) {
            return null;
        }
        if (is_null($this->getLastValue())) {
            return null;
        }

        return $this->calculateRatio($this->getLastValue()->getAmount(), $this->data->getCardLimit());
    }

    public function getCardLimit(): ?Money
    {
        return $this->data->getCardLimit();
    }

    #[Groups(['position:item:read'])]
    #[SerializedName('cardLimit')]
    public function getCardLimitStruct(): ?array
    {
        return $this->formatMoney($this->data->getCardLimit());
    }
}
